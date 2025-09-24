<?php

class Player {
    
    private $db;
    
    public function __construct() {
        $f3 = Base::instance();
        $this->db = $f3->get('DB');
    }
    
    /**
     * Generate 11 balanced players for a team
     */
    public function generateTeamPlayers($team_id) {
        $positions = [
            'GK' => 1,    // Goalkeeper
            'LB' => 1,    // Left Back
            'CB' => 2,    // Center Back (2 players)
            'RB' => 1,    // Right Back
            'CDM' => 1,   // Central Defensive Midfielder
            'CM' => 2,    // Central Midfielder (2 players)
            'CAM' => 1,   // Central Attacking Midfielder
            'LW' => 1,    // Left Wing
            'RW' => 1,    // Right Wing
            'ST' => 1     // Striker
        ];
        
        $players = [];
        
        foreach ($positions as $position => $count) {
            for ($i = 0; $i < $count; $i++) {
                $player = $this->generatePlayer($position, $team_id);
                $players[] = $player;
            }
        }
        
        // Insert all players into database
        foreach ($players as $player) {
            $this->createPlayer($player);
        }
        
        return $players;
    }
    
    /**
     * Generate a single player with position-specific stats
     */
    private function generatePlayer($position, $team_id) {
        $names = [
            'Alex Johnson', 'Marcus Smith', 'David Wilson', 'James Brown', 'Robert Taylor',
            'Michael Davis', 'Chris Miller', 'Daniel Moore', 'Matthew Jackson', 'Anthony White',
            'Joshua Harris', 'Andrew Martin', 'Kevin Thompson', 'Brian Garcia', 'Mark Martinez',
            'Paul Robinson', 'Steven Clark', 'Kenneth Rodriguez', 'Edward Lewis', 'Jason Lee',
            'Ryan Walker', 'Jacob Hall', 'Gary Allen', 'Nicholas Young', 'Eric Hernandez',
            'Jonathan King', 'Stephen Wright', 'Larry Lopez', 'Justin Hill', 'Scott Green'
        ];
        
        $name = $names[array_rand($names)];
        $age = rand(18, 35);
        $morale = rand(40, 80);
        
        // Generate position-specific stats
        $stats = $this->generatePositionStats($position);
        
        return [
            'name' => $name,
            'position' => $position,
            'age' => $age,
            'morale' => $morale,
            'speed' => $stats['speed'],
            'strength' => $stats['strength'],
            'technique' => $stats['technique'],
            'overall_rating' => $stats['overall_rating'],
            'team_id' => $team_id
        ];
    }
    
    /**
     * Generate stats based on player position
     */
    private function generatePositionStats($position) {
        $baseStats = [
            'speed' => rand(30, 70),
            'strength' => rand(30, 70),
            'technique' => rand(30, 70)
        ];
        
        // Position-specific stat bonuses
        switch ($position) {
            case 'GK':
                // Goalkeepers need good technique and strength, speed less important
                $baseStats['technique'] += rand(10, 20);
                $baseStats['strength'] += rand(5, 15);
                break;
                
            case 'LB':
            case 'RB':
                // Full-backs need speed and technique
                $baseStats['speed'] += rand(10, 20);
                $baseStats['technique'] += rand(5, 15);
                break;
                
            case 'CB':
                // Center-backs need strength and some technique
                $baseStats['strength'] += rand(10, 20);
                $baseStats['technique'] += rand(5, 10);
                break;
                
            case 'CDM':
                // Defensive midfielders need balance
                $baseStats['strength'] += rand(5, 15);
                $baseStats['technique'] += rand(5, 15);
                break;
                
            case 'CM':
                // Central midfielders need good technique and some speed
                $baseStats['technique'] += rand(10, 20);
                $baseStats['speed'] += rand(5, 10);
                break;
                
            case 'CAM':
                // Attacking midfielders need excellent technique
                $baseStats['technique'] += rand(15, 25);
                $baseStats['speed'] += rand(5, 10);
                break;
                
            case 'LW':
            case 'RW':
                // Wingers need speed and technique
                $baseStats['speed'] += rand(15, 25);
                $baseStats['technique'] += rand(10, 15);
                break;
                
            case 'ST':
            case 'CF':
                // Strikers need technique and some strength
                $baseStats['technique'] += rand(10, 20);
                $baseStats['strength'] += rand(5, 15);
                break;
        }
        
        // Ensure stats don't exceed 100
        foreach ($baseStats as $key => $value) {
            $baseStats[$key] = min(100, $value);
        }
        
        // Calculate overall rating as weighted average
        $baseStats['overall_rating'] = round(
            ($baseStats['speed'] * 0.3) + 
            ($baseStats['strength'] * 0.3) + 
            ($baseStats['technique'] * 0.4)
        );
        
        return $baseStats;
    }
    
