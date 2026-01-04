<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientObject;
use Illuminate\Http\Request;

class ClientObjectController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientObject::with('client');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $objects = $query->orderBy('is_active', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        if ($request->ajax()) {
            return view('objects.table', compact('objects'))->render();
        }

        return view('objects.index', compact('objects'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('objects.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        ClientObject::create($validated + ['is_active' => true]);

        return redirect()->route('objects.index')->with('status', 'Obiekt został dodany.');
    }

    public function edit(ClientObject $object)
    {
        $clients = Client::orderBy('name')->get();
        return view('objects.edit', compact('object', 'clients'));
    }

    public function update(Request $request, ClientObject $object)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $object->update($validated);

        return redirect()->route('objects.index')->with('status', 'Dane obiektu zostały zaktualizowane.');
    }

    public function destroy(ClientObject $object)
    {
        $object->update(['is_active' => false]);
        return redirect()->route('objects.index')->with('status', 'Obiekt został dezaktywowany.');
    }

    public function getClientData(Client $client)
    {
        return response()->json([
            'address' => $client->address,
            'postal_code' => $client->postal_code,
            'city' => $client->city,
        ]);
    }
}
