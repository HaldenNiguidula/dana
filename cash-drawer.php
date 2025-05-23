<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cash Drawer</title>
  <!-- Link to external CSS file for all styles -->
  <link rel="stylesheet" href="cash-drawer.css" />
</head>
<body>
  <!-- Page header with hamburger menu and site title on left, logo on right -->
  <header>
    <div class="header-left">
      <!-- Hamburger icon for side menu toggle, accessible button with aria attributes -->
      <span class="hamburger" role="button" aria-label="Toggle menu" tabindex="0" aria-expanded="false">☰</span>
      <span class="menu-title">Cash Drawer</span>
    </div>
    <div class="header-right">
      <!-- Logo image on right -->
      <img id="logo-img" src="logo.png" alt="BREWeb Logo" />
    </div>
  </header>

  <!-- Main container holding sales info and cash entries -->
  <div class="container">
    <!-- Top row cards: Cash Sales and GCash Sales -->
    <div class="top-row">
      <div class="card">
        <h3>Cash Sales</h3>
        <div class="amount" id="cashAmount">₱0.00</div>
      </div>
      <div class="card">
        <h3>GCash Sales</h3>
        <div class="amount" id="gcashAmount">₱0.00</div>
      </div>
    </div>

    <!-- Bottom row large boxes: Cash Added and Cash Expenses -->
    <div class="bottom-row">
      <div class="large-box" id="cash-added-box">
        <h3>
          Cash Added
          <!-- Plus button to add new cash added entry -->
          <button class="plus-sign" aria-label="Add Cash Added Entry" id="add-cash-added-btn" title="Add Entry">+</button>
        </h3>
        <!-- Container for dynamically rendered cash added entries -->
        <div
          class="entries-list"
          id="cash-added-entries"
          aria-live="polite"
          aria-label="Cash added entries list"
        >
          <!-- Entries inserted here dynamically -->
        </div>
      </div>
      <div class="large-box" id="cash-expense-box">
        <h3>
          Cash Expenses
          <!-- Plus button to add new cash expense entry -->
          <button class="plus-sign" aria-label="Add Cash Expense Entry" id="add-cash-expense-btn" title="Add Entry">+</button>
        </h3>
        <!-- Container for dynamically rendered cash expense entries -->
        <div
          class="entries-list"
          id="cash-expense-entries"
          aria-live="polite"
          aria-label="Cash expense entries list"
        >
          <!-- Entries inserted here dynamically -->
        </div>
      </div>
    </div>
  </div>

  <!-- Side drawer menu for navigation, hidden by default -->
  <div id="side-drawer" aria-hidden="true">
    <button id="close-drawer" aria-label="Close menu">✖ Close</button>
    <nav class="main-nav">
      <a href="Admin.php">Home</a>
      <a href="profile.php">Profile</a>
      <a href="admin-transaction.php">Transaction</a>
      <a href="cash-drawer.php">Cash Drawer</a>
    </nav>
    <nav class="bottom-nav" aria-label="User Identity">
      <a href="homepage.php">Log Out</a>
    </nav>
  </div>

  <!-- Backdrop overlay behind the side drawer -->
  <div id="drawer-backdrop" tabindex="-1"></div>

  <!-- Modal dialog for adding new entries -->
  <div
    id="entry-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitle"
    aria-describedby="modalDesc"
  >
    <div id="modal-content">
      <h2 id="modalTitle">Add Entry</h2>
      <form id="entry-form">
        <label for="entry-amount">Amount (₱):</label>
        <input type="number" id="entry-amount" min="0.01" step="0.01" required />
        <label for="entry-details">Details:</label>
        <textarea
          id="entry-details"
          maxlength="200"
          placeholder="Enter details here"
        ></textarea>
        <div id="modal-buttons">
          <button type="submit">Add</button>
          <button type="button" id="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    /**
     * Sets up the side drawer toggle functionality with open/close events,
     * keyboard accessibility (Escape key), clicking on backdrop and drawer links to close.
     */
    function setupSideDrawer() {
      const hamburger = document.querySelector('.hamburger');
      const sideDrawer = document.getElementById('side-drawer');
      const backdrop = document.getElementById('drawer-backdrop');
      const closeBtn = document.getElementById('close-drawer');

      // Open drawer on hamburger click
      hamburger.addEventListener('click', () => {
        sideDrawer.classList.add('open');
        backdrop.classList.add('visible');
        hamburger.classList.add('open');
        hamburger.setAttribute('aria-expanded', 'true');
        sideDrawer.setAttribute('aria-hidden', 'false');
      });

      // Common function to close the side drawer
      const closeDrawer = () => {
        sideDrawer.classList.remove('open');
        backdrop.classList.remove('visible');
        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        sideDrawer.setAttribute('aria-hidden', 'true');
      };

      closeBtn.addEventListener('click', closeDrawer);
      backdrop.addEventListener('click', closeDrawer);

      // Close drawer on Escape key press if drawer open
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sideDrawer.classList.contains('open')) {
          closeDrawer();
        }
      });

      // Close drawer when any navigation link clicked
      sideDrawer.querySelectorAll('nav a').forEach((link) => {
        link.addEventListener('click', closeDrawer);
      });
    }

    /**
     * Updates displayed Cash Sales and GCash Sales amounts from localStorage transactions.
     * Calculates sums based on payment type and updates text content with currency formatting.
     */
    function updateSalesAmounts() {
      const storedTransactions = localStorage.getItem('savedTransactions');
      let transactions = [];
      try {
        transactions = storedTransactions ? JSON.parse(storedTransactions) : [];
        if (!Array.isArray(transactions)) transactions = [];
      } catch {
        transactions = [];
      }

      let cashTotal = 0;
      let gcashTotal = 0;

      transactions.forEach((tx) => {
        if (tx.paymentType && tx.items && Array.isArray(tx.items)) {
          const txTotal = tx.items.reduce((sum, item) => sum + (item.price || 0), 0);
          if (tx.paymentType.toLowerCase() === 'gcash') {
            gcashTotal += txTotal;
          } else if (tx.paymentType.toLowerCase() === 'cash') {
            cashTotal += txTotal;
          }
        }
      });

      const gcashEl = document.getElementById('gcashAmount');
      const cashEl = document.getElementById('cashAmount');
      const formatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      });

      gcashEl.textContent = formatter.format(gcashTotal);
      cashEl.textContent = formatter.format(cashTotal);
    }

    // LocalStorage keys for entries
    const KEY_CASH_ADDED = 'cashAddedEntries';
    const KEY_CASH_EXPENSE = 'cashExpenseEntries';

    // DOM references for entry containers and controls
    const cashAddedEntriesEl = document.getElementById('cash-added-entries');
    const cashExpenseEntriesEl = document.getElementById('cash-expense-entries');
    const addCashAddedBtn = document.getElementById('add-cash-added-btn');
    const addCashExpenseBtn = document.getElementById('add-cash-expense-btn');
    const entryModal = document.getElementById('entry-modal');
    const entryForm = document.getElementById('entry-form');
    const entryAmountInput = document.getElementById('entry-amount');
    const entryDetailsInput = document.getElementById('entry-details');
    const cancelBtn = document.getElementById('cancel-btn');

    // Track current entry type being added: 'added' or 'expense'
    let currentEntryType = null;

    /**
     * Load entries array from localStorage JSON string for a given key.
     * Return empty array on error or missing data.
     */
    function loadEntries(key) {
      try {
        const json = localStorage.getItem(key);
        if (!json) return [];
        const arr = JSON.parse(json);
        if (Array.isArray(arr)) return arr;
        return [];
      } catch {
        return [];
      }
    }

    /**
     * Save entries array to localStorage as JSON string under given key.
     */
    function saveEntries(key, entries) {
      localStorage.setItem(key, JSON.stringify(entries));
    }

    /**
     * Render entries in both Cash Added and Cash Expense sections.
     * Displays "No entries." message if none present.
     */
    function renderEntries() {
      const cashAddedEntries = loadEntries(KEY_CASH_ADDED);
      const cashExpenseEntries = loadEntries(KEY_CASH_EXPENSE);

      // Clear and render Cash Added entries container
      cashAddedEntriesEl.innerHTML = '';
      if (cashAddedEntries.length === 0) {
        cashAddedEntriesEl.innerHTML = '<em>No entries.</em>';
      } else {
        cashAddedEntries.forEach((entry, idx) => {
          const entryEl = createEntryElement(entry, 'added', idx);
          cashAddedEntriesEl.appendChild(entryEl);
        });
      }

      // Clear and render Cash Expense entries container
      cashExpenseEntriesEl.innerHTML = '';
      if (cashExpenseEntries.length === 0) {
        cashExpenseEntriesEl.innerHTML = '<em>No entries.</em>';
      } else {
        cashExpenseEntries.forEach((entry, idx) => {
          const entryEl = createEntryElement(entry, 'expense', idx);
          cashExpenseEntriesEl.appendChild(entryEl);
        });
      }
    }

    /**
     * Create a DOM element representing a single entry with details, amount, and remove button.
     * @param {object} entry - Entry object with amount and details
     * @param {string} type - 'added' or 'expense' to distinguish storage key
     * @param {number} index - Index of the entry for removal
     */
    function createEntryElement(entry, type, index) {
      const div = document.createElement('div');
      div.className = 'entry-item';

      const detailsSpan = document.createElement('span');
      detailsSpan.className = 'entry-details';
      detailsSpan.title = entry.details || '';
      detailsSpan.textContent = entry.details || '(No details)';

      const amountSpan = document.createElement('span');
      amountSpan.className = 'entry-amount';
      const formatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      });
      amountSpan.textContent = formatter.format(parseFloat(entry.amount) || 0);

      const removeBtn = document.createElement('button');
      removeBtn.className = 'remove-entry-btn';
      removeBtn.setAttribute('aria-label', 'Remove entry');
      removeBtn.textContent = '×';
      removeBtn.title = 'Remove entry';

      // Remove handler removes entry from storage and rerenders
      removeBtn.addEventListener('click', () => {
        removeEntry(type, index);
      });

      div.appendChild(detailsSpan);
      div.appendChild(amountSpan);
      div.appendChild(removeBtn);

      return div;
    }

    /**
     * Remove entry by type and index, save updated array and rerender entries.
     */
    function removeEntry(type, index) {
      let key = type === 'added' ? KEY_CASH_ADDED : KEY_CASH_EXPENSE;
      let entries = loadEntries(key);
      if (index >= 0 && index < entries.length) {
        entries.splice(index, 1);
        saveEntries(key, entries);
        renderEntries();
      }
    }

    /**
     * Show the modal to add new entry for the specified type.
     * Clears inputs and focuses the amount field.
     */
    function showEntryModal(type) {
      currentEntryType = type;
      entryAmountInput.value = '';
      entryDetailsInput.value = '';
      entryModal.classList.add('active');
      entryAmountInput.focus();
    }

    /**
     * Hide the entry modal and clear state.
     */
    function hideEntryModal() {
      entryModal.classList.remove('active');
      currentEntryType = null;
      entryForm.reset();
    }

    // Event listeners for plus buttons opening the modal for each type
    addCashAddedBtn.addEventListener('click', () => showEntryModal('added'));
    addCashExpenseBtn.addEventListener('click', () => showEntryModal('expense'));

    // Cancel button closes modal without saving
    cancelBtn.addEventListener('click', () => {
      hideEntryModal();
    });

    /**
     * Form submit handler
     * Validates input, saves new entry to storage, updates UI, then closes modal
     */
    entryForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const amount = parseFloat(entryAmountInput.value);
      const details = entryDetailsInput.value.trim();

      if (isNaN(amount) || amount <= 0) {
        alert('Please enter a valid amount greater than 0');
        entryAmountInput.focus();
        return;
      }

      if (currentEntryType !== 'added' && currentEntryType !== 'expense') {
        alert('Invalid entry type');
        hideEntryModal();
        return;
      }

      const key = currentEntryType === 'added' ? KEY_CASH_ADDED : KEY_CASH_EXPENSE;
      const entries = loadEntries(key);
      entries.push({ amount: amount.toFixed(2), details });
      saveEntries(key, entries);
      renderEntries();
      hideEntryModal();
    });

    // Accessibility: close modal on Escape key press
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && entryModal.classList.contains('active')) {
        hideEntryModal();
      }
    });

    /**
     * Initialization: sets up UI, updates amounts and entries after DOM is ready
     */
    function init() {
      setupSideDrawer();
      updateSalesAmounts();
      renderEntries();
    }

    // Wait for DOM to load then initialize the app
    document.addEventListener('DOMContentLoaded', init);
  </script>
</body>
</html>
