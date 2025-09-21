# SwitchedHit MVP Action Plan

## Sprint 0: Project Setup and Initialization

This sprint focuses on setting up the development environment, project structure, and foundational configurations before starting feature development in Sprint 1.

### Backend Setup Actions
- **Install PHP and Composer**: Ensure PHP (version 7.4+) and Composer are installed.
- **Install Fat-Free Framework (F3)**: Run `composer require bcosca/fatfree` to install F3.
- **Set up project structure**: Create directories: `app/` (for controllers and models), `config/` (for configurations), `ui/` (for F3 templates), `db/` (for database files).
- **Create .env file**: Set up `.env` file with placeholders for database credentials, JWT secret, and other environment variables.
- **Configure F3 application**: Create `index.php` as the entry point. Set up basic F3 configuration, routing, and autoloading.
- **Set up development server**: Configure PHP built-in server or web server to run at `localhost:8080`. Ensure URL rewriting for clean URLs.

### Database Setup Actions
- **Setup MySQL**: Setup MySQL by connecting to a Online Server
- **Set up schema file**: Create `db/schema.sql` with initial table structures based on Sprint 1 requirements (users, teams).
- **Set up seed file**: Create `db/seed.sql` with initial data (e.g., admin user).
- **Configure database connection**: In F3 config, set up DB connection using credentials from `.env`.
- **Test database connection**: Run a simple query to verify connection works.

### Development Environment Actions
- **Set up version control**: Create `.gitignore` for PHP, Composer, .env files for DB Connections, and temporary files.
- **Set up local development environment**: Ensure all components (PHP, MySQL, web server) are running and accessible.
- **Create basic documentation**: Set up README.md with project overview, setup instructions, and basic usage.

### Initial Configuration Actions
- **Configure F3 templates**: Convert a sample HTML template to F3 format (e.g., `{{@title}}` variables). Use html\front-pages-no-customizer\landing-page.html for this.
- **Set up basic routing**: Define routes for home page, login, and register in F3.
- **Implement basic error handling**: Set up F3's error handling and logging.
- **Configure CORS**: If needed, set up CORS headers for API access.
- **Set up session management**: Configure F3 sessions for user state management.

### Testing and Validation Actions
- **Test project structure**: Ensure all directories and files are in place.
- **Run initial migrations**: Execute `db/schema.sql` and `db/seed.sql` to set up initial database.
- **Verify server startup**: Start the development server and confirm it loads without errors.
- **Test basic page rendering**: Create a simple "Hello World" page using F3 to verify template rendering.
- **Document setup process**: Update README.md with detailed setup steps for future developers.

### Sprint 0 Completion Criteria
- [ ] Project directory structure is set up
- [ ] All dependencies (F3, PHP, MySQL) are installed
- [ ] Database is created and initial schema is applied
- [ ] Development server runs without errors
- [ ] Basic routing and template rendering work
- [ ] .env file is configured with necessary variables
- [ ] Version control is initialized with proper .gitignore
- [ ] README.md contains setup and project information

---

## Sprint 1: Login/Register + Dashboard
*(To be detailed based on tasks.md)*

## Sprint 2: Register Flow V2
*(To be detailed based on tasks.md)*

## Sprint 3: Setup/Configure Daily Team Lineup
*(To be detailed based on tasks.md)*

## Sprint 4: Auction/MarketPlace
*(To be detailed based on tasks.md)*

## Sprint 5: Simulate Practice Match
*(To be detailed based on tasks.md)*

## Sprint 6: League Simulations
*(To be detailed based on tasks.md)*</content>
<parameter name="filePath">d:\SwitchedHit\self-design\action_plan.md
