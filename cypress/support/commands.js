// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Custom command to check API response
Cypress.Commands.add('checkApiResponse', (url, expectedStatus = 200) => {
  cy.window().its('localStorage.token').then((token) => {
    cy.request({
      method: 'GET',
      url: url,
      headers: {
        'Authorization': `Bearer ${token}`
      },
      failOnStatusCode: false
    }).then((response) => {
      expect(response.status).to.eq(expectedStatus)
    })
  })
})

// Custom command to wait for elements with better error handling
Cypress.Commands.add('waitForElement', (selector, timeout = 10000) => {
  cy.get(selector, { timeout }).should('be.visible')
})

// Custom command to check if user is authenticated
Cypress.Commands.add('shouldBeAuthenticated', () => {
  cy.window().its('localStorage.token').should('exist')
  cy.url().should('not.include', '/login')
})

// Custom command to check if user is not authenticated
Cypress.Commands.add('shouldNotBeAuthenticated', () => {
  cy.window().its('localStorage.token').should('not.exist')
})