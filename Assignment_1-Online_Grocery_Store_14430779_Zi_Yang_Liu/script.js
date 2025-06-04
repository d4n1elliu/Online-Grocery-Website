// Adding products to the cart function 
function addToCart(product, button) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    const existingItem = cart.find(item => item.id === product.product_id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.product_id,
            name: product.product_name,
            price: product.unit_price,
            unit: product.unit_quantity,
            image: product.image,
            quantity: 1
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));

    // Reducing in-stock number shown on card, disable if there are 0 products left
    const stockText = button.parentElement.querySelector('.product-stock');
    if (stockText) {
        let stock = parseInt(stockText.textContent.split(' ')[0]);
        if (stock > 0) {
            stock -= 1;
            stockText.textContent = `${stock} In Stock`;
            if (stock === 0) {
                button.disabled = true;
                button.classList.add("disabled");
                button.textContent = 'Out of Stock';
            }
        }
    }
    alert("Product added to cart!");
    button.classList.add("added-to-cart");
    setTimeout(() => button.classList.remove("added-to-cart"), 1000);
}

// Rendering shopping cart with items from the locastorage 
function renderCartPage() {
    const container = document.getElementById('cart-items');
    const totalPriceDisplay = document.getElementById('total-price');
    const placeOrderBtn = document.getElementById('place-order');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = "<p class='empty-cart-message'>Your cart is empty.</p>";
        totalPriceDisplay.textContent = "Total: $0.00";
        if (placeOrderBtn) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.classList.add("disabled");
        }
        return;
    }

    container.innerHTML = "";
    let totalPrice = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        totalPrice += itemTotal;

        const itemCard = document.createElement('div');
        itemCard.className = 'item-box';
        itemCard.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="product-image" />
            <h3 class="product-name">${item.name}</h3>
            <p class="product-unit">Unit: ${item.unit}</p>
            <p class="product-price">Price: $${item.price.toFixed(2)}</p>

            <div class="qty-wrapper">
                <button class="qty-btn" data-index="${index}" data-action="decrease">-</button>
                <input type="number" min="1" class="cart-qty" value="${item.quantity}" data-index="${index}" />
                <button class="qty-btn" data-index="${index}" data-action="increase">+</button>
            </div>
            <br>
            <button class="remove-from-cart btn" data-index="${index}">Remove</button>
        `;
        container.appendChild(itemCard);
    });

    totalPriceDisplay.textContent = `Total: $${totalPrice.toFixed(2)}`;
    localStorage.setItem('cart', JSON.stringify(cart));

    // Container for handling the user entry in quantity field
    container.querySelectorAll('.cart-qty').forEach(input => {
        input.addEventListener('input', e => {
            const index = parseInt(e.target.dataset.index);
            let value = parseInt(e.target.value);
            if (isNaN(value) || value < 1) value = 1;
            cart[index].quantity = value;
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCartPage();
        });
    });

    // Container for handling +/- buttons
    container.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            const index = parseInt(e.target.dataset.index);
            const action = e.target.dataset.action;

            if (action === "increase") {
                cart[index].quantity += 1;
            } else if (action === "decrease" && cart[index].quantity > 1) {
                cart[index].quantity -= 1;
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            renderCartPage();
        });
    });

    // Remove an product from the shopping cart
    container.querySelectorAll('.remove-from-cart').forEach(btn => {
        btn.addEventListener('click', e => {
            const index = parseInt(e.target.dataset.index);
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCartPage();
        });
    });
}

// Filtering visible products based on the search keyword
function filterProductsByName(keyword) {
    const items = document.querySelectorAll('.item-box');
    keyword = keyword.toLowerCase();

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        item.style.display = name.includes(keyword) ? "flex" : "none";
    });
}

// Runs this after page refreshes
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.getElementById('search-box');
    const searchIcon = document.getElementById('search-btn');          
    const formSearchIcon = document.getElementById('form-search-icon'); 
    const loginForm = document.querySelector('.login-form');
    const navbar = document.querySelector('.navbar');
    const cartBtn = document.getElementById('cart-btn');
    const clearCartBtn = document.getElementById('clear-cart');
    const placeOrderBtn = document.getElementById('place-order');

    // Show/hide search bar
    const toggleSearchBar = () => {
        searchForm.classList.toggle('active');
        if (searchForm.classList.contains('active')) {
            searchInput.focus();
        }
    };

    // Show/hide search bar from top-right icon
    searchIcon?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleSearchBar();
    });

    // Close search field and clear input
    formSearchIcon?.addEventListener('click', (e) => {
        e.preventDefault();
        searchForm.classList.remove('active');
        searchInput.value = ''; // Only clears field, does NOT trigger filter
    });

    // Close search/login/nav on scroll
    window.addEventListener('scroll', () => {
        searchForm.classList.remove('active');
        loginForm?.classList.remove('active');
        navbar?.classList.remove('active');
    });

    // Enter to search and close search bar
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterProductsByName(searchInput.value.toLowerCase());
            searchForm.classList.remove('active');
        }
    });

    // Live filtering while typing
    searchInput?.addEventListener('input', () => {
        filterProductsByName(searchInput.value.toLowerCase());
    });

    // Goes to shopping cart page upon clicked
    cartBtn?.addEventListener("click", () => {
        window.location.href = "cart.php";
    });

    // Toggling navigation and login menus
    document.getElementById('login-btn')?.addEventListener('click', () => loginForm.classList.toggle('active'));
    document.getElementById('menu-btn')?.addEventListener('click', () => navbar.classList.toggle('active'));

    // Add-to-cart functionalities
    document.querySelectorAll(".add-to-cart").forEach(btn => {
        btn.addEventListener("click", () => {
            const product = {
                product_id: btn.dataset.id,
                product_name: btn.dataset.name,
                unit_price: parseFloat(btn.dataset.price),
                unit_quantity: btn.dataset.unit,
                image: btn.dataset.image
            };
            addToCart(product, btn);
        });
    });

    // Clearing the whole shopping cart
    clearCartBtn?.addEventListener('click', () => {
        localStorage.removeItem('cart');
        document.getElementById('total-price').textContent = 'Total: $0.00';
        renderCartPage();
    });

    // Placing order button
    placeOrderBtn?.addEventListener('click', () => {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart.length > 0) {
            window.location.href = "delivery.html";
        }
    });

    // Shopping cart confirmation 
    const placeOrderForm = document.getElementById("place-order-form");
    const cartDataInput = document.getElementById("cart-data");
    if (placeOrderForm && cartDataInput) {
        placeOrderForm.addEventListener("submit", (e) => {
            const cart = JSON.parse(localStorage.getItem("cart")) || [];
            if (cart.length === 0) {
                e.preventDefault();
                alert("Your cart is empty!");
            } else {
                cartDataInput.value = JSON.stringify(cart);
            }
        });
    }

    // Loading shopping cart items if on the same shopping cart page
    if (document.getElementById("cart-items")) {
        renderCartPage();
    }
});