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
            setProcessingId(`${item.request_type}-${item.id}`);

            if (item.request_type === "absence") {
                await absenceRequestApi.signBySigner(item.id);
            }

            if (item.request_type === "expense") {
                if (item.normalized_status === "pending_approval") {
                    await expenseRequestApi.approve(item.id);
                }

                if (item.normalized_status === "pending_admin_approval") {
                    await expenseRequestApi.approveByAdmin(item.id);
                }
            }

            setSelectedItem(null);
            setMessage("Solicitud aprobada correctamente.");
            await loadRequests();
        } catch (error) {
            console.error("Error aprobando solicitud:", error);
            setMessage(error?.response?.data?.message || "Error aprobando la solicitud.");
        } finally {
            setProcessingId(null);
        }
    };

    const handleReject = async (item) => {
        const reason = prompt("Motivo del rechazo:", "");

        try {
            setMessage("");
            setProcessingId(`${item.request_type}-${item.id}`);

            if (item.request_type === "absence") {
                await absenceRequestApi.rejectBySigner(item.id, reason || "");
            }

            if (item.request_type === "expense") {
                if (item.normalized_status === "pending_approval") {
                    await expenseRequestApi.reject(item.id, reason || "");
                }

                if (item.normalized_status === "pending_admin_approval") {
                    await expenseRequestApi.rejectByAdmin(item.id, reason || "");
                }
            }

            setSelectedItem(null);
            setMessage("Solicitud rechazada correctamente.");
            await loadRequests();
        } catch (error) {
            console.error("Error rechazando solicitud:", error);
            setMessage(error?.response?.data?.message || "Error rechazando la solicitud.");
        } finally {
            setProcessingId(null);
        }
    };

    const filteredRequests = useMemo(() => {
        return requests.filter((item) => {
            const term = search.toLowerCase();

            const searchableText = [
                item.id,
                item.request_type,
                item.sap_employee_id,
                item.user?.name,
                item.awart,
                item.title,
                item.description,
                item.normalized_status,
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
            filtered: filteredRequests.length,
            absences: requests.filter((r) => r.request_type === "absence").length,
            expenses: requests.filter((r) => r.request_type === "expense").length,
        };
    }, [requests, filteredRequests]);

    const getStatusLabel = (status) => {
        switch (status) {
            case "pending_signer_signature":
            case "pending_approval":
                return "Pendiente firmante";
            case "pending_admin_approval":
                return "Pendiente administración";
            case "rejected":
                return "Rechazada";
            case "approved":
                return "Aprobada";
            case "exported_to_sap":
            case "sent_to_sap":
                return "Exportada a SAP";
            default:
                return status || "-";
        }
    };

    const getStatusStyle = (status) => {
        switch (status) {
            case "pending_signer_signature":
            case "pending_approval":
                return {
                    background: "#f2f4ed",
                    color: "#2f4a27",
                    border: "1px solid #dfe6d6",
                };
            case "pending_admin_approval":
                return {
                    background: "#fff8ec",
                    color: "#9a6a1f",
                    border: "1px solid #ead7ad",
                };
            case "rejected":
                return {
                    background: "#fff5f5",
                    color: "#b42318",
                    border: "1px solid #f1c4bd",
                };
            default:
                return {
                    background: "#f8fafc",
                    color: "#475569",
                    border: "1px solid #e2e8f0",
                };
        }
    };

    const getExpenseTypeLabel = (type) => {
        switch (type) {
            case "kilometraje":
                return "Kilometraje";
            case "otros_gastos":
                return "Otros gastos";
            case "media_dieta":
                return "Media dieta";
            case "dieta_completa":
                return "Dieta completa";
            default:
                return type || "-";
        }
    };

    const formatDate = (value) => {
        if (!value) return "-";
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return String(value).split("T")[0];
        return date.toLocaleDateString("es-ES");
    };

    const formatMoney = (value) => {
        return new Intl.NumberFormat("es-ES", {
            style: "currency",
            currency: "EUR",
        }).format(value || 0);
    };

    const getTitle = (item) => {
        if (item.request_type === "absence") {
            return item.description || item.awart || "Solicitud de ausencia";
        }

        return item.title || item.description || "Solicitud de gasto";
    };

    const isProcessing = (item) => processingId === `${item.request_type}-${item.id}`;

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
                            <h2 style={modalTitleStyle}>{getTitle(item)}</h2>
                            <p style={modalSubtitleStyle}>
                                {item.user?.name || "Solicitante no indicado"} ·{" "}
                                {item.sap_employee_id || "-"}
                            </p>
                        </div>

                        <button type="button" onClick={onClose} style={modalCloseStyle}>
                            ✕
                        </button>
                    </div>

                    <div style={modalBodyStyle}>
                        <div style={modalSectionStyle}>
                            <p style={modalSectionLabelStyle}>RESUMEN</p>

                            <div style={modalInfoGridStyle}>
                                <Info label="Solicitante" value={item.user?.name || "-"} />
                                <Info label="Empleado SAP" value={item.sap_employee_id || "-"} />
                                <div>
                                    <p style={modalInfoLabelStyle}>Estado</p>
                                    <span
                                        style={{
                                            ...statusBadgeStyle,
                                            ...getStatusStyle(item.normalized_status),
                                        }}
                                    >
                                        {getStatusLabel(item.normalized_status)}
                                    </span>
                                </div>

                                {isExpense ? (
                                    <>
                                        <Info label="Aprobador" value={item.approver?.name || "-"} />
                                        <Info label="Administración" value={item.admin?.name || "-"} />
                                        <Info
                                            label="Total"
                                            value={formatMoney(item.total_amount || 0)}
                                            highlight
                                        />
                                    </>
                                ) : (
                                    <>
                                        <Info label="Tipo/AWART" value={item.awart || "-"} />
                                        <Info label="Desde" value={formatDate(item.begda)} />
                                        <Info label="Hasta" value={formatDate(item.endda)} />
                                    </>
                                )}
                            </div>
                        </div>

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
                                                    <th style={thStyle}>Ticket</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {item.items.map((line, index) => (
                                                    <tr key={index} style={trStyle}>
                                                        <td style={tdStyle}>{formatDate(line.expense_date)}</td>
                                                        <td style={tdStyle}>
                                                            {getExpenseTypeLabel(line.expense_type)}
                                                        </td>
                                                        <td style={tdStyle}>{line.quantity || "-"}</td>
                                                        <td style={tdStyle}>{formatMoney(line.amount)}</td>
                                                        <td style={tdStyle}>
                                                            {line.is_card_payment ? "Tarjeta" : "No"}
                                                        </td>
                                                        <td style={tdStyle}>
                                                            {line.ticket_path ? (
                                                                <a
                                                                    href={`/storage/${line.ticket_path}`}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    style={linkStyle}
                                                                >
                                                                    Ver ticket
                                                                </a>
                                                            ) : (
                                                                "Sin ticket"
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p style={emptyLineStyle}>Sin líneas de gasto.</p>
                                )}
                            </div>
                        ) : (
                            <div style={modalSectionStyle}>
                                <p style={modalSectionLabelStyle}>DETALLE DE AUSENCIA</p>

                                <div style={modalInfoGridStyle}>
                                    <Info label="Descripción" value={item.description || "-"} />
                                    <Info label="Comentario" value={item.comment || "-"} />
                                    <Info label="Localización" value={item.location || "-"} />
                                    <Info label="Teléfono" value={item.phone || "-"} />
                                </div>
                            </div>
                        )}
                    </div>

                    <div style={modalFooterStyle}>
                        <button
                            type="button"
                            onClick={() => handleApprove(item)}
                            style={{
                                ...approveButtonStyle,
                                ...(isProcessing(item) ? disabledButtonStyle : {}),
                            }}
                            disabled={isProcessing(item)}
                        >
                            {isProcessing(item) ? "Procesando..." : "Aprobar"}
                        </button>

                        <button
                            type="button"
                            onClick={() => handleReject(item)}
                            style={{
                                ...rejectButtonStyle,
                                ...(isProcessing(item) ? disabledButtonStyle : {}),
                            }}
                            disabled={isProcessing(item)}
                        >
                            Rechazar
                        </button>

                        <button type="button" onClick={onClose} style={secondaryButtonStyle}>
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        );
    };

    const Info = ({ label, value, highlight = false }) => (
        <div>
            <p style={modalInfoLabelStyle}>{label}</p>
            <p
                style={{
                    ...modalInfoValueStyle,
                    ...(highlight ? { color: "#2f4a27", fontWeight: 800 } : {}),
                }}
            >
                {value}
            </p>
        </div>
    );

    return (
        <>
            <DetailModal item={selectedItem} onClose={() => setSelectedItem(null)} />

            <div style={pageStyle}>
                <div style={containerStyle}>
                    <section style={heroCardStyle}>
                        <div style={{ maxWidth: "720px" }}>
                            <div style={heroEyebrowStyle}>BANDEJA DE ENTRADA</div>
                            <h1 style={heroTitleStyle}>Aprobaciones pendientes</h1>
                            <p style={heroTextStyle}>
                                Revisa y firma solicitudes de ausencias y gastos desde una bandeja
                                más clara, rápida y alineada con el portal interno.
                            </p>
                        </div>

                        <button type="button" onClick={loadRequests} style={heroButtonStyle}>
                            Recargar
                        </button>
                    </section>

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

                    <section style={statsGridStyle}>
                        <StatCard label="Pendientes totales" value={stats.total} text="Solicitudes para revisar" />
                        <StatCard label="Ausencias" value={stats.absences} text="Pendientes de firma" />
                        <StatCard label="Gastos" value={stats.expenses} text="Pendientes de validación" />
                    </section>

                    <section style={filtersCardStyle}>
                        <div>
                            <h2 style={sectionTitleStyle}>Filtros</h2>
                            <p style={sectionTextStyle}>Busca una solicitud por empleado, tipo o concepto.</p>
                        </div>

                        <div style={filtersRowStyle}>
                            <div style={filterGroupStyle}>
                                <label style={labelStyle}>Buscar</label>
                                <input
                                    type="text"
                                    placeholder="ID, empleado, descripción o estado"
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
                                    <option value="pending_signer_signature">Pendiente firmante</option>
                                    <option value="pending_approval">Pendiente firmante</option>
                                    <option value="pending_admin_approval">Pendiente administración</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    {loading ? (
                        <EmptyState title="Cargando bandeja" text="Estamos recuperando tus solicitudes pendientes." />
                    ) : filteredRequests.length === 0 ? (
                        <EmptyState title="Sin solicitudes pendientes" text="No tienes solicitudes para revisar con los filtros actuales." />
                    ) : (
                        <section style={tableCardStyle}>
                            <div style={tableHeaderStyle}>
                                <h2 style={sectionTitleStyle}>Solicitudes</h2>
                                <p style={sectionTextStyle}>
                                    Pulsa sobre una solicitud para ver el detalle completo.
                                </p>
                            </div>

                            <div style={tableWrapperStyle}>
                                <table style={tableStyle}>
                                    <thead>
                                        <tr>
                                            <th style={thStyle}>Solicitud</th>
                                            <th style={thStyle}>Tipo</th>
                                            <th style={thStyle}>Solicitante</th>
                                            <th style={thStyle}>Concepto</th>
                                            <th style={thStyle}>Estado</th>
                                            <th style={thStyle}>Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {filteredRequests.map((item) => (
                                            <tr
                                                key={`${item.request_type}-${item.id}`}
                                                style={{ ...trStyle, cursor: "pointer" }}
                                                onClick={() => setSelectedItem(item)}
                                            >
                                                <td style={tdStyle}>
                                                    <span style={idPillStyle}>#{item.id}</span>
                                                </td>

                                                <td style={tdStyle}>
                                                    <span style={typeBadgeStyle}>
                                                        {item.request_type === "expense" ? "Gasto" : "Ausencia"}
                                                    </span>
                                                </td>

                                                <td style={tdStyle}>
                                                    <div style={mainValueStyle}>
                                                        {item.user?.name || "-"}
                                                    </div>
                                                    <div style={smallMutedStyle}>
                                                        {item.sap_employee_id || "-"}
                                                    </div>
                                                </td>

                                                <td style={tdStyle}>{getTitle(item)}</td>

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

                                                <td style={tdStyle} onClick={(e) => e.stopPropagation()}>
                                                    <div style={actionsContainerStyle}>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleApprove(item)}
                                                            style={{
                                                                ...approveButtonStyle,
                                                                ...(isProcessing(item) ? disabledButtonStyle : {}),
                                                            }}
                                                            disabled={isProcessing(item)}
                                                        >
                                                            {isProcessing(item) ? "..." : "Aprobar"}
                                                        </button>

                                                        <button
                                                            type="button"
                                                            onClick={() => handleReject(item)}
                                                            style={{
                                                                ...rejectButtonStyle,
                                                                ...(isProcessing(item) ? disabledButtonStyle : {}),
                                                            }}
                                                            disabled={isProcessing(item)}
                                                        >
                                                            Rechazar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    )}
                </div>
            </div>
        </>
    );
}

function StatCard({ label, value, text }) {
    return (
        <div style={statCardStyle}>
            <div style={statTopStyle}>
                <span style={statLabelStyle}>{label}</span>
                <span style={statDotStyle}></span>
            </div>
            <div style={statValueStyle}>{value}</div>
            <div style={statDescriptionStyle}>{text}</div>
        </div>
    );
}

function EmptyState({ title, text }) {
    return (
        <div style={emptyCardStyle}>
            <div style={emptyIconStyle}>—</div>
            <p style={emptyTitleStyle}>{title}</p>
            <p style={emptyTextStyle}>{text}</p>
        </div>
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
    background: "linear-gradient(135deg, #2f4a27 0%, #3d5c33 100%)",
    borderRadius: "32px",
    padding: "34px",
    boxShadow: "0 30px 70px rgba(47, 74, 39, 0.22)",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "flex-start",
    gap: "20px",
    flexWrap: "wrap",
};

const heroEyebrowStyle = {
    fontSize: "11px",
    fontWeight: "800",
    letterSpacing: "0.28em",
    color: "#c5a35d",
    marginBottom: "12px",
    textTransform: "uppercase",
};

const heroTitleStyle = {
    margin: 0,
    fontSize: "34px",
    lineHeight: 1.1,
    fontWeight: "800",
    color: "#ffffff",
};

const heroTextStyle = {
    marginTop: "14px",
    color: "rgba(255,255,255,0.72)",
    fontSize: "15px",
    lineHeight: 1.7,
};

const heroButtonStyle = {
    padding: "12px 18px",
    borderRadius: "16px",
    border: "1px solid rgba(255,255,255,0.18)",
    background: "rgba(255,255,255,0.08)",
    color: "#ffffff",
    fontWeight: "800",
    fontSize: "14px",
    cursor: "pointer",
};

const statsGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
    gap: "18px",
};

const statCardStyle = {
    background: "#ffffff",
    border: "1px solid #eef0e8",
    borderRadius: "28px",
    padding: "24px",
    boxShadow: "0 10px 30px rgba(47, 74, 39, 0.06)",
};

const statTopStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
};

const statLabelStyle = {
    fontSize: "13px",
    fontWeight: "800",
    color: "#69705d",
};

const statDotStyle = {
    width: "10px",
    height: "10px",
    borderRadius: "999px",
    background: "#c5a35d",
};

const statValueStyle = {
    marginTop: "14px",
    fontSize: "42px",
    lineHeight: 1,
    fontWeight: "800",
    color: "#2f4a27",
};

const statDescriptionStyle = {
    marginTop: "12px",
    fontSize: "14px",
    color: "#8a927d",
};

const filtersCardStyle = {
    background: "#ffffff",
    border: "1px solid #eef0e8",
    borderRadius: "28px",
    padding: "24px",
    boxShadow: "0 10px 30px rgba(47, 74, 39, 0.05)",
};

const filtersRowStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
    gap: "16px",
    marginTop: "18px",
};

