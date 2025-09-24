#!/bin/bash

# Test Runner Script for SwitchedHit

echo "🚀 SwitchedHit Test Runner"
echo "========================="

# Check if PHP server is running
echo "📋 Checking if PHP server is running on port 8080..."
curl -s http://localhost:8080 > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ PHP server is running"
else
    echo "❌ PHP server is not running. Please start it with: php -S localhost:8080"
    exit 1
fi

# Check if test user exists by trying to login
echo "📋 Checking if test user exists..."
response=$(curl -s -X POST http://localhost:8080/api/login -H "Content-Type: application/json" -d '{"email":"test@gel.com","password":"test@gel.com"}')
if [[ $response == *"token"* ]]; then
    echo "✅ Test user exists and can login"
else
    echo "⚠️  Test user doesn't exist. Creating test user..."
    curl -s -X POST http://localhost:8080/api/register -H "Content-Type: application/json" -d '{"email":"test@gel.com","password":"test@gel.com","team_name":"Test Team","stadium_name":"Test Stadium","pitch_type":"Hard"}' > /dev/null
    echo "✅ Test user created"
fi

# Run tests based on argument
case "$1" in
    "api")
        echo "🧪 Running API integration tests..."
        npx cypress run --spec "cypress/e2e/03_api_integration.cy.js" --headless
        ;;
    "auth")
        echo "🔐 Running authentication tests..."
        npx cypress run --spec "cypress/e2e/01_authentication.cy.js" --headless
        ;;
    "players")
        echo "👥 Running player management tests..."
        npx cypress run --spec "cypress/e2e/02_player_management.cy.js" --headless
        ;;
    "gui")
        echo "🖥️  Opening Cypress GUI..."
        npx cypress open
        ;;
    *)
        echo "🧪 Running all tests..."
        npx cypress run --headless
        ;;
esac

echo "🎉 Test run completed!"