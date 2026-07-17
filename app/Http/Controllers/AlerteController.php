<?php

namespace App\Http\Controllers;

use App\Services\AlerteService;

class AlerteController extends Controller
{
    public function __construct(private AlerteService $alerteService)
    {
    }

    public function index()
    {
        $alertes = $this->alerteService->getAllAlertes();

        return view('pages.alertes.index', compact('alertes'));
    }
}
