<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreExpenseItemRequest;
use App\Models\HrRequest;
use App\Models\RequestStatus;
use App\Models\RequestItem;
use App\Models\User;
use App\Services\SapFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ExpenseController extends Controller
{
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        if (empty($user->sap_employee_id)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no tiene sap_employee_id configurado.',
            ], 422);
        }

        $signerPernr = str_pad(trim($validated['signer_pernr']), 8, '0', STR_PAD_LEFT);
        $signer = User::where('sap_employee_id', $signerPernr)->first();

        if (!$signer) {
            return response()->json([
                'success' => false,
                'message' => 'No existe un usuario para el firmante indicado: ' . $signerPernr,
            ], 422);
        }

        
        // ← Estado inicial: pendiente de firma del empleado (igual que ausencias)
        $status = RequestStatus::where('code', RequestStatus::PENDING_EMPLOYEE_SIGNATURE)->firstOrFail();

        $expenseRequest = HrRequest::create([
            'type'            => HrRequest::TYPE_EXPENSE,
            'user_id'         => $user->id,
            'sap_employee_id' => $user->sap_employee_id,
            'approver_user_id'=> $signer->id,
            'status_id'       => $status->id,
            'title'           => $validated['title'] ?? null,
            'description'     => $validated['description'] ?? null,
        ]);

        $expenseRequest->load(['status', 'user', 'approver', 'items']);

        $adminPernr = str_pad(trim($validated['admin_pernr']), 8, '0', STR_PAD_LEFT);
        $admin = User::where('sap_employee_id', $adminPernr)->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'No existe un usuario para el administrador indicado: ' . $adminPernr,
            ], 422);
        }

        $expenseRequest = HrRequest::create([
            'type'             => HrRequest::TYPE_EXPENSE,
            'user_id'          => $user->id,
            'sap_employee_id'  => $user->sap_employee_id,
            'approver_user_id' => $signer->id,
            'admin_user_id'    => $admin->id, // ← añadir
            'status_id'        => $status->id,
            'title'            => $validated['title'] ?? null,
            'description'      => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de gasto creada correctamente.',
            'data'    => $expenseRequest,
        ], 201);



    }

    // ← NUEVO: el empleado firma su propia solicitud
    public function signByEmployee(int $id): JsonResponse
    {
        $expenseRequest = HrRequest::with('status')->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No puedes firmar esta solicitud.'], 403);
        }

        $pendingEmployeeStatus = RequestStatus::where('code', RequestStatus::PENDING_EMPLOYEE_SIGNATURE)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingEmployeeStatus->id) {
            return response()->json(['message' => 'La solicitud no está pendiente de firma del empleado.'], 409);
        }

        if ($expenseRequest->items()->count() === 0) {
            return response()->json(['message' => 'Debes añadir al menos una línea de gasto antes de firmar.'], 422);
        }

        $pendingApprovalStatus = RequestStatus::where('code', RequestStatus::PENDING_APPROVAL)->firstOrFail();

        $expenseRequest->update([
            'status_id'    => $pendingApprovalStatus->id,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud firmada y enviada al aprobador.',
            'data'    => $expenseRequest->fresh(),
        ]);
    }
    public function approve(int $id, SapFileService $sapFileService): JsonResponse
    {
        $expenseRequest = HrRequest::with(['status', 'items'])->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->approver_user_id !== (int) $user->id) {
            return response()->json(['message' => 'No eres el aprobador asignado.'], 403);
        }

        $pendingApprovalStatus = RequestStatus::where('code', RequestStatus::PENDING_APPROVAL)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingApprovalStatus->id) {
            return response()->json(['message' => 'La solicitud no está pendiente de aprobación.'], 409);
        }

        $pendingAdminStatus = RequestStatus::where('code', RequestStatus::PENDING_ADMIN_APPROVAL)->firstOrFail();

        $expenseRequest->update([
            'status_id'   => $pendingAdminStatus->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada por el jefe, pendiente de aprobación por administración.',
            'data'    => $expenseRequest->fresh(),
        ]);
    }
    // ← El firmante rechaza
    public function reject(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        $expenseRequest = HrRequest::with('status')->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->approver_user_id !== (int) $user->id) {
            return response()->json(['message' => 'No eres el aprobador asignado.'], 403);
        }

        $pendingApprovalStatus = RequestStatus::where('code', RequestStatus::PENDING_APPROVAL)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingApprovalStatus->id) {
            return response()->json(['message' => 'La solicitud no está pendiente de aprobación.'], 409);
        }

        $rejectedStatus = RequestStatus::where('code', RequestStatus::REJECTED)->firstOrFail();

        $expenseRequest->update([
            'status_id'        => $rejectedStatus->id,
            'rejected_at'      => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada.',
            'data'    => $expenseRequest->fresh(),
        ]);
    }

    public function pendingForApprover(): JsonResponse
    {
        $pendingApprovalStatus     = RequestStatus::where('code', RequestStatus::PENDING_APPROVAL)->firstOrFail();
        $pendingAdminApprovalStatus = RequestStatus::where('code', RequestStatus::PENDING_ADMIN_APPROVAL)->firstOrFail();

        $requests = HrRequest::with(['user', 'status', 'items', 'approver', 'admin'])
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->where(function ($query) use ($pendingApprovalStatus, $pendingAdminApprovalStatus) {
                $query->where(function ($q) use ($pendingApprovalStatus) {
                    $q->where('approver_user_id', auth()->id())
                    ->where('status_id', $pendingApprovalStatus->id);
                })->orWhere(function ($q) use ($pendingAdminApprovalStatus) {
                    $q->where('admin_user_id', auth()->id())
                    ->where('status_id', $pendingAdminApprovalStatus->id);
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests,
        ]);
    }

    public function approveByAdmin(int $id, SapFileService $sapFileService): JsonResponse
    {
        $expenseRequest = HrRequest::with(['status', 'items'])->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->admin_user_id !== (int) $user->id) {
            return response()->json(['message' => 'No eres el administrador asignado.'], 403);
        }

        $pendingAdminStatus = RequestStatus::where('code', RequestStatus::PENDING_ADMIN_APPROVAL)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingAdminStatus->id) {
            return response()->json(['message' => 'La solicitud no está pendiente de aprobación por administración.'], 409);
        }

        $approvedStatus  = RequestStatus::where('code', RequestStatus::APPROVED)->firstOrFail();
        $exportedStatus  = RequestStatus::where('code', RequestStatus::EXPORTED_TO_SAP)->firstOrFail();

        $expenseRequest->update([
            'status_id'   => $approvedStatus->id,
            'approved_at' => now(),
        ]);

        $content  = $sapFileService->generateExpenseFile($expenseRequest);
        $fileName = $sapFileService->generateExpenseFileName($expenseRequest->sap_employee_id);
        $sapFileService->saveExpenseFile($content, $fileName);

        $expenseRequest->update([
            'sap_file_name'   => $fileName,
            'sap_exported_at' => now(),
            'status_id'       => $exportedStatus->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada por administración y exportada a SAP.',
            'data'    => $expenseRequest->fresh(),
        ]);
    }

    public function rejectByAdmin(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        $expenseRequest = HrRequest::with('status')->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->admin_user_id !== (int) $user->id) {
            return response()->json(['message' => 'No eres el administrador asignado.'], 403);
        }

        $pendingAdminStatus = RequestStatus::where('code', RequestStatus::PENDING_ADMIN_APPROVAL)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingAdminStatus->id) {
            return response()->json(['message' => 'La solicitud no está pendiente de aprobación por administración.'], 409);
        }

        $rejectedStatus = RequestStatus::where('code', RequestStatus::REJECTED)->firstOrFail();

        $expenseRequest->update([
            'status_id'        => $rejectedStatus->id,
            'rejected_at'      => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada por administración.',
            'data'    => $expenseRequest->fresh(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $expenseRequest = HrRequest::with(['status', 'user', 'approver', 'items'])->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->user_id !== (int) $user->id && (int) $expenseRequest->approver_user_id !== (int) $user->id) {
            return response()->json(['message' => 'No tienes permiso para ver esta solicitud.'], 403);
        }

        if ($expenseRequest->type !== HrRequest::TYPE_EXPENSE) {
            return response()->json(['message' => 'La solicitud indicada no es de tipo gasto.'], 422);
        }

        return response()->json([
            'data' => $expenseRequest,
        ]);
    }

    public function myRequests(): JsonResponse
    {
        $requests = HrRequest::with(['status', 'items', 'approver', 'admin'])
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests,
        ]);
    }

    public function latestDraft(): JsonResponse
    {
        $status = RequestStatus::where('code', RequestStatus::PENDING_EMPLOYEE_SIGNATURE)->first();

        $expenseRequest = HrRequest::with(['status', 'items'])
            ->where('user_id', auth()->id())
            ->where('type', HrRequest::TYPE_EXPENSE)
            ->when($status, fn($q) => $q->where('status_id', $status->id))
            ->latest()
            ->first();

        return response()->json([
            'data' => $expenseRequest,
        ]);
    }

    public function addItem(StoreExpenseItemRequest $request, int $id): JsonResponse
    {

        \Log::info('RAW FILES', [
            'files' => array_keys($_FILES),
            'ticket_exists' => isset($_FILES['ticket']),
        ]);
        $expenseRequest = HrRequest::with('status')->findOrFail($id);
        $user = auth()->user();

        if ((int) $expenseRequest->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No tienes permiso para modificar esta solicitud.'], 403);
        }

        if ($expenseRequest->type !== HrRequest::TYPE_EXPENSE) {
            return response()->json(['message' => 'La solicitud indicada no es de tipo gasto.'], 422);
        }

        $pendingEmployeeStatus = RequestStatus::where('code', RequestStatus::PENDING_EMPLOYEE_SIGNATURE)->firstOrFail();

        if ((int) $expenseRequest->status_id !== (int) $pendingEmployeeStatus->id) {
            return response()->json(['message' => 'Solo se pueden añadir líneas a solicitudes pendientes de firma.'], 422);
        }

        $expenseType = $request->input('expense_type');
        $quantity    = $request->input('quantity');
        $amount      = $request->input('amount');
        $unitAmount  = null;

        if ($expenseType === 'kilometraje') {
            $unitAmount = 0.26;
            $amount     = round((float) $quantity * $unitAmount, 2);
        } elseif ($expenseType === 'media_dieta') {
            $unitAmount = 15.00;
            $amount     = round((float) $quantity * $unitAmount, 2);
        } elseif ($expenseType === 'dieta_completa') {
            $unitAmount = 30.00;
            $amount     = round((float) $quantity * $unitAmount, 2);
        } else {
            $quantity   = null;
            $unitAmount = null;
            $amount     = round((float) $amount, 2);
        }

        $ticketPath         = null;
        $ticketOriginalName = null;

        \Log::info('TICKET INFO', [
            'has_file' => $request->hasFile('ticket'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        if ($request->hasFile('ticket')) {
            $file               = $request->file('ticket');
            $ticketPath         = $file->store('expense-tickets', 'public');
            $ticketOriginalName = $file->getClientOriginalName();
        }

        $item = DB::transaction(function () use (
            $expenseRequest, $request, $expenseType,
            $quantity, $unitAmount, $amount,
            $ticketPath, $ticketOriginalName
        ) {
            return RequestItem::create([
                'request_id'          => $expenseRequest->id,
                'expense_type'        => $expenseType,
                'expense_date'        => $request->input('expense_date'),
                'description'         => $request->input('description'),
                'quantity'            => $quantity,
                'unit_amount'         => $unitAmount,
                'amount'              => $amount,
                'is_card_payment'     => $request->boolean('is_card_payment'),
                'ticket_path'         => $ticketPath,
                'ticket_original_name'=> $ticketOriginalName,
            ]);
        });

        return response()->json([
            'message'       => 'Línea de gasto añadida correctamente.',
            'data'          => $item,
            'request_total' => $expenseRequest->items()->sum('amount'),
        ], 201);


    }
}