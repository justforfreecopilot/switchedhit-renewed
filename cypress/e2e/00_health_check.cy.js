describe('System Health Check', () => {
  it('should verify server is running and database is accessible', () => {
    // Check if server responds
    cy.request('GET', '/').should((response) => {
      expect(response.status).to.eq(200)
    })
    
    // Check if login page loads
    cy.visit('/login')
    cy.get('#email').should('be.visible')
    cy.get('#password').should('be.visible')
    
    // Check if API endpoints are responding
    cy.request({
      method: 'POST',
      url: '/api/login',
      body: { email: 'test', password: 'test' },
      failOnStatusCode: false
    }).should((response) => {
      // Should return an error but not crash
      expect(response.status).to.eq(200)
    })
  })
  
  it('should verify test user exists and can authenticate', () => {
    cy.request({
      method: 'POST',
      url: '/api/login',
      body: {
        email: 'test@gel.com',
        password: 'test@gel.com'
      }
    }).should((response) => {
      expect(response.status).to.eq(200)
      expect(response.body).to.have.property('token')
    })
  })
  
  it('should verify database schema is correct', () => {
    // Login and check if we can access protected endpoints
    cy.request({
      method: 'POST',
      url: '/api/login',
      body: {
        email: 'test@gel.com',
        password: 'test@gel.com'
      }
    }).then((loginResponse) => {
      const token = loginResponse.body.token
      
      // Check team endpoint
      cy.request({
        method: 'GET',
        url: '/api/user/team',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.team).to.have.property('id')
        expect(response.body.team).to.have.property('name')
      })
      
      // Check players endpoint (Sprint 2 feature)
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('players')
        expect(response.body.players).to.be.an('array')
      })
    })
  })
})