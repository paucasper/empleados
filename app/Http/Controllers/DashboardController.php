<?php

namespace App\Http\Controllers;

use App\Models\AbsenceRequest;
use App\Models\HrRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Stats reales
        $unsignedAbsences = AbsenceRequest::where('user_id', $user->id)
            ->where('status', 'pending_employee_signature')
            ->count();

        $activeAbsences = AbsenceRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending_signer_signature', 'pending_employee_signature'])
            ->count();

        $activeExpenses = HrRequest::where('user_id', $user->id)
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->whereHas('status', function ($q) {
                $q->whereNotIn('code', ['exported_to_sap', 'rejected']);
            })
            ->count();

        $stats = [
            'unsigned_absences' => $unsignedAbsences,
            'active_absences'   => $activeAbsences,
            'active_expenses'   => $activeExpenses,
        ];

        /*
        |--------------------------------------------------------------------------
        | Mis tramitaciones (activas)
        |--------------------------------------------------------------------------
        | Aquí NO deben salir las exportadas a SAP ni las rechazadas.
        */

        $myAbsences = AbsenceRequest::with(['signer'])
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['exported_to_sap', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $myExpenses = HrRequest::with(['status', 'approver', 'admin'])
            ->where('user_id', $user->id)
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->whereHas('status', function ($q) {
                $q->whereNotIn('code', ['exported_to_sap', 'rejected']);
            })
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Mis trámites (finalizados / histórico)
        |--------------------------------------------------------------------------
        | Aquí sí deben salir exportados a SAP y rechazados.
        */

        $completedAbsences = AbsenceRequest::with(['signer'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['exported_to_sap', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $completedExpenses = HrRequest::with(['status', 'approver', 'admin'])
            ->where('user_id', $user->id)
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->whereHas('status', function ($q) {
                $q->whereIn('code', ['exported_to_sap', 'rejected']);
            })
            ->latest()
            ->take(5)
            ->get();

        // Pendientes de aprobación reales — ausencias
        $pendingAbsences = AbsenceRequest::with(['user'])
            ->where('signer_user_id', $user->id)
            ->where('status', 'pending_signer_signature')
            ->latest()
            ->take(5)
            ->get();

        // Pendientes de aprobación reales — gastos
        $pendingExpenses = HrRequest::with(['user', 'status'])
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->where(function ($q) use ($user) {
                $q->where('approver_user_id', $user->id)
                  ->orWhere('admin_user_id', $user->id);
            })
            ->whereHas('status', function ($q) {
                $q->whereIn('code', ['pending_approval', 'pending_admin_approval']);
            })
            ->latest()
            ->take(5)
            ->get();

        // Últimas solicitudes para la tabla "Actividad"
        $recentAbsences = $myAbsences->map(function ($absence) {
            return [
                'title'  => $absence->description ?: $absence->awart,
                'type'   => 'Ausencia',
                'status' => $this->mapAbsenceStatus($absence->status),
                'date'   => $absence->created_at->format('d/m/Y'),
                'sort'   => $absence->created_at,
            ];
        });

        $recentExpenses = $myExpenses->map(function ($expense) {
            return [
                'title'  => $expense->description ?: $expense->title,
                'type'   => 'Gasto',
                'status' => $expense->status->name ?? 'Pendiente',
                'date'   => $expense->created_at->format('d/m/Y'),
                'sort'   => $expense->created_at,
            ];
        });

        $recentRequests = collect()
            ->merge($recentAbsences)
            ->merge($recentExpenses)
            ->sortByDesc('sort')
            ->take(5)
            ->values();

        // Pendientes de aprobación para la tarjeta lateral
        $pendingApprovals = collect()
            ->merge($pendingAbsences->map(function ($absence) {
                return [
                    'employee' => $absence->user?->name ?? '-',
                    'type'     => 'Ausencia',
                    'date'     => $absence->created_at->format('d/m/Y'),
                    'sort'     => $absence->created_at,
                ];
            }))
            ->merge($pendingExpenses->map(function ($expense) {
                return [
                    'employee' => $expense->user?->name ?? '-',
                    'type'     => 'Gasto',
                    'date'     => $expense->created_at->format('d/m/Y'),
                    'sort'     => $expense->created_at,
                ];
            }))
            ->sortByDesc('sort')
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'user',
            'stats',
            'myAbsences',
            'myExpenses',
            'completedAbsences',
            'completedExpenses',
            'pendingAbsences',
            'pendingExpenses',
            'recentRequests',
            'pendingApprovals'
        ));
    }

    private function mapAbsenceStatus(string $status): string
    {
        return match ($status) {
            'pending_employee_signature' => 'Pendiente empleado',
            'pending_signer_signature'   => 'Pendiente firmante',
            'approved'                   => 'Aprobada',
            'exported_to_sap'            => 'Exportada a SAP',
            'rejected'                   => 'Rechazada',
            default                      => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}