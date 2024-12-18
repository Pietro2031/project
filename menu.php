<?php
session_start(); // Start the session to access session variables

// Include the database connection file
include('connection.php');

// Default user logged-in status and profile picture
$userLoggedIn = false;
$userImage = 'user.png'; // Default profile image

// Fetch theme settings from the database
$themeQuery = "SELECT * FROM theme LIMIT 1"; // Assuming there's only one theme row
$themeResult = $conn->query($themeQuery);
$theme = $themeResult->fetch_assoc();

// Set default values in case no theme is found
$primaryColor = $theme['primary_color'] ?? '#ffffff';
$secondaryColor = $theme['secondary_color'] ?? '#C9C9A6';
$fontColor = $theme['font_color'] ?? '#9E9B76';
$logo = $theme['logo'] ?? 'logo3.png'; // Default logo if none is found

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $userLoggedIn = true;

    // Fetch user info from the database based on session username
    $query = "SELECT * FROM user_account WHERE userName = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['first_name'] = $user['Fname'];
            $_SESSION['last_name'] = $user['Lname'];
            // Check if user has a profile picture set
            if (!empty($user['profile_picture'])) {
                $userImage = $user['profile_picture']; // Use the uploaded profile picture
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Grid</title>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
        body {
            min-height: 80vh;
            background-color: <?php echo $primaryColor; ?>;
        }
        .menu ul li{
            color:#9E9B76;
        }
    </style>
<body>
<header class="header1">
<img src="<?php echo $logo; ?>" alt="Peter Beans Logo" class="logopic">
    <p class="logo">Peter Beans</p>
    <nav class="navbar">
        <a href="home.php"><strong>Home</strong></a>
        <a href="menu.php"><strong>Menu</strong></a>
        <a href="about.php"><strong>About</strong></a>
        <a href="contactus.php"><strong>Contact Us</strong></a>

        <!-- Account Dropdown -->
        <div class="action">
            <div class="profile" onclick="menuToggle();" aria-expanded="false">
                <img 
                    src="<?php echo $userImage; ?>" 
                    alt="Account Profile" 
                    class="profile-img"
                />
            </div>
            <div class="menu" aria-hidden="true">
                <?php if ($userLoggedIn): ?>
                    <strong><h3><br> <?php echo htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']); ?></h3></strong>

                    <ul>
                    <li>
                    <i class="fas fa-user"></i>
    <a href="profile.php">My Profile</a>
</li>
<li>
    <i class="fas fa-check-circle"></i>
    <a href="otp.php">verification</a> 
</li>
<li>
    <i class="fas fa-sign-out-alt"></i>
    <a href="logout.php">Logout</a> <!-- Link to logout.php -->
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
 <!-- Header Section -->
 <header class="header">
    <h1 class="title1">Peter Beans finest independent coffee house</h1>
    <button class="view-shop-btn">View Shop</button>
  </header>
    <section class="products">
        <h2>Cold Brew</h2>
        <div class="product-grid">
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Cold Brew">
                </div>
                <h3>Cold Brew</h3>
                <p class="price">$2.95</p>
                <button class="add-btn">+</button>
            </div>
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Pumpkin Spice Cream">
                </div>
                <h3>Pumpkin Spice Cream</h3>
                <p class="price">$4.25</p>
                <button class="add-btn">+</button>
            </div>
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Salted Caramel Cold Brew">
                </div>
                <h3>Salted Caramel Cold Brew</h3>
                <p class="price">$4.25</p>
                <button class="add-btn">+</button>
            </div>
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Vanilla Sweet Cream">
                </div>
                <h3>Vanilla Sweet Cream</h3>
                <p class="price">$3.95</p>
                <button class="add-btn">+</button>
            </div>
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Vanilla Sweet Cream">
                </div>
                <h3>Vanilla Sweet Cream</h3>
                <p class="price">$3.95</p>
                <button class="add-btn">+</button>
            </div>
            <div class="product">
                <div class="image-container">
                    <img src="homepics.png" alt="Vanilla Sweet Cream">
                </div>
                <h3>Vanilla Sweet Cream</h3>
                <p class="price">$3.95</p>
                <button class="add-btn">+</button>
            </div>
        </div>
    </section>
    <script>
function menuToggle() {
    const menu = document.querySelector(".menu");
    const profile = document.querySelector(".profile");
    
    menu.classList.toggle("active");

    // Update aria attributes for accessibility
    const expanded = profile.getAttribute("aria-expanded") === "true";
    profile.setAttribute("aria-expanded", !expanded);
    menu.setAttribute("aria-hidden", expanded);
}   

// Close menu if clicked outside
document.addEventListener("click", (event) => {
    const menu = document.querySelector(".menu");
    const profile = document.querySelector(".profile");

    if (!menu.contains(event.target) && !profile.contains(event.target)) {
        menu.classList.remove("active");
        profile.setAttribute("aria-expanded", "false");
        menu.setAttribute("aria-hidden", "true");
    }
});
</script>
</body>
</html>

