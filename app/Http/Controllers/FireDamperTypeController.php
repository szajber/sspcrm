<?php

namespace App\Http\Controllers;

use App\Models\FireDamperType;
use Illuminate\Http\Request;

class FireDamperTypeController extends Controller
{
    public function index()
    {
        $types = FireDamperType::orderBy('name')->get();
        return view('settings.fire-damper-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.fire-damper-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fire_damper_types',
            'description' => 'nullable|string',
        ]);

        FireDamperType::create($validated);

        return redirect()->route('settings.fire-damper-types.index')
            ->with('status', 'Typ klapy został dodany.');
    }

    public function edit(FireDamperType $fireDamperType)
    {
        return view('settings.fire-damper-types.edit', compact('fireDamperType'));
    }

    public function update(Request $request, FireDamperType $fireDamperType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fire_damper_types,name,' . $fireDamperType->id,
            'description' => 'nullable|string',
        ]);

        $fireDamperType->update($validated);

        return redirect()->route('settings.fire-damper-types.index')
            ->with('status', 'Typ klapy został zaktualizowany.');
    }

    public function destroy(FireDamperType $fireDamperType)
    {
        // Sprawdź czy są powiązania
        if ($fireDamperType->fireDampers()->count() > 0) {
            return back()->withErrors(['error' => 'Nie można usunąć typu, który jest przypisany do klap.']);
        }

        $fireDamperType->delete();

        return redirect()->route('settings.fire-damper-types.index')
            ->with('status', 'Typ klapy został usunięty.');
    }
}
