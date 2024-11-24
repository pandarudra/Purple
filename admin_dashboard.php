<?php
// Database connection
include 'dbconnection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Check for form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_FILES['product_image'];

    // Handle image upload
    $imageName = time() . '_' . basename($productImage['name']);
    $targetDir = 'uploads/';
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($productImage['tmp_name'], $targetFile)) {
        // Insert new product into the database
        $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $productName, $productPrice, $imageName);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Failed to upload image.";
    }
}

// Check for form submission for deleting a product
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];

    // Fetch the image filename
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($imageName);
    $stmt->fetch();
    $stmt->close();

    // Delete the image file
    $targetFile = 'uploads/' . $imageName;
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }

    // Delete the product from the database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
}

// Check for form submission for editing a product
if (isset($_POST['edit_product'])) {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];

    // Update product details in the database
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
    $stmt->bind_param("ssi", $productName, $productPrice, $productId);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            transition: margin-left 0.3s ease; /* Smooth transition for main content */
        }

        .topbar {
            position: fixed;
            top: 0;
            width: 100%;
            height: 60px;
            background-color: #333;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }

        .topbar h1 {
            font-size: 20px;
            margin-left: 10px;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            width: 50px; /* Width of the button */
            height: 40px; /* Height of the button */
        }

        .bar {
            display: block;
            width: 100%;
            height: 4px;
            background-color: white;
            border-radius: 2px;
            transition: 0.3s; /* Smooth transition effect for the bars */
        }

        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #2196f3cc;
            padding-top: 80px; /* To align below topbar */
            position: fixed;
            left: -200px; /* Initially hidden */
            transition: left 0.3s ease;
        }

        .sidebar.open {
            left: 0; /* Sidebar is visible */
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar a:hover {
            background-color: #45a049;
        }

        .main {
            margin-left: 0; /* Adjust for sidebar visibility */
            padding: 80px 20px 20px 20px;
            width: 100%;
            transition: margin-left 0.3s ease; /* Smooth transition for main content */
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-start;
        }

        .product-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 200px;
        }

        .product-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .product-item h6 {
            margin: 10px 0 5px;
            font-size: 18px;
        }

        .product-item p {
            color: #555;
        }

        form label {
            margin-top: 10px;
        }

        form input,
        form button {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <h1>Admin Dashboard</h1>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="#">Dashboard</a>
        <a href="#">Manage Products</a>
        <a href="#">Orders</a>
        <a href="#">Settings</a>
        <a href="index.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h2>Add New Product</h2>
        <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" id="product_name" required>

            <label for="product_price">Product Price:</label>
            <input type="number" step="0.01" name="product_price" id="product_price" required>

            <label for="product_image">Product Image:</label>
            <input type="file" name="product_image" id="product_image" required>

            <button type="submit">Add Product</button>
        </form>

        <h2>Product List</h2>
        <div class="product-grid">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="product-item">
                    <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <h6><?php echo $row['name']; ?></h6>
                    <p>$<?php echo $row['price']; ?></p>
                    <form action="admin_dashboard.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_product">Delete</button>
                        <button type="button" onclick="openModal(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>', <?php echo $row['price']; ?>)">Edit</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Product</h2>
            <form action="admin_dashboard.php" method="post">
                <input type="hidden" name="product_id" id="edit_product_id">
                <label for="edit_product_name">Product Name:</label>
                <input type="text" name="product_name" id="edit_product_name" required>

                <label for="edit_product_price">Product Price:</label>
                <input type="number" step="0.01" name="product_price" id="edit_product_price" required>

                <button type="submit" name="edit_product">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- JavaScript to Toggle Sidebar and Handle Modal -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            // Adjust the main content margin when sidebar is toggled
            var main = document.querySelector(".main");
            if (sidebar.classList.contains("open")) {
                main.style.marginLeft = "200px"; // Move content to the right when sidebar is open
            } else {
                main.style.marginLeft = "0"; // Move content back to the left when sidebar is closed
            }
        }

        function openModal(id, name, price) {
            document.getElementById("edit_product_id").value = id;
            document.getElementById("edit_product_name").value = name;
            document.getElementById("edit_product_price").value = price;
            document.getElementById("editProductModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("editProductModal").style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById("editProductModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>

<?php $conn->close(); ?>