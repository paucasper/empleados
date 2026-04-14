export default function ApprovalBadge({ status, children }) {
  const styles = {
    pending_signer_signature:
      "bg-amber-100 text-amber-800 ring-1 ring-amber-200",
    approved: "bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200",
    exported_to_sap:
      "bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200",
    rejected: "bg-rose-100 text-rose-800 ring-1 ring-rose-200",
  };

  return (
    <span
      className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${
        styles[status] || "bg-slate-100 text-slate-700 ring-1 ring-slate-200"
      }`}
    >
      {children}
    </span>
  );
}