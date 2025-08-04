<?php
// Database configuration
$host = "localhost";
$db_name = "coffee_shop";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Payment integration (dummy PayPal link for demo purposes)
function processPayment($amount, $isMember) {
    if ($isMember) {
        $amount *= 0.95; // Apply a 5% discount for members
    }
    $paypalUrl = "https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=your-paypal-email@example.com&amount=$amount&currency_code=USD&item_name=Cart%20Items";
    echo "<div class='payment-section'>";
    echo "<a href='$paypalUrl' class='pay-button'>Pay $amount Taka Now</a>";
    echo "</div>";
}z

// Example usage (for demonstration only, replace with form inputs in real application)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $isMember = isset($_POST['membership']) && $_POST['membership'] === 'yes';
        $amount = $_POST['amount'];
        processPayment($amount, $isMember);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title> Membership</title>
    <link rel="stylesheet" type="text/css" href="member_styles.css">

</head>
<body>
    <div class="container">
        <h1>Payment Process</h1>
        <form method="post" class="cart-form">
            <input type="number" name="amount" placeholder="Enter Total Amount" required class="input-field">
            <p>Do you have a membership?</p>
            <label>
                <input type="radio" name="membership" value="yes">
                Yes
            </label>
            <label>
                <input type="radio" name="membership" value="no">
                No
            </label>
            <button type="submit" name="add" class="submit-button">Processed to Payment</button>
        </form>
    </div>
</body>
</html>
