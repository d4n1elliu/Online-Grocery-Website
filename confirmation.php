<?php include('getProduct.php');

// Only allowing access if the form was submitted through POST with shopping cart data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cartData'])) {
    header("Location: checkout.php");
    exit();
}

// Decoding shopping cart data
$cartItems = json_decode($_POST['cartData'], true);

// If decode fails or input fails, then stop everything 
if (!is_array($cartItems)) {
    die("Invalid cart data.");
}

// Rechecking stock availability
$insufficient = [];
foreach ($cartItems as $item) {
    $stmt = $conn->prepare("SELECT in_stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // If product is not found or is too low, then save it into insufficient variable
    if (!$row || $item['quantity'] > $row['in_stock']) {
        $insufficient[] = $item['name'];
    }
}

// Notify user if any product is out of stock and send them back to cart
if (!empty($insufficient)) {
    $msg = "Some items are out of stock: " . implode(", ", $insufficient) . ". Please update your cart.";
    echo "<script>alert(" . json_encode($msg) . "); window.location.href = 'cart.php';</script>";
    exit();
}

// Updating stock if everything goes well 
foreach ($cartItems as $item) {
    $stmt = $conn->prepare("UPDATE products SET in_stock = in_stock - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $item['quantity'], $item['id']);
    $stmt->execute();
}

// Get user info safely
$name = htmlspecialchars($_POST['name']);
$email = strtolower(htmlspecialchars($_POST['email']));
$address = htmlspecialchars($_POST['address']);
$suburb = htmlspecialchars($_POST['suburb']);
$state = htmlspecialchars($_POST['state']);
$country = htmlspecialchars($_POST['country']);
$mobile = htmlspecialchars($_POST['mobile']);

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { 
            font-family: Arial; 
            font-size: 20px;
            margin: 0 auto; 
            padding: 2rem; 
            max-width: 1000px; 
        }
        .order-details { 
            margin-top: 2rem; 
            border: 1px solid #ddd; 
            padding: 1.5rem; 
            border-radius: 5px; 
            font-size: 15px;
        }
        .order-details h2 {
            margin-bottom: 1rem; 
        }
        .order-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 0.5rem 0; 
        }
        .total { 
            font-weight: bold; 
            border-top: 1px solid #ccc; 
            padding-top: 1rem; 
            margin-top: 1rem; 
        }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Order Confirmed</h1>
    <p style="text-align:center;">Thank you for your purchase!!!</p>
    <p style="text-align:center; color: red;">A confirmation email has been sent to <?php echo $email; ?>.</p>
    
    <!--- Order details Display--->
    <div class="order-details">
        <h2>Shipping Information</h2>
        <p><?= $name ?></p>
        <p><?= $address ?></p>
        <p><?= $suburb ?>, <?= $state ?>, <?= $country ?></p>
        <p>Phone: <?= $mobile ?></p>
        <p>Email: <?= $email ?></p>
        <br>
        <br>
        <h2>Order Summary</h2>
        <?php foreach ($cartItems as $item): ?>
            <div class="order-item">
                <span><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['unit']) ?>) x<?= $item['quantity'] ?></span>
                <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="order-item total">
            <span>Total:</span>
            <span>$<?= number_format($total, 2) ?></span>
        </div>
    </div>
    <div style="text-align:center; margin-top: 2rem;">
        <a href="index.html" style="padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;">Return to Home</a>
    </div>
    
<script> localStorage.removeItem("cart"); </script>
</body>
</html>