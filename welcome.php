<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header("Location: index.html");
    exit;
}

// Database connection settings
$servername = "localhost";
$dbusername = "root";
$password = "";
$dbname = "coffee_shop";

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Retrieve user information from the database using session username
$username = $_SESSION['username'];

$sql = "SELECT user_id, username FROM users WHERE username = :username";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user data was found
if (!$user) {
    die("User not found.");
}

// Store the user ID and username in session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];

// Display error message if admin password is incorrect
if (isset($_SESSION['admin_error'])) {
    echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['admin_error']) . "</p>";
    unset($_SESSION['admin_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">EspressoYourself</div>
        <nav class="nav">
            <a href="customer">Menu</a>
            <form method="POST" action="customer.php" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                <!-- <button type="submit" style="background:none;border:none;color:white;cursor:pointer;">Go to Customer Page</button> -->
            </form>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>This is your Home Page.</p>
        <p>Your user ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
        <p>Feel free to explore and enjoy our features.</p>
    </div>
</body>
</html>
