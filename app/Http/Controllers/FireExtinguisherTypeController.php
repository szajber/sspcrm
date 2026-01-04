<?php

namespace App\Http\Controllers;

use App\Models\FireExtinguisherType;
use Illuminate\Http\Request;

class FireExtinguisherTypeController extends Controller
{
    public function index()
    {
        $types = FireExtinguisherType::orderBy('name')->get();
        return view('settings.fire-extinguisher-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.fire-extinguisher-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fire_extinguisher_types',
            'description' => 'nullable|string',
        ]);

        FireExtinguisherType::create($validated);

        return redirect()->route('settings.fire-extinguisher-types.index')
            ->with('status', 'Typ gaśnicy został dodany.');
    }

    public function edit(FireExtinguisherType $fireExtinguisherType)
    {
        return view('settings.fire-extinguisher-types.edit', compact('fireExtinguisherType'));
    }

    public function update(Request $request, FireExtinguisherType $fireExtinguisherType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fire_extinguisher_types,name,' . $fireExtinguisherType->id,
            'description' => 'nullable|string',
        ]);

        $fireExtinguisherType->update($validated);

        return redirect()->route('settings.fire-extinguisher-types.index')
            ->with('status', 'Typ gaśnicy został zaktualizowany.');
    }

    public function destroy(FireExtinguisherType $fireExtinguisherType)
    {
        // Sprawdź czy są powiązania
        if ($fireExtinguisherType->fireExtinguishers()->count() > 0) {
            return back()->withErrors(['error' => 'Nie można usunąć typu, który jest przypisany do gaśnic.']);
        }

        $fireExtinguisherType->delete();

        return redirect()->route('settings.fire-extinguisher-types.index')
            ->with('status', 'Typ gaśnicy został usunięty.');
    }
}
