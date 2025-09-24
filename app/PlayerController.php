<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PlayerController {
    
    private $player;
    
    public function __construct() {
        $this->player = new Player();
    }
    
    /**
     * Show players page
     */
    public function playersPage() {
        $f3 = Base::instance();
        $f3->set('title', 'Players');
        echo Template::instance()->render('players.html');
    }
    
    /**
     * Show team composition page
     */
    public function teamCompositionPage() {
        $f3 = Base::instance();
        $f3->set('title', 'Team Overview');
        echo Template::instance()->render('team-composition.html');
    }
    
    /**
     * Helper function to get authenticated user info from JWT token
     */
    private function getAuthUser($f3) {
        $authHeader = $f3->get('HEADERS.Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($f3->get('JWT_SECRET'), 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Helper function to check if user is admin
     */
    private function checkAdmin($f3) {
        $user = $this->getAuthUser($f3);
        return $user && $user->role === 'admin';
    }
    
    /**
     * Get players for the authenticated user's team
     */
    public function apiGetMyPlayers() {
        $f3 = Base::instance();
        $db = $f3->get('DB');
        
        $user = $this->getAuthUser($f3);
        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // Get user's team
        $team = $db->exec('SELECT id FROM teams WHERE user_id = ?', [$user->user_id]);
        if (!$team) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Team not found']);
            return;
        }
        
        $players = $this->player->getTeamPlayers($team[0]['id']);
        
        header('Content-Type: application/json');
        echo json_encode(['players' => $players]);
    }
    
    /**
     * Get all players (admin only)
     */
    public function apiGetAllPlayers() {
        $f3 = Base::instance();
        
        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $page = (int)$f3->get('GET.page') ?: 1;
        $limit = (int)$f3->get('GET.limit') ?: 20;
        
        $filters = [];
        if ($f3->get('GET.team_id')) {
            $filters['team_id'] = $f3->get('GET.team_id');
        }
        if ($f3->get('GET.position')) {
            $filters['position'] = $f3->get('GET.position');
        }
        if ($f3->get('GET.name')) {
            $filters['name'] = $f3->get('GET.name');
        }
        
        $result = $this->player->getAllPlayers($page, $limit, $filters);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Get single player details
     */
    public function apiGetPlayer() {
        $f3 = Base::instance();
        $db = $f3->get('DB');
        
        $user = $this->getAuthUser($f3);
        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $player_id = $f3->get('PARAMS.id');
        $player = $this->player->getPlayer($player_id);
        
        if (!$player) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Player not found']);
            return;
        }
        
        // Check if user owns this player or is admin
        if ($user->role !== 'admin') {
            $team = $db->exec('SELECT id FROM teams WHERE user_id = ?', [$user->user_id]);
            if (!$team || $team[0]['id'] != $player['team_id']) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['player' => $player]);
    }
    
    /**
     * Create a new player (admin only)
     */
    public function apiCreatePlayer() {
        $f3 = Base::instance();
        
        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $input = json_decode($f3->get('BODY'), true);
        
        $required = ['name', 'position', 'team_id'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Validate position
        $validPositions = ['GK', 'LB', 'CB', 'RB', 'CDM', 'CM', 'CAM', 'LW', 'ST', 'RW', 'CF'];
        if (!in_array($input['position'], $validPositions)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid position']);
            return;
        }
        
        try {
            $playerData = [
                'name' => $input['name'],
                'position' => $input['position'],
                'age' => $input['age'] ?? rand(18, 35),
                'morale' => $input['morale'] ?? rand(40, 80),
                'speed' => $input['speed'] ?? rand(30, 70),
                'strength' => $input['strength'] ?? rand(30, 70),
                'technique' => $input['technique'] ?? rand(30, 70),
                'team_id' => $input['team_id']
            ];
            
            // Calculate overall rating
            $playerData['overall_rating'] = round(
                ($playerData['speed'] * 0.3) + 
                ($playerData['strength'] * 0.3) + 
                ($playerData['technique'] * 0.4)
            );
            
            // Create player using direct SQL since createPlayer is private
            $db = $f3->get('DB');
            $sql = 'INSERT INTO players (name, position, age, morale, speed, strength, technique, overall_rating, team_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $db->exec($sql, [
                $playerData['name'],
                $playerData['position'],
                $playerData['age'],
                $playerData['morale'],
                $playerData['speed'],
                $playerData['strength'],
                $playerData['technique'],
                $playerData['overall_rating'],
                $playerData['team_id']
            ]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'player' => $playerData]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create player']);
        }
    }
    
    /**
     * Update player (admin only)
     */
    public function apiUpdatePlayer() {
        $f3 = Base::instance();
        
        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $player_id = $f3->get('PARAMS.id');
        $input = json_decode($f3->get('BODY'), true);
        
        if (!$this->player->getPlayer($player_id)) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Player not found']);
            return;
        }
        
        try {
            $this->player->updatePlayer($player_id, $input);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update player']);
        }
    }
    
    /**
     * Delete player (admin only)
     */
    public function apiDeletePlayer() {
        $f3 = Base::instance();
        
        if (!$this->checkAdmin($f3)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $player_id = $f3->get('PARAMS.id');
        
        if (!$this->player->getPlayer($player_id)) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Player not found']);
            return;
        }
        
        try {
            $this->player->deletePlayer($player_id);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete player']);
        }
    }
    
    /**
     * Get team composition and stats summary
     */
    public function apiGetTeamComposition() {
        $f3 = Base::instance();
        $db = $f3->get('DB');
        
        $user = $this->getAuthUser($f3);
        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // Get user's team
        $team = $db->exec('SELECT * FROM teams WHERE user_id = ?', [$user->user_id]);
        if (!$team) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Team not found']);
            return;
        }
        
        $team = $team[0];
        $players = $this->player->getTeamPlayers($team['id']);
        
        // Calculate team stats
        $totalPlayers = count($players);
        $averageAge = $totalPlayers > 0 ? round(array_sum(array_column($players, 'age')) / $totalPlayers, 1) : 0;
        $averageRating = $totalPlayers > 0 ? round(array_sum(array_column($players, 'overall_rating')) / $totalPlayers, 1) : 0;
        
        // Group by position
        $composition = [];
        foreach ($players as $player) {
            if (!isset($composition[$player['position']])) {
                $composition[$player['position']] = [];
            }
            $composition[$player['position']][] = $player;
        }
        
        // Top 3 players
        usort($players, function($a, $b) {
            return $b['overall_rating'] - $a['overall_rating'];
        });
        $topPlayers = array_slice($players, 0, 3);
        
        header('Content-Type: application/json');
        echo json_encode([
            'team' => $team,
            'players' => $players,
            'composition' => $composition,
            'stats' => [
                'total_players' => $totalPlayers,
                'average_age' => $averageAge,
                'average_rating' => $averageRating,
                'top_players' => $topPlayers
            ]
        ]);
    }
}