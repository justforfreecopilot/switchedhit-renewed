describe('Authentication Flow', () => {
  beforeEach(() => {
    // Clear localStorage before each test
    cy.clearLocalStorage()
  })

  describe('User Registration', () => {
    it('should successfully register a new user with team creation', () => {
      const timestamp = Date.now()
      const userData = {
        email: `testuser${timestamp}@example.com`,
        password: 'password123',
        team_name: `Test Team ${timestamp}`,
        stadium_name: `Test Stadium ${timestamp}`,
        pitch_type: 'Hard'
      }

      cy.visit('/register')
      
      // Fill registration form
      cy.get('#email').type(userData.email)
      cy.get('#password').type(userData.password)
      cy.get('#team_name').type(userData.team_name)
      cy.get('#stadium_name').type(userData.stadium_name)
      cy.get('#pitch_type').select(userData.pitch_type)
      
      // Submit form
      cy.get('button').contains('Sign up').click()
      
      // Should redirect to dashboard
      cy.url().should('include', '/dashboard')
      cy.shouldBeAuthenticated()
      
      // Verify token was stored
      cy.window().its('localStorage.token').should('exist')
    })

    it('should show error for duplicate email registration', () => {
      // Try to register with existing user email
      cy.visit('/register')
      
      cy.get('#email').type('test@gel.com')
      cy.get('#password').type('password123')
      cy.get('#team_name').type('Duplicate Team')
      cy.get('#stadium_name').type('Duplicate Stadium')
      cy.get('#pitch_type').select('Green')
      
      cy.get('button').contains('Sign up').click()
      
      // Should show error alert
      cy.on('window:alert', (text) => {
        expect(text).to.contains('Email already exists')
      })
    })

    it('should validate required fields', () => {
      cy.visit('/register')
      
      // Try to submit empty form
      cy.get('button').contains('Sign up').click()
      
      // Should remain on register page
      cy.url().should('include', '/register')
    })
  })

  describe('User Login', () => {
    it('should successfully login with valid credentials', () => {
      cy.visit('/login')
      
      cy.get('#email').type('test@gel.com')
      cy.get('#password').type('test@gel.com')
      cy.get('button[type="submit"]').click()
      
      cy.url().should('include', '/dashboard')
      cy.shouldBeAuthenticated()
    })

    it('should show error for invalid credentials', () => {
      cy.visit('/login')
      
      cy.get('#email').type('invalid@example.com')
      cy.get('#password').type('wrongpassword')
      cy.get('button[type="submit"]').click()
      
      cy.on('window:alert', (text) => {
        expect(text).to.contains('Invalid credentials')
      })
      
      cy.url().should('include', '/login')
      cy.shouldNotBeAuthenticated()
    })

    it('should validate required fields', () => {
      cy.visit('/login')
      
      // Try to submit empty form
      cy.get('button[type="submit"]').click()
      
      // Should remain on login page
      cy.url().should('include', '/login')
    })
  })

  describe('Authentication Flow', () => {
    it('should redirect unauthenticated users to login', () => {
      cy.visit('/dashboard')
      cy.url().should('include', '/login')
    })

    it('should redirect authenticated users away from login/register', () => {
      cy.loginViaAPI()
      cy.visit('/login')
      cy.url().should('include', '/dashboard')
    })
  })
})