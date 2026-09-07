<?php

namespace App\Http\Controllers;

class UiController extends Controller
{
    public function accordion()
    {
        return view('ui.accordion');
    }

    public function alerts()
    {
        return view('ui.alerts');
    }

    public function badges()
    {
        return view('ui.badges');
    }

    public function buttons()
    {
        return view('ui.buttons');
    }

    public function carousel()
    {
        return view('ui.carousel');
    }

    public function collapse()
    {
        return view('ui.collapse');
    }

    public function dropdowns()
    {
        return view('ui.dropdowns');
    }

    public function footer()
    {
        return view('ui.footer');
    }

    public function listGroups()
    {
        return view('ui.list-groups');
    }

    public function modals()
    {
        return view('ui.modals');
    }

    public function navbar()
    {
        return view('ui.navbar');
    }

    public function offcanvas()
    {
        return view('ui.offcanvas');
    }

    public function paginationBreadcrumbs()
    {
        return view('ui.pagination-breadcrumbs');
    }

    public function progress()
    {
        return view('ui.progress');
    }

    public function spinners()
    {
        return view('ui.spinners');
    }

    public function tabsPills()
    {
        return view('ui.tabs-pills');
    }

    public function toasts()
    {
        return view('ui.toasts');
    }

    public function tooltipsPopovers()
    {
        return view('ui.tooltips-popovers');
    }

    public function typography()
    {
        return view('ui.typography');
    }
}
