import React, { useEffect, useMemo, useState } from "react";
import absenceRequestApi from "../services/absenceRequestApi";
import expenseRequestApi from "../services/expenseRequestApi";

export default function PendingApprovals() {
    const [requests, setRequests] = useState([]);
    const [message, setMessage] = useState("");
    const [loading, setLoading] = useState(true);
    const [processingId, setProcessingId] = useState(null);
    const [selectedItem, setSelectedItem] = useState(null);

    const [search, setSearch] = useState("");
    const [statusFilter, setStatusFilter] = useState("all");

    console.log("REQUESTS CARGADOS:", requests);

    const loadRequests = async () => {
        try {
            setLoading(true);

            const [absenceResponse, expenseResponse] = await Promise.all([
                absenceRequestApi.getPendingSigner(),
                expenseRequestApi.getPendingApprover(),
            ]);

            const absenceRequests = (absenceResponse.data.data || []).map((item) => ({
                ...item,
                request_type: "absence",
                normalized_status: item.status || "",
            }));

            const expenseRequests = (expenseResponse.data.data || []).map((item) => ({
                ...item,
                request_type: "expense",
                normalized_status: item.status?.code || "",
            }));

            setRequests([...absenceRequests, ...expenseRequests]);
            setMessage("");
        } catch (error) {
            console.error("Error cargando aprobaciones pendientes:", error);
            setMessage("Error cargando aprobaciones pendientes.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadRequests();
    }, []);

    const handleApprove = async (item) => {
        try {
            setMessage("");
            setProcessingId(item.id);

            if (item.request_type === "absence") {
                await absenceRequestApi.signBySigner(item.id);
            } else if (item.request_type === "expense") {
                if (item.normalized_status === "pending_approval") {
                    await expenseRequestApi.approve(item.id);
                } else if (item.normalized_status === "pending_admin_approval") {
                    await expenseRequestApi.approveByAdmin(item.id);
                }
            }

            setMessage("Solicitud aprobada correctamente.");
            await loadRequests();
        } catch (error) {
            console.error("Error aprobando solicitud:", error);
            setMessage("Error aprobando la solicitud.");
        } finally {
            setProcessingId(null);
        }
    };

    const handleReject = async (item) => {
        const reason = prompt("Motivo del rechazo (opcional):", "");

        try {
            setMessage("");
            setProcessingId(item.id);

            if (item.request_type === "absence") {
                await absenceRequestApi.rejectBySigner(item.id, reason || "");
            } else if (item.request_type === "expense") {
                if (item.normalized_status === "pending_approval") {
                    await expenseRequestApi.reject(item.id, reason || "");
                } else if (item.normalized_status === "pending_admin_approval") {
                    await expenseRequestApi.rejectByAdmin(item.id, reason || "");
                }
            }

            setMessage("Solicitud rechazada.");
            await loadRequests();
        } catch (error) {
            console.error("Error rechazando solicitud:", error);
            setMessage("Error rechazando la solicitud.");
        } finally {
            setProcessingId(null);
        }
    };

    const filteredRequests = useMemo(() => {
        return requests.filter((item) => {
            const term = search.toLowerCase();

            const searchableText = [
                item.sap_employee_id,
                item.awart,
                item.normalized_status,
                item.description,
                item.request_type,
                String(item.id),
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();

            const matchesSearch = searchableText.includes(term);

            const matchesStatus =
                statusFilter === "all" ? true : item.normalized_status === statusFilter;

            return matchesSearch && matchesStatus;
        });
    }, [requests, search, statusFilter]);

    const stats = useMemo(() => {
        return {
            total: requests.length,
            pending: requests.filter((r) =>
                r.normalized_status === "pending_signer_signature" ||
                r.normalized_status === "pending_approval"
            ).length,
            rejected: requests.filter((r) => r.normalized_status === "rejected").length,
            exported: requests.filter((r) =>
                r.normalized_status === "exported_to_sap" ||
                r.normalized_status === "sent_to_sap"
            ).length,
        };
    }, [requests]);

    const getStatusLabel = (status) => {
        switch (status) {
            case "pending_signer_signature":
            case "pending_approval":
                return "Pendiente";
            case "rejected":
                return "Rechazada";
            case "exported_to_sap":
            case "sent_to_sap":
                return "Exportada";
            case "pending_admin_approval":
                return "Pendiente administración";
            case "approved":
                return "Aprobada";
            default:
                return status || "-";
        }
    };

    const getExpenseTypeLabel = (type) => {
        switch (type) {
            case "kilometraje":   return "Kilometraje";
            case "otros_gastos":  return "Otros Gastos";
            case "media_dieta":   return "Media Dieta";
            case "dieta_completa":return "Dieta Completa";
            default:              return type || "-";
        }
    };

    const getStatusStyle = (status) => {
        switch (status) {
            case "pending_signer_signature":
            case "pending_approval":
                return {
                    background: "#ECFDF5",
                    color: "#047857",
                    border: "1px solid #A7F3D0",
                };
            case "rejected":
                return {
                    background: "#FEF2F2",
                    color: "#B91C1C",
                    border: "1px solid #FECACA",
                };
            case "exported_to_sap":
            case "sent_to_sap":
            case "pending_admin_approval":
                return {
                    background: "#FFF7ED",
                    color: "#C2410C",
                    border: "1px solid #FED7AA",
                };
            case "approved":
                return {
                    background: "#F0FDF4",
                    color: "#166534",
                    border: "1px solid #BBF7D0",
                };
            default:
                return {
                    background: "#F8FAFC",
                    color: "#475569",
                    border: "1px solid #E2E8F0",
                };
        }
    };

    const DetailModal = ({ item, onClose }) => {
        if (!item) return null;

        
        const isExpense = item.request_type === "expense";

        return (
            <div style={modalOverlayStyle} onClick={onClose}>
                <div style={modalStyle} onClick={(e) => e.stopPropagation()}>

                    <div style={modalHeaderStyle}>
                        <div>
                            <div style={heroEyebrowStyle}>
                                {isExpense ? "GASTO" : "AUSENCIA"} #{item.id}
                            </div>
                            <h2 style={modalTitleStyle}>
                                {isExpense ? item.description || "Solicitud de gasto" : item.awart}
                            </h2>
                        </div>
                        <button onClick={onClose} style={modalCloseStyle}>✕</button>
                    </div>

                    <div style={modalBodyStyle}>

                        {/* Datos del solicitante */}
                        <div style={modalSectionStyle}>
                            <p style={modalSectionLabelStyle}>SOLICITANTE</p>
                            <div style={modalInfoGridStyle}>
                                <div>
                                    <p style={modalInfoLabelStyle}>Empleado SAP</p>
                                    <p style={modalInfoValueStyle}>{item.sap_employee_id || "-"}</p>
                                </div>
                                <div>
                                    <p style={modalInfoLabelStyle}>Nombre</p>
                                    <p style={modalInfoValueStyle}>{item.user?.name || "-"}</p>
                                </div>
                                <div>
                                    <p style={modalInfoLabelStyle}>Estado</p>
                                    <span style={{ ...statusBadgeStyle, ...getStatusStyle(item.normalized_status) }}>
                                        {getStatusLabel(item.normalized_status)}
                                    </span>
                                </div>
                                {isExpense && (
                                    <div>
                                        <p style={modalInfoLabelStyle}>Total acumulado</p>
                                        <p style={{ ...modalInfoValueStyle, color: "#059669", fontWeight: "700" }}>
                                            {new Intl.NumberFormat("es-ES", { style: "currency", currency: "EUR" }).format(item.total_amount || 0)}
                                        </p>
                                    </div>
                                )}

                                {/* Stepper de firmas - solo para gastos */}
                                {isExpense && (
                                    <div style={modalSectionStyle}>
                                        <p style={modalSectionLabelStyle}>PROGRESO DE FIRMAS</p>
                                        <div style={stepperContainerStyle}>

                                            {/* Paso 1 - Solicitante */}
                                            <div style={stepStyle}>
                                                <div style={{
                                                    ...stepDotStyle,
                                                    background: item.submitted_at ? "#10B981" : "#E2E8F0",
                                                    border: item.submitted_at ? "2px solid #10B981" : "2px solid #CBD5E1",
                                                }}>
                                                    {item.submitted_at && <span style={stepCheckStyle}>✓</span>}
                                                </div>
                                                <p style={stepLabelStyle}>{item.user?.name || "Solicitante"}</p>
                                                <p style={stepSubLabelStyle}>
                                                    {item.submitted_at
                                                        ? new Date(item.submitted_at).toLocaleDateString("es-ES")
                                                        : "Pendiente"}
                                                </p>
                                            </div>

                                            {/* Línea 1 */}
                                            <div style={{
                                                ...stepLineStyle,
                                                background: item.submitted_at ? "#10B981" : "#E2E8F0",
                                            }} />

                                            {/* Paso 2 - Jefe */}
                                            <div style={stepStyle}>
                                                <div style={{
                                                    ...stepDotStyle,
                                                    background: item.approved_at ? "#10B981" : "#E2E8F0",
                                                    border: item.approved_at ? "2px solid #10B981" : "2px solid #CBD5E1",
                                                }}>
                                                    {item.approved_at && <span style={stepCheckStyle}>✓</span>}
                                                </div>
                                                <p style={stepLabelStyle}>{item.approver?.name || "Jefe"}</p>
                                                <p style={stepSubLabelStyle}>
                                                    {item.approved_at
                                                        ? new Date(item.approved_at).toLocaleDateString("es-ES")
                                                        : "Pendiente"}
                                                </p>
                                            </div>

                                            {/* Línea 2 */}
                                            <div style={{
                                                ...stepLineStyle,
                                                background: item.approved_at ? "#10B981" : "#E2E8F0",
                                            }} />

                                            {/* Paso 3 - Administración */}
                                            <div style={stepStyle}>
                                                <div style={{
                                                    ...stepDotStyle,
                                                    background: item.sap_exported_at ? "#10B981" : "#E2E8F0",
                                                    border: item.sap_exported_at ? "2px solid #10B981" : "2px solid #CBD5E1",
                                                }}>
                                                    {item.sap_exported_at && <span style={stepCheckStyle}>✓</span>}
                                                </div>
                                                <p style={stepLabelStyle}>{item.admin?.name || "Administración"}</p>
                                                <p style={stepSubLabelStyle}>
                                                    {item.sap_exported_at
                                                        ? new Date(item.sap_exported_at).toLocaleDateString("es-ES")
                                                        : "Pendiente"}
                                                </p>
                                            </div>

                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Detalle según tipo */}
                        {isExpense ? (
                            <div style={modalSectionStyle}>
                                <p style={modalSectionLabelStyle}>LÍNEAS DE GASTO</p>
                                {item.items?.length > 0 ? (
                                    <div style={modalTableWrapperStyle}>
                                        <table style={tableStyle}>
                                            <thead>
                                                <tr>
                                                    <th style={thStyle}>Fecha</th>
                                                    <th style={thStyle}>Tipo</th>
                                                    <th style={thStyle}>Cantidad</th>
                                                    <th style={thStyle}>Importe</th>
                                                    <th style={thStyle}>Pago</th>
                                                    <th style={thStyle}>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {item.items.map((line, index) => (
                                                    <tr key={index} style={trStyle}>
                                                        <td style={tdStyle}>{line.expense_date?.split("T")[0] || "-"}</td>
                                                        <td style={tdStyle}>
                                                            <span style={idPillStyle}>{getExpenseTypeLabel(line.expense_type)}</span>
                                                        </td>
                                                        <td style={tdStyle}>{line.quantity || "-"}</td>
                                                        <td style={tdStyle}>
                                                            {new Intl.NumberFormat("es-ES", { style: "currency", currency: "EUR" }).format(line.amount || 0)}
                                                        </td>
                                                        <td style={tdStyle}>
                                                            {line.is_card_payment ? (
                                                                <span style={{ ...statusBadgeStyle, background: "#ECFDF5", color: "#047857", border: "1px solid #A7F3D0" }}>Tarjeta</span>
                                                            ) : (
                                                                <span style={{ ...statusBadgeStyle, background: "#F8FAFC", color: "#475569", border: "1px solid #E2E8F0" }}>No</span>
                                                            )}
                                                        </td>
                                                        <td style={tdStyle}>{line.description || "-"}</td>
                                                        <td style={tdStyle}>
                                                            {line.ticket_path ? (
                                                                <a
                                                                    href={`/storage/${line.ticket_path}`}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    style={{
                                                                        color: "#059669",
                                                                        fontWeight: "600",
                                                                        fontSize: "13px",
                                                                        textDecoration: "none",
                                                                    }}
                                                                >
                                                                    Ver ticket
                                                                </a>
                                                            ) : (
                                                                <span style={{ color: "#94A3B8", fontSize: "13px" }}>Sin ticket</span>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p style={{ color: "#94A3B8", fontSize: "14px" }}>Sin líneas de gasto.</p>
                                )}
                            </div>
                        ) : (
                            <div style={modalSectionStyle}>
                                <p style={modalSectionLabelStyle}>DETALLE DE AUSENCIA</p>
                                <div style={modalInfoGridStyle}>
                                    <div>
                                        <p style={modalInfoLabelStyle}>Tipo de ausencia</p>
                                        <p style={modalInfoValueStyle}>{item.awart || "-"}</p>
                                    </div>
                                    <div>
                                        <p style={modalInfoLabelStyle}>Desde</p>
                                        <p style={modalInfoValueStyle}>{item.begda?.split("T")[0] || "-"}</p>
                                    </div>
                                    <div>
                                        <p style={modalInfoLabelStyle}>Hasta</p>
                                        <p style={modalInfoValueStyle}>{item.endda?.split("T")[0] || "-"}</p>
                                    </div>
                                    {item.description && (
                                        <div>
                                            <p style={modalInfoLabelStyle}>Descripción</p>
                                            <p style={modalInfoValueStyle}>{item.description}</p>
                                        </div>
                                    )}
                                    {item.comment && (
                                        <div>
                                            <p style={modalInfoLabelStyle}>Comentario</p>
                                            <p style={modalInfoValueStyle}>{item.comment}</p>
                                        </div>
                                    )}
                                    {item.location && (
                                        <div>
                                            <p style={modalInfoLabelStyle}>Localización</p>
                                            <p style={modalInfoValueStyle}>{item.location}</p>
                                        </div>
                                    )}
                                    {item.phone && (
                                        <div>
                                            <p style={modalInfoLabelStyle}>Teléfono</p>
                                            <p style={modalInfoValueStyle}>{item.phone}</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    <div style={modalFooterStyle}>
                        <button
                            onClick={() => { handleApprove(item); onClose(); }}
                            style={approveButtonStyle}
                        >
                            Aprobar
                        </button>
                        <button
                            onClick={() => { handleReject(item); onClose(); }}
                            style={rejectButtonStyle}
                        >
                            Rechazar
                        </button>
                        <button onClick={onClose} style={heroButtonStyle}>
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <>
            <DetailModal item={selectedItem} onClose={() => setSelectedItem(null)} />
            <div style={pageStyle}>
                <div style={containerStyle}>
                    <div style={heroCardStyle}>
                        <div style={{ maxWidth: "760px" }}>
                            <div style={heroEyebrowStyle}>APROBACIONES</div>
                            <h1 style={heroTitleStyle}>Bandeja de aprobaciones</h1>
                            <p style={heroTextStyle}>
                                Gestiona las solicitudes pendientes de firma, tanto de ausencias como de gastos,
                                desde una bandeja clara, rápida y preparada para revisión.
                            </p>
                        </div>

                        <button onClick={loadRequests} style={heroButtonStyle}>
                            Recargar
                        </button>
                    </div>

                    {message && (
                        <div
                            style={{
                                ...messageBoxStyle,
                                ...(message.toLowerCase().includes("error")
                                    ? errorMessageStyle
                                    : successMessageStyle),
                            }}
                        >
                            {message}
                        </div>
                    )}

                    <div style={statsGridStyle}>
                        <div style={statCardStyle}>
                            <div style={statTopStyle}>
                                <span style={statLabelStyle}>Total</span>
                                <span style={{ ...statDotStyle, background: "#CBD5E1" }}></span>
                            </div>
                            <div style={statValueStyle}>{stats.total}</div>
                            <div style={statDescriptionStyle}>Solicitudes recuperadas</div>
                        </div>

                        <div style={statCardStyle}>
                            <div style={statTopStyle}>
                                <span style={statLabelStyle}>Pendientes</span>
                                <span style={{ ...statDotStyle, background: "#10B981" }}></span>
                            </div>
                            <div style={statValueStyle}>{stats.pending}</div>
                            <div style={statDescriptionStyle}>Pendientes de revisión</div>
                        </div>

                        <div style={statCardStyle}>
                            <div style={statTopStyle}>
                                <span style={statLabelStyle}>Exportadas</span>
                                <span style={{ ...statDotStyle, background: "#059669" }}></span>
                            </div>
                            <div style={statValueStyle}>{stats.exported}</div>
                            <div style={statDescriptionStyle}>Enviadas correctamente</div>
                        </div>

                        <div style={statCardStyle}>
                            <div style={statTopStyle}>
                                <span style={statLabelStyle}>Rechazadas</span>
                                <span style={{ ...statDotStyle, background: "#DC2626" }}></span>
                            </div>
                            <div style={statValueStyle}>{stats.rejected}</div>
                            <div style={statDescriptionStyle}>Marcadas como rechazadas</div>
                        </div>
                    </div>

                    <div style={filtersCardStyle}>
                        <div style={filtersHeaderStyle}>
                            <div>
                                <h2 style={sectionTitleStyle}>Filtros</h2>
                                <p style={sectionTextStyle}>
                                    Encuentra rápidamente una solicitud concreta.
                                </p>
                            </div>
                        </div>

                        <div style={filtersRowStyle}>
                            <div style={filterGroupStyle}>
                                <label style={labelStyle}>Buscar</label>
                                <input
                                    type="text"
                                    placeholder="ID, empleado SAP, tipo, descripción o estado"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    style={inputStyle}
                                />
                            </div>

                            <div style={filterGroupStyle}>
                                <label style={labelStyle}>Estado</label>
                                <select
                                    value={statusFilter}
                                    onChange={(e) => setStatusFilter(e.target.value)}
                                    style={inputStyle}
                                >
                                    <option value="all">Todos</option>
                                    <option value="pending_signer_signature">Pendiente</option>
                                    <option value="pending_approval">Pendiente</option>
                                    <option value="rejected">Rechazada</option>
                                    <option value="exported_to_sap">Exportada</option>
                                    <option value="sent_to_sap">Exportada</option>
                                    <option value="approved">Aprobada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {loading ? (
                        <div style={emptyCardStyle}>
                            <div style={loadingDotStyle}></div>
                            <p style={emptyTitleStyle}>Cargando bandeja</p>
                            <p style={emptyTextStyle}>Estamos recuperando las solicitudes pendientes.</p>
                        </div>
                    ) : filteredRequests.length === 0 ? (
                        <div style={emptyCardStyle}>
                            <div style={emptyIconStyle}>—</div>
                            <p style={emptyTitleStyle}>No hay solicitudes para mostrar</p>
                            <p style={emptyTextStyle}>
                                No tienes solicitudes pendientes de firma con los filtros actuales.
                            </p>
                        </div>
                    ) : (
                        <>
                            <div style={tableCardStyle}>
                                <div style={tableHeaderStyle}>
                                    <div>
                                        <h2 style={sectionTitleStyle}>Solicitudes</h2>
                                        <p style={sectionTextStyle}>
                                            Revisa el detalle y actúa directamente desde la bandeja.
                                        </p>
                                    </div>
                                </div>

                                <div style={tableWrapperStyle}>
                                    <table style={tableStyle}>
                                        <thead>
                                            <tr>
                                                <th style={thStyle}>ID</th>
                                                <th style={thStyle}>Tipo</th>
                                                <th style={thStyle}>Empleado</th>
                                                <th style={thStyle}>Detalle</th>
                                                <th style={thStyle}>Ticket</th>
                                                <th style={thStyle}>Desde / Fecha</th>
                                                <th style={thStyle}>Hasta / Líneas</th>
                                                <th style={thStyle}>Estado</th>
                                                <th style={thStyle}>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {filteredRequests.map((item) => {
                                                const isProcessing = processingId === item.id;

                                                return (
                                                    <tr
                                                        key={`${item.request_type}-${item.id}`}
                                                        style={{ ...trStyle, cursor: "pointer" }}
                                                        onClick={() => {
                                                            console.log("ITEM SELECCIONADO:", item);
                                                            setSelectedItem(item);
                                                        }}
                                                    >
                                                        <td style={tdStyle}>
                                                            <span style={idPillStyle}>#{item.id}</span>
                                                        </td>

                                                        <td style={tdStyle}>
                                                            <span style={idPillStyle}>
                                                                {item.request_type === "expense" ? "Gasto" : "Ausencia"}
                                                            </span>
                                                        </td>

                                                        <td style={tdStyle}>
                                                            <div style={mainValueStyle}>
                                                                {item.sap_employee_id || "-"}
                                                            </div>
                                                        </td>

                                                        <td style={tdStyle}>
                                                            {item.request_type === "absence"
                                                                ? item.awart || "-"
                                                                : item.description || "Gasto"}
                                                        </td>

                                                        <td style={tdStyle}>
                                                            {item.request_type === "absence"
                                                                ? item.begda || "-"
                                                                : item.items?.[0]?.expense_date || "-"}
                                                        </td>

                                                        <td style={tdStyle}>
                                                            {item.request_type === "absence"
                                                                ? item.endda || "-"
                                                                : `${item.items?.length || 0} línea(s)`}
                                                        </td>

                                                        <td style={tdStyle}>
                                                            <span
                                                                style={{
                                                                    ...statusBadgeStyle,
                                                                    ...getStatusStyle(item.normalized_status),
                                                                }}
                                                            >
                                                                {getStatusLabel(item.normalized_status)}
                                                            </span>
                                                        </td>

                                                        <td style={tdStyle}>
                                                            <div style={actionsContainerStyle}>
                                                                <button
                                                                    onClick={() => handleApprove(item)}
                                                                    style={{
                                                                        ...approveButtonStyle,
                                                                        ...(isProcessing ? disabledButtonStyle : {}),
                                                                    }}
                                                                    disabled={isProcessing}
                                                                >
                                                                    {isProcessing ? "Procesando..." : "Aprobar"}
                                                                </button>

                                                                <button
                                                                    onClick={() => handleReject(item)}
                                                                    style={{
                                                                        ...rejectButtonStyle,
                                                                        ...(isProcessing ? disabledButtonStyle : {}),
                                                                    }}
                                                                    disabled={isProcessing}
                                                                >
                                                                    {isProcessing ? "Procesando..." : "Rechazar"}
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div style={mobileCardsWrapperStyle}>
                                {filteredRequests.map((item) => {
                                    const isProcessing = processingId === item.id;

                                    return (
                                        <div key={`${item.request_type}-${item.id}`} style={mobileCardStyle}>
                                            <div style={mobileCardTopStyle}>
                                                <span style={idPillStyle}>
                                                    {item.request_type === "expense" ? "Gasto" : "Ausencia"} #{item.id}
                                                </span>
                                                <span
                                                    style={{
                                                        ...statusBadgeStyle,
                                                        ...getStatusStyle(item.normalized_status),
                                                    }}
                                                >
                                                    {getStatusLabel(item.normalized_status)}
                                                </span>
                                            </div>

                                            <div style={mobileInfoGridStyle}>
                                                <div>
                                                    <p style={mobileLabelStyle}>Empleado</p>
                                                    <p style={mobileValueStyle}>{item.sap_employee_id || "-"}</p>
                                                </div>
                                                <div>
                                                    <p style={mobileLabelStyle}>Detalle</p>
                                                    <p style={mobileValueStyle}>
                                                        {item.request_type === "absence"
                                                            ? item.awart || "-"
                                                            : item.description || "Gasto"}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p style={mobileLabelStyle}>Desde / Fecha</p>
                                                    <p style={mobileValueStyle}>
                                                        {item.request_type === "absence"
                                                            ? item.begda || "-"
                                                            : item.items?.[0]?.expense_date || "-"}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p style={mobileLabelStyle}>Hasta / Líneas</p>
                                                    <p style={mobileValueStyle}>
                                                        {item.request_type === "absence"
                                                            ? item.endda || "-"
                                                            : `${item.items?.length || 0} línea(s)`}
                                                    </p>
                                                </div>
                                            </div>

                                            <div style={actionsContainerStyle}>
                                                <button
                                                    onClick={() => handleApprove(item)}
                                                    style={{
                                                        ...approveButtonStyle,
                                                        ...(isProcessing ? disabledButtonStyle : {}),
                                                    }}
                                                    disabled={isProcessing}
                                                >
                                                    {isProcessing ? "Procesando..." : "Aprobar"}
                                                </button>

                                                <button
                                                    onClick={() => handleReject(item)}
                                                    style={{
                                                        ...rejectButtonStyle,
                                                        ...(isProcessing ? disabledButtonStyle : {}),
                                                    }}
                                                    disabled={isProcessing}
                                                >
                                                    {isProcessing ? "Procesando..." : "Rechazar"}
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </>
                    )}
                </div>
            </div>
        </>  
    );
}

const pageStyle = {
    width: "100%",
};

const containerStyle = {
    maxWidth: "1120px",
    margin: "0 auto",
    display: "flex",
    flexDirection: "column",
    gap: "24px",
};

const heroCardStyle = {
    background: "linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)",
    border: "1px solid #E2E8F0",
    borderRadius: "28px",
    padding: "28px 30px",
    boxShadow: "0 10px 30px rgba(15, 23, 42, 0.06)",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "flex-start",
    gap: "20px",
    flexWrap: "wrap",
};

const heroEyebrowStyle = {
    fontSize: "11px",
    fontWeight: "700",
    letterSpacing: "0.22em",
    color: "#94A3B8",
    marginBottom: "10px",
};

const heroTitleStyle = {
    margin: 0,
    fontSize: "24px",
    lineHeight: 1.2,
    fontWeight: "700",
    color: "#0F172A",
};

const heroTextStyle = {
    marginTop: "12px",
    color: "#64748B",
    fontSize: "15px",
    lineHeight: 1.7,
};

const heroButtonStyle = {
    padding: "12px 18px",
    borderRadius: "16px",
    border: "1px solid #CBD5E1",
    background: "#FFFFFF",
    color: "#0F172A",
    fontWeight: "600",
    fontSize: "14px",
    cursor: "pointer",
    transition: "all 0.2s ease",
};

const statsGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
    gap: "18px",
};

const statCardStyle = {
    background: "#FFFFFF",
    border: "1px solid #E2E8F0",
    borderRadius: "24px",
    padding: "22px",
    boxShadow: "0 6px 20px rgba(15, 23, 42, 0.04)",
};

const statTopStyle = {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
};

const statLabelStyle = {
    fontSize: "13px",
    fontWeight: "600",
    color: "#64748B",
};

const statDotStyle = {
    width: "10px",
    height: "10px",
    borderRadius: "999px",
};

const statValueStyle = {
    marginTop: "14px",
    fontSize: "44px",
    lineHeight: 1,
    fontWeight: "700",
    color: "#0F172A",
};

const statDescriptionStyle = {
    marginTop: "12px",
    fontSize: "14px",
    color: "#94A3B8",
};

const filtersCardStyle = {
    background: "#FFFFFF",
    border: "1px solid #E2E8F0",
    borderRadius: "24px",
    padding: "24px",
    boxShadow: "0 6px 20px rgba(15, 23, 42, 0.04)",
};

const filtersHeaderStyle = {
    marginBottom: "18px",
};

const filtersRowStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
    gap: "16px",
};

const filterGroupStyle = {
    display: "flex",
    flexDirection: "column",
};

const sectionTitleStyle = {
    margin: 0,
    fontSize: "18px",
    fontWeight: "700",
    color: "#0F172A",
};

const sectionTextStyle = {
    marginTop: "6px",
    fontSize: "14px",
    color: "#64748B",
};

const labelStyle = {
    marginBottom: "8px",
    fontSize: "13px",
    fontWeight: "600",
    color: "#334155",
};

const inputStyle = {
    width: "100%",
    padding: "13px 15px",
    borderRadius: "16px",
    border: "1px solid #CBD5E1",
    background: "#FFFFFF",
    fontSize: "14px",
    color: "#0F172A",
    outline: "none",
    boxSizing: "border-box",
};

const tableCardStyle = {
    background: "#FFFFFF",
    border: "1px solid #E2E8F0",
    borderRadius: "24px",
    boxShadow: "0 6px 20px rgba(15, 23, 42, 0.04)",
    overflow: "hidden",
};

const tableHeaderStyle = {
    padding: "24px 24px 0 24px",
};

const tableWrapperStyle = {
    overflowX: "auto",
    padding: "16px 24px 24px 24px",
};

const tableStyle = {
    width: "100%",
    borderCollapse: "collapse",
    minWidth: "860px",
};

const thStyle = {
    textAlign: "left",
    padding: "14px 12px",
    borderBottom: "1px solid #E2E8F0",
    fontSize: "12px",
    fontWeight: "700",
    textTransform: "uppercase",
    letterSpacing: "0.08em",
    color: "#94A3B8",
};

const trStyle = {
    borderBottom: "1px solid #F1F5F9",
};

const tdStyle = {
    padding: "16px 12px",
    fontSize: "14px",
    color: "#334155",
    verticalAlign: "middle",
};

const mainValueStyle = {
    fontWeight: "600",
    color: "#0F172A",
};

const statusBadgeStyle = {
    display: "inline-flex",
    alignItems: "center",
    padding: "7px 12px",
    borderRadius: "999px",
    fontSize: "12px",
    fontWeight: "700",
};

const idPillStyle = {
    display: "inline-flex",
    alignItems: "center",
    justifyContent: "center",
    padding: "7px 12px",
    borderRadius: "999px",
    background: "#F8FAFC",
    color: "#334155",
    border: "1px solid #E2E8F0",
    fontSize: "12px",
    fontWeight: "700",
};

const actionsContainerStyle = {
    display: "flex",
    flexWrap: "wrap",
    gap: "10px",
};

const approveButtonStyle = {
    padding: "10px 14px",
    borderRadius: "14px",
    border: "none",
    background: "#10B981",
    color: "#FFFFFF",
    fontSize: "13px",
    fontWeight: "700",
    cursor: "pointer",
};

const rejectButtonStyle = {
    padding: "10px 14px",
    borderRadius: "14px",
    border: "1px solid #FECACA",
    background: "#FFFFFF",
    color: "#B91C1C",
    fontSize: "13px",
    fontWeight: "700",
    cursor: "pointer",
};

const disabledButtonStyle = {
    opacity: 0.7,
    cursor: "not-allowed",
};

const messageBoxStyle = {
    padding: "15px 18px",
    borderRadius: "18px",
    fontWeight: "600",
    border: "1px solid transparent",
};

const successMessageStyle = {
    background: "#ECFDF5",
    color: "#065F46",
    borderColor: "#A7F3D0",
};

const errorMessageStyle = {
    background: "#FEF2F2",
    color: "#991B1B",
    borderColor: "#FECACA",
};

const emptyCardStyle = {
    background: "#FFFFFF",
    border: "1px solid #E2E8F0",
    borderRadius: "24px",
    boxShadow: "0 6px 20px rgba(15, 23, 42, 0.04)",
    padding: "48px 24px",
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    justifyContent: "center",
    textAlign: "center",
};

const emptyIconStyle = {
    width: "56px",
    height: "56px",
    borderRadius: "18px",
    background: "#F8FAFC",
    border: "1px solid #E2E8F0",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    color: "#94A3B8",
    fontSize: "22px",
    marginBottom: "14px",
};

const emptyTitleStyle = {
    margin: 0,
    fontSize: "18px",
    fontWeight: "700",
    color: "#0F172A",
};

const emptyTextStyle = {
    marginTop: "8px",
    fontSize: "14px",
    color: "#64748B",
    maxWidth: "520px",
};

const loadingDotStyle = {
    width: "18px",
    height: "18px",
    borderRadius: "999px",
    background: "#10B981",
    marginBottom: "16px",
    boxShadow: "0 0 0 8px rgba(16, 185, 129, 0.12)",
};

const mobileCardsWrapperStyle = {
    display: "none",
};

const mobileCardStyle = {
    background: "#FFFFFF",
    border: "1px solid #E2E8F0",
    borderRadius: "24px",
    padding: "18px",
    boxShadow: "0 6px 20px rgba(15, 23, 42, 0.04)",
    marginTop: "16px",
};

const mobileCardTopStyle = {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: "12px",
    marginBottom: "16px",
};

const mobileInfoGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(2, minmax(0, 1fr))",
    gap: "14px",
    marginBottom: "18px",
};

const mobileLabelStyle = {
    margin: 0,
    fontSize: "12px",
    fontWeight: "600",
    color: "#94A3B8",
    textTransform: "uppercase",
    letterSpacing: "0.06em",
};

const mobileValueStyle = {
    margin: "6px 0 0 0",
    fontSize: "14px",
    fontWeight: "600",
    color: "#0F172A",
};

const modalOverlayStyle = {
    position: "fixed",
    inset: 0,
    background: "rgba(15, 23, 42, 0.5)",
    zIndex: 1000,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    padding: "24px",
};

const modalStyle = {
    background: "#FFFFFF",
    borderRadius: "28px",
    boxShadow: "0 24px 60px rgba(15, 23, 42, 0.18)",
    width: "100%",
    maxWidth: "860px",
    maxHeight: "90vh",
    display: "flex",
    flexDirection: "column",
    overflow: "hidden",
};

const modalHeaderStyle = {
    padding: "28px 28px 0 28px",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "flex-start",
};

const modalTitleStyle = {
    margin: "8px 0 0 0",
    fontSize: "20px",
    fontWeight: "700",
    color: "#0F172A",
};

const modalCloseStyle = {
    background: "#F8FAFC",
    border: "1px solid #E2E8F0",
    borderRadius: "12px",
    width: "36px",
    height: "36px",
    cursor: "pointer",
    fontSize: "16px",
    color: "#64748B",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
};

const modalBodyStyle = {
    padding: "24px 28px",
    overflowY: "auto",
    flex: 1,
};

const modalFooterStyle = {
    padding: "20px 28px",
    borderTop: "1px solid #F1F5F9",
    display: "flex",
    gap: "12px",
    alignItems: "center",
};

const modalSectionStyle = {
    marginBottom: "28px",
};

const modalSectionLabelStyle = {
    fontSize: "11px",
    fontWeight: "700",
    letterSpacing: "0.16em",
    color: "#94A3B8",
    marginBottom: "14px",
};

const modalInfoGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(180px, 1fr))",
    gap: "16px",
};

const modalInfoLabelStyle = {
    margin: 0,
    fontSize: "12px",
    fontWeight: "600",
    color: "#94A3B8",
    textTransform: "uppercase",
    letterSpacing: "0.06em",
};

const modalInfoValueStyle = {
    margin: "6px 0 0 0",
    fontSize: "15px",
    fontWeight: "600",
    color: "#0F172A",
};

const modalTableWrapperStyle = {
    overflowX: "auto",
};

const stepperContainerStyle = {
    display: "flex",
    alignItems: "flex-start",
    gap: "0",
};

const stepStyle = {
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    gap: "8px",
    minWidth: "80px",
};

const stepDotStyle = {
    width: "36px",
    height: "36px",
    borderRadius: "50%",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    transition: "all 0.2s ease",
};

const stepCheckStyle = {
    color: "#FFFFFF",
    fontSize: "16px",
    fontWeight: "700",
};

const stepLineStyle = {
    flex: 1,
    height: "2px",
    marginTop: "17px",
    transition: "all 0.2s ease",
};

const stepLabelStyle = {
    fontSize: "12px",
    fontWeight: "600",
    color: "#0F172A",
    textAlign: "center",
    maxWidth: "80px",
};

const stepSubLabelStyle = {
    fontSize: "11px",
    color: "#94A3B8",
    textAlign: "center",
};