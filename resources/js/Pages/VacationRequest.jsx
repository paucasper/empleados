import React, { useEffect, useState } from "react";
import api from "../services/api";
import "../css/vacation-request.css";
import absenceRequestApi from "../services/absenceRequestApi";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";


export default function VacationRequest({ pernr }) {
    const [employee, setEmployee] = useState(null);
    const [absenceTypes, setAbsenceTypes] = useState([]);
    const [requestId, setRequestId] = useState(null);
    const [submitMessage, setSubmitMessage] = useState("");
    const [loading, setLoading] = useState(true);

    const [form, setForm] = useState({
        solicitante: "",
        numeroEmpleado: "",
        identificador: "",
        contingentesDisponibles: "...",
        descripcion: "",
        firmante: "",
        telefono: "",
        diasSolicitados: 0,
        estado: "Sin especificar",
        fechaDesde: "",
        fechaHasta: "",
        motivo: "",
        comentario: "",
        localizacion: "",
    });

    const [summary] = useState({
        totalVacaciones: 21,
        disponiblesVacaciones: 21,
        concedidosVacaciones: 0,
        tramiteVacaciones: 0,
        totalAsuntos: 2,
        disponiblesAsuntos: 2,
        concedidosAsuntos: 0,
        tramiteAsuntos: 0,
        rango: "12/01/2026 - 31/01/2027",
    });

    useEffect(() => {
        if (!pernr) return;

        const fetchData = async () => {
            try {
                setLoading(true);

                const employeeRes = await api.get(`/employees/${pernr}`);
                const employeeData =
                    employeeRes.data.employee ??
                    employeeRes.data.data ??
                    employeeRes.data;

                setEmployee(employeeData);

                setForm((prev) => ({
                    ...prev,
                    solicitante: employeeData.nombreCompleto || employeeData.name || "",
                    numeroEmpleado: employeeData.pernr || pernr || "",
                    identificador: employeeData.identificador || employeeData.nif || "",
                    firmante: employeeData.firmante || employeeData.nombreFirmante || "",
                    telefono: employeeData.telefono || employeeData.phone || "",
                    localizacion: employeeData.localizacion || employeeData.location || "",
                }));

                const typesRes = await api.get(`/employees/${pernr}/absence-types`);
                const typesData = typesRes.data.data ?? typesRes.data ?? [];
                setAbsenceTypes(typesData);
            } catch (error) {
                console.error("Error cargando datos:", error);
                setSubmitMessage("Error cargando los datos del empleado.");
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [pernr]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleCheck = async () => {
        if (!form.fechaDesde || !form.fechaHasta || !form.motivo) {
            setSubmitMessage("Debes indicar fecha desde, fecha hasta y motivo.");
            return;
        }

        try {
            setSubmitMessage("");

            const res = await api.post("/vacations/check", {
                pernr: form.numeroEmpleado,
                begda: form.fechaDesde,
                endda: form.fechaHasta,
                awart: form.motivo,
            });

            setForm((prev) => ({
                ...prev,
                diasSolicitados: res.data.dias ?? 0,
            }));

            setSubmitMessage("Días calculados correctamente.");
        } catch (error) {
            console.error("Error calculando días:", error);
            setSubmitMessage("Error calculando los días solicitados.");
        }
    };

    const parseDate = (value) => {
        if (!value) return null;
        const [year, month, day] = value.split("-");
        return new Date(Number(year), Number(month) - 1, Number(day));
    };

    const formatDate = (date) => {
        if (!date) return "";
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    };

    const handleValidate = async () => {
        if (!form.fechaDesde || !form.fechaHasta || !form.motivo) {
            setSubmitMessage("Debes indicar fecha desde, fecha hasta y motivo.");
            return;
        }

        try {
            setSubmitMessage("");

            const res = await api.post("/vacations/validate", {
                pernr: form.numeroEmpleado,
                begda: form.fechaDesde,
                endda: form.fechaHasta,
                awart: form.motivo,
            });

            setForm((prev) => ({
                ...prev,
                estado: res.data.valido ? "Válido" : `No válido (${res.data.subrc})`,
            }));

            setSubmitMessage(
                res.data.valido
                    ? "Solicitud validada correctamente."
                    : `Solicitud no válida (${res.data.subrc}).`
            );
        } catch (error) {
            console.error("Error validando:", error);
            setForm((prev) => ({
                ...prev,
                estado: "Error al validar",
            }));
            setSubmitMessage("Error al validar la solicitud.");
        }
    };

    const normalizeDateForApi = (value) => {
        if (!value) return "";

        if (value.includes("-")) return value;

        if (value.includes("/")) {
            const [day, month, year] = value.split("/");
            return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
        }

        return value;
    };

    const handleCreateRequest = async () => {
        try {
            setSubmitMessage("");

            const payload = {
                awart: form.motivo,
                begda: normalizeDateForApi(form.fechaDesde),
                endda: normalizeDateForApi(form.fechaHasta),
                description: form.descripcion,
                comment: form.comentario,
                location: form.localizacion,
                phone: form.telefono,
                signer_pernr: form.firmante,
            };

            const response = await absenceRequestApi.create(payload);

            setRequestId(response.data.data.id);
            setSubmitMessage("Solicitud creada correctamente. Ahora debes firmarla.");
        } catch (error) {
            console.error("Error al crear solicitud:", error);
            console.error("Respuesta backend:", error?.response?.data);

            const backendMessage =
                error?.response?.data?.error ||
                error?.response?.data?.message ||
                JSON.stringify(error?.response?.data?.errors) ||
                "Error al crear la solicitud.";

            setSubmitMessage(backendMessage);
        }
    };

    const handleSignEmployee = async () => {
        if (!requestId) {
            setSubmitMessage("Primero debes guardar la solicitud.");
            return;
        }

        try {
            setSubmitMessage("");
            await absenceRequestApi.signByEmployee(requestId);
            setSubmitMessage("Solicitud firmada por el empleado y enviada al firmante.");
        } catch (error) {
            console.error(error);
            setSubmitMessage("Error al firmar la solicitud.");
        }
    };

    const messageClass =
        submitMessage.toLowerCase().includes("error") ||
        submitMessage.toLowerCase().includes("no válida")
            ? "vr-message vr-message-error"
            : "vr-message vr-message-success";

    if (loading) {
        return (
            <div className="vr-loading-card">
                <div className="vr-loading-dot"></div>
                <p>Cargando datos del trámite...</p>
            </div>
        );
    }

    return (
        <div className="vr-page">
            <section className="vr-hero">
                <div className="vr-hero-content">
                    <div className="vr-badge">NUEVO TRÁMITE</div>
                    <h1>Solicitud de ausencia</h1>
                    <p>
                        Completa la solicitud, revisa disponibilidad, valida la información
                        y envíala a firma desde una experiencia más clara y moderna.
                    </p>

                    <div className="vr-hero-meta">
                        <div>
                            <span>Empleado</span>
                            <strong>{employee?.nombreCompleto || employee?.name || form.solicitante || "-"}</strong>
                        </div>
                        <div>
                            <span>ID SAP</span>
                            <strong>{form.numeroEmpleado || "-"}</strong>
                        </div>
                    </div>
                </div>

                <div className="vr-hero-side">
                    <div className="vr-side-card">
                        <span>Periodo</span>
                        <strong>{summary.rango}</strong>
                        <p>Resumen disponible para vacaciones y asuntos propios.</p>
                    </div>
                </div>
            </section>

            <section className="vr-summary-grid">
                <div className="vr-summary-card">
                    <span>Total vacaciones</span>
                    <strong>{summary.totalVacaciones}</strong>
                </div>

                <div className="vr-summary-card">
                    <span>Disponibles</span>
                    <strong>{summary.disponiblesVacaciones}</strong>
                </div>

                <div className="vr-summary-card">
                    <span>Concedidos</span>
                    <strong>{summary.concedidosVacaciones}</strong>
                </div>

                <div className="vr-summary-card">
                    <span>En trámite</span>
                    <strong>{summary.tramiteVacaciones}</strong>
                </div>
            </section>

            <section className="vr-card">
                <div className="vr-card-header">
                    <div>
                        <h2>Datos del solicitante</h2>
                        <p>Información principal del empleado y datos generales del trámite.</p>
                    </div>
                </div>

                <div className="vr-grid vr-grid-2">
                    <div className="vr-field">
                        <label>Solicitante</label>
                        <input type="text" name="solicitante" value={form.solicitante} readOnly />
                    </div>

                    <div className="vr-field">
                        <label>Nº Empleado</label>
                        <input type="text" name="numeroEmpleado" value={form.numeroEmpleado} readOnly />
                    </div>

                    <div className="vr-field">
                        <label>Identificador</label>
                        <input
                            type="text"
                            name="identificador"
                            value={form.identificador}
                            onChange={handleChange}
                        />
                    </div>

                    <div className="vr-field">
                        <label>Contingentes disponibles</label>
                        <input
                            type="text"
                            name="contingentesDisponibles"
                            value={form.contingentesDisponibles}
                            onChange={handleChange}
                        />
                    </div>
                </div>
            </section>

            <section className="vr-card">
                <div className="vr-card-header">
                    <div>
                        <h2>Balance de días</h2>
                        <p>Disponibilidad actual para vacaciones y asuntos propios.</p>
                    </div>
                </div>

                <div className="vr-balance-grid">
                    <div className="vr-balance-column">
                        <div className="vr-balance-item"><span>Total vacaciones</span><strong>{summary.totalVacaciones}</strong></div>
                        <div className="vr-balance-item"><span>Disponibles</span><strong>{summary.disponiblesVacaciones}</strong></div>
                        <div className="vr-balance-item"><span>Concedidos</span><strong>{summary.concedidosVacaciones}</strong></div>
                        <div className="vr-balance-item"><span>En trámite</span><strong>{summary.tramiteVacaciones}</strong></div>
                    </div>

                    <div className="vr-balance-column">
                        <div className="vr-balance-item"><span>Total asuntos propios</span><strong>{summary.totalAsuntos}</strong></div>
                        <div className="vr-balance-item"><span>Disponibles</span><strong>{summary.disponiblesAsuntos}</strong></div>
                        <div className="vr-balance-item"><span>Concedidos</span><strong>{summary.concedidosAsuntos}</strong></div>
                        <div className="vr-balance-item"><span>En trámite</span><strong>{summary.tramiteAsuntos}</strong></div>
                    </div>
                </div>
            </section>

            <section className="vr-card">
                <div className="vr-card-header">
                    <div>
                        <h2>Detalle de la solicitud</h2>
                        <p>Información complementaria que acompañará a la solicitud.</p>
                    </div>
                </div>

                <div className="vr-grid vr-grid-2">
                    <div className="vr-field">
                        <label>Descripción (*)</label>
                        <input
                            type="text"
                            name="descripcion"
                            value={form.descripcion}
                            onChange={handleChange}
                            placeholder="Ej. Vacaciones verano"
                        />
                    </div>

                    <div className="vr-field">
                        <label>Firmante (*)</label>
                        <select name="firmante" value={form.firmante} disabled>
                            <option value={form.firmante}>
                                {form.firmante || "Sin firmante asignado"}
                            </option>
                        </select>
                    </div>

                    <div className="vr-field">
                        <label>Localización</label>
                        <input
                            type="text"
                            name="localizacion"
                            value={form.localizacion}
                            onChange={handleChange}
                        />
                    </div>

                    <div className="vr-field">
                        <label>Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            value={form.telefono}
                            onChange={handleChange}
                        />
                    </div>
                </div>

                <div className="vr-info-grid">
                    <div className="vr-info-card">
                        <span>Días solicitados</span>
                        <strong>{form.diasSolicitados}</strong>
                    </div>

                    <div className="vr-info-card">
                        <span>Estado</span>
                        <strong>{form.estado}</strong>
                    </div>
                </div>
            </section>

            <section className="vr-card">
                <div className="vr-card-header">
                    <div>
                        <h2>Fechas y motivo</h2>
                        <p>Selecciona el rango temporal y el tipo de ausencia.</p>
                    </div>
                </div>

                <div className="vr-grid vr-grid-3">
                    <div className="vr-field">
                        <label>Fecha Desde (*)</label>
                        <DatePicker
                            selected={parseDate(form.fechaDesde)}
                            onChange={(date) =>
                                setForm((prev) => ({
                                    ...prev,
                                    fechaDesde: formatDate(date),
                                }))
                            }
                            dateFormat="dd/MM/yyyy"
                            placeholderText="Selecciona fecha"
                            className="vr-datepicker"
                        />
                    </div>

                    <div className="vr-field">
                        <label>Fecha Hasta (*)</label>
                        <DatePicker
                            selected={parseDate(form.fechaHasta)}
                            onChange={(date) =>
                                setForm((prev) => ({
                                    ...prev,
                                    fechaHasta: formatDate(date),
                                }))
                            }
                            dateFormat="dd/MM/yyyy"
                            placeholderText="Selecciona fecha"
                            className="vr-datepicker"
                        />
                    </div>

                    <div className="vr-field">
                        <label>Motivo (*)</label>
                        <select
                            name="motivo"
                            value={form.motivo}
                            onChange={handleChange}
                        >
                            <option value="">{'<< Elija un motivo >>'}</option>
                            {absenceTypes.map((item) => (
                                <option key={item.codigo} value={item.codigo}>
                                    {item.descripcion}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="vr-field">
                    <label>Comentario</label>
                    <textarea
                        name="comentario"
                        value={form.comentario}
                        onChange={handleChange}
                        rows="5"
                        placeholder="Añade información adicional si es necesario"
                    />
                </div>

                <div className="vr-actions">
                    <button type="button" className="vr-btn vr-btn-secondary" onClick={handleValidate}>
                        Validar
                    </button>

                    <button type="button" className="vr-btn vr-btn-outline" onClick={handleCheck}>
                        Calcular días
                    </button>

                    <button type="button" className="vr-btn vr-btn-muted" onClick={handleCreateRequest}>
                        Guardar solicitud
                    </button>

                    <button type="button" className="vr-btn vr-btn-primary" onClick={handleSignEmployee}>
                        Firmar y enviar
                    </button>
                </div>

                {submitMessage && (
                    <div className={messageClass}>
                        {submitMessage}
                    </div>
                )}
            </section>
        </div>
    );
}