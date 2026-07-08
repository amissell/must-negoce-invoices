<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    public function download(string $view, array $data, string $filename, string $paper = 'A4', string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper($paper, $orientation);

        return $pdf->download($filename);
    }
}
