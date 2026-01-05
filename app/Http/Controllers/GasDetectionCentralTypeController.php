<?php

namespace App\Http\Controllers;

use App\Models\GasDetectionCentralType;
use Illuminate\Http\Request;

class GasDetectionCentralTypeController extends Controller
{
    public function index()
    {
        $types = GasDetectionCentralType::orderBy('name')->get();
        return view('settings.gas-detection.central-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.gas-detection.central-types.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:gas_detection_central_types,name|max:255']);
        GasDetectionCentralType::create($request->all());
        return redirect()->route('settings.gas-detection-central-types.index')->with('success', 'Typ dodany.');
    }

    public function edit(GasDetectionCentralType $gasDetectionCentralType)
    {
        return view('settings.gas-detection.central-types.edit', compact('gasDetectionCentralType'));
    }

    public function update(Request $request, GasDetectionCentralType $gasDetectionCentralType)
    {
        $request->validate(['name' => 'required|string|max:255|unique:gas_detection_central_types,name,' . $gasDetectionCentralType->id]);
        $gasDetectionCentralType->update($request->all());
        return redirect()->route('settings.gas-detection-central-types.index')->with('success', 'Typ zaktualizowany.');
    }

    public function destroy(GasDetectionCentralType $gasDetectionCentralType)
    {
        $gasDetectionCentralType->delete();
        return redirect()->route('settings.gas-detection-central-types.index')->with('success', 'Typ usunięty.');
    }
}
