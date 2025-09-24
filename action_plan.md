# SwitchedHit MVP Action Plan



## Sprint 1: Login/Register + Dashboard

This sprint focuses on implementing user authentication (login/register) and a basic dashboard for users to view their team information.

### Frontend Actions
- **Design and implement login page**: Use the `auth-login-basic.html` template as base. Customize the form to include email and password fields. Add client-side validation using JavaScript (e.g., check for valid email format). Implement AJAX submission to the backend API for authentication. On success, redirect to dashboard; on failure, display error messages.
- **Design and implement registration page**: Create a new page based on form templates (e.g., adapt from `auth-login-basic.html` or use a blank form template). Include fields for team name, stadium name, details (textarea), and pitch type (dropdown with options like Grass, Turf). Add form validation for required fields and email uniqueness. Submit via AJAX to registration API, then redirect to login or auto-login.
- **Create user dashboard**: Adapt the `app-ecommerce-dashboard.html` or similar dashboard template. Display team information (name, stadium, pitch type) and basic stats (e.g., team value, player count). Use dynamic data from backend APIs. Include sections for quick actions like viewing players or configuring lineup.
- **Implement responsive design**: Ensure all pages use Bootstrap or the template's CSS framework for mobile and web compatibility. Test on various screen sizes and adjust layouts as needed (e.g., stack elements vertically on mobile).
- **Add form validation**: Implement JavaScript validation libraries (e.g., included in templates) for real-time feedback on forms. Validate email format, password strength, and required fields. Prevent form submission on invalid data.
- **Create navigation components**: Design a navigation bar or sidebar based on templates like `horizontal-menu-template/app-ecommerce-dashboard.html`. Include links for dashboard, players, lineup, etc., visible only to authenticated users. Implement logout functionality.

### Backend Actions
- **Implement user authentication API**: Create F3 routes for POST /api/login and POST /api/register. Use F3's Auth module or custom logic to verify credentials. Return JWT tokens on success. Handle password hashing with bcrypt.
- **Create user registration endpoint**: Build a POST /api/register route that accepts user email, password, and team details. Validate input, create user and team records in DB, and return success response. Ensure email uniqueness.
- **Implement session management and JWT tokens**: Configure F3 to use sessions for state management. Generate and validate JWT tokens for API authentication. Store tokens in localStorage on frontend for subsequent requests.
- **Create admin authentication system**: Extend authentication to support admin roles. Add role-based access control (RBAC) in F3 routes (e.g., check user role before allowing admin endpoints).
- **Build CRUD endpoints for user management (admin only)**: Implement GET /api/users, POST /api/users, PUT /api/users/:id, DELETE /api/users/:id. Restrict to admin users. Include pagination and filtering.
- **Implement user profile update functionality**: Create PUT /api/user/profile endpoint for users to update their details (e.g., email, team info). Validate changes and update DB.

### Database Actions
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

### Testing and Validation Actions
- **Test login and registration flows**: Verify that users can register, login, and access the dashboard. Check error handling for invalid inputs.
- **Validate database operations**: Ensure user and team data is correctly stored and retrieved. Test foreign key relationships.
- **Test API endpoints**: Use tools like Postman to test authentication APIs. Verify JWT token handling.
- **Check responsive design**: Test pages on different devices and browsers.
- **Perform security checks**: Ensure passwords are hashed, inputs are sanitized, and no SQL injection vulnerabilities.

### Sprint 1 Completion Criteria
- [x] Login page implemented with validation and AJAX submission
- [x] Registration page with team details and validation
- [x] User dashboard displaying team information
- [x] Backend authentication APIs working
- [x] User and team tables created and migrated
- [x] Session and JWT token management configured
- [x] Responsive design across pages
- [x] Navigation and logout functionality
- [x] Admin user management endpoints (if applicable)
- [x] All forms validated and secure

---

## Sprint 2: Register Flow V2

This sprint enhances the registration flow to automatically generate a starting squad of players upon team creation and adds player management features.

