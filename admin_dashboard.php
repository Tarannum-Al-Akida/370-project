<?php
session_start();


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "coffee_shop";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch summary data
$total_revenue_query = "SELECT SUM(total_amount) AS revenue FROM payment WHERE paid_status = 'paid'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['revenue'] ?? 0;

$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'] ?? 0;

$popular_item_query = "
    SELECT m.item_names, COUNT(c.menu_id) AS total_orders
    FROM cart c
    JOIN menu m ON c.menu_id = m.menu_id
    GROUP BY c.menu_id
    ORDER BY total_orders DESC
    LIMIT 1
";
$popular_item_result = $conn->query($popular_item_query);
$popular_item = $popular_item_result->fetch_assoc()['item_names'] ?? 'No data';

// Fetch all menu items
$menu_query = "SELECT * FROM menu";
$menu_result = $conn->query($menu_query);

// Handle POST requests for menu management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add') {
            $item_name = $_POST['item_name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $availability = $_POST['availability'];
            $image = $_POST['image'];
            $add_query = "INSERT INTO menu (item_names, category, price, items_availability, image) 
                          VALUES ('$item_name', '$category', $price, $availability, '$image')";
            $conn->query($add_query);
        } elseif ($action === 'delete') {
            $menu_id = $_POST['menu_id'];
            $delete_query = "DELETE FROM menu WHERE menu_id = $menu_id";
            $conn->query($delete_query);
        } elseif ($action === 'update') {
            $menu_id = $_POST['menu_id'];
            $item_name = $_POST['item_name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $availability = $_POST['availability'];
            $image = $_POST['image'];
            $update_query = "UPDATE menu SET 
                             item_names = '$item_name', 
                             category = '$category', 
                             price = $price, 
                             items_availability = $availability, 
                             image = '$image'
                             WHERE menu_id = $menu_id";
            $conn->query($update_query);
        }
        header("Location: admin_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <button id="darkModeToggle">Dark Mode</button>
    </header>
    <script>
            document.getElementById("darkModeToggle").addEventListener("click", function () {
            document.body.classList.toggle("dark-mode");
        });
    </script>

    <main>
        <section>
            <h2>Overview</h2>
            <p>Total Revenue: <?php echo $total_revenue; ?> Taka</p>
            <p>Total Users: <?php echo $total_users; ?></p>
            <p>Most Popular Item: <?php echo $popular_item; ?></p>
        </section>

        <section>
            <h2>Menu Management</h2>
            <form method="POST">
                <h3>Add New Item</h3>
                <input type="text" name="item_name" placeholder="Item Name" required>
                <select name="category" required>
                    <option value="coffee">Coffee</option>
                    <option value="tea">Tea</option>
                    <option value="snacks">Snacks</option>
                    <option value="desserts">Desserts</option>
                </select>
                <input type="number" step="1" name="price" placeholder="Price" required>
                <input type="number" name="availability" placeholder="Availability" required>
                <input type="text" name="image" placeholder="Image URL (optional)">
                <button type="submit" name="action" value="add">Add Item</button>
            </form>

            <h3>Existing Menu</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $menu_result->fetch_assoc()): ?>
                        <tr>
                            <form method="POST">
                                <td><input type="text" name="item_name" value="<?php echo $row['item_names']; ?>"></td>
                                <td>
                                    <select name="category">
                                        <option value="coffee" <?php echo $row['category'] === 'coffee' ? 'selected' : ''; ?>>Coffee</option>
                                        <option value="tea" <?php echo $row['category'] === 'tea' ? 'selected' : ''; ?>>Tea</option>
                                        <option value="snacks" <?php echo $row['category'] === 'snacks' ? 'selected' : ''; ?>>Snacks</option>
                                        <option value="desserts" <?php echo $row['category'] === 'desserts' ? 'selected' : ''; ?>>Desserts</option>
                                    </select>
                                </td>
                                <td><input type="number" step="1" name="price" value="<?php echo $row['price']; ?>"></td>
                                <td><input type="number" name="availability" value="<?php echo $row['items_availability']; ?>"></td>
                                <td>
                                    <input type="hidden" name="menu_id" value="<?php echo $row['menu_id']; ?>">
                                    <button type="submit" name="action" value="update">Update</button>
                                    <button type="submit" name="action" value="delete">Delete</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
