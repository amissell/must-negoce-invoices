@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">👥 Gestion des Clients</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $clients->total() }} client(s) enregistré(s)</p>
        </div>
        <a href="{{ route('clients.create') }}"
           class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau Client
        </a>
    </div>

    {{-- Search Bar --}}
    <form method="GET" class="flex gap-3">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher par nom, ICE, ville..."
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>
        <button type="submit"
                class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all">
            Rechercher
        </button>
        @if(request('search'))
            <a href="{{ route('clients.index') }}"
               class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition-all">
                ✕ Effacer
            </a>
        @endif
    </form>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nom</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ICE</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ville</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Factures</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($clients as $client)
                <tr class="hover:bg-blue-50/30 transition-colors group">
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $client->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                 style="background: linear-gradient(135deg, #1e3a5f, #3b7cb8)">
                                {{ strtoupper(substr($client->nom, 0, 1)) }}
                            </div>
                            <span class="font-semibold text-gray-800">{{ $client->nom }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($client->ice)
                            <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">{{ $client->ice }}</code>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->ville ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->telephone ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                            {{ $client->invoices_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('clients.show', $client) }}"
                               class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all" title="Voir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('clients.edit', $client) }}"
                               class="p-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-all" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                  onsubmit="return confirm('Supprimer {{ addslashes($client->nom) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="font-medium">Aucun client trouvé</p>
                            <a href="{{ route('clients.create') }}" class="text-blue-500 text-sm hover:underline">Ajouter le premier client</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($clients->hasPages())
    <div class="flex justify-center">
        {{ $clients->links() }}
    </div>
    @endif

</div>
@endsection
