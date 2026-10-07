<?php

namespace App\Http\Controllers;

use App\Models\TelkomselSolution;

class PublicController extends Controller
{
    public function landing()
    {
        return view('public.landing', [
            'solutions' => TelkomselSolution::orderBy('position')->get(),
        ]);
    }

    public function howItWorks()
    {
        $view = request()->routeIs('app.how-it-works') ? 'app.how-it-works' : 'public.how-it-works';

        return view($view, ['solutions' => TelkomselSolution::orderBy('position')->get()->keyBy('key')]);
    }
}
