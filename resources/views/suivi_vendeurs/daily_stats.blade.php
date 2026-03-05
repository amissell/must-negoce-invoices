@extends('layouts.app')
@section('title', 'Évolution des Catégories')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#1e3a5f,#3b7cb8);border-radius:24px;padding:32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-size:28px;font-weight:800;color:white;margin:0">📊 Évolution des Catégories</h1>
            <p style="color:rgba(199,210,254,0.8);margin-top:6px;font-size:15px">
                Progression journalière par catégorie
            </p>
        </div>
        <a href="{{ route('suivi-vendeurs.index') }}"
           style="padding:10px 20px;background:rgba(255,255,255,0.2);color:white;border-radius:14px;font-weight:700;text-decoration:none;border:1px solid rgba(255,255,255,0.3)">
            ← Retour
        </a>
    </div>

    {{-- Date Range Filter --}}
    <form method="GET" style="background:white;border-radius:16px;padding:20px;border:1px solid #E2E8F0;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:12px;font-weight:600;color:#64748B">📅 Date début</label>
            <input type="date" name="date_debut" value="{{ $dateDebut }}"
                style="padding:10px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:12px;font-weight:600;color:#64748B">📅 Date fin</label>
            <input type="date" name="date_fin" value="{{ $dateFin }}"
                style="padding:10px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit">
        </div>
        <button type="submit"
            style="margin-top:18px;padding:10px 24px;background:linear-gradient(135deg,#1e3a5f,#3b7cb8);color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit">
            🔍 Filtrer
        </button>
        <a href="{{ route('statistics.daily') }}"
            style="margin-top:18px;padding:10px 24px;background:#F1F5F9;color:#475569;border-radius:12px;font-weight:700;text-decoration:none;font-size:14px">
            🔄 Reset
        </a>
    </form>

    @if(count($tableData) > 0)

    {{-- Summary Cards par Catégorie --}}
    @php
        $catColors = ['#1e3a5f','#F97316','#10B981','#EF4444','#8B5CF6','#F59E0B','#14B8A6','#EC4899','#6366F1','#84CC16'];
    @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px">
        @foreach($categoriesStats as $i => $cat)
        @php $color = $catColors[$i % count($catColors)]; @endphp
        <div style="background:white;border-radius:20px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);border-top:4px solid {{ $color }}">
            <div style="font-size:13px;font-weight:700;color:{{ $color }};margin-bottom:8px">{{ $cat->categorie }}</div>
            <div style="font-size:22px;font-weight:800;color:#1E293B">{{ number_format($cat->total_ca, 0, ',', ' ') }} DH</div>
            <div style="font-size:12px;color:#64748B;margin-top:4px">Qté: {{ number_format($cat->total_quantite, 0, ',', ' ') }}</div>
        </div>
        @endforeach
    </div>

    {{-- Multi-line Chart --}}
    <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06)">
        <h3 style="font-weight:700;color:#1E293B;margin:0 0 20px">📈 Évolution CA par Catégorie</h3>
        <canvas id="evolutionChart" height="80"></canvas>
    </div>

    {{-- Table: Jours x Catégories --}}
    <div style="background:white;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden">
        <div style="background:linear-gradient(135deg,#1e3a5f,#3b7cb8);padding:20px 28px">
            <h2 style="color:white;font-weight:800;font-size:18px;margin:0">📋 Détail par Jour</h2>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;min-width:600px">
                <thead>
                    <tr style="background:#F8FAFC">
                        <th style="padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0;white-space:nowrap">Date</th>
                        @foreach($categories as $i => $cat)
                        @php $color = $catColors[$i % count($catColors)]; @endphp
                        <th style="padding:14px 16px;text-align:right;font-size:11px;font-weight:700;color:{{ $color }};text-transform:uppercase;border-bottom:2px solid #E2E8F0;white-space:nowrap">
                            {{ $cat }}
                        </th>
                        @endforeach
                        <th style="padding:14px 20px;text-align:right;font-size:11px;font-weight:700;color:#1e3a5f;text-transform:uppercase;border-bottom:2px solid #E2E8F0">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData as $row)
                    @php
                        $maxCat = collect($row['categories'])->max();
                    @endphp
                    <tr style="border-top:1px solid #F1F5F9" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding:14px 20px;font-weight:600;color:#1E293B;white-space:nowrap">
                            {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                            <span style="font-size:11px;color:#94A3B8;display:block">
                                {{ \Carbon\Carbon::parse($row['date'])->translatedFormat('l') }}
                            </span>
                        </td>
                        @foreach($categories as $i => $cat)
                        @php
                            $val = $row['categories'][$cat] ?? 0;
                            $color = $catColors[$i % count($catColors)];
                            $isBest = $val > 0 && $val == $maxCat;
                        @endphp
                        <td style="padding:14px 16px;text-align:right">
                            @if($val > 0)
                            <span style="font-weight:{{ $isBest ? '800' : '600' }};color:{{ $isBest ? $color : '#475569' }};background:{{ $isBest ? $color.'15' : 'transparent' }};padding:4px 8px;border-radius:8px">
                                {{ number_format($val, 0, ',', ' ') }}
                                @if($isBest) 🏆 @endif
                            </span>
                            @else
                            <span style="color:#CBD5E1;font-size:12px">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td style="padding:14px 20px;text-align:right;font-weight:800;color:#1e3a5f;font-size:15px">
                            {{ number_format($row['total'], 0, ',', ' ') }} DH
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#EFF6FF;border-top:2px solid #BFDBFE">
                        <td style="padding:16px 20px;font-weight:800;color:#1e3a5f">TOTAL</td>
                        @foreach($categories as $i => $cat)
                        @php $color = $catColors[$i % count($catColors)]; @endphp
                        <td style="padding:16px 16px;text-align:right;font-weight:800;color:{{ $color }}">
                            {{ number_format($categoriesStats->where('categorie', $cat)->first()?->total_ca ?? 0, 0, ',', ' ') }} DH
                        </td>
                        @endforeach
                        <td style="padding:16px 20px;text-align:right;font-weight:800;color:#1e3a5f;font-size:16px">
                            {{ number_format($categoriesStats->sum('total_ca'), 0, ',', ' ') }} DH
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @else
    <div style="background:white;border-radius:20px;padding:60px;text-align:center;color:#94A3B8;box-shadow:0 4px 20px rgba(0,0,0,0.06)">
        <div style="font-size:50px;margin-bottom:16px">📊</div>
        <div style="font-size:18px;font-weight:700;margin-bottom:8px">Aucune donnée pour cette période</div>
        <div style="font-size:14px">Changez les dates et réessayez</div>
    </div>
    @endif

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
@if(count($tableData) > 0)
<script>
const catColors = ['#1e3a5f','#F97316','#10B981','#EF4444','#8B5CF6','#F59E0B','#14B8A6','#EC4899','#6366F1','#84CC16'];
const categories = @json($categories);
const dataParCategorie = @json($dataParCategorie);
const chartDates = @json($chartDates);

const datasets = categories.map((cat, i) => ({
    label: cat,
    data: dataParCategorie[cat],
    borderColor: catColors[i % catColors.length],
    backgroundColor: catColors[i % catColors.length] + '20',
    borderWidth: 2.5,
    tension: 0.4,
    pointBackgroundColor: catColors[i % catColors.length],
    pointRadius: 5,
    fill: false,
}));

new Chart(document.getElementById('evolutionChart'), {
    type: 'line',
    data: { labels: chartDates, datasets },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 12, weight: 600 }, boxWidth: 14, padding: 16 }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-MA')} DH`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => v.toLocaleString('fr-MA') + ' DH' },
                grid: { color: '#F1F5F9' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endif
@endsection