const filterGroupStyle = {
    display: "flex",
    flexDirection: "column",
};

const sectionTitleStyle = {
    margin: 0,
    fontSize: "20px",
    fontWeight: "800",
    color: "#2f4a27",
};

const sectionTextStyle = {
    marginTop: "6px",
    fontSize: "14px",
    color: "#737b66",
};

const labelStyle = {
    marginBottom: "8px",
    fontSize: "13px",
    fontWeight: "800",
    color: "#2f4a27",
};

const inputStyle = {
    width: "100%",
    padding: "13px 15px",
    borderRadius: "16px",
    border: "1px solid #dfe6d6",
    background: "#fcfcf9",
    fontSize: "14px",
    color: "#2f4a27",
    outline: "none",
    boxSizing: "border-box",
};

const tableCardStyle = {
    background: "#ffffff",
    border: "1px solid #eef0e8",
    borderRadius: "30px",
    boxShadow: "0 12px 34px rgba(47, 74, 39, 0.06)",
    overflow: "hidden",
};

const tableHeaderStyle = {
    padding: "26px 26px 0 26px",
};

const tableWrapperStyle = {
    overflowX: "auto",
    padding: "18px 26px 26px",
};

const tableStyle = {
    width: "100%",
    borderCollapse: "collapse",
    minWidth: "820px",
};

