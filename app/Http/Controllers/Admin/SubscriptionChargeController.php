<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\SubscriptionCharge;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionChargeController extends Controller
{
    // List all subscription charges
    public function index()
    {
        if (request()->ajax()) {
            $charges = SubscriptionCharge::with('categories')
                ->where('account_id', auth()->user()->account_id);
    
            return DataTables::of($charges)
                ->addIndexColumn()
                ->addColumn('categories', function ($charge) {
                    return $charge->categories->map(function ($category) {
                        return "<span class='badge badge-primary'>{$category->name}</span>";
                    })->implode(' ');
                })
                ->addColumn('discounts', function ($charge) {
                    return $charge->categories->map(function ($category) {
                        return "<span class='badge badge-info'>" . ($category->pivot->offered_discount * 100) . "%</span>";
                    })->implode(' ');
                })
                ->addColumn('actions', function ($charge) {
                    if(\Gate::allows('edit_remove_subscription_charges')){
                        return '
                            <a href="#" class="btn btn-sm btn-success edit-button" data-id="' . $charge->id . '">Edit</a>
                            <form action="' . route('admin.subscription-charges.destroy', $charge->id) . '" method="POST" style="display: inline;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-danger delete-button">Delete</button>
                            </form>
                        ';
                    }else{
                        return '-';
                    }
                })
                ->rawColumns(['categories', 'discounts', 'actions'])
                ->make(true);
        }
        $categories = Category::pluck('name', 'id');
        $charges = SubscriptionCharge::where('account_id', auth()->user()->account_id)->get();
        return view('admin.subscription_charges.index', compact('charges','categories'));
    }

    // Show the form to create a new subscription charge
    public function create()
    {
        $categories = Category::pluck('name', 'id'); // Fetch categories as key-value pairs
        $categories->prepend('All', ''); // Add "All" as the first option

        return view('admin.subscription_charges.create', compact('categories'));
    }

    // Store a new subscription charge
    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'array', 'min:1'],
            'category_id.*' => ['required', 'exists:categories,id'],
            'offered_discount' => ['required', 'array', 'min:1'],
            'offered_discount.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'banner' => ['required'],
        ]);

        // Create the subscription charge
        $charge = SubscriptionCharge::create([
            'amount' => $validated['amount'],
            'banner' => $validated['banner'],
            'account_id' => auth()->user()->account_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Attach categories with offered discounts
        foreach ($validated['category_id'] as $index => $categoryId) {
            $charge->categories()->attach($categoryId, [
                'offered_discount' => $validated['offered_discount'][$index] / 100,
            ]);
        }

        return redirect()->route('admin.subscription-charges.index')
            ->with('success', 'Subscription charge created successfully.');
    }

    // Show the form to edit an existing subscription charge
    public function edit($id)
    {
        $charge = SubscriptionCharge::with('categories')
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $categories = Category::pluck('name', 'id');

        // return view('admin.subscription_charges.edit', compact('charge', 'categories'));
        return response()->json([
            'charge' => $charge,
        ]);
    }

    // Update an existing subscription charge
    public function update(Request $request, $id)
    {
        // Validate request data
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'array', 'min:1'],
            'category_id.*' => ['required', 'exists:categories,id'],
            'offered_discount' => ['required', 'array', 'min:1'],
            'offered_discount.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'banner' => ['required'],
        ]);

        // Find and update the subscription charge
        $charge = SubscriptionCharge::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $charge->update([
            'amount' => $validated['amount'],
            'banner' => $validated['banner'],
            'updated_at' => Carbon::now(),
        ]);

        // Sync categories with offered discounts
        $syncData = [];
        foreach ($validated['category_id'] as $index => $categoryId) {
            $syncData[$categoryId] = [
                'offered_discount' => $validated['offered_discount'][$index] / 100,
            ];
        }
        $charge->categories()->sync($syncData);

        return redirect()->route('admin.subscription-charges.index')
            ->with('success', 'Subscription charge updated successfully.');
    }

    // Delete a subscription charge
    public function destroy($id)
    {
        $charge = SubscriptionCharge::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $charge->categories()->detach(); 
        $charge->delete();

        return redirect()->route('admin.subscription-charges.index')
            ->with('success', 'Subscription charge deleted successfully.');
    }
}