### Frontend Actions
- **Enhance registration flow to include team member generation**: Modify the registration page to automatically generate a starting squad of 11 players upon team creation. Display a loading indicator during generation and show a success message with player count. Ensure the flow remains smooth and user-friendly.
- **Create player list view**: Build a new page based on `app-user-list.html` or data table templates. Display a list of players with columns for name, position, age, and key stats (e.g., overall rating). Include sorting and filtering options. Link to individual player details.
- **Implement player detail modal/page**: Create a modal or dedicated page (adapt from `app-user-view-account.html`) showing detailed player info: name, position, stats breakdown (e.g., speed, strength, technique), age, morale. Include edit options for admins.
- **Add team overview page**: Design a page (based on dashboard templates) that shows the full team composition, including player photos/icons, positions, and summary stats. Visualize the team as a formation diagram.
- **Update dashboard to include player summary**: Modify the dashboard to add a section with player count, average age, team strength, and top players. Use charts or tables for visualization.

### Backend Actions
- **Implement team member generation algorithm**: Create a service or function to generate 11 players per team with random but balanced stats. Use predefined positions (GK, LB, CB, RB, CDM, CM, CAM, LW, ST, RW, CF) and assign stats like speed (1-100), strength, technique, etc. Ensure variety and fairness.
- **Create player stats calculation logic**: Develop functions to compute derived stats like overall rating based on individual attributes. Update stats dynamically as needed (e.g., after matches).
- **Build player CRUD endpoints (admin)**: Implement GET /api/players, POST /api/players, PUT /api/players/:id, DELETE /api/players/:id. Restrict to admins. Include bulk operations if needed.
- **Add player detail management API**: Create endpoints for updating player details, such as PUT /api/players/:id/stats for stat adjustments.
- **Implement team composition validation**: Add logic to ensure teams have valid compositions (e.g., at least 1 GK, max 5 DEF, etc.). Validate during lineup setup or generation.

### Database Actions
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

### Testing and Validation Actions
- **Set up Cypress E2E testing framework**: Install and configure Cypress for comprehensive end-to-end testing with custom commands and fixtures.
- **Create authentication test suite**: Test login, registration, session management, and authentication flow with both UI and API validation.
- **Implement player management tests**: Comprehensive testing of player generation, list views, filtering, search, and detail modals.
- **Build API integration tests**: Direct API testing for all endpoints including authentication, player management, and team composition.
- **Test player generation during registration**: Verify that 11 players are created with balanced stats and positions upon team registration.
- **Validate player list and detail views**: Ensure player data displays correctly, with sorting, filtering, and detailed stats.
- **Test team overview and dashboard updates**: Check that team composition and summaries are accurate and visually appealing.
- **Validate backend APIs**: Test player CRUD operations, stat calculations, and composition validation.
- **Check database schema and relationships**: Ensure tables are created correctly with proper constraints and indexes.
- **Run automated test suite**: Execute full Cypress test suite to validate all Sprint 2 features work correctly.

### Sprint 2 Completion Criteria
- [x] Registration flow enhanced with automatic player generation
- [x] Player list view implemented with sorting and filtering
- [x] Player detail modal/page created
- [x] Team overview page designed
- [x] Dashboard updated with player summary
- [x] Player generation algorithm implemented
- [x] Player stats calculation logic developed
- [x] Player CRUD endpoints built (admin)
- [x] Player detail management API added
- [x] Team composition validation implemented
- [x] Player table schema designed and migrated
- [x] Player stats embedded or table created
- [x] Foreign key relationships added
- [x] Database indexes implemented
- [x] Cypress E2E testing framework integrated
- [x] Comprehensive test suite implemented (authentication, player management, API integration)
- [x] All features tested and validated

## Sprint 3: Setup/Configure Daily Team Lineup

This sprint focuses on implementing daily team lineup configuration, player stat progression, age management, and morale systems. Users will be able to set batting orders, assign bowling rotations with over restrictions (max 4 overs per bowler, non-consecutive), configure over-specific strategies (1-4, 5-8, 9-12, 13-16, 17-20), and see daily changes in player attributes for T20 matches.

