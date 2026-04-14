import ApprovalBadge from "./ApprovalBadge";

const STATUS_LABELS = {
  pending_signer_signature: "Pendiente",
  approved: "Aprobada",
  rejected: "Rechazada",
  exported_to_sap: "Exportada a SAP",
};

export default function ApprovalDetailModal({
  item,
  onClose,
  onApprove,
  onReject,
  processingId,
}) {
  if (!item) return null;

  const isPending = item.status === "pending_signer_signature";
  const isProcessing = processingId === item.id;

  function formatDate(date) {
    if (!date) return "-";
    return new Intl.DateTimeFormat("es-ES", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    }).format(new Date(date));
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
      <div className="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
        <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4">
          <div>
            <h2 className="text-lg font-bold text-slate-900">
              Detalle de solicitud
            </h2>
            <p className="text-sm text-slate-500">
              Revisa la información antes de aprobar o rechazar.
            </p>
          </div>

          <button
            onClick={onClose}
            className="rounded-lg px-3 py-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
          >
            ✕
          </button>
        </div>

        <div className="space-y-5 px-6 py-5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p className="text-sm text-slate-500">Empleado</p>
              <p className="text-base font-semibold text-slate-900">
                {item.user?.name || item.employee_name || "Empleado"}
              </p>
            </div>

            <ApprovalBadge status={item.status}>
              {STATUS_LABELS[item.status] || item.status}
            </ApprovalBadge>
          </div>

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">SAP Employee ID</p>
              <p className="mt-1 font-medium text-slate-900">
                {item.sap_employee_id || "-"}
              </p>
            </div>

            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">Tipo de ausencia</p>
              <p className="mt-1 font-medium text-slate-900">
                {item.awart || "-"}
              </p>
            </div>

            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">Fecha inicio</p>
              <p className="mt-1 font-medium text-slate-900">
                {formatDate(item.begda)}
              </p>
            </div>

            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">Fecha fin</p>
              <p className="mt-1 font-medium text-slate-900">
                {formatDate(item.endda)}
              </p>
            </div>
          </div>

          <div className="rounded-xl bg-slate-50 p-4">
            <p className="text-sm text-slate-500">Motivo</p>
            <p className="mt-1 whitespace-pre-line text-slate-900">
              {item.reason || "-"}
            </p>
          </div>

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">Firma empleado</p>
              <p className="mt-1 font-medium text-slate-900">
                {item.employee_signed_at
                  ? formatDate(item.employee_signed_at)
                  : "Pendiente"}
              </p>
            </div>

            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-sm text-slate-500">Firma firmante</p>
              <p className="mt-1 font-medium text-slate-900">
                {item.signer_signed_at
                  ? formatDate(item.signer_signed_at)
                  : "Pendiente"}
              </p>
            </div>
          </div>
        </div>

        <div className="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-6 py-4">
          <button
            onClick={onClose}
            className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
          >
            Cerrar
          </button>

          {isPending && (
            <>
              <button
                onClick={() => onReject(item.id)}
                disabled={isProcessing}
                className="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {isProcessing ? "Procesando..." : "Rechazar"}
              </button>

              <button
                onClick={() => onApprove(item.id)}
                disabled={isProcessing}
                className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {isProcessing ? "Procesando..." : "Aprobar"}
              </button>
            </>
          )}
        </div>
      </div>
    </div>
  );
}