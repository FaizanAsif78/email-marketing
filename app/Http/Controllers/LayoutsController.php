<?php

namespace App\Http\Controllers;

class LayoutsController extends Controller
{
    public function withoutMenu()
    {
        return view('layouts.without-menu');
    }

    public function withoutNavbar()
    {
        return view('layouts.without-navbar');
    }

    public function container()
    {
        return view('layouts.container');
    }

    public function fluid()
    {
        return view('layouts.fluid');
    }

    public function blank()
    {
        return view('layouts.blank');
    }
}
