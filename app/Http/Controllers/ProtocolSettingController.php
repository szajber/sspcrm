<?php

namespace App\Http\Controllers;

use App\Models\ProtocolTemplate;
use App\Models\System;
use Illuminate\Http\Request;

class ProtocolSettingController extends Controller
{
    public function index()
    {
        $systems = System::orderBy('name')->get();
        return view('settings.protocols.index', compact('systems'));
    }

    public function edit(System $system)
    {
        $templates = $system->protocolTemplates()->orderBy('name')->get();
        return view('settings.protocols.edit', compact('system', 'templates'));
    }

    public function createTemplate(System $system)
    {
        return view('settings.protocols.templates.create', compact('system'));
    }

    public function storeTemplate(Request $request, System $system)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            $system->protocolTemplates()->update(['is_default' => false]);
        }

        $system->protocolTemplates()->create($validated);

        return redirect()->route('settings.protocols.edit', $system)->with('status', 'Szablon został dodany.');
    }

    public function editTemplate(System $system, ProtocolTemplate $template)
    {
        return view('settings.protocols.templates.edit', compact('system', 'template'));
    }

    public function updateTemplate(Request $request, System $system, ProtocolTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            $system->protocolTemplates()->where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->route('settings.protocols.edit', $system)->with('status', 'Szablon został zaktualizowany.');
    }

    public function destroyTemplate(System $system, ProtocolTemplate $template)
    {
        $template->delete();
        return redirect()->route('settings.protocols.edit', $system)->with('status', 'Szablon został usunięty.');
    }

    public function setDefault(Request $request, System $system, ProtocolTemplate $template)
    {
        $system->protocolTemplates()->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        return redirect()->route('settings.protocols.edit', $system)->with('status', 'Domyślny szablon został zmieniony.');
    }
}
