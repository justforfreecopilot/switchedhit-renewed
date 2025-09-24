<?php

class Player {
    
    private $db;
    
    public function __construct() {
        $f3 = Base::instance();
        $this->db = $f3->get('DB');
    }
    
    /**
     * Generate 15 balanced players for a cricket squad
     */
    public function generateTeamPlayers($team_id) {
        $positions = [
            'Wicket-keeper' => 2,      // Wicket-keepers (2 - backup keeper)
            'Opening-batsman' => 3,    // Opening batsmen (3 - options for different formats)
            'Middle-order' => 4,       // Middle-order batsmen (4 - core batting lineup)
            'Finisher' => 2,           // Finishers/Lower order (2 - death overs specialists)
            'All-rounder' => 2,        // All-rounders (2 - balance the team)
            'Fast-bowler' => 3,        // Fast bowlers (3 - pace attack)
            'Spin-bowler' => 2         // Spin bowlers (2 - spin options)
        ];
        
        $players = [];
        
        // Pre-fetch names in bulk for better performance (fetch extra to account for potential duplicates)
        $this->preloadNames(20);
        
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
    
    // Name cache to store fetched names
    private $nameCache = [];
    
    /**
     * Pre-fetch names from randomuser.me API in bulk
     */
    private function preloadNames($count) {
        try {
            // Fetch more names than needed to account for duplicates
            $fetchCount = max(15, $count * 2);
            $url = "https://randomuser.me/api/?nat=in&gender=male&inc=name&results={$fetchCount}";
            
            $response = @file_get_contents($url);
            if ($response === false) {
                throw new Exception('Failed to fetch names from API');
            }
            
            $data = json_decode($response, true);
            if (!$data || !isset($data['results'])) {
                throw new Exception('Invalid API response');
            }
            
            // Extract and format names
            foreach ($data['results'] as $user) {
                $firstName = ucfirst($user['name']['first']);
                $lastName = ucfirst($user['name']['last']);
                $fullName = $firstName . ' ' . $lastName;
                
                // Add to cache if not already exists
                if (!in_array($fullName, $this->nameCache)) {
                    $this->nameCache[] = $fullName;
                }
            }
            
            error_log("Preloaded " . count($this->nameCache) . " names from randomuser.me API");
            
        } catch (Exception $e) {
            error_log("Error fetching names from API: " . $e->getMessage());
            // Fallback to default Indian cricket names
            $this->nameCache = [
                'Virat Kohli', 'Rohit Sharma', 'Hardik Pandya', 'Jasprit Bumrah', 'KL Rahul',
                'Rishabh Pant', 'Ravindra Jadeja', 'Mohammed Shami', 'Bhuvneshwar Kumar', 'Yuzvendra Chahal',
                'Shikhar Dhawan', 'Ajinkya Rahane', 'Cheteshwar Pujara', 'Ishant Sharma', 'Kuldeep Yadav',
                'Dinesh Karthik', 'Kedar Jadhav', 'Umesh Yadav', 'Shardul Thakur', 'Deepak Chahar',
                'Prithvi Shaw', 'Shubman Gill', 'Ishan Kishan', 'Axar Patel', 'Washington Sundar',
                'Navdeep Saini', 'T Natarajan', 'Varun Chakravarthy', 'Rahul Chahar', 'Arshdeep Singh'
            ];
        }
    }
    
    /**
     * Get a unique name that doesn't exist in database
     */
    private function getUniqueName() {
        $maxAttempts = 50;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            // Try to get name from cache first
            if (!empty($this->nameCache)) {
                $name = array_shift($this->nameCache);
            } else {
                // If cache is empty, fetch more names
                $this->preloadNames(10);
                if (!empty($this->nameCache)) {
                    $name = array_shift($this->nameCache);
                } else {
                    // Ultimate fallback
                    $name = "Player " . rand(1000, 9999);
                }
            }
            
            // Check if name already exists in database
            if (!$this->nameExists($name)) {
                return $name;
            }
            
            $attempt++;
        }
        
        // If we can't find a unique name, append a number
        $baseName = !empty($this->nameCache) ? array_shift($this->nameCache) : "Player";
        $counter = 1;
        do {
            $name = $baseName . " " . $counter;
            $counter++;
        } while ($this->nameExists($name) && $counter < 1000);
        
        return $name;
    }
    
    /**
     * Check if a player name already exists in the database
     */
    private function nameExists($name) {
        $result = $this->db->exec('SELECT COUNT(*) as count FROM players WHERE name = ?', [$name]);
        return $result && $result[0]['count'] > 0;
    }
    
