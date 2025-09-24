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

        // Handle JSON input
        $input = json_decode($f3->get('BODY'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

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

        // Handle JSON input
        $input = json_decode($f3->get('BODY'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $team_name = $input['team_name'] ?? null;
        $stadium_name = $input['stadium_name'] ?? null;
        $pitch_type = $input['pitch_type'] ?? null;

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
            $team_id = $db->lastInsertId();

            // Generate players for the new team
            $player = new Player();
            $player->generateTeamPlayers($team_id);

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

    // Helper function to check if user is admin
    private function checkAdmin($f3) {
        $authHeader = $f3->get('HEADERS.Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($f3->get('JWT_SECRET'), 'HS256'));
            return $decoded->role === 'admin';
        } catch (Exception $e) {
            return false;
        }
    }

    // Admin endpoints for user management
    function apiGetUsers() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Admin access required']);
            return;
        }

        // Get pagination parameters
        $page = (int)$f3->get('GET.page') ?: 1;
        $limit = (int)$f3->get('GET.limit') ?: 10;
        $offset = ($page - 1) * $limit;

        // Get filter parameters
        $role = $f3->get('GET.role');
        $email = $f3->get('GET.email');

        // Build query
        $where = [];
        $params = [];

        if ($role) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        if ($email) {
            $where[] = 'email LIKE ?';
            $params[] = '%' . $email . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countQuery = 'SELECT COUNT(*) as total FROM users ' . $whereClause;
        $total = $db->exec($countQuery, $params)[0]['total'];

        // Get users with pagination
        $query = 'SELECT id, email, role, created_at FROM users ' . $whereClause . ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $users = $db->exec($query, $params);

        header('Content-Type: application/json');
        echo json_encode([
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    function apiCreateUser() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Admin access required']);
            return;
        }

        // Handle JSON input
        $input = json_decode($f3->get('BODY'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $role = $input['role'] ?? 'user';

        if (!$email || !$password) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email and password required']);
            return;
        }

        if (!in_array($role, ['user', 'admin'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid role']);
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

        try {
            $db->exec('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)', [$email, $password_hash, $role]);
            $user_id = $db->lastInsertId();

            header('Content-Type: application/json');
            echo json_encode([
                'user' => [
                    'id' => $user_id,
                    'email' => $email,
                    'role' => $role,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to create user']);
        }
    }

    function apiUpdateUser() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Admin access required']);
            return;
        }

        $user_id = $f3->get('PARAMS.id');

        // Handle JSON input
        $input = json_decode($f3->get('BODY'), true);
        $email = $input['email'] ?? null;
        $role = $input['role'] ?? null;
        $password = $input['password'] ?? null; // Optional for password update

        if (!$user_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User ID required']);
            return;
        }

        // Check if user exists
        $existing = $db->exec('SELECT id FROM users WHERE id = ?', $user_id);
        if (!$existing) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User not found']);
            return;
        }

        // Build update query
        $updates = [];
        $params = [];

        if ($email) {
            // Check if email is already taken by another user
            $emailCheck = $db->exec('SELECT id FROM users WHERE email = ? AND id != ?', [$email, $user_id]);
            if ($emailCheck) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Email already exists']);
                return;
            }
            $updates[] = 'email = ?';
            $params[] = $email;
        }

        if ($role && in_array($role, ['user', 'admin'])) {
            $updates[] = 'role = ?';
            $params[] = $role;
        }

        if ($password) {
            $updates[] = 'password_hash = ?';
            $params[] = password_hash($password, PASSWORD_BCRYPT);
        }

        if (empty($updates)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }

        $params[] = $user_id;
        $query = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';

        try {
            $db->exec($query, $params);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to update user']);
        }
    }

    function apiDeleteUser() {
        $f3 = Base::instance();
        $db = $f3->get('DB');

        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Admin access required']);
            return;
        }

        $user_id = $f3->get('PARAMS.id');

        if (!$user_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User ID required']);
            return;
        }

        // Check if user exists
        $existing = $db->exec('SELECT id FROM users WHERE id = ?', $user_id);
        if (!$existing) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User not found']);
            return;
        }

        // Don't allow deleting yourself
        $authHeader = $f3->get('HEADERS.Authorization');
        preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
        $token = $matches[1];
        $decoded = JWT::decode($token, new Key($f3->get('JWT_SECRET'), 'HS256'));

        if ($decoded->user_id == $user_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cannot delete your own account']);
            return;
        }

        $db->begin();
        try {
            // Delete team first (due to foreign key constraint)
            $db->exec('DELETE FROM teams WHERE user_id = ?', $user_id);
            // Delete user
            $db->exec('DELETE FROM users WHERE id = ?', $user_id);

            $db->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollback();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to delete user']);
        }
    }

}