<?php
$firstName = "Ethan James";
$lastName = "Carter";
$contactNumber = "09123456789";
$age = 25;
$email = "ethancarter@gmail.com";

$firstNameEsc = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
$lastNameEsc = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
$contactNumberEsc = htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8');
$ageEsc = htmlspecialchars($age, ENT_QUOTES, 'UTF-8');
$emailEsc = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
<body>
    <!-- Container -->
    <div>
        <header>
            <!-- Left: Hamburger and Title -->
            <div class="header-left">
                <span class="hamburger" role="button" aria-label="Toggle menu" tabindex="0" aria-expanded="false">☰</span>
                <span class="menu-title">Profile</span>
            </div>

            <!-- Right: Logo as Image -->
            <div class="header-right">
                <img id="logo-img" src="logo.png" alt="BREWeb Logo" />
            </div>
        </header>

        <div class="container">
            <div class="profile-icon">
                <img src="profile-icon.png" alt="Profile Icon" />
            </div>

            <div class="form-section" id="profile-form">
                <div class="form-row">
                    <div class="form-group half">
                        <label for="firstName">FIRST NAME</label>
                        <input type="text" id="firstName" value="{$firstNameEsc}" readonly />
                    </div>
                    <div class="form-group half">
                        <label for="lastName">LAST NAME</label>
                        <input type="text" id="lastName" value="{$lastNameEsc}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="contactNumber">CONTACT NUMBER</label>
                        <input type="text" id="contactNumber" value="{$contactNumberEsc}" readonly />
                    </div>
                    <div class="form-group half">
                        <label for="age">AGE</label>
                        <input type="number" id="age" min="0" max="120" value="{$ageEsc}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full">
                        <label for="email">EMAIL</label>
                        <input type="email" id="email" value="{$emailEsc}" readonly />
                    </div>
                </div>

                <div class="buttons">
                    <button type="button" id="edit-btn" aria-label="Edit Profile">Edit</button>
                    <button type="button" id="save-btn" aria-label="Save Profile">Save</button>
                </div>
                <div id="error-msg" role="alert"></div>
            </div>
        </div>

        <!-- Side Drawer -->
        <div id="side-drawer" aria-hidden="true">
            <button id="close-drawer" aria-label="Close menu">✖ Close</button>

            <!-- Drawer Content -->
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
        </main>
    </div>

    <script>
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
                if (e.key === 'Escape' && sideDrawer.classList.contains('open')) {
                    closeDrawer();
                }
            });
            sideDrawer.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', closeDrawer);
            });
        }

        function validateEmail(email) {
            // Simple email regex validation
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validateContactNumber(number) {
            // Basic validation: digits only, length between 7 to 15
            const re = /^[0-9]{7,15}$/;
            return re.test(number);
        }

        function setupProfileEdit() {
            const editBtn = document.getElementById('edit-btn');
            const saveBtn = document.getElementById('save-btn');
            const inputs = document.querySelectorAll('#profile-form input');
            const errorMsg = document.getElementById('error-msg');

            // Load saved values from localStorage (if any)
            function loadSavedProfile() {
                const savedData = localStorage.getItem('profileData');
                if (savedData) {
                    try {
                        const profile = JSON.parse(savedData);
                        if (profile.firstName) document.getElementById('firstName').value = profile.firstName;
                        if (profile.lastName) document.getElementById('lastName').value = profile.lastName;
                        if (profile.contactNumber) document.getElementById('contactNumber').value = profile.contactNumber;
                        if (profile.age) document.getElementById('age').value = profile.age;
                        if (profile.email) document.getElementById('email').value = profile.email;
                    } catch {
                        // Ignore invalid data
                    }
                }
            }

            // Disable inputs to read-only state
            function setReadOnlyState(isReadOnly) {
                inputs.forEach(input => {
                    if (isReadOnly) {
                        input.setAttribute('readonly', 'readonly');
                        input.style.backgroundColor = '#eaeaea';
                    } else {
                        input.removeAttribute('readonly');
                        input.style.backgroundColor = 'white';
                        input.focus();
                    }
                });

                if (isReadOnly) {
                    saveBtn.style.display = 'none';
                    editBtn.style.display = 'inline-block';
                    errorMsg.style.display = 'none';
                } else {
                    saveBtn.style.display = 'inline-block';
                    editBtn.style.display = 'none';
                }
            }

            editBtn.addEventListener('click', () => {
                setReadOnlyState(false);
            });

            saveBtn.addEventListener('click', () => {
                // Validate inputs
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                const contactNumber = document.getElementById('contactNumber').value.trim();
                const age = document.getElementById('age').value.trim();
                const email = document.getElementById('email').value.trim();

                if (!firstName || !lastName || !contactNumber || !age || !email) {
                    errorMsg.textContent = 'All fields are required.';
                    errorMsg.style.display = 'block';
                    return;
                }

                if (!validateContactNumber(contactNumber)) {
                    errorMsg.textContent = 'Contact Number must be digits only, length 7-15.';
                    errorMsg.style.display = 'block';
                    return;
                }

                const ageNum = Number(age);
                if (isNaN(ageNum) || ageNum < 0 || ageNum > 120) {
                    errorMsg.textContent = 'Age must be a number between 0 and 120.';
                    errorMsg.style.display = 'block';
                    return;
                }

                if (!validateEmail(email)) {
                    errorMsg.textContent = 'Email address is not valid.';
                    errorMsg.style.display = 'block';
                    return;
                }

                // Save to localStorage
                const profileData = {
                    firstName,
                    lastName,
                    contactNumber,
                    age: ageNum,
                    email,
                };
                localStorage.setItem('profileData', JSON.stringify(profileData));
                errorMsg.style.display = 'none';

                // Set inputs back to readonly
                setReadOnlyState(true);
            });

            // Initialize
            loadSavedProfile();
            setReadOnlyState(true);
        }

        // Call the setup functions once script loads
        setupSideDrawer();
        setupProfileEdit();
    </script>
</body>
</html>
HTML;
?>

