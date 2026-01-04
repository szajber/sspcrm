<?php

namespace App\Http\Controllers;

use App\Models\DoorResistanceClass;
use Illuminate\Http\Request;

class DoorResistanceClassController extends Controller
{
    public function index()
    {
        $classes = DoorResistanceClass::orderBy('name')->get();
        return view('settings.door-resistance-classes.index', compact('classes'));
    }

    public function create()
    {
        return view('settings.door-resistance-classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:door_resistance_classes',
            'description' => 'nullable|string',
        ]);

        DoorResistanceClass::create($validated);

        return redirect()->route('settings.door-resistance-classes.index')
            ->with('status', 'Klasa odporności została dodana.');
    }

    public function edit(DoorResistanceClass $doorResistanceClass)
    {
        return view('settings.door-resistance-classes.edit', compact('doorResistanceClass'));
    }

    public function update(Request $request, DoorResistanceClass $doorResistanceClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:door_resistance_classes,name,' . $doorResistanceClass->id,
            'description' => 'nullable|string',
        ]);

        $doorResistanceClass->update($validated);

        return redirect()->route('settings.door-resistance-classes.index')
            ->with('status', 'Klasa odporności została zaktualizowana.');
    }

    public function destroy(DoorResistanceClass $doorResistanceClass)
    {
        // Sprawdź czy są powiązania
        if ($doorResistanceClass->doors()->count() > 0) {
            return back()->withErrors(['error' => 'Nie można usunąć klasy, która jest przypisana do drzwi.']);
        }

        $doorResistanceClass->delete();

        return redirect()->route('settings.door-resistance-classes.index')
            ->with('status', 'Klasa odporności została usunięta.');
    }
}
