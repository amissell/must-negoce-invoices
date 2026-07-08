<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Exports\CreancesExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CreanceController extends Controller
{
    private \App\Services\CreanceService $creanceService;

    public function __construct(\App\Services\CreanceService $creanceService)
    {
        $this->creanceService = $creanceService;
    }

    public function index()
    {
        $data = $this->creanceService->getCreancesData();
        $amevo = $this->creanceService->getAmevoData();

        return view('creances.index', array_merge($data, [
            'amevoErreur' => $amevo['erreur'],
            'amevoData'   => $amevo['data'],
        ]));
    }

    public function exportExcel()
    {
        $data = $this->creanceService->getCreancesData();
        return Excel::download(new CreancesExport($data['clients']), 'creances_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $data = $this->creanceService->getCreancesData();
        $pdf = Pdf::loadView('creances.pdf', $data);
        return $pdf->download('creances_' . now()->format('Y-m-d') . '.pdf');
    }
}
