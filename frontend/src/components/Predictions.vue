<template>
  <div class="predictions">
    <p v-if="isCompleted" class="completed-message">
      League has completed. You can no longer make predictions.
    </p>

    <p v-else-if="week < 4" class="waiting-message">
      Predictions will be available after week 4.
    </p>

    <div v-else class="prediction-form">
      <p>Predict the final standings:</p>

      <div class="prediction-inputs">
        <div
            v-for="position in 4"
            :key="position"
            class="prediction-row"
        >
          <label :for="'position-' + position">Position {{ position }}:</label>
          <select
              :id="'position-' + position"
              v-model="predictions[position]"
              class="prediction-select"
          >
            <option value="">Select a team</option>
            <option
                v-for="team in availableTeams(position)"
                :key="team.id"
                :value="team.id"
            >
              {{ team.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="actions">
        <button
            @click="savePredictions"
            class="btn-primary"
            :disabled="!isValid"
        >
          Save Predictions
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'Predictions',
  props: {
    teams: {
      type: Array,
      required: true
    },
    week: {
      type: Number,
      required: true
    },
    isCompleted: {
      type: Boolean,
      default: false
    }
  },
  emits: ['save-predictions'],
  setup(props, { emit }) {
    const predictions = ref({
      1: '',
      2: '',
      3: '',
      4: ''
    })

    const isValid = computed(() => {
      // Check if all positions have been filled
      for (let i = 1; i <= 4; i++) {
        if (!predictions.value[i]) {
          return false
        }
      }

      // Check for duplicates
      const selectedTeams = Object.values(predictions.value)
      const uniqueTeams = new Set(selectedTeams)
      return selectedTeams.length === uniqueTeams.size
    })

    const availableTeams = (position) => {
      // Get the teams that haven't been selected for other positions
      const selectedTeamIds = Object.entries(predictions.value)
          .filter(([pos, teamId]) => pos !== position.toString() && teamId)
          .map(([, teamId]) => teamId)  // Use empty placeholder to skip the first variable

      return props.teams.filter(team => !selectedTeamIds.includes(team.id))
    }

    const savePredictions = () => {
      if (isValid.value) {
        emit('save-predictions', predictions.value)
      }
    }

    return {
      predictions,
      isValid,
      availableTeams,
      savePredictions
    }
  }
}
</script>

<style scoped>
.predictions {
  margin-bottom: 1.5rem;
}

.waiting-message, .completed-message {
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
  text-align: center;
  color: #666;
}

.prediction-form {
  margin-top: 1rem;
}

.prediction-inputs {
  margin: 1rem 0;
}

.prediction-row {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.prediction-row label {
  width: 120px;
  text-align: right;
  padding-right: 1rem;
}

.prediction-select {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.actions {
  margin-top: 1.5rem;
  display: flex;
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

.btn-primary:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}
</style>