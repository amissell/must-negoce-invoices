<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function __construct(private PaiementService $service) {}

    public function index(Request $request)
    {
        $query = Paiement::with(['client', 'invoices']);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('mode')) $query->where('mode', $request->mode);
        if ($request->filled('date_from')) $query->whereDate('date_paiement', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('date_paiement', '<=', $request->date_to);
        return view('pages.paiements.index', ['paiements' => $query->latest('date_paiement')->paginate(15)->withQueryString(), 'clients' => Client::orderBy('nom')->get(['id', 'nom'])]);
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('nom')->get(['id', 'nom']);
        $clientId = $request->integer('client_id');
        $invoices = $clientId ? Client::findOrFail($clientId)->invoices()->withSum('paiements as montant_regle_sum', 'paiement_invoice.montant_affecte')->get()->filter(fn ($invoice) => $invoice->reste_a_payer > 0) : collect();
        return view('pages.paiements.form', compact('clients', 'clientId', 'invoices'));
    }

    public function store(Request $request)
    {
        $this->service->save($this->validated($request));
        return redirect()->route('paiements.index')->with('success', 'Paiement enregistré.');
    }

    public function edit(Paiement $paiement)
    {
        $clients = Client::orderBy('nom')->get(['id', 'nom']);
        $invoices = $paiement->client->invoices()->withSum('paiements as montant_regle_sum', 'paiement_invoice.montant_affecte')->get()->filter(fn ($invoice) => $invoice->reste_a_payer > 0 || $paiement->invoices->contains('id', $invoice->id));
        $paiement->load('invoices');
        return view('pages.paiements.form', compact('paiement', 'clients', 'invoices'));
    }

    public function update(Request $request, Paiement $paiement)
    {
        $this->service->save($this->validated($request), $paiement);
        return redirect()->route('paiements.index')->with('success', 'Paiement mis à jour.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['client_id' => 'required|exists:clients,id', 'mode' => 'required|in:chèque,virement,espèces', 'reference' => 'nullable|string|max:255', 'banque' => 'nullable|string|max:255', 'montant_total' => 'required|numeric|gt:0', 'date_paiement' => 'required|date', 'date_encaissement' => 'nullable|date', 'notes' => 'nullable|string', 'allocations' => 'nullable|array', 'allocations.*.invoice_id' => 'required|exists:invoices,id', 'allocations.*.montant_affecte' => 'nullable|numeric|min:0']);
    }
}
