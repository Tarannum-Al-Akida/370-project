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
$username = "Nida";

// Fetch all menu items
$menu_query = "SELECT * FROM menu";
$menu_result = $conn->query($menu_query);

// Fetch recommendation
$order_check_query = "SELECT COUNT(*) AS count FROM cart WHERE customer_id = $user_id";
$order_check_result = $conn->query($order_check_query);
$order_count = $order_check_result->fetch_assoc()['count'];

if ($order_count > 0) {
    // Recommend the most ordered item by the user from the orders table
    $recommendation_query = "
        SELECT m.item_names, COUNT(o.menu_id) AS total_orders
        FROM orders o
        JOIN menu m ON o.menu_id = m.menu_id
        WHERE o.customer_id = $user_id
        GROUP BY o.menu_id
        ORDER BY total_orders DESC
        LIMIT 1
    ";
} else {
    // Recommend the most ordered item by all users from the orders table
    $recommendation_query = "
        SELECT m.item_names, COUNT(o.menu_id) AS total_orders
        FROM orders o
        JOIN menu m ON o.menu_id = m.menu_id
        GROUP BY o.menu_id
        ORDER BY total_orders DESC
        LIMIT 1
    ";
}

$recommendation_result = $conn->query($recommendation_query);
$recommendation = $recommendation_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    $menu_id = (int)$_POST['menu_id'];
    $quantity = (int)$_POST['quantity'];
    $total_cost = 0;

    // Check if menu item exists and its availability
    $menu_check_query = "SELECT price, items_availability FROM menu WHERE menu_id = $menu_id";
    $menu_check_result = $conn->query($menu_check_query);

    if ($menu_check_result->num_rows > 0) {
        $menu_item = $menu_check_result->fetch_assoc();
        if ($menu_item['items_availability'] >= $quantity) {
            $total_cost = $menu_item['price'] * $quantity;

            // Ensure customer exists
            $customer_query = "SELECT customer_id FROM customer WHERE user_id = $user_id";
            $customer_result = $conn->query($customer_query);

            if ($customer_result->num_rows > 0) {
                $customer_id = $customer_result->fetch_assoc()['customer_id'];
            } else {
                // Create a new customer record if it doesn't exist
                $insert_customer_query = "
                    INSERT INTO customer (user_id, phone_number, address)
                    VALUES ($user_id, '000-000-0000', 'Default Address')
                ";
                if ($conn->query($insert_customer_query) === TRUE) {
                    $customer_id = $conn->insert_id;
                } else {
                    die("Error: Unable to create customer record.");
                }
            }

            // Insert into the cart table
            $add_to_cart_query = "
                INSERT INTO cart (menu_id, customer_id, total_cost)
                VALUES ($menu_id, $customer_id, $total_cost)
            ";
            if ($conn->query($add_to_cart_query) === TRUE) {
                // Reduce item availability in the menu table
                $update_availability_query = "
                    UPDATE menu SET items_availability = items_availability - $quantity
                    WHERE menu_id = $menu_id
                ";
                $conn->query($update_availability_query);
                echo "Item added to cart successfully!";
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "We are out of stock for this item.";
        }
    } else {
        echo "Invalid menu item.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['category'])) {
    // Filter menu items by category
    $category = $_GET['category'];
    if ($category === 'All') {
        $menu_query = "SELECT * FROM menu";
    } else {
        $menu_query = "SELECT * FROM menu WHERE category = '$category'";
    }
    $menu_result = $conn->query($menu_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Coffee Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div>
            <p>Welcome, <?php echo $username; ?> (ID: <?php echo $user_id; ?>)</p>
        </div>
        <div>
            <form method="GET" style="display: inline;">
                <button name="category" value="coffee">Coffee</button>
            </form>
            <form method="GET" style="display: inline;">
                <button name="category" value="tea">Tea</button>
            </form>
            <form method="GET" style="display: inline;">
                <button name="category" value="snacks">Snacks</button>
            </form>
            <form method="GET" style="display: inline;">
                <button name="category" value="desserts">Desserts</button>
            </form>
            <form method="GET" style="display: inline;">
                <button name="category" value="All">All Items</button>
            </form>
        </div>
        <div>
            <a href="cart.php" class="cart-button">Cart</a>
        </div>
    </header>

    <main>
        <section>
            <h2>Recommended for You</h2>
            <div>
                <?php
                if ($recommendation) {
                    echo "We recommend trying our <strong>{$recommendation['item_names']}</strong>!";
                } else {
                    echo "No recommendations available.";
                }
                ?>
            </div>
        </section>

        <section>
            <h2>Our Menu</h2>
            <div class="menu-container">
                <?php if ($menu_result->num_rows > 0): ?>
                    <?php while ($row = $menu_result->fetch_assoc()): ?>
                        <div class="menu-item">
                            <img src="<?php echo isset($row['image']) ? $row['image'] : 'placeholder.jpg'; ?>" 
                                alt="<?php echo isset($row['item_names']) ? $row['item_names'] : 'No name'; ?>">
                            <h3><?php echo isset($row['item_names']) ? $row['item_names'] : 'Unknown Item'; ?></h3>
                            <p><?php echo isset($row['description']) ? $row['description'] : 'No description available'; ?></p>
                            <p>Price: <?php echo isset($row['price']) ? $row['price'] : 'N/A'; ?> Taka</p>
                            <p>Available: <?php echo isset($row['items_availability']) ? $row['items_availability'] : 'N/A'; ?></p>
                            <form method="POST" action="">
                                <input type="hidden" name="menu_id" value="<?php echo $row['menu_id']; ?>">
                                <input type="hidden" name="quantity" value="1"> <!-- Default quantity -->
                                <button type="submit">Add to Cart</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No items found in this category.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
