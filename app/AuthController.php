<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {

    function login() {
        $f3 = Base::instance();
        $f3->set('title', 'Login - SwitchedHit');
        echo Template::instance()->render('login.html');
    }

    function register() {
        $f3 = Base::instance();
        $f3->set('title', 'Register - SwitchedHit');
        echo Template::instance()->render('register.html');
    }

    function apiLogin() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');

        if (!$email || !$password) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email and password required']);
            return;
        }

        $user = $db->exec('SELECT * FROM users WHERE email = ?', $email);
        if (!$user || !password_verify($password, $user[0]['password_hash'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $payload = [
            'iss' => 'switchedhit',
            'aud' => 'switchedhit',
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
            'user_id' => $user[0]['id'],
            'role' => $user[0]['role']
        ];

        $jwt = JWT::encode($payload, $f3->get('JWT_SECRET'), 'HS256');

        header('Content-Type: application/json');
        echo json_encode(['token' => $jwt, 'user' => ['id' => $user[0]['id'], 'email' => $user[0]['email'], 'role' => $user[0]['role']]]);
    }

    function apiRegister() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $team_name = $f3->get('POST.team_name');
        $stadium_name = $f3->get('POST.stadium_name');
        $pitch_type = $f3->get('POST.pitch_type');

        if (!$email || !$password || !$team_name || !$pitch_type) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Required fields missing']);
            return;
        }

        // Check if email exists
        $existing = $db->exec('SELECT id FROM users WHERE email = ?', $email);
        if ($existing) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $db->begin();
        try {
            $db->exec('INSERT INTO users (email, password_hash) VALUES (?, ?)', [$email, $password_hash]);
            $user_id = $db->lastInsertId();

            $db->exec('INSERT INTO teams (name, stadium_name, pitch_type, user_id) VALUES (?, ?, ?, ?)', 
                [$team_name, $stadium_name, $pitch_type, $user_id]);

            $db->commit();

            $payload = [
                'iss' => 'switchedhit',
                'aud' => 'switchedhit',
                'iat' => time(),
                'exp' => time() + 3600,
                'user_id' => $user_id,
                'role' => 'user'
            ];

            $jwt = JWT::encode($payload, $f3->get('JWT_SECRET'), 'HS256');

            header('Content-Type: application/json');
            echo json_encode(['token' => $jwt, 'user' => ['id' => $user_id, 'email' => $email, 'role' => 'user']]);
        } catch (Exception $e) {
            $db->rollback();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    function apiUserTeam() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        $authHeader = $f3->get('HEADERS.Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($f3->get('JWT_SECRET'), 'HS256'));
            $user_id = $decoded->user_id;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $team = $db->exec('SELECT * FROM teams WHERE user_id = ?', $user_id);
        if (!$team) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Team not found']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['team' => $team[0]]);
    }

    function apiUserMe() {
        $f3 = Base::instance();

        $authHeader = $f3->get('HEADERS.Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($f3->get('JWT_SECRET'), 'HS256'));
            header('Content-Type: application/json');
            echo json_encode(['user' => ['id' => $decoded->user_id, 'email' => 'hidden', 'role' => $decoded->role]]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid token']);
        }
    }

}