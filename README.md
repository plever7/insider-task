# Insider Champions League

A football league simulation application with a PHP backend and Vue.js frontend.

## Project Overview

This application simulates a football league with 4 teams, complete with:
- Team management with different strength levels
- Match simulations based on team strengths
- League table with proper football statistics
- Prediction system for final standings
- Ability to play matches week by week or all at once
- Option to edit match results manually

## Technology Stack

### Backend
- PHP 8.4
- Slim Framework 4
- Doctrine ORM
- Domain-Driven Design architecture
- Docker containerization

### Frontend
- Vue 3
- Pinia for state management
- Axios for API requests
- Docker containerization

## Project Structure

```
├── backend/
│   ├── bin/
│   ├── config/
│   ├── docker/
│   ├── migrations/
│   ├── public/
│   └── src/
│       ├── Application/
│       ├── Domain/
│       ├── Infrastructure/
│       └── Presentation/
├── frontend/
│   ├── docker/
│   ├── public/
│   └── src/
│       ├── assets/
│       ├── components/
│       ├── router/
│       ├── services/
│       ├── store/
│       └── views/
└── run.sh
```

## Features

- **Team Management**: Create teams with different strength levels (low, medium, high)
- **Match Simulation**: Automatic match simulation based on team strengths and home advantage
- **League Table**: Properly calculated standings based on points, goal difference, and goals scored
- **Prediction System**: Make predictions about final standings and see your accuracy
- **Fixture Generation**: Round-robin tournament format where each team plays against all others twice
- **Multiple Leagues**: Create and manage multiple league simulations

## Getting Started

### Prerequisites
- Docker
- Docker Compose

### Installation

1. Clone the repository:
```bash
git clone https://github.com/plever7/insider-task
cd insider-task
```

2. Run the installation script:
```bash
chmod +x run.sh
./run.sh
```

3. Access the application:
    - Frontend: http://localhost:8081
    - Backend API: http://localhost:8080/api

## API Endpoints

- `POST /api/leagues` - Create a new league with 4 teams
- `GET /api/leagues/current` - Get current league data
- `POST /api/leagues/{id}/play-next` - Play next week's matches
- `POST /api/leagues/{id}/play-all` - Play all remaining matches
- `PUT /api/leagues/{leagueId}/games/{gameId}` - Update a match result
