import axios from 'axios'

const API_URL = process.env.VUE_APP_API_URL || 'http://localhost:8080/api'

const apiClient = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    timeout: 10000
})

export default {
    // League management
    createLeague(teams) {
        return apiClient.post('/leagues', { teams })
    },

    getCurrentLeague() {
        return apiClient.get('/leagues/current')
    },

    // Simulation control
    playNextWeek(leagueId) {
        return apiClient.post(`/leagues/${leagueId}/play-next`)
    },

    playAllWeeks(leagueId) {
        return apiClient.post(`/leagues/${leagueId}/play-all`)
    },

    updateMatchResult(leagueId, gameId, homeGoals, awayGoals) {
        return apiClient.put(`/leagues/${leagueId}/games/${gameId}`, {
            homeGoals,
            awayGoals
        })
    }
}