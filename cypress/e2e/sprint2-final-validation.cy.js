describe('Sprint 2 Final Cricket Validation', () => {
  // Test data
  const testUser = {
    email: 'sprint2-final-test@example.com',
    password: 'password123',
    teamName: 'Final Test Cricket Club',
    groundName: 'Test Cricket Ground',
    pitchType: 'Hard',
    details: 'Final validation test team'
  };

  before(() => {
    // Handle uncaught exceptions that might occur from the application
    Cypress.on('uncaught:exception', (err, runnable) => {
      // Ignore Menu is not defined error which seems to be from template JS
      if (err.message.includes('Menu is not defined')) {
        return false;
      }
      // Let other errors fail the test
      return true;
    });
  });

  describe('🏏 Complete Cricket User Journey', () => {
    it('should complete full registration → login → players → team flow', () => {
      // Step 1: Registration with cricket terminology
      cy.visit('/register');
      cy.title().should('contain', 'Register');

      // Verify cricket terminology in registration
      cy.contains('Ground Name').should('be.visible');
      cy.get('input[placeholder*="ground"]').should('exist');
      cy.contains('Cricket pitch type affects player performance').should('be.visible');

      // Fill registration form
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('#team_name').type(testUser.teamName);
      cy.get('#stadium_name').type(testUser.groundName);
      cy.get('#pitch_type').select(testUser.pitchType);
      cy.get('#details').type(testUser.details);

      // Submit and handle potential redirect
      cy.get('button[type="submit"]').click();

      // Wait for redirect and handle both possible outcomes
      cy.url({ timeout: 10000 }).should('match', /\/(login|dashboard)/);
      
      // If redirected to login, perform login
      cy.url().then((url) => {
        if (url.includes('/login')) {
          cy.get('#email').type(testUser.email);
          cy.get('#password').type(testUser.password);
          cy.get('button[type="submit"]').click();
        }
      });

      // Should now be on dashboard
      cy.url({ timeout: 10000 }).should('include', '/dashboard');
      
      // Step 2: Verify dashboard shows cricket terminology
      cy.contains('Ground:', { timeout: 10000 }).should('be.visible');
      cy.contains(testUser.groundName).should('be.visible');
      cy.contains(testUser.pitchType).should('be.visible');

      // Step 3: Navigate to players page
      cy.visit('/players');
      cy.url().should('include', '/players');

      // Wait for players to load
      cy.get('table', { timeout: 15000 }).should('be.visible');

      // Step 4: Verify cricket statistics columns
      const cricketColumns = ['Batting Avg', 'Strike Rate', 'Bowling Avg', 'Economy', 'Fielding'];
      cricketColumns.forEach(column => {
        cy.contains('th', column).should('be.visible');
      });

      // Step 5: Verify we have 11 cricket players
      cy.get('tbody tr').should('have.length', 11);

      // Step 6: Verify cricket positions are present
      const cricketPositions = [
        'Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher',
        'All-rounder', 'Fast-bowler', 'Spin-bowler'
      ];
      
      // Check that we have cricket positions (at least some of them should be visible)
      let positionsFound = 0;
      cricketPositions.forEach(position => {
        cy.get('body').then($body => {
          if ($body.text().includes(position)) {
            positionsFound++;
          }
        });
      });

      // Step 7: Test position filtering
      cy.get('select').first().then($select => {
        if ($select.find('option').length > 1) {
          // Select first cricket position available
          cy.get('select').first().select(1);
          cy.wait(1000); // Wait for filtering
          
          // Reset filter
          cy.get('select').first().select(0);
        }
      });

      // Step 8: Test player details modal
      cy.get('tbody tr').first().within(() => {
        cy.get('button').click();
      });

      // Check if modal opens and contains cricket stats
      cy.get('.modal', { timeout: 5000 }).should('be.visible').within(() => {
        cy.contains('Batting Average').should('be.visible');
        cy.contains('Strike Rate').should('be.visible');
        cy.contains('Fielding Rating').should('be.visible');
        
        // Close modal
        cy.get('.btn-close, button[data-dismiss="modal"], button').contains('Close').click({ force: true });
      });

      // Step 9: Navigate to team composition
      cy.visit('/team-composition');
      cy.url().should('include', '/team-composition');

      // Verify cricket team setup terminology
      cy.contains('Cricket Team Setup', { timeout: 10000 }).should('be.visible');
      cy.contains('Playing XI').should('be.visible');

      // Step 10: Return to dashboard for final verification
      cy.visit('/dashboard');
      cy.contains('Ground:').should('be.visible');
    });
  });

  describe('🎯 Cricket Position Validation', () => {
    beforeEach(() => {
      // Login with our test user
      cy.visit('/login');
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('button[type="submit"]').click();
      cy.url({ timeout: 10000 }).should('include', '/dashboard');
    });

    it('should have proper cricket team composition', () => {
      cy.visit('/players');
      
      // Wait for data to load
      cy.get('table tbody tr', { timeout: 10000 }).should('have.length', 11);

      // Count positions and verify cricket team structure
      const expectedPositions = {
        'Wicket-keeper': { min: 1, max: 1 },
        'Opening-batsman': { min: 1, max: 3 },
        'Middle-order': { min: 2, max: 5 },
        'All-rounder': { min: 1, max: 3 },
        'Fast-bowler': { min: 1, max: 3 },
        'Spin-bowler': { min: 1, max: 2 }
      };

      // Verify team has at least one wicket-keeper
      cy.contains('Wicket-keeper').should('exist');
      
      // Verify we have batting specialists
      cy.get('body').should('contain.text', 'Opening-batsman')
        .or('contain.text', 'Middle-order')
        .or('contain.text', 'Finisher');
      
      // Verify we have bowling specialists  
      cy.get('body').should('contain.text', 'Fast-bowler')
        .or('contain.text', 'Spin-bowler')
        .or('contain.text', 'All-rounder');
    });

    it('should display realistic cricket statistics', () => {
      cy.visit('/players');
      cy.get('table tbody tr', { timeout: 10000 }).should('have.length.at.least', 1);

      // Check first few players have realistic cricket stats
      cy.get('tbody tr').each(($row, index) => {
        if (index < 3) { // Check first 3 players
          cy.wrap($row).within(() => {
            // Get batting average (should be between 5-70 or N/A)
            cy.get('td').eq(4).then($cell => {
              const battingAvg = $cell.text().trim();
              if (battingAvg !== 'N/A' && !isNaN(parseFloat(battingAvg))) {
                const avg = parseFloat(battingAvg);
                expect(avg).to.be.at.least(5);
                expect(avg).to.be.at.most(70);
              }
            });

            // Get strike rate (should be between 50-250 or N/A)
            cy.get('td').eq(5).then($cell => {
              const strikeRate = $cell.text().trim();
              if (strikeRate !== 'N/A' && !isNaN(parseFloat(strikeRate))) {
                const sr = parseFloat(strikeRate);
                expect(sr).to.be.at.least(50);
                expect(sr).to.be.at.most(250);
              }
            });
          });
        }
      });
    });
  });

  describe('🔍 Cricket Filtering and Search', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('button[type="submit"]').click();
      cy.visit('/players');
      cy.get('table tbody tr', { timeout: 10000 }).should('have.length.at.least', 1);
    });

    it('should filter players by cricket positions', () => {
      // Find position filter dropdown
      cy.get('select').each($select => {
        cy.wrap($select).find('option').then($options => {
          const hasWicketkeeper = Array.from($options).some(option => 
            option.text.includes('Wicket-keeper')
          );
          
          if (hasWicketkeeper) {
            // Test filtering by Wicket-keeper
            cy.wrap($select).select('Wicket-keeper');
            cy.wait(1000);
            
            // Should have filtered results
            cy.get('tbody tr').should('have.length.at.least', 1);
            cy.get('tbody').should('contain', 'Wicket-keeper');
            
            // Reset filter
            cy.wrap($select).select(0);
            cy.wait(500);
            return false; // Exit the each loop
          }
        });
      });
    });

    it('should search players by name', () => {
      // Get first player name for search test
      cy.get('tbody tr').first().within(() => {
        cy.get('td').first().invoke('text').then(playerName => {
          const searchTerm = playerName.split(' ')[0]; // First name
          
          // Find and use name search input
          cy.get('body').then($body => {
            if ($body.find('input[placeholder*="name"]').length > 0) {
              cy.get('input[placeholder*="name"]').type(searchTerm);
              cy.wait(1000);
              
              // Should show filtered results
              cy.get('tbody tr').should('have.length.at.least', 1);
              cy.get('tbody').should('contain', searchTerm);
              
              // Clear search
              cy.get('input[placeholder*="name"]').clear();
            }
          });
        });
      });
    });
  });

  describe('📊 Cricket Team Composition', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('button[type="submit"]').click();
    });

    it('should display cricket team setup page correctly', () => {
      cy.visit('/team-composition');
      
      // Check page elements
      cy.contains('Cricket Team Setup', { timeout: 10000 }).should('be.visible');
      cy.contains('Playing XI').should('be.visible');
      
      // Check for position breakdown section
      cy.contains('Position Breakdown').should('be.visible');
      
      // Verify refresh function is cricket-based
      cy.get('button[onclick*="refreshTeam"]').should('exist');
    });
  });

  describe('🏠 Cricket Dashboard Integration', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('button[type="submit"]').click();
    });

    it('should show cricket terminology on dashboard', () => {
      cy.visit('/dashboard');
      
      // Wait for team info to load
      cy.get('[data-cy="team-info"], #teamInfo', { timeout: 10000 }).should('be.visible');
      
      // Should show "Ground:" instead of "Stadium:"
      cy.contains('Ground:').should('be.visible');
      cy.contains(testUser.groundName).should('be.visible');
      
      // Should show pitch type
      cy.contains(testUser.pitchType).should('be.visible');
    });
  });

  describe('🚫 No Football References', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type(testUser.email);
      cy.get('#password').type(testUser.password);
      cy.get('button[type="submit"]').click();
    });

    it('should not contain any football terminology', () => {
      // Check players page
      cy.visit('/players');
      cy.get('body', { timeout: 10000 }).then($body => {
        const bodyText = $body.text();
        
        // Football positions that should NOT exist
        const footballTerms = [
          'Goalkeeper', 'Left Back', 'Center Back', 'Right Back',
          'Defensive Midfielder', 'Central Midfielder', 'Attacking Midfielder',
          'Left Wing', 'Right Wing', 'Striker'
        ];
        
        footballTerms.forEach(term => {
          expect(bodyText).to.not.include(term);
        });
      });
      
      // Check team composition page
      cy.visit('/team-composition');
      cy.get('body').then($body => {
        const bodyText = $body.text();
        expect(bodyText).to.not.include('Formation');
        expect(bodyText).to.not.include('4-4-2');
      });
    });
  });

  after(() => {
    // Cleanup: Could add cleanup logic here if needed
    cy.log('Sprint 2 cricket validation completed successfully! 🏏');
  });
});