const thStyle = {
    textAlign: "left",
    padding: "14px 12px",
    borderBottom: "1px solid #eef0e8",
    fontSize: "11px",
    fontWeight: "800",
    textTransform: "uppercase",
    letterSpacing: "0.1em",
    color: "#9aa18e",
};

const trStyle = {
    borderBottom: "1px solid #f1f3eb",
};

const tdStyle = {
    padding: "16px 12px",
    fontSize: "14px",
    color: "#374151",
    verticalAlign: "middle",
};

const mainValueStyle = {
    fontWeight: "800",
    color: "#2f4a27",
};

const smallMutedStyle = {
    fontSize: "12px",
    color: "#9ca3af",
    marginTop: "4px",
};

const statusBadgeStyle = {
    display: "inline-flex",
    alignItems: "center",
    padding: "7px 12px",
    borderRadius: "999px",
    fontSize: "12px",
    fontWeight: "800",
};

const idPillStyle = {
    display: "inline-flex",
    alignItems: "center",
    justifyContent: "center",
    padding: "7px 12px",
    borderRadius: "999px",
    background: "#f2f4ed",
    color: "#2f4a27",
    border: "1px solid #dfe6d6",
    fontSize: "12px",
    fontWeight: "800",
};

const typeBadgeStyle = {
    ...idPillStyle,
    background: "#fcfcf9",
    color: "#9a6a1f",
    border: "1px solid #ead7ad",
};

