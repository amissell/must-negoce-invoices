@extends('layouts.app')

@section('title', 'Alertes')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">🔔 Alertes</h1>
        <p class="text-gray-500 mt-1">{{ $alertes->count() }} alerte(s) active(s)</p>
    </div>
    @if($alertes->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl card-shadow p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-2xl">🔴</div>
                <div>
                    <p class="text-sm text-gray-500">Critiques</p>
                    <p class="text-xl font-bold text-red-600">{{ $alertes->where('niveau', 'critique')->count() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl card-shadow p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center text-2xl">🟡</div>
                <div>
                    <p class="text-sm text-gray-500">À surveiller</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $alertes->where('niveau', 'attention')->count() }}</p>
                </div>
            </div>
        </div>
    @endif
    @if($alertes->isEmpty())
        <div class="bg-white rounded-2xl card-shadow p-12 text-center">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">✅</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune alerte</h3>
            <p class="text-gray-500">Tout est en ordre pour le moment !</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($alertes as $alerte)
                <div class="bg-white rounded-2xl card-shadow p-4 flex items-start gap-4 border-l-4
                    {{ $alerte['niveau'] === 'critique' ? 'border-red-500' : 'border-yellow-500' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0
                        {{ $alerte['niveau'] === 'critique' ? 'bg-red-50' : 'bg-yellow-50' }}">
                        @if($alerte['type'] === 'facture') 🧾
                        @elseif($alerte['type'] === 'stock') 📦
                        @else 👤
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-medium {{ $alerte['niveau'] === 'critique' ? 'text-red-800' : 'text-yellow-800' }}">
                            {{ $alerte['message'] }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($alerte['date'])->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold shrink-0
                        {{ $alerte['niveau'] === 'critique' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $alerte['niveau'] === 'critique' ? '🔴 Critique' : '🟡 Attention' }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
