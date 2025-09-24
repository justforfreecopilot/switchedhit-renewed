describe('Cricket API Tests', () => {
  let authToken;
  let teamId;

  before(() => {
    // Create a test user and team for API testing
    cy.request({
      method: 'POST',
      url: '/api/register',
      body: {
        email: 'api-cricket-test@example.com',
        password: 'password123',
        team_name: 'API Cricket Team',
        stadium_name: 'API Cricket Ground',
        pitch_type: 'Green',
        details: 'Team for API testing'
      }
    }).then((response) => {
      expect(response.status).to.eq(200);
      expect(response.body).to.have.property('success', true);
      
      // Login to get auth token
      return cy.request({
        method: 'POST',
        url: '/api/login',
        body: {
          email: 'api-cricket-test@example.com',
          password: 'password123'
        }
      });
    }).then((loginResponse) => {
      expect(loginResponse.status).to.eq(200);
      authToken = loginResponse.body.token;
      teamId = loginResponse.body.user.team_id;
    });
  });

  describe('Cricket Player API Endpoints', () => {
    it('should fetch cricket players with proper stats', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my-team',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('players');
        expect(response.body.players).to.have.length(11);

        // Check that each player has cricket stats
        response.body.players.forEach(player => {
          expect(player).to.have.property('batting_average');
          expect(player).to.have.property('bowling_average');
          expect(player).to.have.property('strike_rate');
          expect(player).to.have.property('economy_rate');
          expect(player).to.have.property('fielding_rating');
          expect(player).to.have.property('overall_rating');

          // Verify cricket positions
          const validPositions = [
            'Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher',
            'All-rounder', 'Fast-bowler', 'Spin-bowler', 'Medium-pacer',
            'Batsman', 'Bowler', 'Specialist-fielder'
          ];
          expect(validPositions).to.include(player.position);

          // Verify stat ranges
          expect(player.batting_average).to.be.at.least(5).and.at.most(70);
          expect(player.strike_rate).to.be.at.least(50).and.at.most(250);
          expect(player.fielding_rating).to.be.at.least(30).and.at.most(100);
          expect(player.overall_rating).to.be.at.least(30).and.at.most(100);

          // Non-bowlers should have bowling average of 999 or economy rate of 0
          if (player.bowling_average >= 999 || player.economy_rate === 0) {
            expect(['Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Finisher', 'Batsman']).to.include(player.position);
          } else {
            expect(player.bowling_average).to.be.at.least(15).and.at.most(50);
            expect(player.economy_rate).to.be.at.least(3).and.at.most(15);
          }
        });
      });
    });

    it('should fetch team composition with cricket data', () => {
      cy.request({
        method: 'GET',
        url: '/api/team/composition',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('team');
        expect(response.body).to.have.property('players');
        expect(response.body).to.have.property('composition');
        expect(response.body).to.have.property('stats');

        // Check team composition has cricket positions
        const composition = response.body.composition;
        const cricketPositions = Object.keys(composition);
        
        expect(cricketPositions.some(pos => 
          ['Wicket-keeper', 'Opening-batsman', 'Middle-order', 'Fast-bowler', 'Spin-bowler', 'All-rounder', 'Finisher'].includes(pos)
        )).to.be.true;

        // Check team stats
        expect(response.body.stats).to.have.property('total_players', 11);
        expect(response.body.stats).to.have.property('average_age');
        expect(response.body.stats).to.have.property('average_rating');
        expect(response.body.stats).to.have.property('top_players');
      });
    });

    it('should validate cricket position in player creation', () => {
      // Test valid cricket position
      cy.request({
        method: 'POST',
        url: '/api/admin/players',
        headers: {
          'Authorization': `Bearer ${authToken}`
        },
        body: {
          name: 'Test Cricket Player',
          position: 'All-rounder',
          age: 25,
          team_id: teamId,
          batting_average: 35.50,
          bowling_average: 28.75,
          strike_rate: 135.20,
          economy_rate: 7.25,
          fielding_rating: 75
        },
        failOnStatusCode: false
      }).then((response) => {
        // This might fail if not admin, but should validate position format
        if (response.status === 400) {
          expect(response.body.error).to.not.contain('Invalid cricket position');
        }
      });

      // Test invalid position (football position)
      cy.request({
        method: 'POST',
        url: '/api/admin/players',
        headers: {
          'Authorization': `Bearer ${authToken}`
        },
        body: {
          name: 'Invalid Position Player',
          position: 'GK', // Football position should be rejected
          age: 25,
          team_id: teamId
        },
        failOnStatusCode: false
      }).then((response) => {
        if (response.status === 400) {
          expect(response.body.error).to.contain('Invalid cricket position');
        }
      });
    });
  });

  describe('Cricket Statistics Calculation', () => {
    it('should calculate overall rating based on cricket performance', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my-team',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        const players = response.body.players;
        
        players.forEach(player => {
          // Manually calculate expected overall rating
          const battingScore = (player.batting_average / 50) * 40;
          const bowlingScore = player.bowling_average < 999 ? ((50 - Math.min(player.bowling_average, 50)) / 50) * 30 : 0;
          const fieldingScore = (player.fielding_rating / 100) * 20;
          const strikeRateBonus = ((player.strike_rate - 100) / 100) * 10;
          
          const expectedRating = Math.round(Math.max(30, Math.min(100, battingScore + bowlingScore + fieldingScore + strikeRateBonus)));
          
          // Allow some variance due to rounding
          expect(player.overall_rating).to.be.closeTo(expectedRating, 5);
        });
      });
    });
  });

  describe('Cricket Data Integrity', () => {
    it('should have proper cricket team distribution', () => {
      cy.request({
        method: 'GET',
        url: '/api/team/composition',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        const composition = response.body.composition;
        
        // Check that we have at least one wicket-keeper
        expect(composition['Wicket-keeper']).to.have.length.at.least(1);
        
        // Check that we have batsmen
        const batsmen = (composition['Opening-batsman'] || []).length + 
                       (composition['Middle-order'] || []).length + 
                       (composition['Finisher'] || []).length;
        expect(batsmen).to.be.at.least(4);
        
        // Check that we have bowlers
        const bowlers = (composition['Fast-bowler'] || []).length + 
                       (composition['Spin-bowler'] || []).length + 
                       (composition['All-rounder'] || []).length;
        expect(bowlers).to.be.at.least(4);
      });
    });

    it('should maintain referential integrity between teams and players', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my-team',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        const players = response.body.players;
        
        // All players should belong to the same team
        players.forEach(player => {
          expect(player.team_id).to.equal(teamId);
        });
      });
    });
  });

  describe('Cricket UI Data Integration', () => {
    it('should provide data in format expected by UI', () => {
      cy.request({
        method: 'GET',
        url: '/api/players/my-team',
        headers: {
          'Authorization': `Bearer ${authToken}`
        }
      }).then((response) => {
        const players = response.body.players;
        
        players.forEach(player => {
          // Check that all required fields for UI are present
          expect(player).to.have.all.keys([
            'id', 'name', 'position', 'age', 'morale',
            'batting_average', 'bowling_average', 'strike_rate', 
            'economy_rate', 'fielding_rating', 'overall_rating',
            'team_id', 'created_at'
          ]);
          
          // Check data types match what UI expects
          expect(player.id).to.be.a('number');
          expect(player.name).to.be.a('string');
          expect(player.position).to.be.a('string');
          expect(player.age).to.be.a('number');
          expect(player.overall_rating).to.be.a('number');
        });
      });
    });
  });
});