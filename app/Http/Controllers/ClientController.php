<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);
        $query = Client::withCount(['invoices']);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('ice', 'like', "%$s%")
                  ->orWhere('ville', 'like', "%$s%")
                  ->orWhere('telephone', 'like', "%$s%");
            });
        }
        $clients = $query->orderBy('nom')->paginate(15)->withQueryString();
        return view('pages.clients.index', compact('clients'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);

        return view('pages.clients.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $data = $request->validate([
            'nom'        => 'required|string|max:255',
            'ice'        => 'nullable|string|max:50|unique:clients,ice',
            'if_number'  => 'nullable|string|max:50',
            'rc'         => 'nullable|string|max:50',
            'cnss'       => 'nullable|string|max:50',
            'telephone'  => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'adresse'    => 'nullable|string',
            'ville'      => 'nullable|string|max:100',
        ]);

        Client::create($data);

        return redirect()->route('clients.index')
                         ->with('success', 'Client ajouté avec succès.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $invoices       = $client->invoices()->latest()->get();
        $manualInvoices = collect();
        $totalFacture   = $client->totalFacture();
        $nombreFactures = $client->nombreFactures();

        return view('pages.clients.show', compact(
            'client', 'invoices', 'manualInvoices', 'totalFacture', 'nombreFactures'
        ));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('pages.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validated();

        $client->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Client mis à jour.',
                'client' => $client,
            ]);
        }

        return redirect()->route('clients.show', $client)
                         ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
                         ->with('success', 'Client supprimé.');
    }

    public function search(Request $request)
    {
        $clients = Client::where('nom', 'like', '%' . $request->q . '%')
                         ->orWhere('ice', 'like', '%' . $request->q . '%')
                         ->limit(10)
                         ->get(['id', 'nom', 'ice', 'adresse', 'ville', 'telephone']);

        return response()->json($clients);
    }
}
