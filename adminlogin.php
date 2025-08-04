<?php
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $pass_word = $_POST["pass_word"];

    $sql = "SELECT * FROM admin WHERE user_name='$user_name' AND pass_word='$pass_word'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION["admin_id"] = $admin_id;
        header("Location: ../admin/admin_dashboard.php"); // Redirect to the admin dashboard page
    } else {
        echo "Invalid admin_id, username, or password";
    }
}

$conn->close();
?>

