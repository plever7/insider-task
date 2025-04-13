CREATE DATABASE IF NOT EXISTS football_league;
USE football_league;

-- Grant privileges
GRANT ALL PRIVILEGES ON football_league.* TO 'app'@'%';
FLUSH PRIVILEGES;