<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Lista strutture per la mappa pubblica.
     * GET /api/v1/locations
     * Params: city, province, category_id[], search, featured, lat, lng, radius_km, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'api.locations.' . md5(serialize($request->all()));

        $locations = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Location::published()
                ->with('categories:id,name,slug')
                ->select([
                    'id', 'name', 'slug', 'address', 'city', 'province',
                    'phone', 'lat', 'lng', 'intro', 'convention_type',
                    'featured', 'hits', 'zoom',
                ]);

            if ($request->filled('city')) {
                $query->inCity($request->city);
            }

            if ($request->filled('province')) {
                $query->inProvince($request->province);
            }

            if ($request->filled('category_id')) {
                $query->withCategory($request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            if ($request->boolean('featured')) {
                $query->featured();
            }

            if ($request->filled(['lat', 'lng'])) {
                $query->nearby(
                    lat: (float) $request->lat,
                    lng: (float) $request->lng,
                    radiusKm: (float) ($request->radius_km ?? 30)
                );
            }

            $perPage = min((int) ($request->per_page ?? 50), 500);

            return $query
                ->orderByDesc('featured')
                ->orderBy('name')
                ->paginate($perPage)
                ->toArray();
        });

        return response()->json($locations);
    }

    /**
     * Dettaglio singola struttura.
     * GET /api/v1/locations/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $location = Location::published()
            ->with('categories:id,name,slug')
            ->where('slug', $slug)
            ->firstOrFail();

        dispatch(fn () => $location->incrementHit())->afterResponse();

        return response()->json($location);
    }

    /**
     * Lista categorie per i filtri.
     * GET /api/v1/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('api.categories', now()->addMinutes(30), function () {
            // HAVING su subquery non è supportato da SQLite: filtriamo in PHP
            return Category::select('id', 'name', 'slug')
                ->where('published', true)
                ->withCount(['locations' => fn ($q) => $q->published()])
                ->orderBy('name')
                ->get()
                ->filter(fn ($cat) => $cat->locations_count > 0)
                ->values()
                ->toArray(); // plain array per evitare problemi di deserializzazione
        });

        return response()->json($categories);
    }

    /**
     * Lista province disponibili per i filtri.
     * GET /api/v1/provinces
     */
    public function provinces(): JsonResponse
    {
        $provinces = Cache::remember('api.provinces', now()->addMinutes(30), function () {
            return Location::published()
                ->distinct()
                ->orderBy('province')
                ->pluck('province')
                ->filter()
                ->values()
                ->toArray(); // plain array per evitare __PHP_Incomplete_Class in cache
        });

        return response()->json($provinces);
    }
}
