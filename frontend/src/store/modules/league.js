import { defineStore } from 'pinia'
import api from '@/services/api'

export const useLeagueStore = defineStore('league', {
    state: () => ({
        league: null,
        loading: false,
        error: null,
        currentResults: [],
        weeklyResults: {},
        predictions: {}
    }),

    getters: {
        isLeagueCreated: (state) => !!state.league,
        isLeagueCompleted: (state) => state.league?.isCompleted || false,
        currentWeek: (state) => state.league?.currentWeek || 0,
        teams: (state) => state.league?.teams || [],
        standings: (state) => {
            if (!state.league) return []

            return [...state.league.teams].sort((a, b) => {
                // Sort by points
                if (a.points !== b.points) {
                    return b.points - a.points
                }

                // Then by goal difference
                if (a.goalDifference !== b.goalDifference) {
                    return b.goalDifference - a.goalDifference
                }

                // Then by goals scored
                return b.goalsFor - a.goalsFor
            })
        }
    },

    actions: {
        async createLeague(teams) {
            this.loading = true
            this.error = null

            try {
                const response = await api.createLeague(teams)
                this.league = response.data.league
                return this.league
            } catch (error) {
                this.error = error.response?.data?.error || 'An error occurred while creating the league'
                throw error
            } finally {
                this.loading = false
            }
        },

        async fetchCurrentLeague() {
            this.loading = true
            this.error = null

            try {
                const response = await api.getCurrentLeague()
                this.league = response.data.league
                return this.league
            } catch (error) {
                if (error.response?.status === 404) {
                    this.league = null
                    return null
                }

                this.error = error.response?.data?.error || 'An error occurred while fetching the league'
                throw error
            } finally {
                this.loading = false
            }
        },

        async playNextWeek() {
            if (!this.league) return

            this.loading = true
            this.error = null

            try {
                const response = await api.playNextWeek(this.league.id)
                this.league = response.data.league
                this.currentResults = response.data.results
                return response.data
            } catch (error) {
                this.error = error.response?.data?.error || 'An error occurred while playing the next week'
                throw error
            } finally {
                this.loading = false
            }
        },

        async playAllWeeks() {
            if (!this.league) return

            this.loading = true
            this.error = null

            try {
                const response = await api.playAllWeeks(this.league.id)
                this.league = response.data.league
                this.weeklyResults = response.data.results
                return response.data
            } catch (error) {
                this.error = error.response?.data?.error || 'An error occurred while playing all weeks'
                throw error
            } finally {
                this.loading = false
            }
        },

        async updateMatchResult(gameId, homeGoals, awayGoals) {
            if (!this.league) return

            this.loading = true
            this.error = null

            try {
                const response = await api.updateMatchResult(this.league.id, gameId, homeGoals, awayGoals)
                this.league = response.data.league
                return response.data
            } catch (error) {
                this.error = error.response?.data?.error || 'An error occurred while updating the match result'
                throw error
            } finally {
                this.loading = false
            }
        },
        resetState() {
            this.league = null
            this.loading = false
            this.error = null
            this.currentResults = []
            this.weeklyResults = {}
            this.predictions = {}
        },
        setPredictions(predictions) {
            this.predictions = predictions
        }
    }
})