<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    public function account()
    {
        return view('dashboard.account-settings.account');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => ! empty($data['password'])
                ? Hash::make($data['password'])
                : $user->password,
        ]);

        return redirect()->route('account-settings.account')->with('success', 'Profile updated successfully.');
    }

    public function notifications()
    {
        return view('pages.account-settings.notifications');
    }

    public function connections()
    {
        return view('pages.account-settings.connections');
    }
}
