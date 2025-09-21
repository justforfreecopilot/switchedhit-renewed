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
- [ ] Login page implemented with validation and AJAX submission
- [ ] Registration page with team details and validation
- [ ] User dashboard displaying team information
- [ ] Backend authentication APIs working
- [x] User and team tables created and migrated
- [ ] Session and JWT token management configured
- [ ] Responsive design across pages
- [ ] Navigation and logout functionality
- [ ] Admin user management endpoints (if applicable)
- [ ] All forms validated and secure

---

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
