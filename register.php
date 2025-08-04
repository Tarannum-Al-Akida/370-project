<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to insert new user
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    try {
        $stmt->execute();
        echo "Registration successful!";
        header("Location: index.php");
        
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { 
            echo "Username already exists. Please choose a different username.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
