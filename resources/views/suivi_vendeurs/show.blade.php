@extends('layouts.app')
@section('title', 'Dashboard Vendeur - ' . $nom)
@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-50">
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:24px;padding:32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-size:28px;font-weight:800;color:white;margin:0">📊 {{ $nom }}</h1>
            <p style="color:rgba(199,210,254,1);margin-top:6px;font-size:15px">
                Performance — {{ \Carbon\Carbon::create($annee, $mois)->translatedFormat('F Y') }}
            </p>
        </div>
        <a href="{{ route('suivi-vendeurs.index') }}"
           style="padding:10px 20px;background:rgba(255,255,255,0.2);color:white;border-radius:14px;font-weight:700;text-decoration:none;border:1px solid rgba(255,255,255,0.3)">
            ← Retour
        </a>
    </div>

    {{-- Filtre --}}
    <form method="GET" style="background:white;border-radius:16px;padding:20px;border:1px solid #E2E8F0;display:flex;gap:12px;flex-wrap:wrap">
        <select name="mois" style="padding:10px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected($m == $mois)>
                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                </option>
            @endfor
        </select>
        <select name="annee" style="padding:10px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit">
            @for($y = 2024; $y <= 2027; $y++)
                <option value="{{ $y }}" @selected($y == $annee)>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" style="padding:10px 24px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit">
            🔍 Filtrer
        </button>
    </form>

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px">
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#4F46E5">{{ number_format($caTotal, 2) }}</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">💰 CA Total</div>
        </div>
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#10B981">{{ number_format($encaisse, 2) }}</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">✅ Encaissé</div>
        </div>
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#F59E0B">{{ number_format($caTotal - $encaisse, 2) }}</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">⏳ Reste</div>
        </div>
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#F59E0B">{{ $taux }}%</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">📈 Taux</div>
        </div>
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#6366F1">{{ $blTotal }}</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">📦 BL Total</div>
        </div>
        <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);text-align:center">
            <div style="font-size:26px;font-weight:800;color:#EC4899">{{ $drops }}</div>
            <div style="color:#64748B;font-size:13px;margin-top:4px;font-weight:600">🎯 Drops/BL</div>
        </div>
    </div>

    {{-- Meilleur / Pire jour --}}
    @if($meilleurJour)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:20px;padding:24px">
            <div style="font-weight:700;color:#15803D;margin-bottom:8px">🏆 Meilleur Jour</div>
            <div style="font-size:22px;font-weight:800;color:#15803D">{{ \Carbon\Carbon::parse($meilleurJour->date)->format('d/m/Y') }}</div>
            <div style="color:#166534;margin-top:4px;font-weight:600">CA: {{ number_format($meilleurJour->chiffre, 2) }} DH</div>
        </div>
        <div style="background:#FEF2F2;border:1.5px solid #FCA5A5;border-radius:20px;padding:24px">
            <div style="font-weight:700;color:#B91C1C;margin-bottom:8px">📉 Pire Jour</div>
            <div style="font-size:22px;font-weight:800;color:#B91C1C">{{ \Carbon\Carbon::parse($pireJour->date)->format('d/m/Y') }}</div>
            <div style="color:#991B1B;margin-top:4px;font-weight:600">CA: {{ number_format($pireJour->chiffre, 2) }} DH</div>
        </div>
    </div>
    @endif

    {{-- Chart --}}
    <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06)">
        <h3 style="font-weight:700;color:#1E293B;margin:0 0 20px">📈 Évolution CA</h3>
        <canvas id="caChart" height="80"></canvas>
    </div>

    {{-- Tableau --}}
    <div style="background:white;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#F8FAFC">
                    <th style="padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0">Date</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0">CA</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0">Encaissé</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0">BL</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;border-bottom:2px solid #E2E8F0">Taux</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lignes as $ligne)
                <tr style="border-top:1px solid #F1F5F9" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                    <td style="padding:16px 20px;font-weight:600;color:#1E293B">{{ \Carbon\Carbon::parse($ligne->date)->format('d/m/Y') }}</td>
                    <td style="padding:16px 20px;text-align:right;font-weight:700;color:#4F46E5">{{ number_format($ligne->chiffre, 2) }}</td>
                    <td style="padding:16px 20px;text-align:right;color:#10B981;font-weight:600">{{ number_format($ligne->total_paye, 2) }}</td>
                    <td style="padding:16px 20px;text-align:right;color:#64748B">{{ $ligne->bl }}</td>
                    <td style="padding:16px 20px;text-align:right">
                        @php $t = $ligne->chiffre > 0 ? round(($ligne->total_paye/$ligne->chiffre)*100,1) : 0; @endphp
                        <span style="background:{{ $t >= 80 ? '#F0FDF4' : ($t >= 50 ? '#FEF9C3' : '#FEF2F2') }};color:{{ $t >= 80 ? '#15803D' : ($t >= 50 ? '#854D0E' : '#B91C1C') }};padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700">
                            {{ $t }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:40px;text-align:center;color:#94A3B8">Aucune donnée pour ce mois</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('caChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartDates) !!},
        datasets: [{
            label: 'CA (DH)',
            data: {!! json_encode($chartCA) !!},
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79,70,229,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#4F46E5',
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endsection