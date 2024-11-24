<?php
// Database connection
include 'dbconnection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Function to determine category based on product name
function determineCategory($productName) {
    $productName = strtolower($productName);
    if (strpos($productName, 'notebook') !== false) {
        return 'notebooks';
    } elseif (strpos($productName, 'pen') !== false || strpos($productName, 'pencil') !== false) {
        return 'pens';
    } elseif (strpos($productName, 'folder') !== false) {
        return 'folders';
    } elseif (strpos($productName, 'art') !== false) {
        return 'art-supplies';
    } elseif (strpos($productName, 'office') !== false) {
        return 'office-supplies';
    } else {
        return 'other';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Purple Star</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Purple Shop Template">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="styles/bootstrap4/bootstrap.min.css">
<link href="plugins/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="plugins/OwlCarousel2-2.2.1/owl.carousel.css">
<link rel="stylesheet" type="text/css" href="plugins/OwlCarousel2-2.2.1/owl.theme.default.css">
<link rel="stylesheet" type="text/css" href="plugins/OwlCarousel2-2.2.1/animate.css">
<link rel="stylesheet" type="text/css" href="styles/main_styles.css">
<link rel="stylesheet" type="text/css" href="styles/responsive.css">
</head>
<body>

<div class="super_container">

    <!-- Header -->
    <?php include 'include/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main_slider" style="background-image:url(images/slider_1.jpg)">
        <div class="container fill_height">
            <div class="row align-items-center fill_height">
                <div class="col">
                    <div class="main_slider_content">
                        <h6>Autumn / Winter Collection 2024</h6>
                        <h1>Up to 40% Off Selected Items</h1>
                        <div class="red_button shop_now_button"><a href="#">shop now</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="new_arrivals">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <div class="section_title new_arrivals_title">
                        <h2>New Arrivals</h2>
                    </div>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col text-center">
                    <div class="new_arrivals_sorting">
                        <ul class="arrivals_grid_sorting clearfix button-group filters-button-group">
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center active is-checked" data-filter="*">All</li>
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center" data-filter=".notebooks">Notebooks</li>
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center" data-filter=".pens">Pens & Pencils</li>
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center" data-filter=".folders">Folders</li>
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center" data-filter=".art-supplies">Art Supplies</li>
                            <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center" data-filter=".office-supplies">Office Supplies</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="product-grid" data-isotope='{ "itemSelector": ".product-item", "layoutMode": "fitRows" }'>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <div class="product-item <?php echo determineCategory($row['name']); ?>">
                                <div class="product discount product_filter">
                                    <div class="product_image">
                                        <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center"><span>-$20</span></div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="single.php"><?php echo $row['name']; ?></a></h6>
                                        <div class="product_price">$<?php echo $row['price']; ?></div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#" onclick="addToCart('<?php echo $row['name']; ?>', <?php echo $row['price']; ?>)">add to cart</a></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'include/footer.php'; ?>

</div>

<script src="js/jquery-3.2.1.min.js"></script>
<script src="styles/bootstrap4/popper.js"></script>
<script src="styles/bootstrap4/bootstrap.min.js"></script>
<script src="plugins/Isotope/isotope.pkgd.min.js"></script>
<script src="plugins/OwlCarousel2-2.2.1/owl.carousel.js"></script>
<script src="plugins/easing/easing.js"></script>
<script src="js/custom.js"></script>
</body>
</html>

<?php $conn->close(); ?>