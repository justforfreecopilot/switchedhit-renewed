describe('API Integration Tests', () => {
  let authToken

  before(() => {
    // Get auth token for API tests
    cy.request({
      method: 'POST',
      url: '/api/login',
      body: {
        email: 'test@gel.com',
        password: 'test@gel.com'
      }
    }).then((response) => {
      authToken = response.body.token
    })
  })

  describe('Authentication APIs', () => {
    it('should login with valid credentials', () => {
      cy.request({
        method: 'POST',
        url: '/api/login',
        body: {
          email: 'test@gel.com',
          password: 'test@gel.com'
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('token')
        expect(response.body).to.have.property('user')
        expect(response.body.user).to.have.property('id')
        expect(response.body.user).to.have.property('email')
        expect(response.body.user).to.have.property('role')
      })
    })

    it('should return error for invalid credentials', () => {
      cy.request({
        method: 'POST',
        url: '/api/login',
        body: {
          email: 'invalid@example.com',
          password: 'wrongpassword'
        },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('error')
        expect(response.body.error).to.eq('Invalid credentials')
      })
    })

    it('should return user info for authenticated user', () => {
      cy.request({
        method: 'GET',
        url: '/api/user/me',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('user')
        expect(response.body.user).to.have.property('id')
        expect(response.body.user).to.have.property('role')
      })
    })

    it('should return team info for authenticated user', () => {
      cy.request({
        method: 'GET',
        url: '/api/user/team',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('team')
        expect(response.body.team).to.have.property('id')
        expect(response.body.team).to.have.property('name')
        expect(response.body.team).to.have.property('stadium_name')
        expect(response.body.team).to.have.property('pitch_type')
      })
    })
  })

  describe('Player APIs', () => {
    it('should return user players', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('players')
        expect(response.body.players).to.be.an('array')
        expect(response.body.players.length).to.be.greaterThan(0)
        
        // Check first player structure
        const player = response.body.players[0]
        expect(player).to.have.property('id')
        expect(player).to.have.property('name')
        expect(player).to.have.property('position')
        expect(player).to.have.property('age')
        expect(player).to.have.property('speed')
        expect(player).to.have.property('strength')
        expect(player).to.have.property('technique')
        expect(player).to.have.property('overall_rating')
        expect(player).to.have.property('morale')
      })
    })

    it('should return specific player details', () => {
      // First get a player ID
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        const playerId = response.body.players[0].id
        
        // Then get player details
        cy.request({
          method: 'GET',
          url: `/api/players/${playerId}`,
          headers: {
            'Authorization': `Bearer ${authToken}`
          }
        }).then((playerResponse) => {
          expect(playerResponse.status).to.eq(200)
          expect(playerResponse.body).to.have.property('player')
          expect(playerResponse.body.player.id).to.eq(playerId)
        })
      })
    })

    it('should return team composition', () => {
      cy.request({
        method: 'GET',
        url: '/api/team/composition',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('team')
        expect(response.body).to.have.property('players')
        expect(response.body).to.have.property('composition')
        expect(response.body).to.have.property('stats')
        
        // Check stats structure
        expect(response.body.stats).to.have.property('total_players')
        expect(response.body.stats).to.have.property('average_age')
        expect(response.body.stats).to.have.property('average_rating')
        expect(response.body.stats).to.have.property('top_players')
        
        // Check composition has positions
        expect(response.body.composition).to.be.an('object')
      })
    })

    it('should require authentication for protected endpoints', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(401)
      })
    })

    it('should reject invalid tokens', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        headers: {
          'Authorization': 'Bearer invalid_token'
        },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(401)
      })
    })
  })

  describe('Error Handling', () => {
    it('should handle missing required fields in registration', () => {
      cy.request({
        method: 'POST',
        url: '/api/register',
        body: {
          email: 'incomplete@example.com'
          // Missing password and team details
        },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('error')
        expect(response.body.error).to.eq('Required fields missing')
      })
    })

    it('should handle missing required fields in login', () => {
      cy.request({
        method: 'POST',
        url: '/api/login',
        body: {
          email: 'test@example.com'
          // Missing password
        },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.have.property('error')
        expect(response.body.error).to.eq('Email and password required')
      })
    })

    it('should handle non-existent player requests', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/99999',
        headers: {
          'Authorization': `Bearer ${authToken}`
        },
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).to.eq(404)
      })
    })
  })
})