### Frontend Actions
- **Create lineup configuration interface**: Build a page (adapt from `app-kanban.html` or custom) with T20 match visualization. Allow users to set batting order (1-11) and configure bowling rotation with over restrictions (max 4 overs per bowler, non-consecutive overs). The interface should display bowling over allocation clearly and provide real-time validation.
- **Implement drag-and-drop player positioning**: Use JavaScript libraries (e.g., SortableJS or included in templates) to enable dragging players between batting order positions and bowling rotation slots. Validate bowling restrictions (4-over limit, non-consecutive constraint) and role compatibility. Include visual feedback for valid/invalid drops and over allocation warnings.
- **Add daily stats preview and changes display**: Show current player stats and preview how they might change after the day (e.g., +1 batting strike rate, +2 bowling economy, -1 morale). Display a summary of daily changes on the dashboard or lineup page with color-coded indicators for improvements/declines.
- **Create age progression visualization**: Add a chart or progress bar showing player ages and when they will age up. Highlight players nearing retirement or peak performance. Include tooltips showing optimal age ranges for T20 specializations (power hitters peak 25-32, death bowlers 26-31, spinners 28-34) and stat impacts.
- **Implement lineup save/submit functionality**: Add a save button that submits the T20 team configuration to the backend via AJAX. Provide feedback on success/failure and validation errors including bowling over violations. Include confirmation dialogs for major lineup changes and display submission timestamps.
- **Design over-specific strategy selector**: Create a strategy interface divided into 5 phases (1-4, 5-8, 9-12, 13-16, 17-20 overs). Allow users to set different bowling and batting approaches for each phase (powerplay aggression, middle-over consolidation, death-over tactics). Display visual timeline of strategy phases.
- **Add squad rotation interface**: Design a squad management area where the full 15-player T20 squad is displayed. Allow easy rotation between playing XI and bench players with visual indicators of T20 role compatibility (openers, middle-order, finishers, death bowlers, spinners) and fitness levels.

### Backend Actions
- **Implement daily lineup management API**: Create POST /api/lineup to save the selected T20 team configuration for the day. Store batting order and bowling rotation with over allocations, validate against T20 composition rules and bowling restrictions. Include GET /api/lineup/:date to retrieve historical lineups and GET /api/lineup/current for today's lineup.
- **Create player stats update logic for daily changes**: Develop a daily job or endpoint to update player stats randomly or based on training. Focus on T20-specific stats like strike rate, bowling economy, boundary percentage, death-over performance, affected by morale and training focus. Implement weighted randomization based on player age, T20 role, and current stats.
- **Build age handling system with automatic progression**: Implement a cron job or scheduled task to increment player ages daily. Adjust stats based on T20-specific age curves (power hitters peak 25-32, death bowlers 26-31, spinners 28-34, decline after 35). Include T20 role-specific age effects and retirement mechanics for players over 38.
- **Add morale management for players**: Create logic to update morale based on T20 events (e.g., scoring boundaries/taking wickets in powerplay increases morale, getting hit for sixes/cheap dismissals decreases it, being dropped affects morale). Use morale to modify stat changes or performance. Implement morale recovery mechanics and team chemistry effects.
- **Implement T20 lineup validation rules**: Ensure lineups have 11 players, include exactly 1 wicket-keeper, have valid batting order (1-11), include adequate bowling options (minimum 5 bowlers with max 4 overs each, non-consecutive overs), and no duplicates. Return detailed error messages for bowling over violations and invalid T20 configurations.
- **Create bowling over management system**: Build endpoints for managing bowling over allocations, validating 4-over limits per bowler, ensuring non-consecutive overs constraint, and optimizing bowling rotations across 20 overs. Include algorithms for suggesting valid bowling sequences.
- **Add training and development system**: Implement training programs that affect daily stat changes. Allow users to focus training on T20-specific skills (power hitting, death bowling, powerplay batting, spin bowling in middle overs) with trade-offs between different stat improvements.

### Database Actions
- **Create lineup table schema**:
  ```sql
  CREATE TABLE lineups (
      id INT AUTO_INCREMENT PRIMARY KEY,
      team_id INT NOT NULL,
      FOREIGN KEY (team_id) REFERENCES teams(id),
      date DATE NOT NULL,
      batting_order JSON NOT NULL,
      bowling_over_allocation JSON NOT NULL, -- {player_id: [over_numbers], max 4 overs per player}
      bowling_sequence JSON NOT NULL, -- Complete 20-over bowling plan
      wicket_keeper_id INT NOT NULL,
      captain_id INT NOT NULL,
      powerplay_strategy ENUM('aggressive', 'balanced', 'cautious') DEFAULT 'balanced',
      middle_overs_strategy ENUM('consolidate', 'accelerate', 'attack') DEFAULT 'consolidate', 
      death_overs_strategy ENUM('all_out_attack', 'calculated_risks', 'target_based') DEFAULT 'calculated_risks',
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
      stat_changes JSON NOT NULL,
      previous_stats JSON,
      new_stats JSON,
      reason VARCHAR(255),
      morale_change INT DEFAULT 0,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(player_id, date)
  );
  ```
