<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuisnessStatuses;
use Illuminate\Http\Request;

class BuisnessStatusesController extends Controller
{
    public function index()
    {
        $statuses = BuisnessStatuses::forAccount(auth()->user()->account_id)
            ->latest()
            ->paginate(10); // ← Change this

        return view('admin.buisness_statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.buisness_statuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:buisness_statuses,name,NULL,id,account_id,' . auth()->user()->account_id,
            'active' => 'sometimes|boolean'
        ]);

        BuisnessStatuses::create([
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
            'account_id' => auth()->user()->account_id
        ]);

        return redirect()->route('admin.buisness-statuses.index')
            ->with('success', 'Buisness status created successfully.');
    }

    public function edit($id)
    {
        $status = BuisnessStatuses::forAccount(auth()->user()->account_id)->findOrFail($id);
        return view('admin.buisness_statuses.edit', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $status = BuisnessStatuses::forAccount(auth()->user()->account_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:buisness_statuses,name,' . $id . ',id,account_id,' . auth()->user()->account_id,
            'active' => 'sometimes|boolean'
        ]);

        $status->update([
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return redirect()->route('admin.buisness-statuses.index')
            ->with('success', 'Buisness status updated successfully.');
    }

    public function destroy($id)
    {
        $status = BuisnessStatuses::forAccount(auth()->user()->account_id)->findOrFail($id);
        $status->delete();

        return redirect()->route('admin.buisness-statuses.index')
            ->with('success', 'Buisness status deleted.');
    }
}
