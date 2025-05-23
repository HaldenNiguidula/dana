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
  <!-- Top Navigation Bar containing menu button, title, and logo -->
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
          <a href="admin-transaction.php" aria-current="page">Transaction</a>
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
      <nav class="transaction-list" aria-live="polite" id="transactionList" tabindex="0">
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
            parsed.forEach(tx => {
              if (Array.isArray(tx.items)) {
                tx.items.forEach(item => {
                  if(typeof item.refunded !== 'boolean') {
                    item.refunded = false;
                  }
                });
              }
            });
            transactions = parsed;
            return;
          }
        }
      } catch(e) {
        console.error('Failed to load transactions from localStorage:', e);
      }
      // If no saved transactions, initialize with sample transactions including paymentType and refunded flag
      transactions = [
        {
          orderNumber: "ORD1001",
          totalPrice: 1500,
          paymentType: "Cash",
          items: [
            { name: "Item A", price: 500, refunded: false },
            { name: "Item B", price: 1000, refunded: false }
          ]
        },
        {
          orderNumber: "ORD1002",
          totalPrice: 2000,
          paymentType: "GCash",
          items: [
            { name: "Item C", price: 2000, refunded: false }
          ]
        },
        {
          orderNumber: "ORD1003",
          totalPrice: 750,
          paymentType: "Cash",
          items: [
            { name: "Item D", price: 750, refunded: false }
          ]
        }
      ];
      saveTransactions();
    }

    // Render transaction list without search filter (search feature removed)
    function renderTransactionList() {
      transactionListEl.innerHTML = '';

      if(transactions.length === 0) {
        const noItems = document.createElement('p');
        noItems.style.color = '#999';
        noItems.style.fontStyle = 'italic';
        noItems.style.padding = '10px 20px';
        noItems.textContent = 'No transactions available';
        transactionListEl.appendChild(noItems);
        clearDetails();
        return;
      }

      transactions.forEach((tx, idx) => {
        const entryDiv = document.createElement('div');
        entryDiv.className = 'transaction-entry';
        entryDiv.setAttribute('tabindex', '0');
        entryDiv.setAttribute('role', 'button');
        entryDiv.setAttribute('aria-pressed', 'false');

        entryDiv.dataset.txIndex = idx;

        const orderNumSpan = document.createElement('span');
        orderNumSpan.textContent = tx.orderNumber;

        const priceSpan = document.createElement('span');
        priceSpan.textContent = formatCurrency(tx.totalPrice);

        const paymentSpan = document.createElement('span');
        paymentSpan.textContent = ` (${tx.paymentType || 'Unknown'})`;
        paymentSpan.style.fontStyle = 'italic';
        paymentSpan.style.color = '#666';
        paymentSpan.style.marginLeft = '6px';

        entryDiv.appendChild(orderNumSpan);
        entryDiv.appendChild(priceSpan);
        entryDiv.appendChild(paymentSpan);

        entryDiv.addEventListener('click', () => selectTransaction(idx, entryDiv));
        entryDiv.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectTransaction(idx, entryDiv);
          }
        });

        transactionListEl.appendChild(entryDiv);
      });

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
      document.querySelectorAll('.transaction-entry.active').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-pressed', 'false');
      });
      selectedItemsForRefund.clear();
    }

    // Select a transaction and display its details, including refunded status
    function selectTransaction(index, entryDiv) {
      if(index < 0 || index >= transactions.length) return;
      activeTransaction = transactions[index];

      document.querySelectorAll('.transaction-entry.active').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-pressed', 'false');
      });

      entryDiv.classList.add('active');
      entryDiv.setAttribute('aria-pressed', 'true');

      const totalActivePrice = activeTransaction.items
        .filter(item => !item.refunded)
        .reduce((sum, item) => sum + (item.price || 0), 0);

      totalPriceEl.textContent = formatCurrency(totalActivePrice);

      let itemsCount = activeTransaction.items.length;
      document.getElementById('itemsCount').textContent = `${itemsCount} item${itemsCount !== 1 ? 's' : ''}`;

      orderNumberLabel.textContent = activeTransaction.orderNumber || '';
      paymentTypeLabel.textContent = 'Payment: ' + (activeTransaction.paymentType || 'Unknown');

      itemsBoxEl.innerHTML = '';
      selectedItemsForRefund.clear();

      if(Array.isArray(activeTransaction.items) && activeTransaction.items.length > 0) {
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

        const updateSelectAllState = () => {
          const selectableItems = activeTransaction.items
            .map((item, idx) => ({ item, idx }))
            .filter(({item}) => !item.refunded);
          const allChecked = selectableItems.length > 0 && selectableItems.every(({idx}) => selectedItemsForRefund.has(idx));
          selectAllCheckbox.checked = allChecked;
          refundBtn.disabled = selectedItemsForRefund.size === 0;
          refundBtn.setAttribute('aria-disabled', refundBtn.disabled ? 'true' : 'false');
        };

        activeTransaction.items.forEach((item, idx) => {
          const itemRow = document.createElement('div');
          itemRow.className = 'item-row';

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'item-checkbox';
          checkbox.dataset.itemIndex = idx;

          const nameSpan = document.createElement('span');
          nameSpan.textContent = item.name || '';

          const priceSpan = document.createElement('span');
          priceSpan.textContent = formatCurrency(item.price || 0);

          if(item.refunded) {
            itemRow.classList.add('refunded');
            checkbox.disabled = true;
            checkbox.setAttribute('aria-label', `Item ${item.name} has been refunded`);
            checkbox.style.display = 'none';

            const refundedLabel = document.createElement('span');
            refundedLabel.className = 'refunded-label';
            refundedLabel.textContent = 'Refunded';

            itemRow.appendChild(nameSpan);
            itemRow.appendChild(refundedLabel);
            itemRow.appendChild(priceSpan);
          } else {
            checkbox.setAttribute('aria-label', `Select item ${item.name} for refund`);
            itemRow.appendChild(checkbox);
            itemRow.appendChild(nameSpan);
            itemRow.appendChild(priceSpan);

            checkbox.addEventListener('change', (e) => {
              const checked = e.target.checked;
              const itemIdx = parseInt(e.target.dataset.itemIndex, 10);
              if (checked) selectedItemsForRefund.add(itemIdx);
              else selectedItemsForRefund.delete(itemIdx);
              updateSelectAllState();
            });
          }

          itemsBoxEl.appendChild(itemRow);
        });

        selectAllCheckbox.addEventListener('change', (e) => {
          const checked = e.target.checked;
          selectedItemsForRefund.clear();
          if(checked) {
            activeTransaction.items.forEach((item, idx) => {
              if(!item.refunded) selectedItemsForRefund.add(idx);
            });
          }
          const itemCheckboxes = itemsBoxEl.querySelectorAll('.item-checkbox');
          itemCheckboxes.forEach(cb => {
            if(!cb.disabled) cb.checked = checked;
          });
          refundBtn.disabled = selectedItemsForRefund.size === 0;
          refundBtn.setAttribute('aria-disabled', refundBtn.disabled ? 'true' : 'false');
        });

        updateSelectAllState();

      } else {
        itemsBoxEl.innerHTML = '<p style="color:#999; font-style: italic;">No items available for this transaction</p>';
      }

      const subtotal = activeTransaction.items
        .filter(item => !item.refunded)
        .reduce((sum, item) => sum + (item.price || 0), 0);
      subtotalRowEl.style.display = 'flex';
      subtotalPriceEl.textContent = formatCurrency(subtotal);

      refundBtn.disabled = true;
      refundBtn.setAttribute('aria-disabled', 'true');
      reprintBtn.disabled = false;
      reprintBtn.setAttribute('aria-disabled', 'false');
    }

    reprintBtn.addEventListener('click', () => {
      if(!activeTransaction) return;
      alert(`Reprinting receipt for order number ${activeTransaction.orderNumber}.`);
    });

    refundBtn.addEventListener('click', () => {
      if(!activeTransaction || selectedItemsForRefund.size === 0) return;
      if(!confirm(`Are you sure you want to refund ${selectedItemsForRefund.size} selected item${selectedItemsForRefund.size > 1 ? 's' : ''} from order ${activeTransaction.orderNumber}?`)) return;

      selectedItemsForRefund.forEach(i => {
        if(i >= 0 && i < activeTransaction.items.length) {
          activeTransaction.items[i].refunded = true;
        }
      });

      activeTransaction.totalPrice = activeTransaction.items
        .filter(item => !item.refunded)
        .reduce((sum, item) => sum + (item.price || 0), 0);

      const allRefunded = activeTransaction.items.every(item => item.refunded);
      if(allRefunded) {
        const idx = transactions.indexOf(activeTransaction);
        if(idx > -1) {
          transactions.splice(idx, 1);
          activeTransaction = null;
        }
        clearDetails();
      } else {
        const index = transactions.indexOf(activeTransaction);
        renderTransactionList();
        const entryDiv = document.querySelector(`.transaction-entry[data-tx-index="${index}"]`);
        if(entryDiv) selectTransaction(index, entryDiv);
      }

      saveTransactions();
    });

    // Save transactions array to localStorage
    function saveTransactions() {
      localStorage.setItem(TRANSACTION_STORAGE_KEY, JSON.stringify(transactions));
    }

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

