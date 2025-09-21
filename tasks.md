# SwitchedHit MVP Tasks

This document outlines the detailed tasks for building the SwitchedHit MVP, organized by sprint and categorized into Frontend, Backend, and Database tasks.

---

## Development Structure

### Frontend
- **Setup**: Utilize the HTML templates located in the `/html` folder as the foundation for the application. These templates are pre-built with various page types and components (e.g., authentication pages, dashboards, forms) using HTML, CSS, and JavaScript. Ensure the project is set up to serve static assets from `/html/assets/` (CSS, JS, images, etc.).
- **Configuration**: Convert templates to Fat-Free Framework (F3) template format by replacing static content with F3 template variables (e.g., `{{@variable}}` for dynamic data). Link necessary CSS/JS libraries (e.g., Bootstrap for responsive design, jQuery for interactivity). Configure routing in F3 to render these templates for specific pages.
- **Explanation**: The templates cover essential UI components like login forms, dashboards, and data tables. We will customize them by integrating dynamic data from the backend, adding form validations using JavaScript, and ensuring mobile responsiveness. For each feature, identify the closest matching template (e.g., use `auth-login-basic.html` for login page) and adapt it to include app-specific elements like team selection or player stats.

### Backend
- **Setup**: Install Fat-Free Framework (F3) via Composer (`composer require bcosca/fatfree`). Set up the project structure with directories like `app/` for controllers and models, `config/` for configurations, and `ui/` for templates. Start the development server using PHP's built-in server or a web server like Apache/Nginx, accessible at `localhost:8080`.
- **Configuration**: Use `.env` files for environment variables (e.g., database credentials, JWT secret keys). Configure F3's database connection in a config file (e.g., `config.ini`) using the DB module. Set up routing in `index.php` or a routes file to handle API endpoints and page rendering. Enable CORS if needed for API access.
- **Explanation**: All backend logic, including APIs and page serving, will be handled by F3. Implement controllers for business logic (e.g., user authentication, player management), models for data interaction, and use F3's built-in features like sessions and caching. APIs should follow RESTful conventions and return JSON responses. For authentication, use JWT tokens stored in sessions or cookies.

### Database
- **Setup**: Use MySQL as the database engine. Create `db/schema.sql` to define all tables, stored procedures (SPs), and relationships. Create `db/seed.sql` for initial seed data (e.g., admin user). Run migrations by executing the SQL files in a MySQL client or via a script.
- **Configuration**: Connect to the database using F3's DB module with credentials from `.env`. Ensure foreign key constraints and indexes are defined for performance. Use transactions for data integrity in complex operations.
- **Explanation**: Define database schema incrementally per sprint. Use SPs for complex logic like match simulations or auction resolutions, while simple CRUD operations can use direct SQL queries in models. Avoid storing sensitive data; use hashing for passwords. Ensure data validation at the database level with constraints.

---

## Sprint 1: Login/Register + Dashboard

### Frontend Tasks
- **Design and implement login page**: Use the `auth-login-basic.html` template as base. Customize the form to include email and password fields. Add client-side validation using JavaScript (e.g., check for valid email format). Implement AJAX submission to the backend API for authentication. On success, redirect to dashboard; on failure, display error messages.
- **Design and implement registration page**: Create a new page based on form templates (e.g., adapt from `auth-login-basic.html` or use a blank form template). Include fields for team name, stadium name, details (textarea), and pitch type (dropdown with options like Grass, Turf). Add form validation for required fields and email uniqueness. Submit via AJAX to registration API, then redirect to login or auto-login.
- **Create user dashboard**: Adapt the `app-ecommerce-dashboard.html` or similar dashboard template. Display team information (name, stadium, pitch type) and basic stats (e.g., team value, player count). Use dynamic data from backend APIs. Include sections for quick actions like viewing players or configuring lineup.
- **Implement responsive design**: Ensure all pages use Bootstrap or the template's CSS framework for mobile and web compatibility. Test on various screen sizes and adjust layouts as needed (e.g., stack elements vertically on mobile).
- **Add form validation**: Implement JavaScript validation libraries (e.g., included in templates) for real-time feedback on forms. Validate email format, password strength, and required fields. Prevent form submission on invalid data.
- **Create navigation components**: Design a navigation bar or sidebar based on templates like `horizontal-menu-template/app-ecommerce-dashboard.html`. Include links for dashboard, players, lineup, etc., visible only to authenticated users. Implement logout functionality.

