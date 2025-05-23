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
    <link rel="stylesheet" href="style.css" />
</head>
<body>

    <div class="header">
        <h1>PROFILE</h1>
        <div class="logo">BREWeb</div>
    </div>

    <div class="container">
        <div class="profile-icon">
            <img src="profile-icon.png" alt="Profile Icon" />
        </div>

        <div class="form-section">
            <div class="form-row">
                <div class="form-group half">
                    <label>FIRST NAME</label>
                    <input type="text" value="{$firstNameEsc}" readonly />
                </div>
                <div class="form-group half">
                    <label>LAST NAME</label>
                    <input type="text" value="{$lastNameEsc}" readonly />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>CONTACT NUMBER</label>
                    <input type="text" value="{$contactNumberEsc}" readonly />
                </div>
                <div class="form-group half">
                    <label>AGE</label>
                    <input type="text" value="{$ageEsc}" readonly />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full">
                    <label>EMAIL</label>
                    <input type="email" value="{$emailEsc}" readonly />
                </div>
            </div>
        </div>
    </div>

</body>
</html>
HTML;
?>