    /**
     * Fetch single name from API (fallback method)
     */
    private function fetchSingleName() {
        try {
            $url = "https://randomuser.me/api/?nat=in&gender=male&inc=name&results=1";
            $response = @file_get_contents($url);
            
            if ($response === false) {
                throw new Exception('Failed to fetch name from API');
            }
            
            $data = json_decode($response, true);
            if (!$data || !isset($data['results'][0]['name'])) {
                throw new Exception('Invalid API response');
            }
            
            $user = $data['results'][0];
            $firstName = ucfirst($user['name']['first']);
            $lastName = ucfirst($user['name']['last']);
            
            return $firstName . ' ' . $lastName;
            
        } catch (Exception $e) {
            error_log("Error fetching single name: " . $e->getMessage());
            // Return a random fallback name
            $fallbackNames = ['Raj Patel', 'Amit Kumar', 'Sanjay Singh', 'Vikash Sharma', 'Deepak Gupta'];
            return $fallbackNames[array_rand($fallbackNames)];
        }
    }
    
    /**
     * Generate a single player with position-specific stats
     */
    private function generatePlayer($position, $team_id) {
        // Get a unique name for the player
        $name = $this->getUniqueName();
        $age = rand(18, 35);
        $morale = rand(40, 80);
        
        // Generate position-specific stats
        $stats = $this->generatePositionStats($position);
        
        return [
            'name' => $name,
            'position' => $position,
            'age' => $age,
            'morale' => $morale,
            'batting_average' => $stats['batting_average'],
            'bowling_average' => $stats['bowling_average'],
            'strike_rate' => $stats['strike_rate'],
            'economy_rate' => $stats['economy_rate'],
            'fielding_rating' => $stats['fielding_rating'],
            'overall_rating' => $stats['overall_rating'],
            'team_id' => $team_id
        ];
    }
    
    /**
     * Generate stats based on cricket player position
     */
    private function generatePositionStats($position) {
        $baseStats = [
            'batting_average' => rand(15, 35),
            'bowling_average' => rand(25, 45), 
            'strike_rate' => rand(80, 160),
            'economy_rate' => rand(6, 12),
            'fielding_rating' => rand(40, 80)
        ];
        
        // Position-specific stat adjustments for cricket
        switch ($position) {
            case 'Wicket-keeper':
                // Wicket-keepers need excellent fielding and decent batting
                $baseStats['fielding_rating'] += rand(15, 25);
                $baseStats['batting_average'] += rand(5, 15);
                $baseStats['bowling_average'] = 999; // Non-bowlers
                $baseStats['economy_rate'] = 0;
                break;
                
            case 'Opening-batsman':
                // Openers need good batting stats and strike rate
                $baseStats['batting_average'] += rand(10, 25);
                $baseStats['strike_rate'] += rand(10, 30);
                $baseStats['bowling_average'] = 999; // Non-bowlers typically
                $baseStats['economy_rate'] = 0;
                break;
                
            case 'Middle-order':
                // Middle order batsmen need consistent batting
                $baseStats['batting_average'] += rand(8, 20);
                $baseStats['strike_rate'] += rand(5, 20);
                $baseStats['bowling_average'] = 999;
                $baseStats['economy_rate'] = 0;
                break;
                
            case 'Finisher':
                // Finishers need high strike rate
                $baseStats['batting_average'] += rand(3, 12);
                $baseStats['strike_rate'] += rand(20, 40);
                $baseStats['bowling_average'] = 999;
                $baseStats['economy_rate'] = 0;
                break;
                
            case 'All-rounder':
                // All-rounders need balanced batting and bowling
                $baseStats['batting_average'] += rand(5, 15);
                $baseStats['bowling_average'] -= rand(5, 15);
                $baseStats['strike_rate'] += rand(8, 18);
                $baseStats['economy_rate'] -= (rand(100, 300) / 100); // 1.00 to 3.00
                $baseStats['fielding_rating'] += rand(5, 15);
                break;
                
            case 'Fast-bowler':
                // Fast bowlers need good bowling stats
                $baseStats['bowling_average'] -= rand(8, 18);
                $baseStats['economy_rate'] -= (rand(150, 400) / 100); // 1.50 to 4.00
                $baseStats['batting_average'] = rand(8, 18); // Lower order
                $baseStats['strike_rate'] = rand(60, 100);
                break;
                
            case 'Spin-bowler':
                // Spinners need good bowling economy
                $baseStats['bowling_average'] -= rand(5, 15);
                $baseStats['economy_rate'] -= (rand(200, 500) / 100); // 2.00 to 5.00
                $baseStats['batting_average'] = rand(10, 20); // Lower order
                $baseStats['strike_rate'] = rand(70, 110);
                break;
                
            case 'Medium-pacer':
                // Medium pacers are often all-rounders
                $baseStats['batting_average'] += rand(3, 10);
                $baseStats['bowling_average'] -= rand(3, 12);
                $baseStats['economy_rate'] -= (rand(100, 250) / 100); // 1.00 to 2.50
                break;
        }
        
        // Ensure stats are within realistic cricket ranges
        $baseStats['batting_average'] = max(5, min(70, $baseStats['batting_average']));
        $baseStats['bowling_average'] = max(15, min(999, $baseStats['bowling_average']));
        $baseStats['strike_rate'] = max(50, min(250, $baseStats['strike_rate']));
        $baseStats['economy_rate'] = max(3, min(15, $baseStats['economy_rate']));
        $baseStats['fielding_rating'] = max(30, min(100, $baseStats['fielding_rating']));
        
        // Calculate overall rating based on cricket performance
        $battingScore = ($baseStats['batting_average'] / 50) * 40; // Max 40 points for batting
        $bowlingScore = $baseStats['bowling_average'] < 999 ? ((50 - min($baseStats['bowling_average'], 50)) / 50) * 30 : 0; // Max 30 for bowling
        $fieldingScore = ($baseStats['fielding_rating'] / 100) * 20; // Max 20 for fielding
        $strikeRateBonus = (($baseStats['strike_rate'] - 100) / 100) * 10; // Strike rate bonus/penalty
        
        $baseStats['overall_rating'] = round(max(30, min(100, $battingScore + $bowlingScore + $fieldingScore + $strikeRateBonus)));
        
        return $baseStats;
    }
    
