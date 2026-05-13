<?php

namespace App\Services;

use App\Mail\WorkflowNotificationMail;
use App\Models\AbsenceRequest;
use App\Models\HrRequest;
use Illuminate\Support\Facades\Mail;

class WorkflowNotificationService
{
    /**
     * Notifica al empleado que su solicitud ha sido registrada y firmada.
     */
    public function sendAbsenceCreatedToEmployee(AbsenceRequest $absence): void
    {
        $absence->loadMissing(['user', 'signer']);
        $recipient = $absence->user?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de ausencia registrada',
            'Tu solicitud de ausencia ha sido registrada correctamente',
            'Tu solicitud se ha creado y firmado. Ahora queda pendiente de la revisión del firmante asignado.',
            $this->getAbsenceDetails($absence)
        ));
    }

    /**
     * Notifica al firmante que tiene una nueva solicitud pendiente.
     */
    public function sendAbsenceCreatedToSigner(AbsenceRequest $absence): void
    {
        $absence->loadMissing(['user', 'signer']);
        $recipient = $absence->signer?->email;

        \Log::info('ENVIO CORREO FIRMANTE AUSENCIA', [
            'absence_id' => $absence->id,
            'signer_id' => $absence->signer?->id,
            'signer_name' => $absence->signer?->name,
            'signer_email' => $recipient,
        ]);

        if (!$recipient) {
            return;
        }

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Nueva solicitud de ausencia pendiente de firma',
            'Tienes una solicitud de ausencia para revisar',
            'Se ha registrado una nueva solicitud de ausencia que requiere tu firma.',
            [
                'Solicitante'  => $absence->user?->name ?? '-',
                'Firmante'     => $absence->signer?->name ?? '-',
                'Tipo/AWART'   => $absence->awart ?: '-',
                'Fecha Inicio' => $absence->begda instanceof \DateTime ? $absence->begda->format('d/m/Y') : $absence->begda,
                'Fecha Fin'    => $absence->endda instanceof \DateTime ? $absence->endda->format('d/m/Y') : $absence->endda,
                'Estado'       => 'Pendiente de firma del responsable',
            ]
        ));
    }

    /**
     * Notifica la aprobación y exportación.
     */
    public function sendAbsenceApproved(AbsenceRequest $absence): void
    {
        $absence->loadMissing(['user', 'signer']);
        $recipient = $absence->user?->email;

        if (!$recipient) return;

        $details = $this->getAbsenceDetails($absence);
        $details['Estado'] = 'Aprobada y Exportada a SAP';

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de ausencia aprobada',
            'La solicitud de ausencia ha sido aprobada y exportada',
            'La solicitud ha sido firmada por el responsable y los datos se han enviado a SAP.',
            $details
        ));
    }

    public function sendAbsenceRejected(AbsenceRequest $absence): void
    {
        $absence->loadMissing(['user', 'signer']);
        $recipient = $absence->user?->email;

        if (!$recipient) return;

        $details = $this->getAbsenceDetails($absence);
        $details['Estado'] = 'Rechazada';
        $details['Motivo rechazo'] = $absence->rejection_reason ?: '-';

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de ausencia rechazada',
            'Tu solicitud de ausencia ha sido rechazada',
            'La solicitud ha sido revisada por el firmante y ha sido rechazada.',
            $details
        ));
    }

    /**
     * GASTOS
     */

    public function sendExpenseSubmittedToEmployee(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->user?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de gasto enviada',
            'Tu solicitud de gasto ha sido enviada correctamente',
            'Tu solicitud se ha registrado y ha quedado pendiente de revisión por el firmante asignado.',
            [
                'Solicitante'    => $expense->user?->name ?? '-',
                'Firmante'       => $expense->approver?->name ?? '-',
                'Administración' => $expense->admin?->name ?? '-',
                'Título'         => $expense->title ?: $expense->description ?: '-',
                'Estado'         => 'Pendiente de firma del responsable',
            ]
        ));
    }

    public function sendExpenseSubmittedToApprover(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->approver?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Nueva solicitud de gasto pendiente de aprobación',
            'Tienes una solicitud de gasto pendiente',
            'Se ha registrado una nueva solicitud de gasto que requiere tu revisión y aprobación.',
            [
                'Solicitante'    => $expense->user?->name ?? '-',
                'Título'         => $expense->title ?: $expense->description ?: '-',
                'Administración' => $expense->admin?->name ?? '-',
                'Estado'         => 'Pendiente de firma del responsable',
            ]
        ));
    }

    public function sendExpenseApprovedByApproverToAdmin(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->admin?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de gasto pendiente de aprobación por administración',
            'Tienes una solicitud de gasto pendiente de revisión',
            'La solicitud ha sido aprobada por el responsable y está pendiente de aprobación por administración.',
            [
                'Solicitante' => $expense->user?->name ?? '-',
                'Firmante'    => $expense->approver?->name ?? '-',
                'Título'      => $expense->title ?: $expense->description ?: '-',
                'Estado'      => $expense->status?->name ?? 'Pendiente de aprobación por administración',
            ]
        ));
    }

    public function sendExpenseApprovedByAdminToEmployee(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->user?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de gasto aprobada',
            'Tu solicitud de gasto ha sido aprobada',
            'La solicitud ha sido aprobada por administración y exportada correctamente a SAP.',
            [
                'Solicitante'    => $expense->user?->name ?? '-',
                'Firmante'       => $expense->approver?->name ?? '-',
                'Administración' => $expense->admin?->name ?? '-',
                'Título'         => $expense->title ?: $expense->description ?: '-',
                'Estado'         => 'Aprobado y exportado a SAP',
            ]
        ));
    }

    public function sendExpenseRejectedByApproverToEmployee(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->user?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de gasto rechazada',
            'Tu solicitud de gasto ha sido rechazada por el responsable',
            'La solicitud de gasto ha sido revisada por el responsable y ha sido rechazada.',
            [
                'Solicitante'      => $expense->user?->name ?? '-',
                'Firmante'         => $expense->approver?->name ?? '-',
                'Administración'   => $expense->admin?->name ?? '-',
                'Título'           => $expense->title ?: $expense->description ?: '-',
                'Estado'           => 'Rechazada',
                'Motivo rechazo'   => $expense->rejection_reason ?: '-',
            ]
        ));
    }

    public function sendExpenseRejectedByAdminToEmployee(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipient = $expense->user?->email;

        if (!$recipient) return;

        Mail::to($recipient)->send(new WorkflowNotificationMail(
            'Solicitud de gasto rechazada por administración',
            'Tu solicitud de gasto ha sido rechazada por administración',
            'La solicitud de gasto ha sido revisada por administración y ha sido rechazada.',
            [
                'Solicitante'      => $expense->user?->name ?? '-',
                'Firmante'         => $expense->approver?->name ?? '-',
                'Administración'   => $expense->admin?->name ?? '-',
                'Título'           => $expense->title ?: $expense->description ?: '-',
                'Estado'           => 'Rechazada',
                'Motivo rechazo'   => $expense->rejection_reason ?: '-',
            ]
        ));
    }

    private function collectEmails(array $emails): array
    {
        return array_values(array_unique(array_filter($emails)));
    }
}