- **Implement age and morale fields in player table**: Update the `players` table to include age progression tracking and morale management:
  ```sql
  ALTER TABLE players 
  ADD COLUMN morale INT DEFAULT 50 CHECK (morale >= 0 AND morale <= 100),
  ADD COLUMN last_age_update DATE DEFAULT CURRENT_DATE,
  ADD COLUMN retirement_age INT DEFAULT 38,
  ADD COLUMN peak_age_start INT DEFAULT 28,
  ADD COLUMN peak_age_end INT DEFAULT 32,
  ADD COLUMN specialization ENUM('batsman', 'bowler', 'all_rounder', 'wicket_keeper') DEFAULT 'batsman',
  ADD COLUMN bowling_type ENUM('fast', 'medium', 'spin', 'none') DEFAULT 'none',
  ADD COLUMN batting_style ENUM('right_handed', 'left_handed') DEFAULT 'right_handed';
  ```
- **Create over-specific strategy templates table**:
  ```sql
  CREATE TABLE t20_strategies (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(50) NOT NULL UNIQUE,
      type ENUM('batting', 'bowling') NOT NULL,
      display_name VARCHAR(100) NOT NULL,
      over_phase ENUM('1-4', '5-8', '9-12', '13-16', '17-20') NOT NULL,
      parameters JSON NOT NULL,
      description TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(name, type, over_phase)
  );
  ```
- **Add player training preferences table**:
  ```sql
  CREATE TABLE player_training (
      id INT AUTO_INCREMENT PRIMARY KEY,
      player_id INT NOT NULL,
      FOREIGN KEY (player_id) REFERENCES players(id),
      training_focus ENUM('power_hitting', 'death_bowling', 'powerplay_batting', 'middle_over_bowling', 'finishing_skills', 'wicket_keeping', 'overall') DEFAULT 'overall',
      intensity ENUM('light', 'moderate', 'intensive') DEFAULT 'moderate',
      t20_role_focus ENUM('opener', 'middle_order', 'finisher', 'death_bowler', 'powerplay_bowler', 'spinner', 'all_rounder', 'wicket_keeper') DEFAULT 'all_rounder',
      last_updated DATE DEFAULT CURRENT_DATE,
      UNIQUE(player_id)
  );
  ```
- **Add team over-specific strategy assignments table**:
  ```sql
  CREATE TABLE team_over_strategies (
      id INT AUTO_INCREMENT PRIMARY KEY,
      team_id INT NOT NULL,
      FOREIGN KEY (team_id) REFERENCES teams(id),
      over_phase ENUM('1-4', '5-8', '9-12', '13-16', '17-20') NOT NULL,
      batting_strategy_id INT,
      bowling_strategy_id INT,
      FOREIGN KEY (batting_strategy_id) REFERENCES t20_strategies(id),
      FOREIGN KEY (bowling_strategy_id) REFERENCES t20_strategies(id),
      date_set DATE DEFAULT CURRENT_DATE,
      UNIQUE(team_id, over_phase)
  );
  ```
- **Create database triggers for automatic age updates**: Add a stored procedure or trigger to update player ages daily and adjust stats based on T20-specific age milestones:
  ```sql
  DELIMITER //
  CREATE PROCEDURE UpdatePlayerAges()
  BEGIN
      UPDATE players 
      SET age = age + 1, 
          last_age_update = CURRENT_DATE
      WHERE last_age_update < CURRENT_DATE;
  END //
  DELIMITER ;
  ```

