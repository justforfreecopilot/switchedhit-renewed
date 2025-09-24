describe('Cricket Functionality Tests', () => {
  beforeEach(() => {
    // Reset to a clean state before each test
    cy.visit('/register');
  });

  describe('Cricket Team Registration', () => {
    it('should register a new cricket team successfully', () => {
      // Fill out registration form
      cy.get('#email').type('testcricket@example.com');
      cy.get('#password').type('password123');
      cy.get('#confirm_password').type('password123');
      cy.get('#team_name').type('Mumbai Cricketers');
      cy.get('#stadium_name').type('Wankhede Cricket Ground');
      cy.get('#pitch_type').select('Hard');
      cy.get('#details').type('A competitive cricket team focused on T20 format');

      // Submit registration
      cy.get('button[type="submit"]').click();

      // Should redirect to login or dashboard
      cy.url().should('match', /\/(login|dashboard)/);
    });

    it('should show proper cricket terminology in registration form', () => {
      // Check that ground name is used instead of stadium
      cy.get('label[for="stadium_name"]').should('contain', 'Ground Name');
      cy.get('#stadium_name').should('have.attr', 'placeholder', 'Enter ground name');
      
      // Check cricket-specific help text
      cy.contains('Cricket pitch type affects player performance').should('be.visible');
    });
  });

  describe('Cricket Player Generation', () => {
    it('should generate cricket team with proper positions after registration', () => {
      // Complete registration process
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('#confirm_password').type('password123');
      cy.get('#team_name').type('Delhi Capitals');
      cy.get('#stadium_name').type('Feroz Shah Kotla');
      cy.get('#pitch_type').select('Dusty');
      cy.get('#details').type('Delhi based cricket team');
      
      cy.get('button[type="submit"]').click();
      
      // Login if redirected to login page
      cy.url().then((url) => {
        if (url.includes('/login')) {
          cy.get('#email').type('crickettest@example.com');
          cy.get('#password').type('password123');
          cy.get('button[type="submit"]').click();
        }
      });

      // Navigate to players page
      cy.visit('/players');
      
      // Wait for player data to load
      cy.get('[data-cy="player-table"]', { timeout: 10000 }).should('be.visible');
      
      // Check that cricket positions are present
      const cricketPositions = [
        'Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher', 
        'All-rounder', 'Fast-bowler', 'Spin-bowler'
      ];
      
      cricketPositions.forEach(position => {
        cy.get('[data-cy="player-table"]').should('contain', position);
      });

      // Verify we have exactly 11 players
      cy.get('[data-cy="player-row"]').should('have.length', 11);
    });

    it('should display cricket statistics correctly', () => {
      // Login with existing cricket team
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      
      // Navigate to players page
      cy.visit('/players');
      
      // Check cricket stat columns are present
      cy.get('th').should('contain', 'Batting Avg');
      cy.get('th').should('contain', 'Strike Rate');
      cy.get('th').should('contain', 'Bowling Avg');
      cy.get('th').should('contain', 'Economy');
      cy.get('th').should('contain', 'Fielding');
      
      // Check that stats are displayed properly (not N/A for all)
      cy.get('[data-cy="player-row"]').first().within(() => {
        cy.get('td').eq(4).should('not.contain', 'N/A'); // Batting average should have value
        cy.get('td').eq(5).should('not.contain', 'N/A'); // Strike rate should have value
        cy.get('td').eq(8).should('not.contain', 'N/A'); // Fielding should have value
      });
    });
  });

  describe('Cricket Position Filtering', () => {
    beforeEach(() => {
      // Login with cricket team
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/players');
    });

    it('should filter players by cricket positions', () => {
      // Test filtering by Wicket-keeper
      cy.get('#position-filter').select('Wicket-keeper');
      cy.get('[data-cy="player-row"]').should('have.length.at.least', 1);
      cy.get('[data-cy="player-row"]').each(($row) => {
        cy.wrap($row).should('contain', 'Wicket-keeper');
      });

      // Test filtering by Fast-bowler
      cy.get('#position-filter').select('Fast-bowler');
      cy.get('[data-cy="player-row"]').should('have.length.at.least', 1);
      cy.get('[data-cy="player-row"]').each(($row) => {
        cy.wrap($row).should('contain', 'Fast-bowler');
      });

      // Reset filter
      cy.get('#position-filter').select('');
      cy.get('[data-cy="player-row"]').should('have.length', 11);
    });

    it('should have all cricket positions in filter dropdown', () => {
      const cricketPositions = [
        'Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher',
        'All-rounder', 'Fast-bowler', 'Spin-bowler', 'Medium-pacer',
        'Batsman', 'Bowler'
      ];

      cy.get('#position-filter option').then(($options) => {
        cricketPositions.forEach(position => {
          expect($options.text()).to.include(position);
        });
      });
    });
  });

  describe('Cricket Player Details Modal', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/players');
    });

    it('should show cricket stats in player detail modal', () => {
      // Click on first player's view button
      cy.get('[data-cy="player-row"]').first().find('button[onclick*="showPlayerDetails"]').click();
      
      // Check modal opens
      cy.get('#playerDetailModal').should('be.visible');
      
      // Check cricket stats labels are present
      cy.get('#playerDetailModal').should('contain', 'Batting Average');
      cy.get('#playerDetailModal').should('contain', 'Strike Rate');
      cy.get('#playerDetailModal').should('contain', 'Bowling Average');
      cy.get('#playerDetailModal').should('contain', 'Economy Rate');
      cy.get('#playerDetailModal').should('contain', 'Fielding Rating');
      
      // Check progress bars are present
      cy.get('#modal-batting-bar').should('exist');
      cy.get('#modal-strike-bar').should('exist');
      cy.get('#modal-bowling-bar').should('exist');
      cy.get('#modal-economy-bar').should('exist');
      cy.get('#modal-fielding-bar').should('exist');
      
      // Close modal
      cy.get('#playerDetailModal .btn-close').click();
      cy.get('#playerDetailModal').should('not.be.visible');
    });
  });

  describe('Cricket Team Composition', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/team-composition');
    });

    it('should display cricket team setup instead of football formation', () => {
      // Check page title and headers
      cy.get('h5').should('contain', 'Cricket Team Setup');
      cy.get('small').should('contain', 'Playing XI: Cricket Formation');
      
      // Check refresh button has correct function name
      cy.get('button[onclick="refreshTeam()"]').should('exist');
      
      // Check position breakdown section
      cy.get('h5').should('contain', 'Position Breakdown');
    });

    it('should show cricket positions in team overview', () => {
      const cricketPositions = [
        'Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher',
        'All-rounder', 'Fast-bowler', 'Spin-bowler'
      ];

      // Wait for data to load
      cy.get('#position-breakdown', { timeout: 10000 }).should('be.visible');
      
      // Check that cricket positions are displayed
      cricketPositions.forEach(position => {
        cy.get('#position-breakdown').should('contain', position);
      });
    });
  });

  describe('Cricket Dashboard Integration', () => {
    beforeEach(() => {
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');  
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/dashboard');
    });

    it('should display ground name instead of stadium', () => {
      // Wait for dashboard data to load
      cy.get('[data-cy="team-info"]', { timeout: 10000 }).should('be.visible');
      
      // Check that "Ground:" is used instead of "Stadium:"
      cy.get('[data-cy="team-info"]').should('contain', 'Ground:');
      cy.get('[data-cy="team-info"]').should('not.contain', 'Stadium:');
    });

    it('should show cricket team statistics', () => {
      // Check team stats section shows cricket-relevant info
      cy.get('[data-cy="team-stats"]').should('contain', 'Players');
      cy.get('[data-cy="team-stats"]').should('contain', 'Average Age');
      cy.get('[data-cy="team-stats"]').should('contain', 'Team Rating');
    });
  });

  describe('Cricket Data Validation', () => {
    it('should have realistic cricket statistics ranges', () => {
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/players');

      // Check that batting averages are in realistic range (5-70)
      cy.get('[data-cy="player-row"]').each(($row) => {
        cy.wrap($row).find('td').eq(4).then(($cell) => {
          const battingAvg = parseFloat($cell.text());
          if (!isNaN(battingAvg)) {
            expect(battingAvg).to.be.at.least(5);
            expect(battingAvg).to.be.at.most(70);
          }
        });
      });

      // Check that strike rates are in realistic range (50-250)
      cy.get('[data-cy="player-row"]').each(($row) => {
        cy.wrap($row).find('td').eq(5).then(($cell) => {
          const strikeRate = parseFloat($cell.text());
          if (!isNaN(strikeRate)) {
            expect(strikeRate).to.be.at.least(50);
            expect(strikeRate).to.be.at.most(250);
          }
        });
      });
    });

    it('should handle non-bowlers correctly', () => {
      cy.visit('/login');
      cy.get('#email').type('crickettest@example.com');
      cy.get('#password').type('password123');
      cy.get('button[type="submit"]').click();
      cy.visit('/players');

      // Check that some players have "N/A" for bowling stats (non-bowlers)
      cy.get('[data-cy="player-row"]').then(($rows) => {
        let hasNonBowler = false;
        $rows.each((index, row) => {
          const bowlingAvgCell = Cypress.$(row).find('td').eq(6);
          if (bowlingAvgCell.text().includes('N/A')) {
            hasNonBowler = true;
          }
        });
        expect(hasNonBowler).to.be.true;
      });
    });
  });
});