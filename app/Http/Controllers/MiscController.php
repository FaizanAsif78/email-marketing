<?php

namespace App\Http\Controllers;

class MiscController extends Controller
{
    public function error()
    {
        return view('pages.misc.error');
    }

    public function maintenance()
    {
        return view('pages.misc.under-maintenance');
    }
}
