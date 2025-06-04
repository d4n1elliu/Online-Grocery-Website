<?php include('getProduct.php');

// Storing items 
$cartItems = [];

// Total accumlated shopping cart value
$grand = 0;

// Counting items that are out of stock
$unavailable = [];

// Used for handling POST request from shopping cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cartData'])) {
    $cartDataJson = $_POST['cartData'];
    $cartItems = json_decode($cartDataJson, true);

    // Checking stock availablity for each item
    foreach ($cartItems as $item) {
        $id = $item['id'];
        $requestedQty = $item['quantity'];

        // Getting current in_stock from database
        $stmt = $conn->prepare("SELECT in_stock FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // If not enough stock or item does not exist, then add to unavaiable list
        if (!$product || $product['in_stock'] === null || $product['in_stock'] < $requestedQty) {
            $unavailable[] = $item['name'];
        }
    }
    // If item is unavailable, then show the alert and redirect it back to the shopping cart
    if (!empty($unavailable)) {
        $msg = "The following items are unavailable or insufficient: " . implode(", ", $unavailable) . ". 
        Please update your cart.";
        echo "<script>alert(" . json_encode($msg) . "); window.location.href = 'cart.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        html {
            font-size: 15px;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0 auto;
            padding: 1rem;
            max-width: 700px;
            font-size: 17px;
        }
        body { 
            font-family: Arial; 
            margin: 0 auto; 
            padding: 2rem; 
            max-width: 1200px; 
        }
        label { 
            display: flex; 
            margin: 1rem 0 0rem; 
        }
        input, select { 
            width: 100%; 
            padding: 0.7rem;
            margin-bottom: 1rem; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 2rem; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 1rem; 
            text-align: center; 
        }
        .btn { 
            padding: 1rem 3rem; 
            background: #4BAAC8; 
            color: white; 
            border: none; 
            cursor: pointer; 
        }
    </style>
</head>
<body>
    <h2 style="text-align:center">Checkout</h2>
    
    <!-- Display cart table-->
    <table>
        <tr><th>Product Name</th><th>Unit Price</th><th>Quantity</th><th>Total Price</th></tr>
        <?php
        $grand = 0;

        // Looping through shopping cart items and displaying them
        if (!empty($cartItems)) {
            foreach ($cartItems as $item):
                $total = $item['price'] * $item['quantity'];
                $grand += $total;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['unit']) ?>)</td>
            <td>$<?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= number_format($total, 2) ?></td>
        </tr>
        <?php
            endforeach;
        } else {
            echo '<tr><td colspan="4">Your cart is empty.</td></tr>';
        }
        ?>
        <tr>
            <td colspan="3"><strong>Grand Total</strong></td>
            <td><strong>$<?= number_format($grand, 2) ?></strong></td>
        </tr>
    </table>
    
    <!--- User checkout form---> 
    <form id="checkout-form" action="confirmation.php" method="POST" novalidate>
        <input type="hidden" name="cartData" id="cart-data">

        <label>Full Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required autocapitalize="none" autocorrect="off">

        <label>Address:</label>
        <input type="text" name="address" required>

        <label>Suburb:</label>
        <input type="text" name="suburb" required>

        <label>Mobile:</label>
        <input type="tel" name="mobile" required pattern="[0-9]{10}">

        <label>State:</label>
        <select name="state" required>
            <option value="">-- Select State --</option>
            <option value="NSW">NSW</option>
            <option value="VIC">VIC</option>
            <option value="QLD">QLD</option>
            <option value="WA">WA</option>
            <option value="SA">SA</option>
            <option value="TAS">TAS</option>
            <option value="ACT">ACT</option>
            <option value="NT">NT</option>
            <option value="Other">Other</option>
        </select>

        <label>Country:</label>
        <input type="text" name="country" value="Australia">

        <!-- Confirmation button -->
        <button type="submit" class="btn">Place Order</button>
    </form>
<script>
    // When the checkout form is submitted
    document.getElementById("checkout-form").addEventListener("submit", function (read) {
        const name = document.querySelector("input[name='name']").value.trim();
        const emailInput = document.querySelector("input[name='email']");
        emailInput.value = emailInput.value.trim().toLowerCase();
        const email = emailInput.value;
        const address = document.querySelector("input[name='address']").value.trim();
        const suburb = document.querySelector("input[name='suburb']").value.trim();
        const mobile = document.querySelector("input[name='mobile']").value.trim();
        const state = document.querySelector("select[name='state']").value;

        // Validating full name
        if (!name) {
            alert("Full Name is required. Please fill in this field.");
            read.preventDefault();
            return;
        }

        // Validating email address (popup message for invalid format)
        if (!email.match(/@.+\./)) {
            alert("Please enter a valid email address. Such as bob@example.com");
            read.preventDefault();
            return;
        }

        // Validating address
        if (!address) {
            alert("Address is required. Please fill in this field.");
            read.preventDefault();
            return;
        }

        // Validating suburb
        if (!suburb) {
            alert("Suburb is required. Please fill in this field.");
            read.preventDefault();
            return;
        }

        // Validating mobile number (10 digits only)
        if (!mobile.match(/^[0-9]{10}$/)) {
            alert("Invalid mobile number. Please enter a 10-digit number.");
            read.preventDefault();
            return;
        }

        // Validating state
        if (!state) {
            alert("Please select your state.");
            read.preventDefault();
            return;
        }
        // Attaching the shopping cart to hidden field for submission
        const cart = JSON.parse(localStorage.getItem("cart") || "[]");
        document.getElementById("cart-data").value = JSON.stringify(cart);
    });
</script>
</body>
</html>