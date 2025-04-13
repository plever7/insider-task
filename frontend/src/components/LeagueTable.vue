<template>
  <div class="league-table">
    <table>
      <thead>
      <tr>
        <th>Pos</th>
        <th>Team</th>
        <th>P</th>
        <th>W</th>
        <th>D</th>
        <th>L</th>
        <th>GF</th>
        <th>GA</th>
        <th>GD</th>
        <th>Pts</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(team, index) in teams" :key="team.id">
        <td>{{ index + 1 }}</td>
        <td>{{ team.name }}</td>
        <td>{{ team.played }}</td>
        <td>{{ team.won }}</td>
        <td>{{ team.drawn }}</td>
        <td>{{ team.lost }}</td>
        <td>{{ team.goalsFor }}</td>
        <td>{{ team.goalsAgainst }}</td>
        <td>{{ team.goalDifference }}</td>
        <td><strong>{{ team.points }}</strong></td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'LeagueTable',
  props: {
    teams: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>
.league-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.75rem;
  text-align: center;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
}

tr:hover {
  background-color: #f5f5f5;
}

tbody tr:nth-child(1) {
  background-color: #dff0d8;
}

tbody tr:nth-child(2) {
  background-color: #d9edf7;
}

tbody tr:nth-child(3) {
  background-color: #fcf8e3;
}

tbody tr:nth-child(4) {
  background-color: #f2dede;
}

@media (max-width: 768px) {
  th, td {
    padding: 0.5rem 0.25rem;
    font-size: 0.9rem;
  }
}
</style>