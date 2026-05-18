<?php
// connect.php - SQLite Database
class Database {
    private $db_file = '../database.db';
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new SQLite3($this->db_file);
            $this->connection->enableExceptions(true);
            $this->createTables();
        } catch (Exception $e) {
            die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        }
    }
    
    private function createTables() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                phone TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                balance REAL DEFAULT 0,
                cumulative_income REAL DEFAULT 0,
                invite_code TEXT UNIQUE,
                invited_by INTEGER,
                role TEXT DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                amount REAL NOT NULL,
                status TEXT DEFAULT 'pending',
                reference TEXT UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price REAL NOT NULL,
                daily_income REAL NOT NULL,
                total_income REAL NOT NULL,
                term_days INTEGER DEFAULT 150,
                is_active INTEGER DEFAULT 1
            )",
            "CREATE TABLE IF NOT EXISTS investments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT,
                amount REAL NOT NULL,
                daily_income REAL NOT NULL,
                days_remaining INTEGER DEFAULT 150,
                purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS checkins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                checkin_date TEXT NOT NULL,
                bonus REAL DEFAULT 500,
                UNIQUE(user_id, checkin_date)
            )"
        ];
        
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
        
        // Insert default products
        $result = $this->connection->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetchArray();
        if ($row['count'] == 0) {
            $products = [
                "('VIP1 Autel MaxiCharger A', 6000, 1200, 180000, 150)",
                "('VIP2 Autel MaxiCharger B', 12000, 2520, 378000, 150)",
                "('VIP3 Autel MaxiCharger C', 24000, 5280, 792000, 150)",
                "('VIP4 Autel MaxiCharger D', 48000, 11040, 1656000, 150)"
            ];
            foreach ($products as $product) {
                $this->connection->exec("INSERT INTO products (name, price, daily_income, total_income, term_days) VALUES $product");
            }
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
?>