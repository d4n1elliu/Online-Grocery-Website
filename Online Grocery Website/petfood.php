<?php include('getProduct.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet food products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<!------Header----->
<header class="header">

    <a href="index.html" class="logo"><i class="fa-solid fa-crown"></i> Freshify</a>
    <nav class="navbar">

        <!------Pet Food Products DropDown------>
        <div class="dropDown">
            <a href="frozen.php">Frozen</a>
            <div class="dropDown-content">
                <a href="frozen.php?filter=meals">Meals</a>
                <a href="frozen.php?filter=desserts">Desserts</a>
            </div>
        </div>
        <div class="dropDown">
            <a href="fresh.php">Fresh</a>
            <div class="dropDown-content">
                <a href="fresh.php?filter=fruits">Fruits</a>
                <a href="fresh.php?filter=steak">Steak</a>
                <a href="fresh.php?filter=cheese">Cheese</a>
            </div>
        </div>
        <div class="dropDown">
            <a href="beverages.php">Beverages</a>
            <div class="dropDown-content">
                <a href="beverages.php?filter=coffee">Coffee</a>
                <a href="beverages.php?filter=tea">Tea</a>
                <a href="beverages.php?filter=chocolate">Chocolate</a>
            </div>
        </div>
        <div class="dropDown">
            <a href="homeuse.php">HomeUse</a>
            <div class="dropDown-content">
                <a href="homeuse.php?filter=cleaning">Cleaning</a>
                <a href="homeuse.php?filter=health">Health</a>
            </div>
        </div>
        <div class="dropDown">
            <a href="petfood.php">PetFood</a>
            <div class="dropDown-content">
                <a href="petfood.php?filter=cat">Cat Food</a>
                <a href="petfood.php?filter=dog">Dog Food</a>
                <a href="petfood.php?filter=bird">Bird Food</a>
                <a href="petfood.php?filter=fish">Fish Food</a>
            </div>
        </div>
    </nav>

    <!-- Search and cart icon -->
    <div class="storeIcons">
        <div class="fas fa-bars" id="menu-btn"></div>
        <div class="fas fa-search" id="search-btn"></div>
        <a href="cart.php" class="fas fa-shopping-cart" id="cart-btn"></a>
    </div>

    <form action="petfood.php" class="search-form" method="get">
        <input type="search" name="search" id="search-box" 
        placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <label for="search-box" class="fas fa-search" id="form-search-icon"></label>
        <div id="search-hints"></div>
    </form>
</header>

<!-- Displaying pet food products -->
<section class="product" id="products">
    <div class="item-list">
        <?php
        $search = strtolower(trim($_GET['search'] ?? ''));
        $filter = strtolower(trim($_GET['filter'] ?? ''));

        $sql = "SELECT * FROM products WHERE product_id BETWEEN 5000 AND 5004";
        $result = $conn->query($sql);
        $matched = [];
        
        // Looping through each product and applying the filter
        if ($result && $result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                $productName = htmlspecialchars($product['product_name']);
                $unitQty = htmlspecialchars($product['unit_quantity']);
                $price = number_format($product['unit_price'], 2);
                $stock = (int) $product['in_stock'];
                $productLower = strtolower($productName);

                // Category Filtering 
                $category = 'other';
                if (str_contains($productLower, 'cat food')) {
                    $category = 'cat';
                }
                if (str_contains($productLower, 'dry dog food')) {
                    $category = 'dog';
                }
                if (str_contains($productLower, 'bird food')) {
                    $category = 'bird';
                }
                if (str_contains($productLower, 'fish food')) {
                    $category = 'fish';
                }

                // Applying search and filter logic
                if (!empty($search) &&
                    !str_contains(strtolower($productName), $search) &&
                    !str_contains(strtolower($unitQty), $search)
                ) {
                    continue;
                }
                if (!empty($filter) && $filter !== $category) {
                    continue;
                }
                // Collect matched product for rendering
                $product['category'] = $category;
                $matched[] = $product;
            }
        }
        // Display matched products
        if (!empty($matched)) {
            foreach ($matched as $product) {
                $productName = htmlspecialchars($product['product_name']);
                $unitQty = htmlspecialchars($product['unit_quantity']);
                $price = number_format($product['unit_price'], 2);
                $stock = (int) $product['in_stock'];
                $category = $product['category'];
                
                // Getting the product image from database
                $imgName = strtolower($product['product_name'] . ' ' . $product['unit_quantity']);
                $imgName = preg_replace('/[()\/\\\\]/', '', $imgName);
                $imgPath = "Assets/$imgName.png";

                if (!file_exists($imgPath)) {
                    $imgPath = "Assets/default.png";
                }

                echo "
                <div class='item-box' data-category='{$category}' data-name='" . strtolower($productName . ' ' . $unitQty) . "'>
                    <div class='image-box'>
                        <img src='$imgPath' class='product-image' alt='$productName'>
                    </div>
                    <h3 class='product-name'>$productName</h3>
                    <p class='product-price'>$$price</p>
                    <p class='product-unit'>$unitQty</p>
                    <p class='product-stock'>$stock In Stock</p>
                    <button class='add-to-cart'
                        data-id='" . $product['product_id'] . "'
                        data-name='" . $productName . "'
                        data-price='" . $product['unit_price'] . "'
                        data-unit='" . $unitQty . "'
                        data-image='" . $imgPath . "'
                        " . ($stock == 0 ? "disabled style='background-color: grey;'" : "") . ">
                        " . ($stock == 0 ? "Out of Stock" : "Add to Cart") . "
                    </button>
                </div>
                ";
            }
        } else {
            echo '<p style="text-align:center;">No PetFood products available.</p>';
        }
        ?>
    </div>
</section>

<script src="script.js"></script>
</body>
</html>

