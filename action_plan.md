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
*(To be detailed based on tasks.md)*

## Sprint 4: Auction/MarketPlace
*(To be detailed based on tasks.md)*

## Sprint 5: Simulate Practice Match
*(To be detailed based on tasks.md)*

## Sprint 6: League Simulations
*(To be detailed based on tasks.md)*</content>
<parameter name="filePath">d:\SwitchedHit\self-design\action_plan.md
