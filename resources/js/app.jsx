import React from "react";
import ReactDOM from "react-dom/client";

import VacationRequest from "./Pages/VacationRequest";
import PendingApprovals from "./Pages/PendingApprovals";
import ExpenseRequest from "./Pages/ExpenseRequest";
import EmployeeCalendar from "./Pages/EmployeeCalendar";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

const vacationEl = document.getElementById("vacation-request-root");
if (vacationEl) {
    const pernr = vacationEl.dataset.pernr;
    ReactDOM.createRoot(vacationEl).render(<VacationRequest pernr={pernr} />);
}

const pendingEl = document.getElementById("pending-approvals-root");
if (pendingEl) {
    ReactDOM.createRoot(pendingEl).render(<PendingApprovals />);
}

const expenseEl = document.getElementById("expense-request-root");
if (expenseEl) {
    const pernr = expenseEl.dataset.pernr;
    ReactDOM.createRoot(expenseEl).render(<ExpenseRequest pernr={pernr} />);
}

const calendarEl = document.getElementById("calendar-root");
if (calendarEl) {
    const pernr = calendarEl.dataset.pernr;
    ReactDOM.createRoot(calendarEl).render(<EmployeeCalendar pernr={pernr} />);
}