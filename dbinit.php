<?php
$host = 'localhost';
$db = 'ecommerce';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS shoes (
        ShoeID INT AUTO_INCREMENT PRIMARY KEY,
        ShoeName VARCHAR(255) NOT NULL,
        ShoeDescription TEXT NOT NULL,
        QuantityAvailable INT NOT NULL,
        Price DECIMAL(10, 2) NOT NULL,
        ProductAddedBy VARCHAR(100) NOT NULL DEFAULT 'Heni Patel'
    )";
  //  ALTER TABLE shoes ADD COLUMN Size VARCHAR(50);
  //ALTER TABLE shoes ADD COLUMN Color VARCHAR(50);
    
    $pdo->exec($sql);
    echo "Database and table created successfully.";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
