<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleMedia;
use Illuminate\Http\Request;

/** لوحة المعرض — تتطلب تسجيل دخول */
class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'vehicles' => Vehicle::where('status', '!=', 'hidden')->count(),
            'available' => Vehicle::where('status', 'available')->count(),
            'views' => (int) Vehicle::sum('views_count'),
            'new_leads' => Lead::where('status', 'new')->count(),
        ]);
    }

    // ---- السيارات ----
    public function vehicles()
    {
        return response()->json(Vehicle::with('cover')->latest()->get());
    }

    public function vehicle(Vehicle $vehicle)
    {
        return response()->json($vehicle->load('media'));
    }

    public function store(Request $request)
    {
        $vehicle = Vehicle::create($this->validated($request));
        $this->syncMedia($vehicle, $request->input('media', []));

        return response()->json($vehicle->load('media'), 201);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $vehicle->update($this->validated($request));
        if ($request->has('media')) $this->syncMedia($vehicle, $request->input('media', []));

        return response()->json($vehicle->load('media'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->media()->delete();
        $vehicle->delete();

        return response()->json(['message' => 'تم حذف السيارة']);
    }

    // ---- الاستفسارات ----
    public function leads(Request $request)
    {
        $q = Lead::with('vehicle:id,make,model,year')->latest();
        if ($request->filled('status')) $q->where('status', $request->input('status'));

        return response()->json($q->get());
    }

    public function leadStatus(Request $request, Lead $lead)
    {
        $data = $request->validate(['status' => 'required|in:new,replied,closed']);
        $lead->update($data);

        return response()->json(['message' => 'تم التحديث', 'lead' => $lead]);
    }

    // ---- الإعدادات ----
    public function getSettings()
    {
        return response()->json(Setting::all_map());
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'showroom_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'hours' => 'nullable|string|max:120',
            'about' => 'nullable|string|max:1000',
        ]);

        foreach ($data as $k => $v) Setting::put($k, $v);

        return response()->json(['message' => 'تم حفظ الإعدادات']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'make' => 'required|string|max:60',
            'model' => 'required|string|max:80',
            'year' => 'required|integer|min:1980|max:2100',
            'mileage' => 'required|integer|min:0',
            'fuel' => 'required|in:petrol,diesel,hybrid,electric',
            'transmission' => 'required|in:automatic,manual',
            'color' => 'nullable|string|max:40',
            'drivetrain' => 'nullable|string|max:30',
            'price' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:60',
            'status' => 'required|in:available,reserved,sold,hidden',
            'is_featured' => 'boolean',
        ]);
    }

    private function syncMedia(Vehicle $vehicle, array $paths): void
    {
        $vehicle->media()->delete();
        foreach (array_values($paths) as $i => $path) {
            VehicleMedia::create([
                'vehicle_id' => $vehicle->id,
                'file_path' => $path,
                'is_cover' => $i === 0,
                'sort_order' => $i,
            ]);
        }
    }
}
