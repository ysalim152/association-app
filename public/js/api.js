// =====================================================
// API Client Wrapper
// =====================================================

class ApiClient {
    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl;
        this.token = localStorage.getItem('api_token');
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('api_token', token);
    }

    async request(method, endpoint, data = null) {
        const url = `${this.baseUrl}${endpoint}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        };

        if (this.token) {
            options.headers['Authorization'] = `Bearer ${this.token}`;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Erreur API');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    get(endpoint) {
        return this.request('GET', endpoint);
    }

    post(endpoint, data) {
        return this.request('POST', endpoint, data);
    }

    put(endpoint, data) {
        return this.request('PUT', endpoint, data);
    }

    delete(endpoint) {
        return this.request('DELETE', endpoint);
    }

    // Members
    getMembers(page = 1, limit = 20) {
        return this.get(`/members?page=${page}&limit=${limit}`);
    }

    getMember(id) {
        return this.get(`/members/${id}`);
    }

    createMember(data) {
        return this.post('/members', data);
    }

    updateMember(id, data) {
        return this.put(`/members/${id}`, data);
    }

    deleteMember(id) {
        return this.delete(`/members/${id}`);
    }

    // Teams
    getTeams() {
        return this.get('/teams');
    }

    getTeam(id) {
        return this.get(`/teams/${id}`);
    }

    getTeamMembers(teamId) {
        return this.get(`/teams/${teamId}/members`);
    }

    createTeam(data) {
        return this.post('/teams', data);
    }

    updateTeam(id, data) {
        return this.put(`/teams/${id}`, data);
    }

    // Matches
    getMatches(status = null) {
        let endpoint = '/matches';
        if (status) {
            endpoint += `?status=${status}`;
        }
        return this.get(endpoint);
    }

    getUpcomingMatches() {
        return this.get('/matches/upcoming');
    }

    createMatch(data) {
        return this.post('/matches', data);
    }

    updateMatch(id, data) {
        return this.put(`/matches/${id}`, data);
    }

    // Payments
    getPayments(memberId) {
        return this.get(`/payments/member/${memberId}`);
    }

    createPayment(data) {
        return this.post('/payments', data);
    }

    // Stats
    getDashboardStats() {
        return this.get('/stats/dashboard');
    }

    getRevenueStats(year) {
        return this.get(`/stats/revenue?year=${year}`);
    }

    getMembersStats() {
        return this.get('/stats/members');
    }
}

// Instance globale
const api = new ApiClient();

// Helper pour afficher erreurs API
function handleApiError(error) {
    console.error('API Error:', error);
    showNotification(
        error.message || 'Une erreur est survenue',
        'danger'
    );
}

// Helper pour afficher succès
function handleApiSuccess(response, message = 'Opération réussie') {
    showNotification(message, 'success');
    return response;
}
