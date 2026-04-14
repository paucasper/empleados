import React, { useEffect, useState } from "react";
import api from "../services/api";
import "../css/expense-request.css";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

export default function ExpenseRequest({ pernr }) {
    const [employee, setEmployee] = useState(null);
    const [message, setMessage] = useState("");
    const [requestId, setRequestId] = useState(null);
    const [saving, setSaving] = useState(false);
    

    
    const mapExpenseTypeFromBackend = (type) => {
        switch (type) {
            case "kilometraje":
                return "KM";
            case "otros_gastos":
                return "OTROS";
            case "media_dieta":
                return "MEDIA_DIETA";
            case "dieta_completa":
                return "DIETA_COMPLETA";
            default:
                return type || "";
        }
    };

    const [form, setForm] = useState({
        solicitante: "",
        numeroEmpleado: "",
        identificador: "",
        descripcion: "",
        mes: "",
        anio: "",
        firmante: "",
        firmaAdministr: "", 
    });

    const [expense, setExpense] = useState({
        fecha: "",
        tipo: "",
        cantidad: "",
        importe: "",
        pagoTarjeta: false,
        motivo: "",
        archivo: null,
    });

    const [expenses, setExpenses] = useState([]);
    
    const isOtrosGastos = expense.tipo === "OTROS";

    const [pendingExpenses, setPendingExpenses] = useState([]);

    useEffect(() => {
        if (!pernr) return;

        const load = async () => {
            try {
                const res = await api.get(`/employees/${pernr}`);
                const emp = res.data.employee ?? res.data;


                
                console.log("FIRMANTE RECIBIDO:", emp.firmante);
                console.log("EMP COMPLETO:", emp);
                setEmployee(emp);

                setForm((prev) => ({
                    ...prev,
                    solicitante: emp.nombreCompleto || emp.name,
                    numeroEmpleado: emp.pernr,
                    identificador: emp.nif || "",
                    firmante: emp.firmante || "",
                    firmaAdministr: emp.firmaAdministr || "",
                }));
                console.log("EMPLOYEE DATA", emp);
            } catch (e) {
                console.error(e);
                setMessage("Error cargando empleado");
            }
        };

        load();
    }, [pernr]);

    useEffect(() => {
        const loadPendingExpenses = async () => {
            try {
                const response = await fetch('/expenses/pending-approver', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                setPendingExpenses(data.data || []);
            } catch (error) {
                console.error('Error cargando gastos pendientes:', error);
            }
        };

        loadPendingExpenses();
    }, []);

    useEffect(() => {
        const loadDraft = async () => {
            try {
                const response = await fetch("/expenses/latest/draft", {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        "Accept": "application/json",
                    },
                });

                const data = await response.json();

                if (!response.ok || !data.data) {
                    return;
                }

                const request = data.data;

                setRequestId(request.id);

                setForm((prev) => ({
                    ...prev,
                    descripcion: "",
                }));

                console.log("ARCHIVO AL AÑADIR:", expense.archivo);
                setExpenses(
                    (request.items || []).map((item) => ({
                        fecha: item.expense_date,
                        tipo: mapExpenseTypeFromBackend(item.expense_type),
                        cantidad: item.quantity || "",
                        importe: item.amount || "",
                        pagoTarjeta: !!item.is_card_payment,
                        motivo: item.description || "",
                        archivo: null,
                        saved: true,
                    }))
                );
            } catch (error) {
                console.error("Error cargando borrador:", error);
            }
        };

        loadDraft();
    }, []);

    const handleChange = (e) => {
        const { name, value } = e.target;

        if (name === "tipo") {
            const isOtros = value === "OTROS";

            setExpense((prev) => ({
                ...prev,
                tipo: value,
                cantidad: isOtros ? "" : prev.cantidad,
                importe: isOtros ? prev.importe : "",
            }));
            return;
        }

        setExpense((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleAddExpense = () => {
        if (!expense.fecha || !expense.tipo) {
            setMessage("Debes completar al menos la fecha y el tipo de gasto.");
            return;
        }

        if (expense.tipo === "OTROS" && !expense.importe) {
            setMessage("Para 'Otros Gastos' debes indicar el importe.");
            return;
        }

        if (expense.tipo !== "OTROS" && !expense.cantidad) {
            setMessage("Debes indicar la cantidad para este tipo de gasto.");
            return;
        }

        const newExpense = {
            ...expense,
            importe:
                expense.tipo === "OTROS"
                    ? expense.importe
                    : expense.importe || "0",
            saved: false,
        };

        console.log("GUARDANDO ARCHIVO EN ESTADO:", expense.archivo instanceof File);
        setExpenses((prev) => [...prev, newExpense]);
        setMessage("Gasto añadido correctamente.");

        setExpense({
            fecha: "",
            tipo: "",
            cantidad: "",
            importe: "",
            pagoTarjeta: false,
            motivo: "",
            archivo: null,
        });
    };

    const formatCurrency = (value) => {
        const number = parseFloat(value || 0);
        return new Intl.NumberFormat("es-ES", {
            style: "currency",
            currency: "EUR",
        }).format(number);
    };

    const getTypeLabel = (type) => {
        switch (type) {
            case "KM":
                return "Kilometraje";
            case "OTROS":
                return "Otros Gastos";
            case "MEDIA_DIETA":
                return "Media Dieta";
            case "DIETA_COMPLETA":
                return "Dieta Completa";
            default:
                return type || "-";
        }
    };

    const totalAmount = expenses.reduce((acc, item) => {
        if (item.tipo === "OTROS") {
            return acc + parseFloat(item.importe || 0);
        }

        return acc + parseFloat(item.importe || 0);
    }, 0);

    const handleRemoveExpense = (indexToRemove) => {
        setExpenses((prev) => prev.filter((_, index) => index !== indexToRemove));
    };

    const mapExpenseTypeToBackend = (type) => {
        switch (type) {
            case "KM":
                return "kilometraje";
            case "OTROS":
                return "otros_gastos";
            case "MEDIA_DIETA":
                return "media_dieta";
            case "DIETA_COMPLETA":
                return "dieta_completa";
            default:
                return "";
        }
    };

    const handleSubmit = async () => {
        try {
            if (!requestId) {
                setMessage("Primero debes guardar la solicitud.");
                return;
            }

            const response = await fetch(`/expenses/${requestId}/sign`, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Error al firmar la solicitud");
            }

            setMessage("Solicitud firmada y enviada al aprobador correctamente.");
            setExpenses([]);
            setRequestId(null);
            setForm(prev => ({ ...prev, descripcion: "" }));
        } catch (error) {
            console.error(error);
            setMessage(error.message || "Error al firmar la solicitud.");
        }
    };
    
    const handleSaveRequest = async () => {
        try {
            if (expenses.length === 0) {
                setMessage("Debes añadir al menos un gasto antes de guardar.");
                return;
            }

            setMessage("");

            const payload = {
                signer_pernr: form.firmante,
                admin_pernr: form.firmaAdministr,
                title: "Solicitud de gastos prueba",
                description: ''
            };

            console.log("Payload cabecera:", payload);



            const response = await fetch("/expenses", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            console.log("STATUS CABECERA:", response.status);
            console.log("RESPUESTA CABECERA:", data);

            if (!response.ok) {
                throw new Error(data.message || "Error al crear la solicitud");
            }

            const currentRequestId = data.data.id;
            console.log("REQUEST ID:", currentRequestId);

            setRequestId(currentRequestId);

            for (const item of expenses) {

                console.log("ARCHIVO ES FILE:", item.archivo instanceof File);



                const formData = new FormData();
                formData.append(
                    "expense_type",
                    item.tipo === "KM"
                        ? "kilometraje"
                        : item.tipo === "OTROS"
                        ? "otros_gastos"
                        : item.tipo === "MEDIA_DIETA"
                        ? "media_dieta"
                        : item.tipo === "DIETA_COMPLETA"
                        ? "dieta_completa"
                        : ""
                );

                formData.append("expense_date", item.fecha);
                formData.append("description", item.motivo || "");
                formData.append("is_card_payment", item.pagoTarjeta ? "1" : "0");


                console.log("ARCHIVO DEL ITEM:", item.archivo);
                console.log("FORMDATA TICKET:", formData.get("ticket"));

                if (item.tipo === "OTROS") {
                    formData.append("amount", item.importe);
                } else {
                    formData.append("quantity", item.cantidad);
                }

                if (item.archivo) {
                    formData.append("ticket", item.archivo);
                    console.log("DESPUÉS DEL APPEND:", formData.get("ticket"));
                }

                const itemResponse = await fetch(`/expenses/${currentRequestId}/items`, {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                const itemData = await itemResponse.json();

                console.log("STATUS ITEM:", itemResponse.status);
                console.log("RESPUESTA ITEM:", itemData);

                if (!itemResponse.ok) {
                    throw new Error(itemData.message || "Error al guardar una línea");
                }
            }

            setMessage("Solicitud y líneas guardadas correctamente.");
        } catch (error) {
            console.error("ERROR GUARDANDO:", error);
            setMessage(error.message || "Error al guardar.");
        }
    };

    return (
        <div className="er-page">

            {/* HERO */}
            <section className="er-hero">
                <h1>Gestión de gastos</h1>
                <p>Registra gastos, adjunta justificantes y envía a aprobación.</p>
            </section>

            {/* DATOS */}
            <section className="er-card">
                <h2>Datos del solicitante</h2>

                <div className="er-grid">
                    <input value={form.solicitante} readOnly placeholder="Nombre"/>
                    <input value={form.numeroEmpleado} readOnly placeholder="Número de empleado"/>
    
                    <input
                        name="descripcion"
                        value={form.descripcion}
                        onChange={(e) =>
                            setForm((prev) => ({
                                ...prev,
                                descripcion: e.target.value,
                            }))
                        }
                        placeholder="Descripción"
                    />
                </div>
            </section>

            {/* FORM GASTO */}
            <section className="er-card">
                <div className="er-card-header">
                    <div>
                        <h2>Añadir gasto</h2>
                        <p>Introduce una línea de gasto y adjunta el justificante si corresponde.</p>
                    </div>
                </div>

                <div className="er-form-grid">
                <div className="er-field">
                    <label>Fecha</label>
                    <DatePicker
                        selected={expense.fecha ? new Date(expense.fecha) : null}
                        onChange={(date) =>
                            setExpense((prev) => ({
                                ...prev,
                                fecha: date ? date.toISOString().split("T")[0] : "",
                            }))
                        }
                        dateFormat="dd/MM/yyyy"
                        placeholderText="Selecciona fecha"
                        className="er-datepicker"
                    />
                </div>

                    <div className="er-field">
                        <label>Tipo</label>
                        <select
                            name="tipo"
                            value={expense.tipo}
                            onChange={handleChange}
                        >
                            <option value="">Selecciona tipo</option>
                            <option value="KM">Kilometraje</option>
                            <option value="OTROS">Otros Gastos</option>
                            <option value="MEDIA_DIETA">Media Dieta</option>
                            <option value="DIETA_COMPLETA">Dieta Completa</option>
                        </select>
                    </div>

                    <div className="er-field">
                        <label>Cantidad</label>
                        <input
                            type="number"
                            name="cantidad"
                            placeholder={isOtrosGastos ? "No aplica" : "Cantidad"}
                            value={expense.cantidad}
                            onChange={handleChange}
                            disabled={isOtrosGastos}
                        />
                    </div>

                    <div className="er-field">
                        <label>Importe</label>
                        <input
                            type="number"
                            step="0.01"
                            name="importe"
                            placeholder={isOtrosGastos ? "Importe" : "No aplica"}
                            value={expense.importe}
                            onChange={handleChange}
                            disabled={!isOtrosGastos}
                        />
                    </div>

                    <div className="er-field er-field-checkbox">
                        <label>Pago</label>
                        <button
                            type="button"
                            onClick={() =>
                                setExpense((prev) => ({
                                    ...prev,
                                    pagoTarjeta: !prev.pagoTarjeta,
                                }))
                            }
                            style={{
                                padding: "10px 16px",
                                borderRadius: "12px",
                                border: expense.pagoTarjeta ? "2px solid #10B981" : "2px solid #CBD5E1",
                                background: expense.pagoTarjeta ? "#ECFDF5" : "#FFFFFF",
                                color: expense.pagoTarjeta ? "#047857" : "#64748B",
                                fontWeight: "600",
                                fontSize: "14px",
                                cursor: "pointer",
                            }}
                        >
                            {expense.pagoTarjeta ? "✓ Pago con tarjeta" : "Pago con tarjeta"}
                        </button>
                    </div>

                    <div className="er-field">
                        <label>Ticket</label>

                        <label className="er-upload">
                            Subir ticket
                            <input
                                type="file"
                                hidden
                                onChange={(e) =>
                                    setExpense((prev) => ({
                                        ...prev,
                                        archivo: e.target.files[0],
                                    }))
                                }
                            />
                        </label>

                        {expense.archivo && (
                            <div className="er-upload-preview">
                                {expense.archivo.name}
                            </div>
                        )}
                    </div>

                    <div className="er-field er-field-full">
                        <label>Motivo</label>
                        <textarea
                            name="motivo"
                            placeholder="Describe el motivo del gasto"
                            value={expense.motivo}
                            onChange={handleChange}
                            rows="4"
                        />
                    </div>
                </div>

                <div className="er-actions">
                    <button onClick={handleAddExpense}>Añadir gasto</button>
                </div>
            </section>

            {/* LISTA */}
            <section className="er-card">
                <div className="er-card-header er-card-header-inline">
                    <div>
                        <h2>Gastos añadidos</h2>
                        <p>Revisa las líneas incorporadas antes de guardar o enviar.</p>
                    </div>

                    <div className="er-total-card">
                        <span>Total acumulado</span>
                        <strong>{formatCurrency(totalAmount)}</strong>
                    </div>
                </div>

                {expenses.length === 0 ? (
                    <div className="er-empty-state">
                        <div className="er-empty-icon">€</div>
                        <p className="er-empty-title">Todavía no has añadido gastos</p>
                        <p className="er-empty-text">
                            Añade una línea de gasto para empezar a construir la liquidación.
                        </p>
                    </div>
                ) : (
                    <div className="er-table-wrapper">
                        <table className="er-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Importe</th>
                                    <th>Pago</th>
                                    <th>Ticket</th>
                                    <th>Motivo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {expenses.map((item, index) => (
                                    <tr key={index}>
                                        <td>{item.fecha || "-"}</td>

                                        <td>
                                            <span className={`er-badge er-badge-${item.tipo || "default"}`}>
                                                {getTypeLabel(item.tipo)}
                                            </span>
                                        </td>

                                        <td>{item.tipo === "OTROS" ? "-" : item.cantidad || "-"}</td>

                                        <td className="er-amount">
                                            {item.tipo === "OTROS"
                                                ? formatCurrency(item.importe || 0)
                                                : item.importe
                                                    ? formatCurrency(item.importe)
                                                    : "-"}
                                        </td>

                                        <td>
                                            {item.pagoTarjeta ? (
                                                <span className="er-badge er-badge-success">Tarjeta</span>
                                            ) : (
                                                <span className="er-badge er-badge-muted">No</span>
                                            )}
                                        </td>

                                        <td>
                                            {item.archivo ? (
                                                <span className="er-file-pill">{item.archivo.name}</span>
                                            ) : (
                                                <span className="er-text-muted">Sin ticket</span>
                                            )}
                                        </td>

                                        <td className="er-motivo-cell">
                                            {item.motivo || <span className="er-text-muted">Sin detalle</span>}
                                        </td>

                                        <td className="er-actions-cell">
                                            <button
                                                type="button"
                                                className="er-delete-btn"
                                                onClick={() => handleRemoveExpense(index)}
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>

            {/* BOTONES */}
            <div className="er-footer">
                <button
                    type="button"
                    className="primary"
                    onClick={handleSaveRequest}
                    disabled={saving}
                >
                    {saving ? "Guardando..." : "Guardar solicitud"}
                </button>

                <button
                    type="button"
                    className="primary"
                    onClick={handleSubmit}
                >
                    Firmar y enviar
                </button>

                <button type="button" className="secondary">
                    Volver
                </button>
            </div>

            {message && (
                <div className="er-message">
                    {message}
                </div>
            )}

        </div>
    );
}