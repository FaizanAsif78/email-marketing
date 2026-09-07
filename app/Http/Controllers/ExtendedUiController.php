<?php

namespace App\Http\Controllers;

class ExtendedUiController extends Controller
{
    public function perfectScrollbar()
    {
        return view('extended-ui.perfect-scrollbar');
    }

    public function textDivider()
    {
        return view('extended-ui.text-divider');
    }
}
