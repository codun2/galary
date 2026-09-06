<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Http\Request;

/** الموقع العام — بلا تسجيل دخول */
class SiteController extends Controller
{
    public function vehicles(Request $request)
    {
        $q = Vehicle::with('cover')->whereIn('status', ['available', 'reserved']);

        if ($s = $request->input('search')) {
            $q->where(fn ($w) => $w->where('make', 'like', "%$s%")->orWhere('model', 'like', "%$s%"));
        }
        foreach (['make', 'model', 'fuel', 'transmission'] as $f) {
            if ($request->filled($f)) $q->where($f, $request->input($f));
        }
        if ($request->filled('year_min')) $q->where('year', '>=', $request->input('year_min'));
        if ($request->filled('price_max')) $q->where('price', '<=', $request->input('price_max'));
        if ($request->filled('mileage_max')) $q->where('mileage', '<=', $request->input('mileage_max'));

        match ($request->input('sort', 'latest')) {
            'price_asc' => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            default => $q->orderByDesc('is_featured')->orderByDesc('created_at'),
        };

        return response()->json($q->paginate(12));
    }

    public function featured()
    {
        return response()->json(
            Vehicle::with('cover')->where('status', 'available')
                ->orderByDesc('is_featured')->orderByDesc('created_at')
                ->limit(6)->get()
        );
    }

    public function show(Vehicle $vehicle)
    {
        abort_if($vehicle->status === 'hidden', 404);
        $vehicle->increment('views_count');

        return response()->json($vehicle->load('media'));
    }

    public function settings()
    {
        return response()->json(Setting::all_map());
    }

    /** قوائم البحث: الماركات وموديلاتها وسنواتها (من المخزون الفعلي) */
    public function facets()
    {
        $rows = Vehicle::whereIn('status', ['available', 'reserved'])
            ->get(['make', 'model', 'year']);

        return response()->json([
            'makes' => $rows->groupBy('make')->map(fn ($g, $make) => [
                'make' => $make,
                'models' => $g->pluck('model')->unique()->values(),
            ])->values(),
            'years' => $rows->pluck('year')->unique()->sortDesc()->values(),
            'total' => $rows->count(),
        ]);
    }

    /** استفسار زبون — يُحفظ للوحة المعرض */
    public function lead(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'subject' => 'nullable|string|max:150',
            'message' => 'nullable|string|max:1000',
        ]);

        Lead::create($data);

        return response()->json(['message' => 'وصلنا استفسارك — سنتواصل معك قريباً'], 201);
    }
}