const actionsContainerStyle = {
    display: "flex",
    flexWrap: "wrap",
    gap: "10px",
};

const approveButtonStyle = {
    padding: "10px 16px",
    borderRadius: "14px",
    border: "none",
    background: "#2f4a27",
    color: "#ffffff",
    fontSize: "13px",
    fontWeight: "800",
    cursor: "pointer",
};

const rejectButtonStyle = {
    padding: "10px 16px",
    borderRadius: "14px",
    border: "1px solid #ead8d2",
    background: "#fff7f5",
    color: "#b42318",
    fontSize: "13px",
    fontWeight: "800",
    cursor: "pointer",
};

const secondaryButtonStyle = {
    padding: "10px 16px",
    borderRadius: "14px",
    border: "1px solid #dfe6d6",
    background: "#ffffff",
    color: "#2f4a27",
    fontSize: "13px",
    fontWeight: "800",
    cursor: "pointer",
};

const disabledButtonStyle = {
    opacity: 0.65,
    cursor: "not-allowed",
};

const messageBoxStyle = {
    padding: "15px 18px",
    borderRadius: "18px",
    fontWeight: "800",
};

const successMessageStyle = {
    background: "#eef6ea",
    color: "#2f4a27",
    border: "1px solid #dbe7d1",
};

const errorMessageStyle = {
    background: "#fff5f5",
    color: "#b42318",
    border: "1px solid #f1c4bd",
};

