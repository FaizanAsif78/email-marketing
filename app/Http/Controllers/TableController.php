<?php

namespace App\Http\Controllers;

class TableController extends Controller
{
    public function index()
    {
        return view('tables.basic');
    }
}
