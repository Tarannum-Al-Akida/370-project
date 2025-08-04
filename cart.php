<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "coffee_shop";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mock user data
$user_id = 1;

// Ensure customer exists
$customer_query = "SELECT customer_id FROM customer WHERE user_id = $user_id";
$customer_result = $conn->query($customer_query);
$customer_id = $customer_result->fetch_assoc()['customer_id'];

// Fetch cart items for the user
$cart_query = "SELECT c.menu_id, c.total_cost, m.item_names FROM cart c JOIN menu m ON c.menu_id = m.menu_id WHERE c.customer_id = $customer_id";
$cart_result = $conn->query($cart_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Process the order from the cart
    while ($cart_item = $cart_result->fetch_assoc()) {
        $menu_id = $cart_item['menu_id'];
        $total_cost = $cart_item['total_cost'];

        // Insert into the orders table
        $order_query = "
            INSERT INTO orders (menu_id, customer_id, total_cost)
            VALUES ($menu_id, $customer_id, $total_cost)
        ";
        $conn->query($order_query);
    }

    // Clear the cart after the order is placed
    $delete_cart_query = "DELETE FROM cart WHERE customer_id = $customer_id";
    $conn->query($delete_cart_query);

    echo "Order placed successfully!";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Your Cart</h1>
    </header>
    
    <main>
        <?php if ($cart_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Total Cost</th>
                </tr>
                <?php while ($cart_item = $cart_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $cart_item['item_names']; ?></td>
                        <td><?php echo $cart_item['total_cost']; ?> Taka</td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <form method="POST">
                <button type="submit" name="place_order">Place Order</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </main>
</body>
</html>
