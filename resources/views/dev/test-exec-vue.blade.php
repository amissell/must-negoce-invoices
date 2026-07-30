@extends('layouts.app')

@section('title', 'Dev Test — Executive Vue')

@section('content')
<div class="p-8">
    <h2 class="text-lg font-semibold mb-4">Dev Test: Executive Vue Widgets</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div id="vue-sales-pulse" class="bg-white rounded-2xl card-shadow p-4" data-initial='{"current":12345.67,"sparkline":[100,200,150,300,250,400,350,200,100,50,300,250,200,150],"vs_last_week":{"percent":12.3,"delta":123}}'></div>
        <div id="vue-ceo-scorecard" class="bg-white rounded-2xl card-shadow p-4" data-initial='{"mtd_revenue":54321.12,"outstanding_receivables":12000.5,"overdue_invoices":7}'></div>
    </div>
</div>
@endsection
