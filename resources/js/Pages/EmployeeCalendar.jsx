import React, { useEffect, useMemo, useState } from "react";
import {
    ChevronLeft,
    ChevronRight,
    Calendar as CalendarIcon,
    Info,
} from "lucide-react";
import api from "../services/api";

// --- HELPERS ---
const parseLocalDate = (dateString) => {
    if (!dateString) return null;
    const [year, month, day] = dateString.split("-").map(Number);
    return new Date(year, month - 1, day, 12, 0, 0);
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    return parseLocalDate(dateString).toLocaleDateString("es-ES", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const toIsoDate = (date) => {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
};

const expandRange = (startDate, endDate, texto, subtipo) => {
    const result = [];
    const start = parseLocalDate(startDate);
    const end = parseLocalDate(endDate);

    if (!start || !end) return result;

    const current = new Date(start);

    while (current <= end) {
        result.push({
            date: toIsoDate(current),
            texto,
            subtipo,
            fechaInicio: startDate,
            fechaFin: endDate,
        });

        current.setDate(current.getDate() + 1);
    }

    return result;
};

const getMonthDays = (year, month) => {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];
    const startWeekDay = (firstDay.getDay() + 6) % 7; // lunes inicio

    for (let i = 0; i < startWeekDay; i++) {
        days.push(null);
    }

    for (let day = 1; day <= lastDay.getDate(); day++) {
        const mm = String(month + 1).padStart(2, "0");
        const dd = String(day).padStart(2, "0");
        days.push(`${year}-${mm}-${dd}`);
    }

    return days;
};

const getTypeStyles = (texto, mini = false) => {
    const val = (texto || "").toLowerCase();

    if (val.includes("vacaciones")) {
        return mini
            ? "bg-[#c5a35d] text-white"
            : "bg-[#f2f4ed] text-[#2f4a27] border-[#d6ddcb]";
    }

    if (val.includes("festivo")) {
        return mini
            ? "bg-red-400 text-white"
            : "bg-red-50 text-red-700 border-red-200";
    }

    return mini
        ? "bg-[#d9c08a] text-[#2f4a27]"
        : "bg-[#f8f2e7] text-[#9a6b17] border-[#ead7aa]";
};

export default function EmployeeCalendar({ pernr }) {
    const today = new Date();

    const [year, setYear] = useState(today.getFullYear());
    const [month, setMonth] = useState(today.getMonth());
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [selectedDay, setSelectedDay] = useState(toIsoDate(today));
    const [viewMode, setViewMode] = useState("month");

    useEffect(() => {
        if (!pernr) return;

        const fetchCalendar = async () => {
            try {
                setLoading(true);
                setError("");

                const res = await api.get(`/employees/${pernr}/calendar/${year}`);
                const rawData = Array.isArray(res.data) ? res.data : [];

                setRows(
                    rawData.map((item) => ({
                        subtipo: item.subtipo ?? item.Subtipo ?? item.SUBTY ?? "",
                        texto: item.texto ?? item.Texto ?? item.ATEXT ?? "",
                        fechaInicio:
                            item.fechaInicio ?? item.FechaInicio ?? item.BEGDA ?? "",
                        fechaFin:
                            item.fechaFin ?? item.FechaFin ?? item.ENDDA ?? "",
                    }))
                );
            } catch (err) {
                setError("Error al cargar datos.");
            } finally {
                setLoading(false);
            }
        };

        fetchCalendar();
    }, [pernr, year]);

    useEffect(() => {
        if (selectedDay) {
            const selected = parseLocalDate(selectedDay);
            if (selected && selected.getFullYear() !== year) {
                setSelectedDay(null);
            }
        }
    }, [year, selectedDay]);

    const dayMap = useMemo(() => {
        const map = {};

        rows.forEach((item) => {
            expandRange(item.fechaInicio, item.fechaFin, item.texto, item.subtipo).forEach(
                (d) => {
                    if (!map[d.date]) {
                        map[d.date] = [];
                    }
                    map[d.date].push(d);
                }
            );
        });

        return map;
    }, [rows]);

    const selectedItems = selectedDay ? dayMap[selectedDay] || [] : [];

    const handlePrev = () => {
        if (viewMode === "month") {
            if (month === 0) {
                setMonth(11);
                setYear((y) => y - 1);
            } else {
                setMonth((m) => m - 1);
            }
        } else {
            setYear((y) => y - 1);
        }
    };

    const handleNext = () => {
        if (viewMode === "month") {
            if (month === 11) {
                setMonth(0);
                setYear((y) => y + 1);
            } else {
                setMonth((m) => m + 1);
            }
        } else {
            setYear((y) => y + 1);
        }
    };

    if (loading) {
        return (
            <div className="rounded-[2rem] border border-[#e7eadf] bg-white p-10 text-center text-[#6b7280] shadow-sm">
                Cargando...
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-7xl space-y-6 p-4">
            <div className="rounded-[2rem] border border-[#e7eadf] bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-4">
                        <div className="rounded-[1.25rem] bg-[#f2f4ed] p-3">
                            <CalendarIcon className="text-[#2f4a27]" size={28} />
                        </div>

                        <div>
                            <h1 className="text-2xl font-bold text-[#2f4a27]">
                                Calendario de Ausencias
                            </h1>

                            <div className="mt-1 flex flex-wrap items-center gap-4 text-sm text-[#6b7280]">
                                <span>
                                    Empleado:{" "}
                                    <span className="font-medium text-[#425346]">
                                        {pernr}
                                    </span>
                                </span>

                                <div className="flex items-center gap-2">
                                    <label htmlFor="calendar-year">Año:</label>
                                    <select
                                        id="calendar-year"
                                        value={year}
                                        onChange={(e) => setYear(Number(e.target.value))}
                                        className="rounded-xl border border-[#d6ddcb] bg-white px-6 py-2 text-sm font-medium text-[#2f4a27] outline-none transition hover:bg-[#f8faf5] focus:border-[#c5a35d]"
                                    >
                                        {Array.from({ length: 7 }, (_, i) => {
                                            const currentYear = new Date().getFullYear();
                                            const optionYear = currentYear - 3 + i;
                                            return (
                                                <option key={optionYear} value={optionYear}>
                                                    {optionYear}
                                                </option>
                                            );
                                        })}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div className="flex rounded-xl bg-[#f8faf5] p-1">
                            <button
                                onClick={() => setViewMode("month")}
                                className={`rounded-lg px-4 py-2 text-xs font-bold tracking-wide transition ${
                                    viewMode === "month"
                                        ? "bg-white text-[#2f4a27] shadow-sm"
                                        : "text-[#6b7280]"
                                }`}
                            >
                                MES
                            </button>

                            <button
                                onClick={() => setViewMode("year")}
                                className={`rounded-lg px-4 py-2 text-xs font-bold tracking-wide transition ${
                                    viewMode === "year"
                                        ? "bg-white text-[#2f4a27] shadow-sm"
                                        : "text-[#6b7280]"
                                }`}
                            >
                                AÑO
                            </button>
                        </div>

                        <div className="flex gap-2">
                            <button
                                onClick={handlePrev}
                                className="rounded-xl border border-[#d6ddcb] bg-white p-2 text-[#2f4a27] transition hover:bg-[#f8faf5]"
                            >
                                <ChevronLeft size={18} />
                            </button>

                            <button
                                onClick={handleNext}
                                className="rounded-xl border border-[#d6ddcb] bg-white p-2 text-[#2f4a27] transition hover:bg-[#f8faf5]"
                            >
                                <ChevronRight size={18} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {error && (
                <div className="rounded-[1.5rem] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            )}

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div className="lg:col-span-8">
                    {viewMode === "month" ? (
                        <div className="rounded-[2rem] border border-[#e7eadf] bg-white p-6 shadow-sm">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-lg font-bold capitalize text-[#2f4a27]">
                                    {new Date(year, month).toLocaleDateString("es-ES", {
                                        month: "long",
                                        year: "numeric",
                                    })}
                                </h2>

                                <div className="flex flex-wrap gap-2 text-xs">
                                    <span className="rounded-full border border-[#d6ddcb] bg-[#f2f4ed] px-3 py-1 font-medium text-[#2f4a27]">
                                        Vacaciones
                                    </span>
                                    <span className="rounded-full border border-red-200 bg-red-50 px-3 py-1 font-medium text-red-700">
                                        Festivo
                                    </span>
                                    <span className="rounded-full border border-[#ead7aa] bg-[#f8f2e7] px-3 py-1 font-medium text-[#9a6b17]">
                                        Otras ausencias
                                    </span>
                                </div>
                            </div>

                            <div className="grid grid-cols-7 gap-2">
                                {["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"].map((d) => (
                                    <div
                                        key={d}
                                        className="pb-2 text-center text-[10px] font-bold uppercase tracking-[0.18em] text-[#9ca3af]"
                                    >
                                        {d}
                                    </div>
                                ))}

                                {getMonthDays(year, month).map((date, i) => {
                                    if (!date) {
                                        return (
                                            <div
                                                key={i}
                                                className="h-24 rounded-[1rem] bg-[#f8faf5]"
                                            />
                                        );
                                    }

                                    const items = dayMap[date] || [];
                                    const isSelected = selectedDay === date;

                                    return (
                                        <button
                                            key={date}
                                            onClick={() => setSelectedDay(date)}
                                            className={`h-24 rounded-[1rem] border p-3 text-left transition ${
                                                isSelected
                                                    ? "border-[#c5a35d] bg-[#fcfcf9] shadow-sm"
                                                    : "border-[#eef1e8] bg-white hover:bg-[#f8faf5]"
                                            }`}
                                        >
                                            <span className="text-sm font-semibold text-[#2f4a27]">
                                                {Number(date.split("-")[2])}
                                            </span>

                                            <div className="mt-2 flex flex-col gap-1">
                                                {items.slice(0, 2).map((it, idx) => (
                                                    <div
                                                        key={idx}
                                                        className={`h-2 w-full rounded-full ${getTypeStyles(
                                                            it.texto,
                                                            true
                                                        )}`}
                                                    />
                                                ))}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                            {Array.from({ length: 12 }).map((_, mIndex) => (
                                <div
                                    key={mIndex}
                                    className="rounded-[1.5rem] border border-[#e7eadf] bg-white p-4 shadow-sm"
                                >
                                    <h3 className="mb-3 text-center text-xs font-bold uppercase tracking-[0.18em] text-[#9ca3af]">
                                        {new Date(year, mIndex).toLocaleDateString("es-ES", {
                                            month: "short",
                                        })}
                                    </h3>

                                    <div className="grid grid-cols-7 gap-1">
                                        {getMonthDays(year, mIndex).map((date, di) => {
                                            if (!date) {
                                                return <div key={di} className="h-7 w-7" />;
                                            }

                                            const items = dayMap[date] || [];

                                            return (
                                                <button
                                                    key={date}
                                                    onClick={() => {
                                                        setMonth(mIndex);
                                                        setSelectedDay(date);
                                                        setViewMode("month");
                                                    }}
                                                    className={`flex h-7 w-7 items-center justify-center rounded-md text-[10px] font-bold transition ${
                                                        items.length > 0
                                                            ? `${getTypeStyles(items[0].texto, true)}`
                                                            : "text-[#6b7280] hover:bg-[#f8faf5]"
                                                    }`}
                                                >
                                                    {Number(date.split("-")[2])}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="space-y-4 lg:col-span-4">
                    <div className="relative overflow-hidden rounded-[2rem] bg-[#2f4a27] p-6 text-white shadow-[0_20px_50px_-20px_rgba(47,74,39,0.4)]">
                        <p className="text-[11px] font-bold uppercase tracking-[0.22em] text-white/60">
                            Detalle del día
                        </p>
                        <p className="mt-2 text-xl font-semibold">
                            {selectedDay ? formatDate(selectedDay) : "Selecciona un día"}
                        </p>
                        <CalendarIcon className="absolute -bottom-5 -right-5 h-24 w-24 rotate-12 text-white/10" />
                    </div>

                    <div className="min-h-[320px] rounded-[2rem] border border-[#e7eadf] bg-white p-6 shadow-sm">
                        {!selectedDay || selectedItems.length === 0 ? (
                            <div className="flex h-full flex-col items-center justify-center py-10 text-center">
                                <div className="mb-3 rounded-2xl bg-[#f8faf5] p-3 text-[#6b7280]">
                                    <Info size={30} />
                                </div>
                                <p className="text-sm font-medium text-[#6b7280]">
                                    Sin ausencias registradas
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {selectedItems.map((item, idx) => (
                                    <div
                                        key={idx}
                                        className={`rounded-[1.25rem] border p-4 ${getTypeStyles(
                                            item.texto
                                        )}`}
                                    >
                                        <div className="mb-2 flex items-center justify-between gap-3">
                                            <span className="text-sm font-bold">
                                                {item.texto}
                                            </span>
                                            <span className="rounded-md bg-white/60 px-2 py-1 text-[10px] font-semibold">
                                                {item.subtipo}
                                            </span>
                                        </div>

                                        <p className="text-[11px] uppercase tracking-[0.12em] opacity-80">
                                            {formatDate(item.fechaInicio)} · {formatDate(item.fechaFin)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}