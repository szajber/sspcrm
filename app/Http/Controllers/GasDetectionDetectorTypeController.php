<?php

namespace App\Http\Controllers;

use App\Models\GasDetectionDetectorType;
use Illuminate\Http\Request;

class GasDetectionDetectorTypeController extends Controller
{
    public function index()
    {
        $types = GasDetectionDetectorType::orderBy('name')->get();
        return view('settings.gas-detection.detector-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.gas-detection.detector-types.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:gas_detection_detector_types,name|max:255']);
        GasDetectionDetectorType::create($request->all());
        return redirect()->route('settings.gas-detection-detector-types.index')->with('success', 'Typ dodany.');
    }

    public function edit(GasDetectionDetectorType $gasDetectionDetectorType)
    {
        return view('settings.gas-detection.detector-types.edit', compact('gasDetectionDetectorType'));
    }

    public function update(Request $request, GasDetectionDetectorType $gasDetectionDetectorType)
    {
        $request->validate(['name' => 'required|string|max:255|unique:gas_detection_detector_types,name,' . $gasDetectionDetectorType->id]);
        $gasDetectionDetectorType->update($request->all());
        return redirect()->route('settings.gas-detection-detector-types.index')->with('success', 'Typ zaktualizowany.');
    }

    public function destroy(GasDetectionDetectorType $gasDetectionDetectorType)
    {
        $gasDetectionDetectorType->delete();
        return redirect()->route('settings.gas-detection-detector-types.index')->with('success', 'Typ usunięty.');
    }
}
