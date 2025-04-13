<template>
  <div class="simulation">
    <div v-if="store.error" class="alert alert-danger">
      {{ store.error }}
    </div>

    <div class="grid-layout">
      <div class="card">
        <h2 class="card-title">League Table</h2>
        <LeagueTable :teams="store.standings" />
      </div>

      <div class="card">
        <h2 class="card-title">
          Match Results - Week {{ store.currentWeek }}
        </h2>
        <MatchResult
            :results="store.currentResults"
            :editable="true"
            @update-result="updateMatchResult"
        />

        <div class="actions">
          <button
              @click="playNextWeek"
              class="btn-primary"
              :disabled="store.loading || store.isLeagueCompleted"
          >
            {{ store.loading ? 'Playing...' : 'Play Next Week' }}
          </button>

          <button
              @click="playAllWeeks"
              class="btn-secondary"
              :disabled="store.loading || store.isLeagueCompleted"
          >
            {{ store.loading ? 'Playing...' : 'Play All Remaining Weeks' }}
          </button>
        </div>
      </div>

      <div class="card" v-if="store.currentWeek >= 4 || Object.keys(store.predictions).length > 0">
        <h2 class="card-title">Predictions</h2>
        <Predictions
            :teams="store.standings"
            :week="store.currentWeek"
            :is-completed="store.isLeagueCompleted"
            @save-predictions="savePredictions"
        />
      </div>
    </div>

    <div v-if="store.isLeagueCompleted" class="card">
      <h2 class="card-title">League Completed!</h2>
      <p>The winner is: <strong>{{ getWinner() }}</strong></p>

      <div v-if="Object.keys(store.predictions).length > 0" class="prediction-results">
        <h3>Your Predictions</h3>
        <p>You predicted: {{ getPredictionText() }}</p>
        <p>Accuracy: {{ getPredictionAccuracy() }}</p>
      </div>

      <div class="actions">
        <button @click="resetAndCreateNew" class="btn-primary">
          Create New League
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLeagueStore } from '@/store/modules/league'
import LeagueTable from '../components/LeagueTable.vue'
import MatchResult from '../components/MatchResult.vue'
import Predictions from '../components/Predictions.vue'

export default {
  name: 'SimulationView',
  components: {
    LeagueTable,
    MatchResult,
    Predictions
  },
  props: {
    id: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const store = useLeagueStore()
    const router = useRouter()

    onMounted(async () => {
      try {
        if (!store.league || store.league.id !== parseInt(props.id)) {
          await store.fetchCurrentLeague()
        }
      } catch (error) {
        console.error('Error fetching league:', error)
      }
    })

    const playNextWeek = async () => {
      try {
        await store.playNextWeek()
      } catch (error) {
        console.error('Error playing next week:', error)
      }
    }

    const playAllWeeks = async () => {
      try {
        await store.playAllWeeks()
      } catch (error) {
        console.error('Error playing all weeks:', error)
      }
    }

    const updateMatchResult = async (gameId, homeGoals, awayGoals) => {
      try {
        await store.updateMatchResult(gameId, homeGoals, awayGoals)
      } catch (error) {
        console.error('Error updating match result:', error)
      }
    }

    const savePredictions = (predictions) => {
      store.setPredictions(predictions)
    }

    const getWinner = () => {
      if (!store.standings.length) return 'N/A'
      return store.standings[0].name
    }

    const getPredictionText = () => {
      if (Object.keys(store.predictions).length === 0) return 'None'

      return Object.entries(store.predictions)
          .map(([position, teamId]) => {
            const team = store.teams.find(t => t.id === parseInt(teamId))
            return `${position}. ${team?.name || 'Unknown'}`
          })
          .join(', ')
    }

    const getPredictionAccuracy = () => {
      if (Object.keys(store.predictions).length === 0 || !store.isLeagueCompleted) {
        return 'N/A'
      }

      let correctPredictions = 0
      const totalPredictions = Object.keys(store.predictions).length

      store.standings.forEach((team, index) => {
        const position = (index + 1).toString()
        if (store.predictions[position] === team.id) {
          correctPredictions++
        }
      })

      const accuracy = (correctPredictions / totalPredictions) * 100
      return `${correctPredictions}/${totalPredictions} (${accuracy.toFixed(2)}%)`
    }

    const resetAndCreateNew = () => {
      router.push('/')
    }

    return {
      store,
      playNextWeek,
      playAllWeeks,
      updateMatchResult,
      savePredictions,
      getWinner,
      getPredictionText,
      getPredictionAccuracy,
      resetAndCreateNew
    }
  }
}
</script>

<style scoped>
.grid-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .grid-layout {
    grid-template-columns: 1fr 1fr;
  }
}

@media (min-width: 1200px) {
  .grid-layout {
    grid-template-columns: 1fr 1fr 1fr;
  }
}

.actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  justify-content: center;
}

.btn-primary {
  background-color: #3498db;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary:hover {
  background-color: #2980b9;
}

.btn-secondary {
  background-color: #2ecc71;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-secondary:hover {
  background-color: #27ae60;
}

.prediction-results {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}
</style>