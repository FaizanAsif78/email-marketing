<?php

namespace App\Http\Controllers;

class FormController extends Controller
{
    public function basicInputs()
    {
        return view('forms.basic-inputs');
    }

    public function inputGroups()
    {
        return view('forms.input-groups');
    }

    public function layoutsVertical()
    {
        return view('forms.layouts-vertical');
    }

    public function layoutsHorizontal()
    {
        return view('forms.layouts-horizontal');
    }
}