const emptyCardStyle = {
    background: "#ffffff",
    border: "1px solid #eef0e8",
    borderRadius: "28px",
    padding: "46px 24px",
    textAlign: "center",
    boxShadow: "0 10px 30px rgba(47, 74, 39, 0.05)",
};

const emptyIconStyle = {
    width: "56px",
    height: "56px",
    borderRadius: "18px",
    background: "#f2f4ed",
    color: "#2f4a27",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    margin: "0 auto 14px",
    fontSize: "22px",
};

const emptyTitleStyle = {
    margin: 0,
    fontSize: "18px",
    fontWeight: "800",
    color: "#2f4a27",
};

const emptyTextStyle = {
    marginTop: "8px",
    fontSize: "14px",
    color: "#737b66",
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
    background: "#ffffff",
    borderRadius: "30px",
    boxShadow: "0 24px 60px rgba(15, 23, 42, 0.18)",
    width: "100%",
    maxWidth: "860px",
    maxHeight: "90vh",
    display: "flex",
    flexDirection: "column",
    overflow: "hidden",
};

const modalHeaderStyle = {
    padding: "28px 28px 0",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "flex-start",
};

const modalTitleStyle = {
    margin: "8px 0 0",
    fontSize: "24px",
    fontWeight: "800",
    color: "#2f4a27",
};

