@extends('layouts.app')

@section('title', 'Gestion des Vendeurs')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🧑‍💼 Gestion des Vendeurs</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $vendeurs->total() }} vendeur(s) enregistré(s)</p>
        </div>
        <a href="{{ route('vendeurs.create') }}"
           class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau Vendeur
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('vendeurs.index') }}" class="flex gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Rechercher par nom..."
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>
        <button type="submit"
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-all">
            Rechercher
        </button>
        @if(request('q'))
        <a href="{{ route('vendeurs.index') }}"
           class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-all">
            ✕ Effacer
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($vendeurs->isEmpty())
            <div class="text-center py-16">
                <div class="text-5xl mb-4">🧑‍💼</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    @if(request('q')) Aucun résultat @else Aucun vendeur @endif
                </h3>
                <p class="text-gray-500 mb-6">
                    @if(request('q'))
                        Aucun vendeur ne correspond à "{{ request('q') }}"
                    @else
                        Commencez par ajouter votre premier vendeur.
                    @endif
                </p>
                @if(!request('q'))
                <a href="{{ route('vendeurs.create') }}"
                   class="btn-primary inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-xl text-sm font-semibold">
                    + Nouveau Vendeur
                </a>
                @endif
            </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nom Complet</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID AMEVO</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Créé le</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($vendeurs as $vendeur)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">{{ $vendeur->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                <span class="text-blue-700 font-bold text-sm">
                                    {{ mb_strtoupper(mb_substr($vendeur->nom_complet, 0, 1)) }}
                                </span>
                            </div>
                            <span class="font-semibold text-gray-800">{{ $vendeur->nom_complet }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($vendeur->amevo_id)
                            <span class="inline-block bg-purple-50 text-purple-700 text-xs font-mono px-2 py-1 rounded-lg">
                                {{ $vendeur->amevo_id }}
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        {{ $vendeur->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('vendeurs.edit', $vendeur) }}"
                               class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition-all">
                                ✏️ Modifier
                            </a>
                            <form method="POST" action="{{ route('vendeurs.destroy', $vendeur) }}"
                                  onsubmit="return confirm('Supprimer ce vendeur ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition-all">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($vendeurs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $vendeurs->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
