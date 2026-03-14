{{-- resources/views/creances/index.blade.php --}}
@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">📊 Suivi des Créances</h1>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Impayé</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalCreances, 2) }} MAD</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">0-30 jours</p>
            <p class="text-xl font-bold">{{ number_format($total30, 2) }} MAD</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">31-60 jours</p>
            <p class="text-xl font-bold">{{ number_format($total60, 2) }} MAD</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">60+ jours</p>
            <p class="text-xl font-bold">{{ number_format($total90, 2) }} MAD</p>
        </div>
    </div>

    {{-- Aging Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-right">Nb Factures</th>
                    <th class="px-4 py-3 text-right">0-30j</th>
                    <th class="px-4 py-3 text-right">31-60j</th>
                    <th class="px-4 py-3 text-right">60j+</th>
                    <th class="px-4 py-3 text-right">Total Impayé</th>
                    <th class="px-4 py-3 text-center">Risque</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $data)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $data['client']->name }}</td>
                    <td class="px-4 py-3 text-right">{{ $data['nb_factures'] }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($data['j30'], 2) }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($data['j60'], 2) }}</td>
                    <td class="px-4 py-3 text-right text-red-600 font-semibold">{{ number_format($data['j90_plus'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold">{{ number_format($data['total_impaye'], 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($data['risque'] === 'critique')
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">🔴 Critique</span>
                        @elseif($data['risque'] === 'attention')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">🟡 Attention</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">🟢 Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
