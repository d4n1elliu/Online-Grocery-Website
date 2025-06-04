<?php include('getProduct.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<!---Header--->
<header class="header">
    <a href="index.html" class="logo"><i class="fa-solid fa-crown"></i> Freshify</a>
    <!--Search and cart icons-->
    <div class="storeIcons">
        <div class="fas fa-bars" id="menu-btn"></div>
        <div class="fas fa-search" id="search-btn"></div>
        <a href="cart.php" class="fas fa-shopping-cart" id="cart-btn"></a>
    </div>
    <!---Search bar--->
    <form action="petfood.php" class="search-form" method="get">
        <input type="search" name="search" id="search-box" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <label for="search-box" class="fas fa-search" id="form-search-icon"></label>
        <div id="search-hints"></div>
    </form>
</header>
<!--Shopping cart section-->
<section class="product" id="cart-section">

    <!---Page title--->
    <h2 style="text-align: center; margin-top: 2rem;">Your Shopping Cart</h2>

    <!---Cart list--->
    <div id="cart-items" class="item-list" style="padding: 2rem;"></div>

    <!---Cart summary-->
    <div id="cart-summary" style="text-align: center; margin-top: 2rem;">

        <!--Total price display-->
        <p id="total-price" style="font-size: 2rem; font-weight: 700; color: blue;">Total: $0.00</p>  
        
        <!--Clear button-->  
        <button id="clear-cart" class="btn" style="margin: 1rem;">Clear Cart</button>

        <!-- Checkout form-->
        <form id="place-order-form" action="checkout.php" method="POST">
            <input type="hidden" name="cartData" id="cart-data">
            <button type="submit" class="btn">Checkout</button>
        </form>
    </div>
</section>

<script src="script.js"></script>
</body>
</html>