# SwitchedHit MVP

SwitchedHit is a fantasy sports management game where users create and manage virtual cricket teams, participate in auctions, simulate matches, and compete in leagues.

## Project Overview

This project is built using the Fat-Free Framework (F3) for PHP, with MySQL as the database. It includes a backend API and serves HTML templates for the frontend.

## Setup Instructions

### Prerequisites
- PHP 7.4+ (with CLI)
- Composer
- MySQL database (online server configured)

### Installation
1. Clone the repository:
   ```
   git clone https://github.com/justforfreecopilot/switchedhit-renewed.git
   cd switchedhit-renewed
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Configure environment:
   - Copy `.env` and fill in your database credentials and JWT secret.

4. Set up the database:
   - Run the schema: `mysql -h <host> -u <user> -p <db> < db/schema.sql`
   - Run the seed: `mysql -h <host> -u <user> -p <db> < db/seed.sql`

5. Start the development server:
   ```
   php -S localhost:8080
   ```

6. Access the application at `http://localhost:8080`

## Project Structure
- `app/` - Controllers and models
- `config/` - Configuration files
- `ui/` - F3 templates
- `db/` - Database schema and seed files
- `html/` - Static HTML templates

## Usage
- Home page: `/`
- Login: `/login`
- Register: `/register`

## Development
- Run tests: (TBD)
- Build: (TBD)

## Contributing
(TBD)
