// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Alternatively you can use CommonJS syntax:
// require('./commands')

// Add custom commands for authentication
Cypress.Commands.add('login', (email = 'test@gel.com', password = 'test@gel.com') => {
  cy.visit('/login')
  cy.get('#email').type(email)
  cy.get('#password').type(password)
  cy.get('button[type="submit"]').click()
  cy.url().should('include', '/dashboard')
  cy.window().its('localStorage.token').should('exist')
})

Cypress.Commands.add('register', (userData = {}) => {
  const defaultUserData = {
    email: `test${Date.now()}@example.com`,
    password: 'password123',
    team_name: `Test Team ${Date.now()}`,
    stadium_name: `Test Stadium ${Date.now()}`,
    pitch_type: 'Hard'
  }
  
  const user = { ...defaultUserData, ...userData }
  
  cy.visit('/register')
  cy.get('#email').type(user.email)
  cy.get('#password').type(user.password)
  cy.get('#team_name').type(user.team_name)
  cy.get('#stadium_name').type(user.stadium_name)
  cy.get('#pitch_type').select(user.pitch_type)
  cy.get('button').contains('Sign up').click()
  cy.url().should('include', '/dashboard')
  cy.window().its('localStorage.token').should('exist')
  
  return cy.wrap(user)
})

Cypress.Commands.add('loginViaAPI', (email = 'test@gel.com', password = 'test@gel.com') => {
  cy.request({
    method: 'POST',
    url: '/api/login',
    body: {
      email,
      password
    }
  }).then((response) => {
    expect(response.status).to.eq(200)
    window.localStorage.setItem('token', response.body.token)
  })
})

Cypress.Commands.add('logout', () => {
  cy.window().then((win) => {
    win.localStorage.removeItem('token')
  })
  cy.visit('/login')
})