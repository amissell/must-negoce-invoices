
<thead>
    <tr class="bg-gray-50">
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant HT</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TVA</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total TTC</th>
        
        {{-- ✅ NOUVELLE COLONNE POUR LE CRÉATEUR --}}
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé par</th>
        
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
    </tr>
</thead>

{{-- Dans la section <tbody>, ajoutez: --}}
<tbody class="bg-white divide-y divide-gray-200">
    @foreach($invoices as $invoice)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-semibold text-gray-900">
                        {{ $invoice->invoice_number }}
                    </span>
                    @if($invoice->type === 'manual')
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            Manuel
                        </span>
                    @endif
                </div>
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $invoice->client_name }}
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $invoice->invoice_date->format('d/m/Y') }}
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ number_format($invoice->montant_ht, 2) }} MAD
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ number_format($invoice->tva, 2) }} MAD
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-bold text-blue-600">
                    {{ number_format($invoice->total_ttc, 2) }} MAD
                </span>
            </td>
            
            {{-- ✅ AFFICHAGE DU CRÉATEUR AVEC BADGE ET TOOLTIP --}}
            <td class="px-6 py-4 whitespace-nowrap">
                @if($invoice->creator)
                    <div class="flex items-center gap-2">
                        {{-- Avatar avec initiale --}}
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center" 
                             title="{{ $invoice->creator->name }}">
                            <span class="text-xs font-semibold text-blue-600">
                                {{ substr($invoice->creator->name, 0, 1) }}
                            </span>
                        </div>
                        
                        {{-- Nom de l'utilisateur --}}
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $invoice->creator->name }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $invoice->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        Système
                    </span>
                @endif
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    {{-- Télécharger --}}
                    <a href="{{ route('invoices.download', $invoice->id) }}" 
                       class="text-blue-600 hover:text-blue-900 transition-colors"
                       title="Télécharger PDF">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                    
                    {{-- Modifier (seulement pour les factures manuelles) --}}
                    @if($invoice->type === 'manual')
                        <a href="{{ route('invoices.edit', $invoice->id) }}" 
                           class="text-green-600 hover:text-green-900 transition-colors"
                           title="Modifier">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    @endif
                    
                    {{-- Supprimer --}}
                    <form action="{{ route('invoices.destroy', $invoice->id) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900 transition-colors"
                                title="Supprimer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
</tbody>


{{-- ✅ VERSION ALTERNATIVE SIMPLE: Badge uniquement avec nom --}}
{{-- Si vous préférez une version plus simple, utilisez ce code à la place: --}}

<td class="px-6 py-4 whitespace-nowrap">
    @if($invoice->creator)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            {{ $invoice->creator->name }}
        </span>
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
            Système
        </span>
    @endif
</td>


{{-- ✅ VERSION ULTRA-SIMPLE: Texte uniquement --}}
{{-- La version la plus simple possible: --}}

<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
    {{ $invoice->creator_name }}
</td>