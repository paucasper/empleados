export default function ApprovalStats({ stats }) {
  const cards = [
    {
      label: "Total",
      value: stats.total,
      className: "bg-white ring-slate-200",
      textClass: "text-slate-900",
    },
    {
      label: "Pendientes",
      value: stats.pending,
      className: "bg-amber-50 ring-amber-200",
      textClass: "text-amber-800",
    },
    {
      label: "Aprobadas",
      value: stats.approved,
      className: "bg-emerald-50 ring-emerald-200",
      textClass: "text-emerald-800",
    },
    {
      label: "Rechazadas",
      value: stats.rejected,
      className: "bg-rose-50 ring-rose-200",
      textClass: "text-rose-800",
    },
  ];

  return (
    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      {cards.map((card) => (
        <div
          key={card.label}
          className={`rounded-2xl p-5 shadow-sm ring-1 ${card.className}`}
        >
          <p className="text-sm font-medium text-slate-600">{card.label}</p>
          <p className={`mt-2 text-3xl font-bold ${card.textClass}`}>
            {card.value}
          </p>
        </div>
      ))}
    </div>
  );
}