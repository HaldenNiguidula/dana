<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Transactions</title>
  <!-- Link to external CSS file for styling -->
  <link rel="stylesheet" href="admin-transaction.css" />
</head>
<body>
  <!-- Top Navigation Bar containing menu button, title, search, and logo -->
  <header class="topbar" role="banner" aria-label="Application header">
    <div class="left-header">
      <!-- Hamburger menu icon that toggles side navigation drawer -->
      <div
        class="menu-icon"
        tabindex="0"
        aria-label="Toggle menu"
        role="button"
        aria-expanded="false"
        aria-controls="side-drawer drawer-backdrop"
      >☰</div>
      <!-- Page title -->
      <div class="title">Transaction</div>
      <!-- Search input container for filtering transactions -->
      <div class="search-container" aria-label="Search transactions">
        <input
          class="search-input"
          type="search"
          placeholder="Search by order number, price, or items..."
          aria-label="Search transactions"
          id="searchInput"
        />
      </div>
      <!-- Search icon, decorative and focusable for accessibility -->
      <div class="search-icon" tabindex="0" aria-label="Search icon">🔍</div>
    </div>
    <!-- Right side header containing company logo -->
    <div class="right-header">
      <img class="logo" src="Logo.png" alt="Company Logo"/>
    </div>
  </header>

  <!-- Main container holding sidebar (transaction list) and details section -->
  <div class="container" role="main">
    <!-- Side drawer navigation menu (hidden by default) -->
    <div id="side-drawer" aria-hidden="true" tabindex="-1">
      <!-- Button to close the side drawer -->
      <button id="close-drawer" aria-label="Close menu">✖ Close</button>

      <!-- Main navigation links in the side drawer -->
      <nav class="main-nav">
          <a href="Admin.php">Home</a>
          <a href="profile.php">Profile</a>
          <a href="admin-transaction.php">Transaction</a>
          <a href="cash-drawer.php">Cash Drawer</a>
      </nav>

      <!-- Bottom navigation for user identity actions -->
      <nav class="bottom-nav" aria-label="User Identity">
          <a href="homepage.php">Log Out</a>
      </nav>
    </div>

    <!-- Backdrop overlay for side drawer, clicks closes the menu -->
    <div id="drawer-backdrop" tabindex="-1" aria-hidden="true"></div>

    <!-- Sidebar showing the list of transactions -->
    <aside class="sidebar" aria-label="Transaction list">
      <nav class="transaction-list" aria-live="polite" id="transactionList">
        <!-- Transaction entries dynamically rendered here -->
      </nav>
    </aside>

    <!-- Section showing details of selected transaction -->
    <section class="details" aria-live="polite" aria-atomic="true" aria-label="Transaction details">
      <!-- Header showing label and order number -->
      <div class="detail-header">
        <span class="left-label">Total</span>
        <span id="orderNumberLabel" style="font-weight:bold;"></span>
      </div>

      <!-- Payment type display -->
      <div class="payment-type" id="paymentTypeLabel" aria-live="polite" aria-atomic="true">Payment: -</div>

      <!-- Total price and item count summary -->
      <div class="total-info">
        <div class="total-price" id="totalPrice">P0.00</div>
        <div class="items-count" id="itemsCount">0 items</div>
      </div>

      <!-- Box listing individual items with checkboxes for refund selection -->
      <div class="items-box" id="itemsBox" tabindex="0">
        <p style="color:#999; font-style: italic;">No transaction selected</p>
      </div>

      <!-- Subtotal row, hidden by default -->
      <div class="subtotal-row" id="subtotalRow" style="display:none;">
        <span>Subtotal</span>
        <span id="subtotalPrice">P0.00</span>
      </div>

      <!-- Buttons to trigger reprint and refund actions -->
      <div class="button-row">
        <button class="btn reprint" id="reprintBtn" disabled aria-disabled="true" aria-label="Reprint receipt">Reprint<br />Receipt #000</button>
        <button class="btn refund" id="refundBtn" disabled aria-disabled="true" aria-label="Refund selected items">REFUND</button>
      </div>
    </section>
  </div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Key for storing transaction data in localStorage
    const TRANSACTION_STORAGE_KEY = 'savedTransactions';

    // Cache DOM elements for interaction
    const transactionListEl = document.getElementById('transactionList');
    const totalPriceEl = document.getElementById('totalPrice');
    const itemsBoxEl = document.getElementById('itemsBox');
    const subtotalRowEl = document.getElementById('subtotalRow');
    const subtotalPriceEl = document.getElementById('subtotalPrice');
    const reprintBtn = document.getElementById('reprintBtn');
    const refundBtn = document.getElementById('refundBtn');
    const searchInput = document.getElementById('searchInput');
    const orderNumberLabel = document.getElementById('orderNumberLabel');
    const paymentTypeLabel = document.getElementById('paymentTypeLabel');

    const menuIcon = document.querySelector('.menu-icon');
    const sideDrawer = document.getElementById('side-drawer');
    const closeDrawerBtn = document.getElementById('close-drawer');
    const drawerBackdrop = document.getElementById('drawer-backdrop');

    // Data storage for transactions and UI state tracking
    let transactions = []; // Array of all transaction objects
    let activeTransaction = null; // Currently selected transaction
    let selectedItemsForRefund = new Set(); // Indices of items selected for refund

    // Utility: Format number as Philippine Peso currency string
    function formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount);
    }

    // Load transactions from localStorage, fallback to empty array if invalid or none
    function loadTransactions() {
      try {
        const stored = localStorage.getItem(TRANSACTION_STORAGE_KEY);
        if(stored) {
          const parsed = JSON.parse(stored);
          if(Array.isArray(parsed)) {
            transactions = parsed;
            return;
          }
        }
      } catch(e) {
        console.error('Failed to load transactions from localStorage:', e);
      }
      // If no saved transactions, initialize with sample transactions including paymentType
      transactions = [
        {
          orderNumber: "ORD1001",
          totalPrice: 1500,
          paymentType: "Cash",
          items: [
            { name: "Item A", price: 500 },
            { name: "Item B", price: 1000 }
          ]
        },
        {
          orderNumber: "ORD1002",
          totalPrice: 2000,
          paymentType: "GCash",
          items: [
            { name: "Item C", price: 2000 }
          ]
        },
        {
          orderNumber: "ORD1003",
          totalPrice: 750,
          paymentType: "Cash",
          items: [
            { name: "Item D", price: 750 }
          ]
        }
      ];
      saveTransactions();
    }

    // Render transaction list filtering by search term (default empty)
    function renderTransactionList(filter = '') {
      transactionListEl.innerHTML = '';
      const filterLower = filter.toLowerCase();

      // Filter transactions by order number, total price, item names, or paymentType matching search
      const filteredTxns = transactions.filter(tx => {
        return (
          tx.orderNumber.toLowerCase().includes(filterLower)
          || tx.totalPrice.toString().includes(filterLower)
          || (tx.items && tx.items.some(item => item.name.toLowerCase().includes(filterLower)))
          || (tx.paymentType && tx.paymentType.toLowerCase().includes(filterLower))
        );
      });

      if(filteredTxns.length === 0) {
        // Show message if no transactions match filter
        const noItems = document.createElement('p');
        noItems.style.color = '#999';
        noItems.style.fontStyle = 'italic';
        noItems.style.padding = '10px 20px';
        noItems.textContent = 'No transactions found';
        transactionListEl.appendChild(noItems);
        clearDetails();
        return;
      }

      // Render each transaction entry as a selectable button-like div
      filteredTxns.forEach((tx, idx) => {
        const entryDiv = document.createElement('div');
        entryDiv.className = 'transaction-entry';
        entryDiv.setAttribute('tabindex', '0');
        entryDiv.setAttribute('role', 'button');
        entryDiv.setAttribute('aria-pressed', 'false');

        // Store original index for referencing selected transaction
        const originalIndex = transactions.indexOf(tx);
        entryDiv.dataset.txIndex = originalIndex;

        // Show order number and formatted price
        const orderNumSpan = document.createElement('span');
        orderNumSpan.textContent = tx.orderNumber;

        const priceSpan = document.createElement('span');
        priceSpan.textContent = formatCurrency(tx.totalPrice);

        // Show payment type next to price, styled smaller and italic
        const paymentSpan = document.createElement('span');
        paymentSpan.textContent = ` (${tx.paymentType || 'Unknown'})`;
        paymentSpan.style.fontStyle = 'italic';
        paymentSpan.style.color = '#666';
        paymentSpan.style.marginLeft = '6px';

        entryDiv.appendChild(orderNumSpan);
        entryDiv.appendChild(priceSpan);
        entryDiv.appendChild(paymentSpan);

        // Add click and keyboard (Enter/Space) handlers to select the transaction
        entryDiv.addEventListener('click', () => selectTransaction(originalIndex, entryDiv));
        entryDiv.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectTransaction(originalIndex, entryDiv);
          }
        });

        transactionListEl.appendChild(entryDiv);
      });

      // Clear details on new list render since no selection active
      clearDetails();
      activeTransaction = null;
    }

    // Clear transaction details UI and reset buttons and selection
    function clearDetails() {
      totalPriceEl.textContent = 'P0.00';
      itemsBoxEl.innerHTML = '<p style="color:#999; font-style: italic;">No transaction selected</p>';
      subtotalRowEl.style.display = 'none';
      subtotalPriceEl.textContent = 'P0.00';
      orderNumberLabel.textContent = '';
      paymentTypeLabel.textContent = 'Payment: -';
      reprintBtn.disabled = true;
      reprintBtn.setAttribute('aria-disabled', 'true');
      refundBtn.disabled = true;
      refundBtn.setAttribute('aria-disabled', 'true');
      // Remove active style and aria-pressed from all transaction entries
      document.querySelectorAll('.transaction-entry.active').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-pressed', 'false');
      });
      selectedItemsForRefund.clear();
    }

    // Select a transaction and display its details
    function selectTransaction(index, entryDiv) {
      if(index < 0 || index >= transactions.length) return;
      activeTransaction = transactions[index];

      // Clear any previous active selections
      document.querySelectorAll('.transaction-entry.active').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-pressed', 'false');
      });

      // Mark this entry as active
      entryDiv.classList.add('active');
      entryDiv.setAttribute('aria-pressed', 'true');

      // Display total price and items count
      totalPriceEl.textContent = formatCurrency(activeTransaction.totalPrice);

      let itemsCount = activeTransaction.items ? activeTransaction.items.length : 0;
      document.getElementById('itemsCount').textContent = `${itemsCount} item${itemsCount !== 1 ? 's' : ''}`;

      // Show order number label
      orderNumberLabel.textContent = activeTransaction.orderNumber || '';

      // Show payment type or "Unknown" if missing
      paymentTypeLabel.textContent = 'Payment: ' + (activeTransaction.paymentType || 'Unknown');

      // Clear and prepare the items box for item list and refund selection
      itemsBoxEl.innerHTML = '';
      let subtotal = 0;

      selectedItemsForRefund.clear();

      if(Array.isArray(activeTransaction.items) && activeTransaction.items.length > 0) {
        // Create a checkbox to select all items at once for refund
        const selectAllRow = document.createElement('div');
        selectAllRow.className = 'select-all-row';

        const selectAllCheckbox = document.createElement('input');
        selectAllCheckbox.type = 'checkbox';
        selectAllCheckbox.className = 'select-all-checkbox';
        selectAllCheckbox.id = 'selectAllCheckbox';
        selectAllCheckbox.setAttribute('aria-label', 'Select all items for refund');

        const selectAllLabel = document.createElement('label');
        selectAllLabel.htmlFor = 'selectAllCheckbox';
        selectAllLabel.textContent = 'Select All Items';

        selectAllRow.appendChild(selectAllCheckbox);
        selectAllRow.appendChild(selectAllLabel);

        itemsBoxEl.appendChild(selectAllRow);

        // Update refund button state and 'select all' checkbox based on item selections
        const updateSelectAllState = () => {
          const allChecked = activeTransaction.items.every((item, idx) => selectedItemsForRefund.has(idx));
          selectAllCheckbox.checked = allChecked;
          refundBtn.disabled = selectedItemsForRefund.size === 0;
          refundBtn.setAttribute('aria-disabled', refundBtn.disabled ? 'true' : 'false');
        };

        // Render each item with a checkbox to select it for refund
        activeTransaction.items.forEach((item, idx) => {
          const itemRow = document.createElement('div');
          itemRow.className = 'item-row';

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'item-checkbox';
          checkbox.setAttribute('aria-label', `Select item ${item.name} for refund`);
          checkbox.dataset.itemIndex = idx;

          // Track changes to each checkbox to manage refund selections
          checkbox.addEventListener('change', (e) => {
            const checked = e.target.checked;
            const itemIdx = parseInt(e.target.dataset.itemIndex, 10);
            if (checked) {
              selectedItemsForRefund.add(itemIdx);
            } else {
              selectedItemsForRefund.delete(itemIdx);
            }
            updateSelectAllState();
          });

          const nameSpan = document.createElement('span');
          nameSpan.textContent = item.name || '';

          const priceSpan = document.createElement('span');
          priceSpan.textContent = formatCurrency(item.price || 0);

          itemRow.appendChild(checkbox);
          itemRow.appendChild(nameSpan);
          itemRow.appendChild(priceSpan);

          itemsBoxEl.appendChild(itemRow);

          subtotal += item.price || 0;
        });

        // Handler for 'select all' checkbox changes state across all item checkboxes
        selectAllCheckbox.addEventListener('change', (e) => {
          const checked = e.target.checked;
          selectedItemsForRefund.clear();
          if(checked) {
            activeTransaction.items.forEach((item, idx) => {
              selectedItemsForRefund.add(idx);
            });
          }
          // Synchronize all item checkboxes
          const itemCheckboxes = itemsBoxEl.querySelectorAll('.item-checkbox');
          itemCheckboxes.forEach(cb => {
            cb.checked = checked;
          });
          refundBtn.disabled = selectedItemsForRefund.size === 0;
          refundBtn.setAttribute('aria-disabled', refundBtn.disabled ? 'true' : 'false');
        });

        // Initialize buttons state
        updateSelectAllState();

      } else {
        // No items case: show message inside items box
        itemsBoxEl.innerHTML = '<p style="color:#999; font-style: italic;">No items available for this transaction</p>';
      }

      // Show the subtotal row with calculated subtotal price
      subtotalRowEl.style.display = 'flex';
      subtotalPriceEl.textContent = formatCurrency(subtotal);

      // Reset refund (disabled) and enable reprint buttons when transaction selected
      refundBtn.disabled = true;
      refundBtn.setAttribute('aria-disabled', 'true');
      reprintBtn.disabled = false;
      reprintBtn.setAttribute('aria-disabled', 'false');
    }

    // Reprint button action: alert and dummy action for receipt reprint
    reprintBtn.addEventListener('click', () => {
      if(!activeTransaction) return;
      alert(`Reprinting receipt for order number ${activeTransaction.orderNumber}.`);
    });

    // Refund button action: confirm, remove selected items from transaction, and update UI & storage
    refundBtn.addEventListener('click', () => {
      if(!activeTransaction || selectedItemsForRefund.size === 0) return;
      if(!confirm(`Are you sure you want to refund ${selectedItemsForRefund.size} selected item${selectedItemsForRefund.size > 1 ? 's' : ''} from order ${activeTransaction.orderNumber}?`)) return;

      // Remove items from transaction starting from highest index to avoid array shifting issues
      const sortedIndexes = Array.from(selectedItemsForRefund).sort((a,b) => b - a);

      sortedIndexes.forEach(i => {
        if(i >= 0 && i < activeTransaction.items.length) {
          activeTransaction.items.splice(i, 1);
        }
      });

      // Recalculate updated total price from remaining items
      activeTransaction.totalPrice = activeTransaction.items.reduce((sum, item) => sum + (item.price || 0), 0);

      if(activeTransaction.items.length === 0) {
        // If no items left, remove entire transaction from list & clear details
        const idx = transactions.indexOf(activeTransaction);
        if(idx > -1) {
          transactions.splice(idx, 1);
          activeTransaction = null;
        }
        clearDetails();
      } else {
        // Refresh UI for updated transaction item list and selection
        const index = transactions.indexOf(activeTransaction);
        selectTransaction(index, document.querySelector(`.transaction-entry[data-tx-index="\${index}"]`));
      }

      // Persist updated transactions to storage and refresh list rendering
      saveTransactions();
      renderTransactionList(searchInput.value.trim());
    });

    // Save transactions array to localStorage
    function saveTransactions() {
      localStorage.setItem(TRANSACTION_STORAGE_KEY, JSON.stringify(transactions));
    }

    // Debounced search input handler for filtering transaction list
    let searchTimeout = null;
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        renderTransactionList(searchInput.value.trim());
      }, 250);
    });

    // Functions to open and close side drawer menu with aria and focus management
    function openDrawer() {
      sideDrawer.classList.add('open');
      drawerBackdrop.classList.add('visible');
      sideDrawer.setAttribute('aria-hidden', 'false');
      drawerBackdrop.setAttribute('aria-hidden', 'false');
      menuIcon.setAttribute('aria-expanded', 'true');
      closeDrawerBtn.focus();
    }
    function closeDrawer() {
      sideDrawer.classList.remove('open');
      drawerBackdrop.classList.remove('visible');
      sideDrawer.setAttribute('aria-hidden', 'true');
      drawerBackdrop.setAttribute('aria-hidden', 'true');
      menuIcon.setAttribute('aria-expanded', 'false');
      menuIcon.focus();
    }
    // Event listeners to toggle drawer open and close on click and keyboard
    menuIcon.addEventListener('click', () => {
      if(sideDrawer.classList.contains('open')) closeDrawer();
      else openDrawer();
    });
    menuIcon.addEventListener('keydown', e => {
      if(e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if(sideDrawer.classList.contains('open')) closeDrawer();
        else openDrawer();
      }
    });
    closeDrawerBtn.addEventListener('click', closeDrawer);
    drawerBackdrop.addEventListener('click', closeDrawer);

    // Initial load: fetch transactions from storage and render the initial list
    loadTransactions();
    renderTransactionList();
  });
</script>
</body>
</html>

