<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;

class UnsubscribeController extends Controller
{
    public function unsubscribeform()
    {
        return view('unsubscription.index');
    }
    public function unsubscribe(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->un_subscribe = 1;
            $user->save();
            return back()->with('success', 'You have successfully unsubscribed.');
        }

        return back()->with('error', 'Email not found.');
    }
}