const modalSubtitleStyle = {
    margin: "8px 0 0",
    fontSize: "14px",
    color: "#737b66",
};

const modalCloseStyle = {
    background: "#f2f4ed",
    border: "1px solid #dfe6d6",
    borderRadius: "14px",
    width: "38px",
    height: "38px",
    cursor: "pointer",
    color: "#2f4a27",
    fontWeight: "800",
};

const modalBodyStyle = {
    padding: "24px 28px",
    overflowY: "auto",
    flex: 1,
};

const modalFooterStyle = {
    padding: "20px 28px",
    borderTop: "1px solid #eef0e8",
    display: "flex",
    gap: "12px",
    alignItems: "center",
    flexWrap: "wrap",
};

const modalSectionStyle = {
    marginBottom: "28px",
};

const modalSectionLabelStyle = {
    fontSize: "11px",
    fontWeight: "800",
    letterSpacing: "0.16em",
    color: "#9aa18e",
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
    fontWeight: "800",
    color: "#9aa18e",
    textTransform: "uppercase",
    letterSpacing: "0.06em",
};

const modalInfoValueStyle = {
    margin: "6px 0 0",
    fontSize: "15px",
    fontWeight: "700",
    color: "#111827",
};

const modalTableWrapperStyle = {
    overflowX: "auto",
};

const linkStyle = {
    color: "#2f4a27",
    fontWeight: "800",
    textDecoration: "none",
};

const emptyLineStyle = {
    color: "#9ca3af",
    fontSize: "14px",
};