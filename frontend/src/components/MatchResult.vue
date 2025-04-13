<template>
  <div class="match-results">
    <div v-if="!results || results.length === 0" class="no-results">
      No matches to display for this week.
    </div>

    <div v-else class="results-container">
      <div
          v-for="result in results"
          :key="result.id"
          class="match-card"
      >
        <div class="match-teams">
          <div class="team home-team">{{ result.homeTeamName }}</div>
          <div v-if="!editable || result.isPlayed" class="score">
            {{ result.score || '0 - 0' }}
          </div>
          <div v-else class="score-input">
            <input
                type="number"
                v-model.number="editedScores[result.id].home"
                min="0"
                class="score-field"
            >
            -
            <input
                type="number"
                v-model.number="editedScores[result.id].away"
                min="0"
                class="score-field"
            >
            <button
                @click="saveResult(result.id)"
                class="save-btn"
                :disabled="!isValidScore(result.id)"
            >
              Save
            </button>
          </div>
          <div class="team away-team">{{ result.awayTeamName }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'

export default {
  name: 'MatchResult',
  props: {
    results: {
      type: Array,
      default: () => []
    },
    editable: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update-result'],
  setup(props, { emit }) {
    const editedScores = ref({})

    // Initialize edited scores when results change
    watch(() => props.results, (newResults) => {
      if (newResults && newResults.length > 0) {
        newResults.forEach(result => {
          if (!editedScores.value[result.id]) {
            editedScores.value[result.id] = {
              home: result.homeGoals || 0,
              away: result.awayGoals || 0
            }
          }
        })
      }
    }, { immediate: true })

    const isValidScore = (resultId) => {
      const score = editedScores.value[resultId]
      return score &&
          typeof score.home === 'number' &&
          typeof score.away === 'number' &&
          score.home >= 0 &&
          score.away >= 0
    }

    const saveResult = (resultId) => {
      if (!isValidScore(resultId)) return

      const { home, away } = editedScores.value[resultId]
      emit('update-result', resultId, home, away)
    }

    return {
      editedScores,
      isValidScore,
      saveResult
    }
  }
}
</script>

<style scoped>
.match-results {
  margin-bottom: 1.5rem;
}

.no-results {
  text-align: center;
  padding: 1rem;
  color: #888;
}

.results-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.match-card {
  background-color: #f8f9fa;
  border-radius: 4px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.match-teams {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.team {
  flex: 1;
}

.home-team {
  text-align: right;
  padding-right: 0.5rem;
}

.away-team {
  text-align: left;
  padding-left: 0.5rem;
}

.score {
  font-weight: bold;
  padding: 0 0.5rem;
  min-width: 60px;
  text-align: center;
}

.score-input {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.score-field {
  width: 40px;
  padding: 0.25rem;
  text-align: center;
  border: 1px solid #ccc;
  border-radius: 3px;
}

.save-btn {
  margin-left: 0.5rem;
  padding: 0.25rem 0.5rem;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.8rem;
}

.save-btn:hover {
  background-color: #2980b9;
}

.save-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}
</style>