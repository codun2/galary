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

    // ---- الإعدادات ونصوص الموقع ----
    /** كل المفاتيح المسموح للمعرض التحكم بها (معلومات + نصوص الموقع) */
    public const SETTING_KEYS = [
        // معلومات المعرض
        'showroom_name', 'phone', 'whatsapp', 'address', 'hours', 'about',
        // الواجهة الرئيسية
        'hero_badge', 'hero_title', 'hero_subtitle', 'search_title',
        'featured_title', 'featured_subtitle',
        // قسم لماذا نحن (4 بطاقات)
        'why_title',
        'why1_title', 'why1_text', 'why2_title', 'why2_text',
        'why3_title', 'why3_text', 'why4_title', 'why4_text',
        // دعوة أسفل الصفحة
        'cta_title', 'cta_text', 'cta_button',
        // الفوتر
        'footer_text',
    ];

    public function getSettings()
    {
        return response()->json(Setting::all_map());
    }

    public function saveSettings(Request $request)
    {
        $rules = [];
        foreach (self::SETTING_KEYS as $k) $rules[$k] = 'nullable|string|max:1000';
        $data = $request->validate($rules);

        foreach ($data as $k => $v) {
            if ($request->has($k)) Setting::put($k, $v);
        }

        return response()->json(['message' => 'تم حفظ الإعدادات']);
    }

    // ---- الأنواع والموديلات ----
    public function catalog()
    {
        return response()->json(\App\Models\Make::with('models')->orderBy('name')->get());
    }

    public function storeMake(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60|unique:makes,name']);
        $make = \App\Models\Make::create($data);

        return response()->json($make->load('models'), 201);
    }

    public function deleteMake(\App\Models\Make $make)
    {
        $make->delete();

        return response()->json(['message' => 'تم حذف النوع وموديلاته']);
    }

    public function storeModel(Request $request, \App\Models\Make $make)
    {
        $data = $request->validate(['name' => 'required|string|max:80']);
        $model = $make->models()->firstOrCreate(['name' => $data['name']]);

        return response()->json($model, 201);
    }

    public function deleteModel(\App\Models\CarModel $carModel)
    {
        $carModel->delete();

        return response()->json(['message' => 'تم حذف الموديل']);
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
