import api from "./api";

const expenseRequestApi = {
    async getPendingApprover() {
        const response = await fetch('/expenses/pending-approver', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        return { data };
    },

    async approve(id) {
        const response = await fetch(`/expenses/${id}/approve`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error aprobando gasto');
        }

        return { data };
    },

    async reject(id, reason = "") {
        const response = await fetch(`/expenses/${id}/reject`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ reason }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error rechazando gasto');
        }

        return { data };
    },

    async approveByAdmin(id) {
        const response = await fetch(`/expenses/${id}/approve-admin`, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error aprobando gasto por administración');
        }

        return { data };
    },

    async rejectByAdmin(id, reason = "") {
        const response = await fetch(`/expenses/${id}/reject-admin`, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ reason }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error rechazando gasto por administración');
        }

        return { data };
    },
};

export default expenseRequestApi;