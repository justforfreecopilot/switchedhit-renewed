@echo off
REM Test Runner Script for SwitchedHit (Windows)

echo 🚀 SwitchedHit Test Runner
echo =========================

REM Check if PHP server is running
echo 📋 Checking if PHP server is running on port 8080...
curl -s http://localhost:8080 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ PHP server is running
) else (
    echo ❌ PHP server is not running. Please start it with: php -S localhost:8080
    exit /b 1
)

REM Check if test user exists by trying to login
echo 📋 Checking if test user exists...
for /f "delims=" %%i in ('curl -s -X POST http://localhost:8080/api/login -H "Content-Type: application/json" -d "{\"email\":\"test@gel.com\",\"password\":\"test@gel.com\"}"') do set response=%%i
echo %response% | findstr "token" >nul
if %errorlevel% equ 0 (
    echo ✅ Test user exists and can login
) else (
    echo ⚠️  Test user doesn't exist. Creating test user...
    curl -s -X POST http://localhost:8080/api/register -H "Content-Type: application/json" -d "{\"email\":\"test@gel.com\",\"password\":\"test@gel.com\",\"team_name\":\"Test Team\",\"stadium_name\":\"Test Stadium\",\"pitch_type\":\"Hard\"}" >nul
    echo ✅ Test user created
)

REM Run tests based on argument
if "%1"=="api" (
    echo 🧪 Running API integration tests...
    npx cypress run --spec "cypress/e2e/03_api_integration.cy.js" --headless
) else if "%1"=="auth" (
    echo 🔐 Running authentication tests...
    npx cypress run --spec "cypress/e2e/01_authentication.cy.js" --headless
) else if "%1"=="players" (
    echo 👥 Running player management tests...
    npx cypress run --spec "cypress/e2e/02_player_management.cy.js" --headless
) else if "%1"=="gui" (
    echo 🖥️  Opening Cypress GUI...
    npx cypress open
) else if "%1"=="chrome" (
    echo 🌐 Running tests in Chrome browser...
    npx cypress run --browser chrome --headed
) else if "%1"=="firefox" (
    echo 🦊 Running tests in Firefox browser...
    npx cypress run --browser firefox --headed
) else if "%1"=="edge" (
    echo 🌊 Running tests in Edge browser...
    npx cypress run --browser edge --headed
) else if "%1"=="browsers" (
    echo 🌐 Running cross-browser tests...
    npx cypress run --browser chrome --headless
    npx cypress run --browser firefox --headless
    npx cypress run --browser edge --headless
) else (
    echo 🧪 Running all tests...
    npx cypress run --headless
)

echo 🎉 Test run completed!