    /**
     * Create a player in the database
     */
    private function createPlayer($playerData) {
        $sql = 'INSERT INTO players (name, position, age, morale, speed, strength, technique, overall_rating, team_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        return $this->db->exec($sql, [
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
    }
    
    /**
     * Get all players for a team
     */
    public function getTeamPlayers($team_id) {
        return $this->db->exec('SELECT * FROM players WHERE team_id = ? ORDER BY position, name', [$team_id]);
    }
    
    /**
     * Get a single player by ID
     */
    public function getPlayer($player_id) {
        $result = $this->db->exec('SELECT * FROM players WHERE id = ?', [$player_id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Update player stats
     */
    public function updatePlayer($player_id, $data) {
        $updates = [];
        $params = [];
        
        $allowedFields = ['name', 'age', 'morale', 'speed', 'strength', 'technique'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updates)) {
            // Recalculate overall rating if stats changed
            if (isset($data['speed']) || isset($data['strength']) || isset($data['technique'])) {
                $player = $this->getPlayer($player_id);
                if ($player) {
                    $speed = $data['speed'] ?? $player['speed'];
                    $strength = $data['strength'] ?? $player['strength'];
                    $technique = $data['technique'] ?? $player['technique'];
                    
                    $overall = round(($speed * 0.3) + ($strength * 0.3) + ($technique * 0.4));
                    $updates[] = "overall_rating = ?";
                    $params[] = $overall;
                }
            }
            
            $params[] = $player_id;
            $sql = 'UPDATE players SET ' . implode(', ', $updates) . ' WHERE id = ?';
            
            return $this->db->exec($sql, $params);
        }
        
        return false;
    }
    
    /**
     * Delete a player
     */
    public function deletePlayer($player_id) {
        return $this->db->exec('DELETE FROM players WHERE id = ?', [$player_id]);
    }
    
    /**
     * Get all players (admin function)
     */
    public function getAllPlayers($page = 1, $limit = 20, $filters = []) {
        $where = [];
        $params = [];
        
        if (isset($filters['team_id'])) {
            $where[] = 'p.team_id = ?';
            $params[] = $filters['team_id'];
        }
        
        if (isset($filters['position'])) {
            $where[] = 'p.position = ?';
            $params[] = $filters['position'];
        }
        
        if (isset($filters['name'])) {
            $where[] = 'p.name LIKE ?';
            $params[] = '%' . $filters['name'] . '%';
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $countSql = 'SELECT COUNT(*) as total FROM players p ' . $whereClause;
        $total = $this->db->exec($countSql, $params)[0]['total'];
        
        // Get players with team info
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT p.*, t.name as team_name 
                FROM players p 
                JOIN teams t ON p.team_id = t.id ' . 
                $whereClause . 
                ' ORDER BY p.overall_rating DESC, p.name 
                LIMIT ? OFFSET ?';
        
        $params[] = $limit;
        $params[] = $offset;
        
        $players = $this->db->exec($sql, $params);
        
        return [
            'players' => $players,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
}