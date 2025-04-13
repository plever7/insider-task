#!/bin/bash

# Colors for terminal output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting Insider Champions League setup...${NC}"

# Check if Docker is installed
if ! command -v docker &> /dev/null
then
    echo -e "${RED}Docker could not be found. Please install Docker and try again.${NC}"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null
then
    echo -e "${RED}Docker Compose could not be found. Please install Docker Compose and try again.${NC}"
    exit 1
fi

# Setup Backend
echo -e "${YELLOW}Setting up backend...${NC}"
cd backend

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env file...${NC}"
    cp .env.example .env
    echo -e "${GREEN}.env file created.${NC}"
fi

# Build and start the backend containers
echo -e "${YELLOW}Building and starting backend containers...${NC}"
docker-compose up -d --build

# Wait for containers to be ready
echo -e "${YELLOW}Waiting for containers to be ready...${NC}"
sleep 10

# Install Composer dependencies
echo -e "${YELLOW}Installing Composer dependencies...${NC}"
docker-compose exec app composer install

# Run migrations
echo -e "${YELLOW}Running database migrations...${NC}"
docker-compose exec app ./bin/console migrations:migrate --no-interaction

# Run tests
echo -e "${YELLOW}Running tests...${NC}"
docker-compose exec app vendor/bin/phpunit tests

# Setup Frontend
echo -e "${YELLOW}Setting up frontend...${NC}"
cd ../frontend

# Create directories if they don't exist
mkdir -p src/store/modules

# Move files to their correct locations
if [ ! -f src/store/modules/league.js ]; then
    echo -e "${YELLOW}Setting up store modules...${NC}"
    cp -n src/store/modules/league.js src/store/modules/ 2>/dev/null || true
fi

# Build and start the frontend container
echo -e "${YELLOW}Building and starting frontend container...${NC}"
docker-compose up -d --build

# Install NPM dependencies if needed
echo -e "${YELLOW}Installing NPM dependencies...${NC}"
docker-compose exec frontend npm install

echo -e "${GREEN}Setup complete!${NC}"
echo -e "${GREEN}Backend is running at: http://localhost:8080${NC}"
echo -e "${GREEN}Frontend is running at: http://localhost:8081${NC}"
echo -e "${YELLOW}To stop the application, run: docker-compose down in both backend and frontend directories${NC}"