<?php

namespace App\Http\Controllers;

use App\Models\SmokeExtractionCentralType;
use Illuminate\Http\Request;

class SmokeExtractionCentralTypeController extends Controller
{
    public function index()
    {
        $types = SmokeExtractionCentralType::orderBy('name')->get();
        return view('settings.smoke-extraction-central-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.smoke-extraction-central-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:smoke_extraction_central_types',
            'description' => 'nullable|string',
        ]);

        SmokeExtractionCentralType::create($validated);

        return redirect()->route('settings.smoke-extraction-central-types.index')
            ->with('status', 'Typ centrali został dodany.');
    }

    public function edit(SmokeExtractionCentralType $smokeExtractionCentralType)
    {
        return view('settings.smoke-extraction-central-types.edit', compact('smokeExtractionCentralType'));
    }

    public function update(Request $request, SmokeExtractionCentralType $smokeExtractionCentralType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:smoke_extraction_central_types,name,' . $smokeExtractionCentralType->id,
            'description' => 'nullable|string',
        ]);

        $smokeExtractionCentralType->update($validated);

        return redirect()->route('settings.smoke-extraction-central-types.index')
            ->with('status', 'Typ centrali został zaktualizowany.');
    }

    public function destroy(SmokeExtractionCentralType $smokeExtractionCentralType)
    {
        // Sprawdź czy są powiązania
        if ($smokeExtractionCentralType->smokeExtractionSystems()->count() > 0) {
            return back()->withErrors(['error' => 'Nie można usunąć typu, który jest przypisany do systemów.']);
        }

        $smokeExtractionCentralType->delete();

        return redirect()->route('settings.smoke-extraction-central-types.index')
            ->with('status', 'Typ centrali został usunięty.');
    }
}