### Backend Tasks
- **Implement user authentication API**: Create F3 routes for POST /api/login and POST /api/register. Use F3's Auth module or custom logic to verify credentials. Return JWT tokens on success. Handle password hashing with bcrypt.
- **Create user registration endpoint**: Build a POST /api/register route that accepts user email, password, and team details. Validate input, create user and team records in DB, and return success response. Ensure email uniqueness.
- **Implement session management and JWT tokens**: Configure F3 to use sessions for state management. Generate and validate JWT tokens for API authentication. Store tokens in localStorage on frontend for subsequent requests.
- **Create admin authentication system**: Extend authentication to support admin roles. Add role-based access control (RBAC) in F3 routes (e.g., check user role before allowing admin endpoints).
- **Build CRUD endpoints for user management (admin only)**: Implement GET /api/users, POST /api/users, PUT /api/users/:id, DELETE /api/users/:id. Restrict to admin users. Include pagination and filtering.
- **Implement user profile update functionality**: Create PUT /api/user/profile endpoint for users to update their details (e.g., email, team info). Validate changes and update DB.

### Database Tasks
- **Design user table schema**:
  ```sql
  CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      email VARCHAR(255) UNIQUE NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      role ENUM('user', 'admin') DEFAULT 'user',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Create team table schema**:
  ```sql
  CREATE TABLE teams (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      stadium_name VARCHAR(255),
      details TEXT,
      pitch_type ENUM('grass', 'turf', 'artificial') NOT NULL,
      user_id INT NOT NULL,
      FOREIGN KEY (user_id) REFERENCES users(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Set up database migrations**: Add the above schemas to `db/schema.sql`. Run the script to create tables in MySQL. Ensure foreign key constraints are enforced.
- **Implement data validation constraints**: Add CHECK constraints or triggers for data integrity (e.g., ensure email is valid format, pitch_type is from allowed values). Use UNIQUE constraints for email.

---

## Sprint 2: Register Flow V2

### Frontend Tasks
- **Enhance registration flow to include team member generation**: Modify the registration page to automatically generate a starting squad of 11 players upon team creation. Display a loading indicator during generation and show a success message with player count. Ensure the flow remains smooth and user-friendly.
- **Create player list view**: Build a new page based on `app-user-list.html` or data table templates. Display a list of players with columns for name, position, age, and key stats (e.g., overall rating). Include sorting and filtering options. Link to individual player details.
- **Implement player detail modal/page**: Create a modal or dedicated page (adapt from `app-user-view-account.html`) showing detailed player info: name, position, stats breakdown (e.g., speed, strength, technique), age, morale. Include edit options for admins.
- **Add team overview page**: Design a page (based on dashboard templates) that shows the full team composition, including player photos/icons, positions, and summary stats. Visualize the team as a formation diagram.
- **Update dashboard to include player summary**: Modify the dashboard to add a section with player count, average age, team strength, and top players. Use charts or tables for visualization.

### Backend Tasks
- **Implement team member generation algorithm**: Create a service or function to generate 11 players per team with random but balanced stats. Use predefined positions (GK, LB, CB, RB, CDM, CM, CAM, LW, ST, RW, CF) and assign stats like speed (1-100), strength, technique, etc. Ensure variety and fairness.
- **Create player stats calculation logic**: Develop functions to compute derived stats like overall rating based on individual attributes. Update stats dynamically as needed (e.g., after matches).
- **Build player CRUD endpoints (admin)**: Implement GET /api/players, POST /api/players, PUT /api/players/:id, DELETE /api/players/:id. Restrict to admins. Include bulk operations if needed.
- **Add player detail management API**: Create endpoints for updating player details, such as PUT /api/players/:id/stats for stat adjustments.
- **Implement team composition validation**: Add logic to ensure teams have valid compositions (e.g., at least 1 GK, max 5 DEF, etc.). Validate during lineup setup or generation.

### Database Tasks
- **Design player table schema**:
  ```sql
  CREATE TABLE players (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      position ENUM('GK', 'LB', 'CB', 'RB', 'CDM', 'CM', 'CAM', 'LW', 'ST', 'RW', 'CF') NOT NULL,
      age INT DEFAULT 18,
      morale INT DEFAULT 50,
      team_id INT NOT NULL,
      FOREIGN KEY (team_id) REFERENCES teams(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Create player stats table or embed stats in player table**: Embed stats in `players` table with columns like speed (INT), strength (INT), technique (INT), overall_rating (INT computed). Alternatively, create a separate `player_stats` table linked by player_id for flexibility.
- **Add foreign key relationships**: Ensure `players.team_id` references `teams.id`, and `teams.user_id` references `users.id`. Add CASCADE on delete for data integrity.
- **Implement database indexes for performance**: Add indexes on `players.team_id`, `players.position`, and `teams.user_id` to speed up queries.

---

## Sprint 3: Setup/Configure Daily Team Lineup

### Frontend Tasks
- **Create lineup configuration interface**: Build a page (adapt from `app-kanban.html` or custom) with a pitch diagram. Allow users to select a formation (e.g., 4-4-2, 3-5-2) and assign players to positions via dropdowns or drag-and-drop.
- **Implement drag-and-drop player positioning**: Use JavaScript libraries (e.g., SortableJS or included in templates) to enable dragging players from a bench list to pitch positions. Validate position compatibility (e.g., GK only in goal).
- **Add daily stats preview and changes display**: Show current player stats and preview how they might change after the day (e.g., +1 speed). Display a summary of daily changes on the dashboard or lineup page.
- **Create age progression visualization**: Add a chart or progress bar showing player ages and when they will age up. Highlight players nearing retirement or peak performance.
- **Implement lineup save/submit functionality**: Add a save button that submits the lineup to the backend via AJAX. Provide feedback on success/failure and validation errors.

### Backend Tasks
- **Implement daily lineup management API**: Create POST /api/lineup to save the selected lineup for the day. Store player positions and validate against team composition rules.
- **Create player stats update logic for daily changes**: Develop a daily job or endpoint to update player stats randomly or based on training. For example, slight increases in attributes, affected by morale.
- **Build age handling system with automatic progression**: Implement a cron job or scheduled task to increment player ages daily. Adjust stats based on age (e.g., peak at 25-30, decline after).
- **Add morale management for players**: Create logic to update morale based on events (e.g., winning matches increases morale, losing decreases). Use it to modify stat changes or performance.
- **Implement lineup validation rules**: Ensure lineups have 11 players, correct positions, and no duplicates. Return errors if invalid.

### Database Tasks
- **Create lineup table schema**:
  ```sql
  CREATE TABLE lineups (
      id INT AUTO_INCREMENT PRIMARY KEY,
      team_id INT NOT NULL,
      FOREIGN KEY (team_id) REFERENCES teams(id),
      date DATE NOT NULL,
      formation VARCHAR(10),
      player_positions JSON,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(team_id, date)
  );
  ```
- **Add daily_stats table for tracking changes**:
  ```sql
  CREATE TABLE daily_stats (
      id INT AUTO_INCREMENT PRIMARY KEY,
      player_id INT NOT NULL,
      FOREIGN KEY (player_id) REFERENCES players(id),
      date DATE NOT NULL,
      stat_changes JSON,
      reason VARCHAR(255)
  );
  ```
- **Implement age and morale fields in player table**: Add columns age (INT), morale (INT) to `players` table if not already present. Update from Sprint 2 schema.
- **Create database triggers for automatic age updates**: Add a trigger or stored procedure to update player ages daily. Optionally, update stats based on age milestones.

---

## Sprint 4: Auction/MarketPlace

### Frontend Tasks
- **Design auction/marketplace interface**: Create a page (based on `app-ecommerce-product-list.html`) listing available players for auction or sale. Include filters for position, price, and status. Separate tabs or sections for auctions and marketplace.
- **Implement blind bid system UI**: For auctions, show player details and a bid input form. Users enter their maximum bid without seeing others. Display bid submission confirmation and countdown to auction end.
- **Create player listing with buy/sell options**: Allow users to list their players for sale with a price. Include buy buttons for marketplace items. Show ownership and transaction history.
- **Add user wallet/budget display**: Display current budget on the dashboard and marketplace pages. Update in real-time after transactions.
- **Implement bid submission and confirmation**: Use AJAX to submit bids. Show success messages and prevent multiple bids on the same item.

### Backend Tasks
- **Build auction system with blind bidding logic**: Create endpoints for starting auctions (admin), submitting bids (POST /api/auctions/:id/bid), and resolving auctions at end time (e.g., via cron). Assign winner to highest bid, handle ties randomly.
- **Implement marketplace buy/sell endpoints**: Build POST /api/marketplace/list to list a player for sale, POST /api/marketplace/buy/:id to purchase. Transfer ownership and deduct/add money.
- **Create bid processing and resolution system**: Store bids securely, resolve auctions by comparing max bids. Notify winners/losers via email or in-app messages.
- **Add user money management API**: Implement GET /api/user/budget, POST /api/user/add-money (admin), and automatic deductions on purchases. Prevent negative balances.
- **Implement transaction validation and security**: Validate bids (e.g., sufficient funds), use transactions for atomicity. Add rate limiting to prevent spam.

### Database Tasks
- **Create auction table schema**:
  ```sql
  CREATE TABLE auctions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      player_id INT NOT NULL,
      FOREIGN KEY (player_id) REFERENCES players(id),
      starting_price DECIMAL(10,2),
      end_time DATETIME,
      status ENUM('active', 'ended'),
      winner_id INT,
      FOREIGN KEY (winner_id) REFERENCES users(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Design marketplace table for buy/sell listings**:
  ```sql
  CREATE TABLE marketplace (
      id INT AUTO_INCREMENT PRIMARY KEY,
      player_id INT NOT NULL,
      FOREIGN KEY (player_id) REFERENCES players(id),
      seller_id INT NOT NULL,
      FOREIGN KEY (seller_id) REFERENCES users(id),
      price DECIMAL(10,2),
      status ENUM('listed', 'sold'),
      buyer_id INT,
      FOREIGN KEY (buyer_id) REFERENCES users(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Add user_money table or field for budget tracking**: Add column `budget` (DECIMAL(10,2) DEFAULT 10000) to `users` table. Alternatively, create a separate `user_wallets` table.
- **Implement transaction history logging**:
  ```sql
  CREATE TABLE transactions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      type ENUM('bid', 'purchase', 'sale'),
      amount DECIMAL(10,2),
      description TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```

---

## Sprint 5: Simulate Practice Match

### Frontend Tasks
- **Create match simulation viewer**: Build a page (adapt from `app-calendar.html` or custom) to display ongoing or completed matches. Show team lineups, score, and progress.
- **Implement ball-by-ball commentary display**: Create a live feed or scrollable list showing detailed events (e.g., "Player X passes to Y", "Goal scored by Z"). Update in real-time during simulation.
- **Add match scorecard visualization**: Display a summary scorecard with goals, assists, player performances, and final result. Use tables or charts.
- **Design practice match setup interface**: Allow users to select opponent (AI or another team), formation, and start simulation. Show pre-match details.
- **Implement real-time simulation progress**: Use WebSockets or polling to update the viewer as the match progresses. Show a progress bar for simulation steps.

### Backend Tasks
- **Implement deterministic match simulation algorithm**: Create a simulation engine that uses player stats to determine outcomes. For each "ball" or event, calculate probabilities (e.g., chance of goal based on attack/defense stats). Use seeded random for determinism.
- **Create ball-by-ball commentary generation**: Generate textual commentary for each event (e.g., random phrases based on event type). Store and stream to frontend.
- **Build match result calculation logic**: Compute final score, winner, and statistics based on simulation events. Update player stats post-match (e.g., goals scored).
- **Add configurable simulation parameters**: Allow admins to set parameters like event frequency, stat weights via config or API. Store in DB.
- **Implement match scheduling and execution**: Create POST /api/matches/simulate to start a simulation. Use background jobs or async processing for long simulations.

### Database Tasks
- **Create match table schema**:
  ```sql
  CREATE TABLE matches (
      id INT AUTO_INCREMENT PRIMARY KEY,
      home_team_id INT NOT NULL,
      FOREIGN KEY (home_team_id) REFERENCES teams(id),
      away_team_id INT,
      date DATETIME,
      status ENUM('scheduled', 'ongoing', 'completed'),
      result VARCHAR(10),
      commentary TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Design ball-by-ball event logging**:
  ```sql
  CREATE TABLE match_events (
      id INT AUTO_INCREMENT PRIMARY KEY,
      match_id INT NOT NULL,
      FOREIGN KEY (match_id) REFERENCES matches(id),
      event_type ENUM('goal', 'pass', 'foul'),
      description TEXT,
      minute INT,
      player_id INT,
      FOREIGN KEY (player_id) REFERENCES players(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Add simulation parameters configuration table**:
  ```sql
  CREATE TABLE simulation_config (
      id INT AUTO_INCREMENT PRIMARY KEY,
      param_name VARCHAR(255),
      value FLOAT,
      description TEXT
  );
  ```
- **Implement match history storage**: Ensure matches are stored with full details. Add indexes on match_id for event queries.

---

## Sprint 6: League Simulations

### Frontend Tasks
- **Create league standings display**: Build a page (based on `app-ecommerce-manage-reviews.html` or table templates) showing league tables with positions, points, wins/losses. Include filters for different leagues/divisions.
- **Implement promotion/relegation visualization**: Add animations or highlights for teams moving between leagues. Show relegation zone warnings.
- **Add league match scheduling view**: Display upcoming fixtures in a calendar or list view. Show results for completed matches.
- **Design league management interface (admin)**: Create an admin page for creating leagues, assigning teams, and configuring rules (e.g., number of teams, promotion spots).

### Backend Tasks
- **Implement league assignment logic**: Automatically assign teams to leagues based on performance (e.g., after season, promote top teams). Handle new registrations by placing in appropriate division.
- **Create promotion/relegation system**: At season end, move top X teams up and bottom Y down. Update league assignments and notify users.
- **Build league match simulation scheduling**: Generate fixtures for each league (e.g., round-robin). Schedule simulations at set times and execute them automatically.
- **Add league management APIs (admin)**: Implement CRUD for leagues: POST /api/leagues, GET /api/leagues, etc. Include endpoints for manual adjustments.
- **Implement league statistics calculation**: Calculate points, goal difference, etc., after each match. Update standings in real-time.

### Database Tasks
- **Create league table schema**:
  ```sql
  CREATE TABLE leagues (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      division INT DEFAULT 1,
      max_teams INT,
      promotion_spots INT,
      relegation_spots INT,
      rules TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Design league_standings table**:
  ```sql
  CREATE TABLE league_standings (
      id INT AUTO_INCREMENT PRIMARY KEY,
      league_id INT NOT NULL,
      FOREIGN KEY (league_id) REFERENCES leagues(id),
      team_id INT NOT NULL,
      FOREIGN KEY (team_id) REFERENCES teams(id),
      position INT,
      points INT,
      wins INT,
      draws INT,
      losses INT,
      goals_for INT,
      goals_against INT,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```
- **Add promotion/relegation tracking**:
  ```sql
  CREATE TABLE league_history (
      id INT AUTO_INCREMENT PRIMARY KEY,
      team_id INT,
      from_league_id INT,
      to_league_id INT,
      season VARCHAR(10),
      reason ENUM('promotion', 'relegation'),
      date DATE
  );
  ```
- **Implement league match scheduling tables**:
  ```sql
  CREATE TABLE league_fixtures (
      id INT AUTO_INCREMENT PRIMARY KEY,
      league_id INT NOT NULL,
      home_team_id INT,
      away_team_id INT,
      match_date DATETIME,
      status ENUM('scheduled', 'completed'),
      result VARCHAR(10),
      match_id INT,
      FOREIGN KEY (match_id) REFERENCES matches(id)
  );
  ```