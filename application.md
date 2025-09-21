# SwitchedHit MVP

This is one line definition of the app.

a web/mobile MVP named SwitchedHit focused on club creation, squad management, and daily simulated league matches. Matches are not live; they are pre-scheduled and, at kickoff time, a simulation runs automatically and publishes a scorecard + ball-by-ball commentary.


Lets plan the Sprint Design to build this in steps to give optimal iterations
1. Login/Register + Dashboard
Register Flow V1
Team Name, Stadium Name, Details, Pitch Type

Admin:
Login, CRUD Users, CRUD User Details

2. Register Flow V2 
Generate Team Members and stats
List/view Players
Admin:
CRUD Players, CRUD Player Details

3. Setup/Configure Daily Team Lineup
Daily Player Stats changes 
Age handling
Admin:
Manage Player Aging, Morale

4. Auction/MarketPlace
Buy/Sell players
(Blind Bid system)
Admin:
Control Bid System, Buy/sell player, CRUD User Money

5. Simulate Practice match
Ball by ball commentary
Deterministic Match generation
Admin: Configurable parameters for Deterministic match


6. League Simulations
Assigning leagues and promotion relegation, set matched within the 
Admin:
League Management