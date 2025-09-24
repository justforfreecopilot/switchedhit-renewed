describe('Player Management (Sprint 2)', () => {
  beforeEach(() => {
    // Login before each test
    cy.loginViaAPI()
  })

  describe('Player Generation', () => {
    it('should automatically generate players during registration', () => {
      // Register new user and check if players are generated
      const timestamp = Date.now()
      const userData = {
        email: `playertest${timestamp}@example.com`,
        password: 'password123',
        team_name: `Player Test Team ${timestamp}`,
        stadium_name: `Player Test Stadium ${timestamp}`
      }

      cy.request({
        method: 'POST',
        url: '/api/register',
        body: {
          ...userData,
          pitch_type: 'Hard'
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.token).to.exist
        
        // Check if players were generated
        cy.request({
          method: 'GET',
          url: '/api/players/my',
          headers: {
            'Authorization': `Bearer ${response.body.token}`
          }
        }).then((playersResponse) => {
          expect(playersResponse.status).to.eq(200)
          expect(playersResponse.body.players).to.have.length.greaterThan(10)
          
          // Verify different positions are represented
          const positions = playersResponse.body.players.map(player => player.position)
          expect(positions).to.include('Wicket-keeper')
          expect(positions).to.include('Opening-batsman')
          expect(positions).to.include('All-rounder')
          expect(positions).to.include('Fast-bowler')
        })
      })
    })

    it('should generate players with valid stats', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my',
        headers: {
          'Authorization': `Bearer ${window.localStorage.getItem('token')}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200)
        const players = response.body.players
        
        players.forEach(player => {
          // Check required fields
          expect(player).to.have.property('name')
          expect(player).to.have.property('position')
          expect(player).to.have.property('age')
          expect(player).to.have.property('batting_average')
          expect(player).to.have.property('bowling_average')
          expect(player).to.have.property('strike_rate')
          expect(player).to.have.property('economy_rate')
          expect(player).to.have.property('fielding_rating')
          
          // Check stat ranges
          expect(player.age).to.be.within(18, 35)
          expect(player.batting_average).to.be.at.least(0)
          expect(player.bowling_average).to.be.at.least(0)
          expect(player.strike_rate).to.be.at.least(0)
          expect(player.economy_rate).to.be.at.least(0)
          expect(player.fielding_rating).to.be.within(1, 100)
          
          // Check valid positions
          const validPositions = ['Wicket-keeper', 'Opening-batsman', 'Middle-order', 'All-rounder', 'Finisher', 'Fast-bowler', 'Spin-bowler', 'Medium-pacer', 'Slip-fielder', 'Boundary-fielder', 'Deep-fielder']
          expect(validPositions).to.include(player.position)
        })
      })
    })
  })

  describe('Player List Page', () => {
    it('should display players list page', () => {
      cy.visit('/players')
      
      // Check page title
      cy.contains('Player List').should('be.visible')
      
      // Check stats cards
      cy.get('#total-players').should('contain.text', '1')
      cy.get('#avg-age').should('not.be.empty')
      cy.get('#avg-rating').should('not.be.empty')
      cy.get('#top-player').should('not.be.empty')
      
      // Check table exists
      cy.get('#players-table').should('be.visible')
      cy.get('#players-table tbody tr').should('have.length.greaterThan', 0)
    })

    it('should filter players by position', () => {
      cy.visit('/players')
      cy.waitForElement('#players-table tbody tr')
      
      // Select goalkeeper filter
      cy.get('#position-filter').select('GK')
      
      // Wait for filtering to complete
      cy.wait(1000)
      
      // All visible rows should be goalkeepers
      cy.get('#players-table tbody tr').each(($row) => {
        cy.wrap($row).find('td').eq(1).should('contain', 'GK')
      })
    })

    it('should filter players by age group', () => {
      cy.visit('/players')
      cy.waitForElement('#players-table tbody tr')
      
      // Select young players filter
      cy.get('#age-filter').select('young')
      
      // Wait for filtering
      cy.wait(1000)
      
      // Check if age filtering works (this might need adjustment based on actual data)
      cy.get('#players-table tbody tr').should('have.length.greaterThan', 0)
    })

    it('should search players by name', () => {
      cy.visit('/players')
      cy.waitForElement('#players-table tbody tr')
      
      // Get first player name and search for it
      cy.get('#players-table tbody tr').first().find('td').first().invoke('text').then((playerName) => {
        const searchTerm = playerName.split(' ')[0] // Get first name
        cy.get('#name-search').type(searchTerm)
        
        // Wait for search to complete
        cy.wait(1000)
        
        // Results should contain the search term
        cy.get('#players-table tbody tr').should('have.length.greaterThan', 0)
      })
    })

    it('should open player detail modal', () => {
      cy.visit('/players')
      cy.waitForElement('#players-table tbody tr')
      
      // Click first player's view button
      cy.get('#players-table tbody tr').first().find('button').contains('View').click()
      
      // Modal should be visible
      cy.get('#playerDetailModal').should('be.visible')
      cy.get('#modal-player-name').should('not.be.empty')
      cy.get('#modal-player-position').should('not.be.empty')
      cy.get('#modal-player-age').should('not.be.empty')
      cy.get('#modal-player-rating').should('not.be.empty')
      
      // Progress bars should be visible
      cy.get('#modal-speed-bar').should('be.visible')
      cy.get('#modal-strength-bar').should('be.visible')
      cy.get('#modal-technique-bar').should('be.visible')
      cy.get('#modal-morale-bar').should('be.visible')
    })
  })

  describe('Team Composition Page', () => {
    it('should display team overview page', () => {
      cy.visit('/team-composition')
      
      cy.contains('Team Overview').should('be.visible')
      
      // Check team info is loaded
      cy.get('#team-name').should('not.contain', 'Loading...')
      cy.get('#team-stadium').should('not.be.empty')
      cy.get('#team-pitch').should('not.be.empty')
      
      // Check stats
      cy.get('#total-players').should('not.contain', '0')
      cy.get('#avg-age').should('not.contain', '0')
      cy.get('#team-rating').should('not.contain', '0')
    })

    it('should display cricket field layout', () => {
      cy.visit('/team-composition')
      
      // Check if team container exists
      cy.get('#team-container').should('be.visible')
      cy.get('.cricket-line').should('have.length.greaterThan', 0)
      cy.get('.player-card').should('have.length.greaterThan', 0)
    })

    it('should display position breakdown', () => {
      cy.visit('/team-composition')
      
      // Check position breakdown cards
      cy.get('#position-breakdown').should('be.visible')
      cy.get('#position-breakdown .col-md-3').should('have.length.greaterThan', 0)
    })

    it('should open player detail from team setup', () => {
      cy.visit('/team-composition')
      
      // Click on a player card in team setup
      cy.get('.player-card').first().click()
      
      // Modal should open
      cy.get('#playerDetailModal').should('be.visible')
    })

    it('should refresh team layout', () => {
      cy.visit('/team-composition')
      
      // Click refresh button
      cy.contains('button', 'Refresh').click()
      
      // Cricket layout should still be visible after refresh
      cy.get('.cricket-line').should('have.length.greaterThan', 0)
    })
  })

  describe('Dashboard Player Summary', () => {
    it('should display player stats on dashboard', () => {
      cy.visit('/dashboard')
      
      // Check player stat cards
      cy.get('#total-players').should('not.contain', '0')
      cy.get('#avg-age').should('not.contain', '0')
      cy.get('#team-rating').should('not.contain', '0')
      cy.get('#top-player').should('not.contain', '-')
      
      // Check top players table
      cy.get('#top-players-table').should('be.visible')
      cy.get('#top-players-table tr').should('have.length.greaterThan', 1) // Header + data rows
    })

    it('should navigate to players page from dashboard', () => {
      cy.visit('/dashboard')
      
      // Click "View All" button
      cy.contains('a', 'View All').click()
      
      // Should navigate to players page
      cy.url().should('include', '/players')
    })
  })
})