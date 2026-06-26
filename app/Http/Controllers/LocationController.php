<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        return view('locations.map');
    }

    public function show(string $slug): View
    {
        $location = Location::published()
            ->with('categories:id,name,slug')
            ->where('slug', $slug)
            ->firstOrFail();

        dispatch(fn () => $location->incrementHit())->afterResponse();

        return view('locations.show', compact('location'));
    }
}