### Testing and Validation Actions
- **Create T20 lineup management tests**: Test team configuration interface with batting order setup (1-11), bowling over allocation with 4-over limits and non-consecutive constraints, and role validation. Verify that T20 interface displays correctly and player assignments are saved properly.
- **Implement bowling over allocation tests**: Validate bowling over assignment system, ensure 4-over maximum per bowler, test non-consecutive overs constraint, and verify complete 20-over coverage. Test error handling for invalid bowling sequences and over allocation violations.
- **Build player progression tests**: Test daily stats updates, T20-specific age progression system, morale changes, and stat modification based on training/events. Verify that T20 age curves work correctly (power hitters peak 25-32, death bowlers 26-31, spinners 28-34) and retirement mechanics function as expected.
- **Add over-specific strategy tests**: Test 5-phase strategy interface (1-4, 5-8, 9-12, 13-16, 17-20 overs), strategy switching between phases, and tactical parameter validation. Ensure responsive layout displays strategy timeline correctly on different screen sizes.
- **Create T20 lineup validation tests**: Ensure invalid lineups are rejected (wrong number of players, missing wicket-keeper, insufficient bowling options, bowling over violations, duplicate players), and proper error messages are displayed. Test edge cases like insufficient death bowlers or powerplay specialists.
- **Implement daily T20 workflow tests**: Test complete daily cycle including T20 lineup submission with over allocations, stat updates, age progression, morale adjustments, and training effects. Verify that all T20-specific systems work together correctly.
- **Add database integrity tests**: Validate T20 lineup table constraints, bowling over allocation JSON validation, foreign key relationships, daily_stats tracking, and trigger functionality for automatic updates. Test over-specific strategy data structures.
- **Test T20 morale system**: Verify morale calculations, T20 event-based morale changes (boundaries scored, wickets in powerplay, death-over performance), and impact on stat progression. Test team chemistry effects and morale recovery mechanisms.
- **Create over-phase strategy tests**: Test strategy loading for different over phases, batting/bowling strategy validation per phase, tactical parameter application, and strategy template management for T20 matches.
- **Add T20 training system tests**: Test T20-specific training focus selection (power hitting, death bowling, powerplay batting), intensity effects on T20 stat changes, and long-term player development tracking for T20 specializations (opener, finisher, death bowler).

### Sprint 3 Completion Criteria
- [ ] T20 team configuration interface implemented with batting order and bowling over allocation
- [ ] Drag-and-drop player positioning working for batting order and bowling rotation with over limits
- [ ] Daily stats preview and changes display showing T20-specific player progression
- [ ] Age progression visualization with retirement and peak performance indicators for T20 specializations
- [ ] Lineup save/submit functionality with comprehensive T20 team validation including bowling over constraints
- [ ] Over-specific strategy selector with 5-phase tactical options (1-4, 5-8, 9-12, 13-16, 17-20 overs)
- [ ] Squad rotation interface for managing 15-player T20 squad and playing XI selection with role indicators
- [ ] Daily T20 lineup management API with bowling over allocation and validation
- [ ] Player stats update logic implementing T20-specific age-based progression curves
- [ ] Age handling system with automatic daily progression and T20 role-specific stat adjustments
- [ ] Morale management system affecting player performance based on T20 events and achievements
- [ ] T20 lineup validation rules enforcing proper team composition and bowling over restrictions
- [ ] Bowling over management system ensuring 4-over limits and non-consecutive constraints
- [ ] Training and development system for T20-specific skill improvement (power hitting, death bowling, etc.)
- [ ] Lineup table schema created with T20-specific fields (batting order, bowling over allocation, strategy phases)
- [ ] Daily_stats table for comprehensive T20 performance change tracking
- [ ] Player table updated with T20 specializations, bowling types, and batting styles
- [ ] Over-specific strategy templates table with T20 tactical configurations for 5 phases
- [ ] Team over-strategy assignments table linking teams to phase-specific strategies
- [ ] Player training preferences system for T20-specific skill development and role focus
- [ ] Database triggers for automatic age updates with T20 specialization considerations
- [ ] Comprehensive test suite covering all T20 team management features including bowling over allocation
- [ ] API integration tests for T20-specific endpoints and bowling over validation rules
- [ ] Database integrity tests for T20 data structures, over allocation, and strategy relationships
- [ ] End-to-end workflow tests for daily T20 team management cycle with over-specific strategies
- [ ] All features tested across multiple browsers and screen sizes with T20-specific scenarios

## Sprint 4: Auction/MarketPlace
*(To be detailed based on tasks.md)*

## Sprint 5: Simulate Practice Match
*(To be detailed based on tasks.md)*

## Sprint 6: League Simulations
*(To be detailed based on tasks.md)*</content>
<parameter name="filePath">d:\SwitchedHit\self-design\action_plan.md
