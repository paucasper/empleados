<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Services\SapFileService;
use Illuminate\Http\Request;
use App\Services\WorkflowNotificationService;
use Carbon\Carbon;

class AbsenceRequestController extends Controller
{
public function store(Request $request)
    {
        $validated = $request->validate([
            'awart' => ['required', 'string'],
            'begda' => ['required', 'date'],
            'endda' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'signer_pernr' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (empty($user->sap_employee_id)) {
            return response()->json(['success' => false, 'message' => 'Usuario sin sap_employee_id.'], 422);
        }

        $signerPernr = str_pad(trim($validated['signer_pernr']), 8, '0', STR_PAD_LEFT);
        $signer = \App\Models\User::where('sap_employee_id', $signerPernr)->first();

        if (!$signer) {
            return response()->json(['success' => false, 'message' => 'Firmante no encontrado.'], 422);
        }

        $absenceRequest = AbsenceRequest::create([
            'user_id' => $user->id,
            'signer_user_id' => $signer->id,
            'sap_employee_id' => $user->sap_employee_id,
            'awart' => $validated['awart'],
            'begda' => $validated['begda'],
            'endda' => $validated['endda'],
            'description' => $validated['description'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'location' => $validated['location'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'pending_signer_signature',
            'employee_signed_at' => now(), 
        ]);

        try {
            $service = app(WorkflowNotificationService::class);
            $service->sendAbsenceCreatedToEmployee($absenceRequest->fresh());
            $service->sendAbsenceCreatedToSigner($absenceRequest->fresh());
        } catch (\Exception $e) {
            \Log::error("Error avisos: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $absenceRequest], 201);
    }

    public function signBySigner(AbsenceRequest $absenceRequest, SapFileService $sapFileService)
    {
        $user = auth()->user();

        abort_unless($absenceRequest->signer_user_id === $user->id, 403);
        abort_unless($absenceRequest->status === 'pending_signer_signature', 409);

        // Aseguramos que las fechas sean objetos Carbon para poder usar format()
        $begda = Carbon::parse($absenceRequest->begda);
        $endda = Carbon::parse($absenceRequest->endda);

        $content = $sapFileService->generateAbsenceFile([
            'awart' => $absenceRequest->awart,
            'pernr' => $absenceRequest->sap_employee_id,
            'begda' => $begda->format('Y-m-d'),
            'endda' => $endda->format('Y-m-d'),
        ]);

        $fileName = $sapFileService->generateFileName($absenceRequest->sap_employee_id);
        $sapFileService->saveFile($content, $fileName);

        $absenceRequest->update([
            'signer_signed_at' => now(),
            'sap_file_name' => $fileName,
            'sap_exported_at' => now(),
            'status' => 'exported_to_sap',
        ]);

        try {
            app(WorkflowNotificationService::class)
                ->sendAbsenceApproved($absenceRequest->fresh());
        } catch (\Throwable $e) {
            \Log::error('Error enviando correo de ausencia aprobada', [
                'absence_request_id' => $absenceRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Exportado a SAP.']);
    }

    public function signByEmployee(AbsenceRequest $absenceRequest)
    {
        $user = auth()->user();

        // Validaciones de seguridad
        abort_unless($absenceRequest->user_id === $user->id, 403, 'No puedes firmar esta solicitud.');
        abort_unless($absenceRequest->status === 'pending_employee_signature', 409, 'La solicitud no está pendiente de tu firma.');

        $absenceRequest->update([
            'employee_signed_at' => now(),
            'status' => 'pending_signer_signature',
        ]);

        // Notificar al firmante que ya puede firmar
        try {
            app(WorkflowNotificationService::class)
                ->sendAbsenceCreatedToSigner($absenceRequest->fresh());
        } catch (\Throwable $e) {
            \Log::error("Error al notificar firma de empleado: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud firmada correctamente.',
            'data' => $absenceRequest->fresh()
        ]);
    }

    public function rejectBySigner(Request $request, AbsenceRequest $absenceRequest)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        $user = auth()->user();

        abort_unless($absenceRequest->signer_user_id === $user->id, 403, 'No eres el firmante asignado.');
        abort_unless($absenceRequest->status === 'pending_signer_signature', 409, 'La solicitud no está pendiente de firma del firmante.');

        $absenceRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        try {
            app(WorkflowNotificationService::class)
                ->sendAbsenceRejected($absenceRequest->fresh());
        } catch (\Throwable $e) {
            \Log::error('Error enviando correo de ausencia rechazada', [
                'absence_request_id' => $absenceRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada.',
            'data' => $absenceRequest->fresh(),
        ]);
    }

    public function myRequests()
    {
        $user = auth()->user();

        $requests = AbsenceRequest::with(['signer'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function pendingForSigner()
    {
        $user = auth()->user();

        $requests = AbsenceRequest::where('signer_user_id', $user->id)
            ->where('status', 'pending_signer_signature')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }
}