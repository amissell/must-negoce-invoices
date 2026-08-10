<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Créances - {{ now()->format('d/m/Y') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .kpi { display: inline-block; width: 23%; text-align: center; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>📊 Suivi des Créances - {{ now()->format('d/m/Y') }}</h1>
    <p>Total impayé: <strong>{{ number_format($totalCreances, 2) }} MAD</strong> | 0-30j: {{ number_format($total30, 2) }} | 31-60j: {{ number_format($total60, 2) }} | 60j+: {{ number_format($total90, 2) }}</p>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th class="text-center">Nb Factures</th>
                <th class="text-right">0-30j</th>
                <th class="text-right">31-60j</th>
                <th class="text-right">60j+</th>
                <th class="text-right">Total Impayé</th>
                <th class="text-center">Risque</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $data)
            <tr>
                <td>{{ $data['client']->nom }}</td>
                <td class="text-center">{{ $data['nb_factures'] }}</td>
                <td class="text-right">{{ number_format($data['j30'], 2) }}</td>
                <td class="text-right">{{ number_format($data['j60'], 2) }}</td>
                <td class="text-right">{{ number_format($data['j90_plus'], 2) }}</td>
                <td class="text-right">{{ number_format($data['total_impaye'], 2) }}</td>
                <td class="text-center">{{ ucfirst($data['risque']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
