<?php

namespace App\Http\Controllers;

class AccountSettingsController extends Controller
{
    public function account()
    {
        return view('pages.account-settings.account');
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
