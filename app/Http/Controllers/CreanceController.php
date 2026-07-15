<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Exports\CreancesExport;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;    

class CreanceController extends Controller
{
    private \App\Services\CreanceService $creanceService;
    private PdfService $pdfService;

    public function __construct(\App\Services\CreanceService $creanceService, PdfService $pdfService)
    {
        $this->creanceService = $creanceService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $data = $this->creanceService->getCreancesData();
        $amevo = $this->creanceService->getAmevoData();

        return view('pages.creances.index', array_merge($data, [
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

        return $this->pdfService->download(
            'creances.pdf',
            $data,
            'creances_' . now()->format('Y-m-d') . '.pdf'
        );
    }
}
