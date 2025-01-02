<link rel="stylesheet" href="css/global.css">
<?php
$userLoggedIn = false;
$userImage = 'user.png';
$themeQuery = "SELECT * FROM theme LIMIT 1";
$themeResult = $conn->query($themeQuery);
$theme = $themeResult->fetch_assoc();
$primaryColor = $theme['primary_color'] ?? '#fff';
$secondaryColor = $theme['secondary_color'] ?? '#C9C9A6';
$fontColor = $theme['font_color'] ?? '#9E9B76';
$logo = $theme['logo'] ?? 'logo3.png'; ?>
<header class="header">
    <img src="<?php echo $logo; ?>" alt="Peter Beans Logo" class="logopic">
    <p class="logo">Peter Beans</p>
    <nav class="navbar">
        <a href="home.php"><strong>Home</strong></a>
        <a href="menu.php"><strong>Menu</strong></a>
        <a href="about.php"><strong>About</strong></a>
        <a href="contactus.php"><strong>Contact Us</strong></a>
        <div class="action">
            <div class="profile" onclick="menuToggle();" aria-expanded="false">
                <img
                    src="<?php echo $userImage; ?>"
                    alt="Account Profile"
                    class="profile-img" />
            </div>
            <div class="menu" aria-hidden="true">
                <?php if ($userLoggedIn): ?>
                    <strong>
                        <h3><br> <?php echo htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']); ?></h3>
                    </strong>

                    <ul>
                        <li>
                            <i class="fas fa-user"></i>
                            <a href="profile.php">My Profile</a>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <a href="otp.php">Verification</a>
                        </li>
                        <li>
                            <i class="fas fa-sign-out-alt"></i>
                            <a href="logout.php">Logout</a>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul>
                        <li>
                            <i class="fas fa-user-plus"></i>
                            <a href="login.php">Sign Up</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>