<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('is_active', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        if ($request->ajax()) {
            return view('clients.table', compact('clients'))->render();
        }

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'nip' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        Client::create($validated + ['is_active' => true]);

        return redirect()->route('clients.index')->with('status', 'Klient został dodany.');
    }

    public function show(Client $client)
    {
        $client->load(['objects' => function ($query) {
            $query->orderBy('is_active', 'desc')->orderBy('created_at', 'desc');
        }]);
        
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'nip' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $client->update($validated);

        return redirect()->route('clients.index')->with('status', 'Dane klienta zostały zaktualizowane.');
    }

    public function destroy(Client $client)
    {
        $client->update(['is_active' => false]);
        return redirect()->route('clients.index')->with('status', 'Klient został dezaktywowany.');
    }
}
