import axios from 'axios';

const absenceRequestApi = {
    create(payload) {
        return axios.post('/hr/absence-requests', payload);
    },

    signByEmployee(id) {
        return axios.post(`/hr/absence-requests/${id}/sign-employee`);
    },

    signBySigner(id) {
        return axios.post(`/hr/absence-requests/${id}/sign-signer`);
    },

    rejectBySigner(id, reason) {
        return axios.post(`/hr/absence-requests/${id}/reject`, { reason });
    },

    getMine() {
        return axios.get('/hr/absence-requests/mine');
    },

    getPendingSigner() {
        return axios.get('/hr/absence-requests/pending-signer');
    },
};

export default absenceRequestApi;