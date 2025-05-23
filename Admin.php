<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin</title>
  <link rel="stylesheet" href="Admin.css" />
</head>
<body>
  <!-- Container -->
  <div>
    <header style="display: flex; padding: 10px 0; background: #D0AC77; text-align: center; align-items: center;">
      <!-- Left: Hamburger -->
      <div style="flex: 1; display: flex; align-items: center; justify-content: flex-start; padding-left: 20px;">
        <span class="hamburger" role="button" aria-label="Toggle menu" tabindex="0" aria-expanded="false">☰</span>
      </div>

      <!-- Center: Navigation Buttons -->
      <div style="display: flex; justify-content: center; margin-top: 5px;">
        <nav role="navigation" aria-label="Menu Categories">
          <button id="btn-a">Hot Coffee</button>
          <button id="btn-b">Iced Coffee</button>
          <button id="btn-c">Non Coffee</button>
          <button id="btn-d">Pastries</button>
          <button class="manage-items-btn-header" id="manage-items-header-btn">Manage Items</button>
        </nav>
      </div>

      <!-- Right: Logo as Image -->
      <div style="flex: 1; display: flex; align-items: center; justify-content: flex-end; padding-right: 20px;">
        <img id="logo-img" src="logo.png" alt="BREWeb Logo" />
      </div>
    </header>

    <!-- Side Drawer -->
    <div id="side-drawer" aria-hidden="true">
      <button id="close-drawer" aria-label="Close menu">✖ Close</button>

      <!-- Drawer Content -->
      <nav class="main-nav">
          <a href="profile.php">Profile</a>
          <a href="admin-transaction.php">Transaction</a>
          <a href="cash-drawer.php">Cash Drawer</a>
      </nav>

      <nav class="bottom-nav" aria-label="User Identity">
          <a href="homepage.php">Log Out</a>
      </nav>
    </div>

    <!-- Drawer Backdrop -->
    <div id="drawer-backdrop" tabindex="-1"></div>

    <!-- Main Content -->
    <main style="display: flex; height: 100%;">
      <!-- Items Section -->
      <section style="flex: 2; padding: 20px;">
        <div id="items-grid" class="items-grid" aria-live="polite" aria-label="Menu items">
          <!-- Items rendered by JS -->
        </div>
      </section>
    
      <!-- Orders Section -->
      <section style="flex: 1; padding: 20px; border-left: 1px solid #ccc; background-color: #f9f9f9; display: flex; flex-direction: column; justify-content: space-between; height: 90vh;">
        <div>
          <h3 style="background-color: #FFD483; padding: 20px; border-radius: 5px;">Orders</h3>
          <div id="order-list"></div>
        </div>

        <div style="margin-top: auto; padding-top: 10px; border-top: 1px solid #000000;">
          <div style="text-align: right; font-weight: bold; margin-bottom: 10px;">
            Total: ₱<span id="order-total">0.00</span>
          </div>
          <div class="order-controls">
            <button onclick="clearOrders()">Clear All</button>
            <button onclick="checkout()">Checkout</button>
          </div>
        </div>
      </section>

      <!-- Payment Modal -->
      <div id="payment-modal" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title" tabindex="-1" style="display:none; position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.4); align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 5px; max-width: 400px; width: 90%;">
          <h3 id="payment-modal-title">Select Payment Method</h3>
          <div id="order-type-section" role="radiogroup" aria-label="Select Order Type">
            <label>
              <input type="radio" name="order-type" value="Dine In" checked />
              Dine In
            </label>
            <label>
              <input type="radio" name="order-type" value="Take Out" />
              Take Out
            </label>
          </div>
          <select id="payment-method" aria-label="Select Payment Method">
            <option value="cash">Cash</option>
            <option value="gcash">GCash</option>
          </select>
          <div id="cash-section">
            <p>Total Due: ₱<span id="payment-total">0.00</span></p>
            <label>Received Cash:
                <input type="number" id="cash-input" min="0" step="0.01" aria-label="Received Cash Amount"/>
            </label>
            <p>Change: ₱<span id="change-amount">0.00</span></p>
          </div>
          <div id="gcash-section" style="display:none;">
            <p>Please confirm payment via GCash on your mobile device.</p>
          </div>
          <div style="margin-top:20px; text-align:right;">
            <button onclick="closePaymentModal()">Cancel</button>
            <button onclick="confirmPayment()">Confirm</button>
          </div>
        </div>
      </div>

      <!-- Manage Items Modal -->
      <div id="manage-items-modal" role="dialog" aria-modal="true" aria-labelledby="manage-items-title" tabindex="-1" style="display:none; position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.4); align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 5px; max-width: 600px; width: 90%;">
          <h2 id="manage-items-title">Manage Menu Items</h2>
          <button id="add-item-btn" aria-label="Add new menu item">+ Add New Item</button>
          <div id="manage-items-list" role="list" aria-live="polite" aria-label="Menu items management list" style="max-height: 300px; overflow-y: auto; margin-top: 10px;">
          </div>
          <div class="manage-actions" style="margin-top: 15px; text-align: right;">
            <button id="save-items-btn">Save Changes</button>
            <button id="cancel-items-btn" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
    </main>      
  </div>

  <script>
    // Initial Items & Variables
    const defaultItems = [
      { id: 'a1', name: "Cafe Americano", price: 120, category:'a' },
      { id: 'a2', name: "Cafe Mocha", price: 140, category:'a' },
      { id: 'a3', name: "Caramel Macchiato", price: 160, category:'a' },
      { id: 'a4', name: "Cafe Latte", price: 130, category:'a' },
      { id: 'a5', name: "Flat White", price: 125, category:'a' },
      { id: 'a6', name: "Espresso", price: 110, category:'a' },
      { id: 'b1', name: "Coffee Jelly", price: 130, category:'b' },
      { id: 'b2', name: "Dark Mocha", price: 150, category:'b' },
      { id: 'b3', name: "Dirty Matcha", price: 145, category:'b' },
      { id: 'b4', name: "Iced Americano", price: 120, category:'b' },
      { id: 'b5', name: "Iced Latte", price: 130, category:'b' },
      { id: 'c1', name: "Red Velvet Latte", price: 155, category:'c' },
      { id: 'c2', name: "Salted Caramel", price: 160, category:'c' },
      { id: 'c3', name: "Sea Salt Latte", price: 150, category:'c' },
      { id: 'c4', name: "Toasted Vanilla", price: 140, category:'c' },
      { id: 'd1', name: "Pumpkin Spice Latte", price: 170, category:'d' },
      { id: 'd2', name: "Toffee Nut Crunch", price: 165, category:'d' },
      { id: 'd3', name: "Cinnamon Roll Latte", price: 150, category:'d' }
    ];
    const categories = {
      'btn-a': 'a',
      'btn-b': 'b',
      'btn-c': 'c',
      'btn-d': 'd'
    };
    let currentCategory = 'a';
    let items = [];
    const orders = {};
    let total = 0;
    let orderNumberCount = 0;

    // Transaction storage key
    const TRANSACTION_STORAGE_KEY = 'savedTransactions';

    window.onload = () => {
      loadItems();
      setupCategoryButtons();
      renderItemsGrid();
      setupSideDrawer();
      setupManageItems();
      setupPaymentModal();
      updateTotal();
    };

    function loadItems() {
      // Load menu items from localStorage or use default items
      const stored = localStorage.getItem('menuItems');
      if (stored) {
        try {
          items = JSON.parse(stored);
          if (!Array.isArray(items)) throw new Error();
        } catch(e) {
          items = [...defaultItems];
        }
      } else {
        items = [...defaultItems];
      }
    }
    function saveItems() {
      // Save current menu items to localStorage
      localStorage.setItem('menuItems', JSON.stringify(items));
    }
    function setupCategoryButtons() {
      // Attach event listeners to category buttons to switch displayed items
      Object.keys(categories).forEach(btnId => {
        document.getElementById(btnId).addEventListener('click', () => {
          currentCategory = categories[btnId];
          renderItemsGrid();
        });
      });
      // Setup Manage Items button in header
      const manageBtnHeader = document.getElementById('manage-items-header-btn');
      manageBtnHeader.addEventListener('click', () => {
        openManageItemsModal();
      });
    }
    function renderItemsGrid() {
      // Render menu items based on selected category
      const container = document.getElementById('items-grid');
      container.innerHTML = '';
      const filteredItems = items.filter(i => i.category === currentCategory);
      if (filteredItems.length === 0) {
        const noItemsMsg = document.createElement('div');
        noItemsMsg.textContent = "No items in this category.";
        noItemsMsg.style.fontStyle = 'italic';
        noItemsMsg.style.gridColumn = 'span 3';
        container.appendChild(noItemsMsg);
        return;
      }
      filteredItems.forEach(item => {
        const box = document.createElement('div');
        box.className = 'item-box';
        box.setAttribute('data-id', item.id);
        box.setAttribute('data-name', item.name);
        box.setAttribute('data-price', item.price.toFixed(2));
        box.tabIndex = 0;
        box.innerHTML = `<div><strong>${item.name}</strong></div><div>₱${item.price.toFixed(2)}</div>`;
        box.addEventListener('click', () => addItemToOrder(item.id));
        box.addEventListener('keydown', e => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            addItemToOrder(item.id);
          }
        });
        container.appendChild(box);
      });
    }
    function addItemToOrder(id) {
      // Add item to the orders, update quantities and prices accordingly
      const item = items.find(i => i.id === id);
      if (!item) return;
      if (orders[id]) {
        orders[id].quantity++;
        orders[id].totalPrice += item.price;
        updateOrderItem(id);
      } else {
        orders[id] = {
          quantity: 1,
          price: item.price,
          totalPrice: item.price
        };
        addNewOrderItem(id);
      }
      total += item.price;
      updateTotal();
    }
    function addNewOrderItem(id) {
      // Add new order entry in the order list UI
      const orderList = document.getElementById("order-list");
      const item = orders[id];

      const orderItem = document.createElement("div");
      orderItem.classList.add("order-entry");

      const itemName = document.createElement("span");
      itemName.classList.add("order-name");
      const menuItem = items.find(i => i.id === id);
      itemName.textContent = menuItem ? menuItem.name : "";

      const quantityControls = document.createElement("div");
      quantityControls.classList.add("quantity-controls");

      const minusBtn = document.createElement("button");
      minusBtn.textContent = "−";
      minusBtn.title = "Decrease quantity";
      minusBtn.onclick = () => {
        if(item.quantity > 1) {
          item.quantity--;
          item.totalPrice -= item.price;
          total -= item.price;
          updateOrderItem(id);
          updateTotal();
        } else {
          total -= item.totalPrice;
          updateTotal();
          orderItem.remove();
          delete orders[id];
        }
      };

      const quantityDisplay = document.createElement("span");
      quantityDisplay.classList.add("item-quantity");
      quantityDisplay.textContent = `x${item.quantity}`;

      const plusBtn = document.createElement("button");
      plusBtn.textContent = "+";
      plusBtn.title = "Increase quantity";
      plusBtn.onclick = () => {
        item.quantity++;
        item.totalPrice += item.price;
        total += item.price;
        updateOrderItem(id);
        updateTotal();
      };

      quantityControls.appendChild(minusBtn);
      quantityControls.appendChild(quantityDisplay);
      quantityControls.appendChild(plusBtn);

      const itemPrice = document.createElement("span");
      itemPrice.classList.add("item-price");
      itemPrice.textContent = `₱${item.totalPrice.toFixed(2)}`;

      const removeBtn = document.createElement("button");
      removeBtn.className = "remove-btn";
      removeBtn.textContent = "❌";
      removeBtn.title = "Remove item";
      removeBtn.onclick = () => {
        total -= item.totalPrice;
        updateTotal();
        orderItem.remove();
        delete orders[id];
      };

      orderItem.appendChild(itemName);
      orderItem.appendChild(quantityControls);
      orderItem.appendChild(itemPrice);
      orderItem.appendChild(removeBtn);

      orderList.appendChild(orderItem);

      orders[id].orderItem = orderItem;
    }
    function updateOrderItem(id) {
      // Update the UI quantity and price for an order item
      const item = orders[id];
      if(!item || !item.orderItem) return;
      const orderItem = item.orderItem;
      const quantityDisplay = orderItem.querySelector(".item-quantity");
      const itemPrice = orderItem.querySelector(".item-price");
      quantityDisplay.textContent = `x${item.quantity}`;
      itemPrice.textContent = `₱${item.totalPrice.toFixed(2)}`;
    }
    function updateTotal() {
      // Update total amount display
      document.getElementById("order-total").textContent = total.toFixed(2);
    }
    function clearOrders() {
      // Clear all orders and reset UI and total
      const orderList = document.getElementById("order-list");
      orderList.innerHTML = "";
      total = 0;
      for (const key in orders) {
        delete orders[key];
      }
      updateTotal();
    }
    function setupSideDrawer() {
      // Set up hamburger menu toggle and side drawer behavior
      const hamburger = document.querySelector('.hamburger');
      const sideDrawer = document.getElementById('side-drawer');
      const backdrop = document.getElementById('drawer-backdrop');
      const closeBtn = document.getElementById('close-drawer');

      hamburger.addEventListener('click', () => {
        sideDrawer.classList.add('open');
        backdrop.classList.add('visible');
        hamburger.classList.add('open');
        hamburger.setAttribute('aria-expanded', 'true');
        sideDrawer.setAttribute('aria-hidden', 'false');
      });

      const closeDrawer = () => {
        sideDrawer.classList.remove('open');
        backdrop.classList.remove('visible');
        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        sideDrawer.setAttribute('aria-hidden', 'true');
      };

      closeBtn.addEventListener('click', closeDrawer);
      backdrop.addEventListener('click', closeDrawer);
      document.addEventListener('keydown', e => {
        if(e.key === 'Escape' && sideDrawer.classList.contains('open')) {
          closeDrawer();
        }
      });
      sideDrawer.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', closeDrawer);
      });
    }
    function setupManageItems() {
      // Setup Manage Items modal with add, delete, save, cancel functionality
      const modal = document.getElementById('manage-items-modal');
      const addBtn = document.getElementById('add-item-btn');
      const listContainer = document.getElementById('manage-items-list');
      const saveBtn = document.getElementById('save-items-btn');
      const cancelBtn = document.getElementById('cancel-items-btn');

      window.openManageItemsModal = function() {
        renderManageItemsList();
        modal.style.display = 'flex';
        modal.focus();
      };
      cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
      });
      addBtn.addEventListener('click', () => {
        addManageItemRow({ id: generateId(), name: '', price: 0, category: 'a' }, true);
      });
      saveBtn.addEventListener('click', () => {
        const updatedItems = [];
        let valid = true;
        const rows = listContainer.querySelectorAll('.manage-item-row');
        rows.forEach(row => {
          const id = row.getAttribute('data-id');
          const nameInput = row.querySelector('input.name-input');
          const priceInput = row.querySelector('input.price-input');
          const categorySelect = row.querySelector('select.category-select');
          const name = nameInput.value.trim();
          const price = parseFloat(priceInput.value);
          const category = categorySelect.value;
          if (!name) {
            alert("Item name cannot be empty.");
            valid = false;
            return;
          }
          if (isNaN(price) || price < 0) {
            alert("Price must be a valid non-negative number.");
            valid = false;
            return;
          }
          updatedItems.push({ id, name, price, category });
        });
        if(!valid) return;
        items = updatedItems;
        saveItems();
        modal.style.display = 'none';
        renderItemsGrid();
        clearOrders();
      });
    }
    function renderManageItemsList() {
      // Render the list of items in Manage Items modal
      const container = document.getElementById('manage-items-list');
      container.innerHTML = '';
      items.forEach(item => {
        addManageItemRow(item, false);
      });
    }
    function addManageItemRow(item, isNew) {
      // Create and append a row for an individual manage item entry
      const container = document.getElementById('manage-items-list');
      const row = document.createElement('div');
      row.className = 'manage-item-row';
      row.setAttribute('data-id', item.id);

      const nameInput = document.createElement('input');
      nameInput.type = 'text';
      nameInput.className = 'name-input';
      nameInput.value = item.name;
      nameInput.placeholder = 'Item Name';
      nameInput.setAttribute('aria-label', 'Item Name');

      const priceInput = document.createElement('input');
      priceInput.type = 'number';
      priceInput.className = 'price-input';
      priceInput.min = 0;
      priceInput.step = 0.01;
      priceInput.value = item.price;
      priceInput.placeholder = 'Price';
      priceInput.setAttribute('aria-label', 'Price');

      const categorySelect = document.createElement('select');
      categorySelect.className = 'category-select';
      categorySelect.setAttribute('aria-label', 'Item Category');
      const optionNames = {
        'a': 'Hot Coffee',
        'b': 'Iced Coffee',
        'c': 'Non Coffee',
        'd': 'Pastries'
      };
      for (const key in optionNames) {
        let opt = document.createElement('option');
        opt.value = key;
        opt.textContent = optionNames[key];
        if (item.category === key) {
          opt.selected = true;
        }
        categorySelect.appendChild(opt);
      }

      const deleteBtn = document.createElement('button');
      deleteBtn.className = 'delete-item-btn';
      deleteBtn.title = "Delete Item";
      deleteBtn.textContent = '🗑️';
      deleteBtn.addEventListener('click', () => {
        row.remove();
      });

      row.appendChild(nameInput);
      row.appendChild(priceInput);
      row.appendChild(categorySelect);
      row.appendChild(deleteBtn);

      container.appendChild(row);

      if(isNew) {
        nameInput.focus();
      }
    }
    function generateId() {
      // Generate a unique id string
      return 'id-' + Math.random().toString(36).substr(2, 9);
    }
    function setupPaymentModal() {
      // Setup payment modal UI interactions and inputs
      const paymentModal = document.getElementById('payment-modal');
      const paymentMethodSelect = document.getElementById('payment-method');
      const cashSection = document.getElementById('cash-section');
      const gcashSection = document.getElementById('gcash-section');
      const cashInput = document.getElementById('cash-input');
      const changeAmount = document.getElementById('change-amount');

      paymentMethodSelect.addEventListener('change', () => {
        if(paymentMethodSelect.value === 'cash') {
          cashSection.style.display = "block";
          gcashSection.style.display = "none";
        } else {
          cashSection.style.display = "none";
          gcashSection.style.display = "block";
        }
      });

      cashInput.addEventListener('input', () => {
        const received = parseFloat(cashInput.value);
        const change = received - total;
        changeAmount.textContent = change >= 0 ? change.toFixed(2) : "0.00";
      });
    }
    function checkout() {
      // Open the payment modal if there are items in the order
      if (total <= 0) {
        alert("No orders to checkout.");
        return;
      }
      document.getElementById('payment-modal').style.display = 'flex';
      document.getElementById('payment-total').textContent = total.toFixed(2);
      document.getElementById('cash-input').value = '';
      document.getElementById('change-amount').textContent = '0.00';
      document.getElementById('payment-method').value = 'cash';
      document.getElementById('cash-section').style.display = 'block';
      document.getElementById('gcash-section').style.display = 'none';
    }
    function closePaymentModal() {
      // Close the payment modal
      document.getElementById('payment-modal').style.display = 'none';
    }
    function getSelectedOrderType() {
      // Get order type from radio buttons
      const radios = document.getElementsByName('order-type');
      for (const radio of radios) {
        if (radio.checked) return radio.value;
      }
      return "Dine In";
    }
    function generateOrderNumber() {
      // Generate unique order number
      orderNumberCount++;
      return 'ORD' + String(orderNumberCount).padStart(4, '0');
    }
    function confirmPayment() {
      // Confirm payment, validate cash if selected, and save transaction
      const method = document.getElementById('payment-method').value;
      const orderType = getSelectedOrderType();
      if (method === 'cash') {
          const received = parseFloat(document.getElementById('cash-input').value);
          if (isNaN(received) || received < total) {
            alert("Insufficient cash.");
            return;
          }
          saveTransactionAndConfirm(received, method, orderType);
      } else if (method === 'gcash') {
          saveTransactionAndConfirm(null, method, orderType);
      }
    }

    function saveTransactionAndConfirm(receivedCash, paymentMethod, orderType) {
      // Save the transaction data to localStorage and notify user
      const orderItems = [];
      for(const id in orders) {
        const ord = orders[id];
        const menuItem = items.find(i => i.id === id);
        if(menuItem) {
          for(let i=0; i<ord.quantity; i++) {
            orderItems.push({ name: menuItem.name, price: menuItem.price });
          }
        }
      }
      const now = new Date();
      const dateISO = now.toISOString().slice(0,10);
      const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: true});

      const transaction = {
        date: dateISO,
        time: timeString,
        totalPrice: total,
        items: orderItems,
        paymentType: paymentMethod, // fixed property name
        orderType: orderType,
        orderNumber: generateOrderNumber()
      };

      let savedTransactions = [];
      try {
        savedTransactions = JSON.parse(localStorage.getItem(TRANSACTION_STORAGE_KEY)) || [];
        if (!Array.isArray(savedTransactions)) savedTransactions = [];
      } catch(e) {
        savedTransactions = [];
      }

      savedTransactions.unshift(transaction); // newest first
      localStorage.setItem(TRANSACTION_STORAGE_KEY, JSON.stringify(savedTransactions));

      if(paymentMethod === 'cash') {
        alert(`Cash payment confirmed. Change: ₱${(receivedCash - total).toFixed(2)}\nOrder Type: ${orderType}\nYour order number is: ${transaction.orderNumber}`);
      } else {
        alert(`GCash payment recorded. Thank you!\nOrder Type: ${orderType}\nYour order number is: ${transaction.orderNumber}`);
      }
      closePaymentModal();
      clearOrders();
    }

  </script>
</body>
</html>