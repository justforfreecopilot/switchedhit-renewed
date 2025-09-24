# Cypress Testing Documentation

## Overview
This project uses Cypress for end-to-end testing to ensure all features work correctly across the entire application stack.

## Test Structure

### Test Files
- `01_authentication.cy.js` - Tests login, registration, and authentication flows
- `02_player_management.cy.js` - Tests player generation, listing, filtering, and team composition
- `03_api_integration.cy.js` - Tests all API endpoints directly

### Test Categories
1. **Authentication Tests**
   - User registration with team creation
   - User login with valid/invalid credentials
   - Session management and redirects
   - Form validation

2. **Player Management Tests**
   - Automatic player generation during registration
   - Player list view with filtering and search
   - Player detail modals
   - Team composition and formation display
   - Dashboard player summaries

3. **API Integration Tests**
   - Authentication endpoints
   - Player management endpoints
   - Team composition endpoints
   - Error handling and validation

## Running Tests

### Prerequisites
1. Ensure PHP development server is running on port 8080
2. Database should be set up and accessible
3. Node.js and npm should be installed

### Commands
```bash
# Install dependencies
npm install

# Run all tests in headless mode
npm run test

# Run all tests with GUI
npm run test:open

# Run specific test file
npx cypress run --spec "cypress/e2e/01_authentication.cy.js"

# Run tests in specific browser
npm run test:chrome
npm run test:firefox
```

### Test Environment Setup
Tests expect the application to be running on `http://localhost:8080` with:
- A registered user: `test@gel.com` / `test@gel.com`
- Database with proper schema
- All API endpoints functional

## Custom Commands
The following custom Cypress commands are available:

- `cy.login(email, password)` - Login via UI
- `cy.register(userData)` - Register new user via UI
- `cy.loginViaAPI(email, password)` - Login via API call
- `cy.logout()` - Logout and clear session
- `cy.shouldBeAuthenticated()` - Assert user is logged in
- `cy.shouldNotBeAuthenticated()` - Assert user is not logged in
- `cy.checkApiResponse(url, status)` - Test API endpoint
- `cy.waitForElement(selector)` - Wait for element with timeout

## Test Data
Test fixtures are stored in `cypress/fixtures/test-data.json` containing:
- User credentials
- API endpoints
- Test team data

## Screenshots and Videos
- Screenshots are captured on test failures
- Videos can be enabled in `cypress.config.js`
- All artifacts stored in `cypress/screenshots` and `cypress/videos`

## Continuous Integration
Tests can be integrated into CI/CD pipelines using:
```bash
npm run test:headless
```

## Best Practices
1. Tests are independent - each test can run in isolation
2. Database state is managed through API calls rather than direct DB manipulation
3. Custom commands reduce code duplication
4. Tests follow the AAA pattern (Arrange, Act, Assert)
5. Descriptive test names explain the expected behavior