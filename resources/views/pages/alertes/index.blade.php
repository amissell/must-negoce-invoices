@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">🔔 Alertes</h1>

    @if($alertes->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <p class="text-green-700 text-lg">✅ Aucune alerte — tout est en ordre !</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($alertes as $alerte)
                <div class="flex items-start gap-3 p-4 rounded-lg border
                    {{ $alerte['niveau'] === 'critique' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }}">
                    <span class="text-lg">
                        @if($alerte['type'] === 'facture') 🧾
                        @elseif($alerte['type'] === 'stock') 📦
                        @else 👤
                        @endif
                    </span>
                    <div class="flex-1">
                        <p class="font-medium {{ $alerte['niveau'] === 'critique' ? 'text-red-800' : 'text-yellow-800' }}">
                            {{ $alerte['message'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $alerte['niveau'] === 'critique' ? '🔴 Critique' : '🟡 Attention' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
