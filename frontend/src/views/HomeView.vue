<template>
  <div class="home">
    <div v-if="store.error" class="alert alert-danger">
      {{ store.error }}
    </div>

    <div v-if="store.isLeagueCreated">
      <div class="card">
        <h2 class="card-title">League Table</h2>
        <LeagueTable :teams="store.standings" />

        <div class="actions">
          <button
              @click="navigateToSimulation"
              class="btn-primary"
          >
            Go to Simulation
          </button>
          <button @click="resetAndCreateNew" class="btn-secondary">
            Create New League
          </button>
        </div>
      </div>
    </div>

    <div v-else class="card">
      <h2 class="card-title">Create New League</h2>
      <form @submit.prevent="createLeague">
        <div class="form-group">
          <p>Enter 4 teams for the league:</p>
        </div>

        <div v-for="(team, index) in teams" :key="index" class="form-group">
          <label :for="'team-' + index">Team {{ index + 1 }}</label>
          <div class="team-input">
            <input
                :id="'team-' + index"
                v-model="team.name"
                type="text"
                class="form-control"
                required
                placeholder="Team name"
            >
            <select v-model="team.strength" class="form-control">
              <option value="low">Low Strength</option>
              <option value="medium">Medium Strength</option>
              <option value="high">High Strength</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn-primary" :disabled="store.loading">
          {{ store.loading ? 'Creating...' : 'Create League' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLeagueStore } from '@/store/modules/league'
import LeagueTable from '../components/LeagueTable.vue'

export default {
  name: 'HomeView',
  components: {
    LeagueTable
  },
  setup() {
    const store = useLeagueStore()
    const router = useRouter()

    const teams = ref([
      { name: 'Manchester City', strength: 'high' },
      { name: 'Liverpool', strength: 'high' },
      { name: 'Arsenal', strength: 'medium' },
      { name: 'Chelsea', strength: 'medium' }
    ])

    onMounted(async () => {
      try {
        await store.fetchCurrentLeague()
      } catch (error) {
        console.error('Error fetching league:', error)
      }
    })

    const createLeague = async () => {
      try {
        const league = await store.createLeague(teams.value)
        // Navigate to simulation after league creation
        router.push({ name: 'simulation', params: { id: league.id } })
      } catch (error) {
        console.error('Error creating league:', error)
      }
    }

    const resetAndCreateNew = () => {
      store.resetState()
    }

    const navigateToSimulation = () => {
      if (store.league) {
        router.push({ name: 'simulation', params: { id: store.league.id } })
      }
    }

    return {
      store,
      teams,
      createLeague,
      navigateToSimulation,
      resetAndCreateNew
    }
  }
}
</script>

<style scoped>
.actions {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
}

.team-input {
  display: flex;
  gap: 0.5rem;
}

.team-input input {
  flex: 3;
}

.team-input select {
  flex: 2;
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

.btn-primary:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}
</style>