    /**
     * Create a cricket player in the database
     */
    private function createPlayer($playerData) {
        $sql = 'INSERT INTO players (name, position, age, morale, batting_average, bowling_average, strike_rate, economy_rate, fielding_rating, overall_rating, team_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        return $this->db->exec($sql, [
            $playerData['name'],
            $playerData['position'],
            $playerData['age'],
            $playerData['morale'],
            $playerData['batting_average'],
            $playerData['bowling_average'],
            $playerData['strike_rate'],
            $playerData['economy_rate'],
            $playerData['fielding_rating'],
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
     * Update cricket player stats
     */
    public function updatePlayer($player_id, $data) {
        $updates = [];
        $params = [];
        
        $allowedFields = ['name', 'age', 'morale', 'batting_average', 'bowling_average', 'strike_rate', 'economy_rate', 'fielding_rating'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updates)) {
            // Recalculate overall rating if cricket stats changed
            if (isset($data['batting_average']) || isset($data['bowling_average']) || isset($data['strike_rate']) || isset($data['fielding_rating'])) {
                $player = $this->getPlayer($player_id);
                if ($player) {
                    $battingAvg = $data['batting_average'] ?? $player['batting_average'];
                    $bowlingAvg = $data['bowling_average'] ?? $player['bowling_average'];
                    $strikeRate = $data['strike_rate'] ?? $player['strike_rate'];
                    $fieldingRating = $data['fielding_rating'] ?? $player['fielding_rating'];
                    
                    // Calculate overall rating based on cricket performance
                    $battingScore = ($battingAvg / 50) * 40;
                    $bowlingScore = $bowlingAvg < 999 ? ((50 - min($bowlingAvg, 50)) / 50) * 30 : 0;
                    $fieldingScore = ($fieldingRating / 100) * 20;
                    $strikeRateBonus = (($strikeRate - 100) / 100) * 10;
                    
                    $overall = round(max(30, min(100, $battingScore + $bowlingScore + $fieldingScore + $strikeRateBonus)));
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
     * Get players available for batting order (15 squad players)
     */
    public function getSquadPlayers($team_id) {
        return $this->db->exec('SELECT * FROM players WHERE team_id = ? ORDER BY overall_rating DESC', [$team_id]);
    }
    
    /**
     * Get current batting order for a team
     */
    public function getBattingOrder($team_id) {
        $result = $this->db->exec('SELECT batting_order FROM teams WHERE id = ?', [$team_id]);
        if ($result && !empty($result[0]['batting_order'])) {
            return json_decode($result[0]['batting_order'], true);
        }
        return [];
    }
    
    /**
     * Save batting order for a team
     */
    public function saveBattingOrder($team_id, $battingOrder) {
        $sql = 'UPDATE teams SET batting_order = ? WHERE id = ?';
        return $this->db->exec($sql, [json_encode($battingOrder), $team_id]);
    }
    
    /**
     * Get current bowling order for a team
     */
    public function getBowlingOrder($team_id) {
        $result = $this->db->exec('SELECT bowling_order FROM teams WHERE id = ?', [$team_id]);
        if ($result && !empty($result[0]['bowling_order'])) {
            return json_decode($result[0]['bowling_order'], true);
        }
        return [];
    }
    
    /**
     * Save bowling order for a team
     */
    public function saveBowlingOrder($team_id, $bowlingOrder) {
        $sql = 'UPDATE teams SET bowling_order = ? WHERE id = ?';
        return $this->db->exec($sql, [json_encode($bowlingOrder), $team_id]);
    }
    
    /**
     * Get bowlers from squad (All-rounders, Fast-bowlers, Spin-bowlers)
     */
    public function getBowlers($team_id) {
        $sql = 'SELECT * FROM players WHERE team_id = ? AND position IN (?, ?, ?) ORDER BY bowling_average ASC';
        return $this->db->exec($sql, [$team_id, 'All-rounder', 'Fast-bowler', 'Spin-bowler']);
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