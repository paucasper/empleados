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
    public function sendExpenseCreated(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipients = $this->collectEmails([$expense->user?->email, $expense->approver?->email, $expense->admin?->email]);

        if (empty($recipients)) return;

        Mail::to($recipients)->send(new WorkflowNotificationMail(
            'Nueva solicitud de gasto',
            'Nueva solicitud de gasto registrada',
            'Se ha creado una nueva solicitud de gasto en el portal.',
            [
                'Solicitante' => $expense->user?->name ?? '-',
                'Título' => $expense->title ?: $expense->description ?: '-',
                'Estado' => 'Pendiente de firma del responsable',
            ]
        ));
    }

    public function sendExpenseApprovedByAdmin(HrRequest $expense): void
    {
        $expense->loadMissing(['user', 'approver', 'admin', 'status']);
        $recipients = $this->collectEmails([$expense->user?->email, $expense->approver?->email, $expense->admin?->email]);

        if (empty($recipients)) return;

        Mail::to($recipients)->send(new WorkflowNotificationMail(
            'Solicitud de gasto aprobada por administración',
            'La solicitud de gasto ha sido aprobada',
            'La solicitud ha sido aprobada y exportada correctamente a SAP.',
            [
                'Solicitante' => $expense->user?->name ?? '-',
                'Estado' => 'Aprobado y Exportado a SAP',
            ]
        ));
    }

    private function getAbsenceDetails(AbsenceRequest $absence): array
    {
        return [
            'Solicitante'  => $absence->user?->name ?? '-',
            'Firmante'     => $absence->signer?->name ?? '-',
            'Tipo/AWART'   => $absence->awart ?: '-',
            'Fecha Inicio' => $absence->begda instanceof \DateTime ? $absence->begda->format('d/m/Y') : $absence->begda,
            'Fecha Fin'    => $absence->endda instanceof \DateTime ? $absence->endda->format('d/m/Y') : $absence->endda,
            'Estado'       => 'Pendiente de firma del responsable',
        ];
    }

    private function collectEmails(array $emails): array
    {
        return array_values(array_unique(array_filter($emails)));
    }
}