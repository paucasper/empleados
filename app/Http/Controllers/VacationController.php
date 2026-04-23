<?php

namespace App\Http\Controllers;

use App\Services\SapVacationService;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    public function index(Request $request, SapVacationService $sapVacationService)
    {
        $user = $request->user();

        $pernr = $user->sap_employee_id ?? $user->pernr ?? null;

        if (! $pernr) {
            $vacationSummary = [
                'pernr' => null,
                'periodoInicio' => null,
                'periodoFin' => null,
                'totalVacaciones' => 0,
                'disponibles' => 0,
                'concedidos' => 0,
                'enTramite' => 0,
            ];
        } else {
            $vacationSummary = $sapVacationService->getSummary($pernr);
        }

        return view('vacations', compact('vacationSummary'));
    }
}