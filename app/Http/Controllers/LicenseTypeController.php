<?php
namespace App\Http\Controllers;
use App\Models\LicenseType;
use Illuminate\Http\Request;
class LicenseTypeController extends Controller {
    public function index() {
        $licenses = LicenseType::orderBy('category')->orderBy('name')->get()->groupBy('category');
        return view('licenses.index', compact('licenses'));
    }
    public function create() {
        $categories = LicenseType::distinct()->orderBy('category')->pluck('category');
        return view('licenses.create', compact('categories'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'name'          => 'required|string|max:100|unique:license_types',
            'category'      => 'required|string|max:50',
            'description'   => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
        ]);
        LicenseType::create($data);
        return redirect()->route('licenses.index')->with('success', "Licenza \"{$data['name']}\" aggiunta!");
    }
    public function edit(LicenseType $license) {
        $categories = LicenseType::distinct()->orderBy('category')->pluck('category');
        return view('licenses.edit', compact('license', 'categories'));
    }
    public function update(Request $request, LicenseType $license) {
        $data = $request->validate([
            'name'          => 'required|string|max:100|unique:license_types,name,' . $license->id,
            'category'      => 'required|string|max:50',
            'description'   => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'is_active'     => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $license->update($data);
        return redirect()->route('licenses.index')->with('success', 'Licenza aggiornata!');
    }
    public function destroy(LicenseType $license) {
        $license->update(['is_active' => false]);
        return redirect()->route('licenses.index')->with('info', "Licenza \"{$license->name}\" disattivata.");
    }
    public function priceApi(LicenseType $license) {
        return response()->json(['price_monthly' => $license->price_monthly, 'price_yearly' => $license->price_yearly]);
    }
}
