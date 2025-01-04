<link rel="stylesheet" href="css/global.css">
<?php
$userLoggedIn = false;
$userImage = 'user.png';
$cartCount = 0;

$themeQuery = "SELECT * FROM theme LIMIT 1";
$themeResult = $conn->query($themeQuery);
$theme = $themeResult->fetch_assoc();
$primaryColor = $theme['primary_color'] ?? '#fff';
$secondaryColor = $theme['secondary_color'] ?? '#C9C9A6';
$fontColor = $theme['font_color'] ?? '#9E9B76';
$logo = $theme['logo'] ?? 'logo3.png';
if (isset($_SESSION['username'])) {
    $userLoggedIn = true;
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
            if (!empty($user['profile_picture'])) {
                $userImage = $user['profile_picture'];
            }
        }
        $userId = $user['id'];
        $cartCountQuery = "SELECT COUNT(*) AS count FROM cart WHERE user_id = ?";
        $cartStmt = $conn->prepare($cartCountQuery);
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();
        if ($cartResult->num_rows > 0) {
            $cartData = $cartResult->fetch_assoc();
            $cartCount = $cartData['count'];
        }
        $stmt->close();
    }
} ?>
<header class=" header">
    <img src="<?php echo $logo; ?>" alt="Peter Beans Logo" class="logopic">
    <p class="logo">Peter Beans</p>
    <nav class="navbar">
        <a href="home.php"><strong>Home</strong></a>
        <a href="menu.php"><strong>Menu</strong></a>
        <a href="about.php"><strong>About</strong></a>
        <a href="contactus.php"><strong>Contact Us</strong></a>
        <div class="slideright" style="gap: 20px;">
            <?php if ($userLoggedIn): ?>
                <div class="cart" style="cursor: pointer;" onclick="goToCart()">
                    <img src="img/icon/cart.png" alt="">
                    <div class="count"><?= $cartCount ?></div>
                </div>

                <script>
                    function goToCart() {
                        window.location.href = "cart.php";
                    }
                </script>

            <?php endif; ?>
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
        </div>
    </nav>
</header>
<script>
    function menuToggle() {
        const menu = document.querySelector(".menu");
        const profile = document.querySelector(".profile");

        menu.classList.toggle("active");

        const expanded = profile.getAttribute("aria-expanded") === "true";
        profile.setAttribute("aria-expanded", !expanded);
        menu.setAttribute("aria-hidden", expanded);
    }
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
<style>
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 30px;
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 100;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .navbar {
        margin-left: 125px;
        display: flex;
        justify-content: center;
        gap: 2.5rem;
        flex: 1;
        text-align: center;
        align-items: center;
    }

    .logo-container {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-shrink: 0;
    }

    .logopic {
        height: 60px;
        width: auto;
        margin-left: 15px;
        transition: transform 0.3s ease, filter 0.3s ease;
        filter: grayscale(80%);
    }

    .logopic:hover {
        transform: rotate(360deg);
        filter: grayscale(0%);
    }

    .logo {
        font-size: 30px;
        font-weight: bold;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-family: "Poppins", sans-serif;
        transition: transform 0.3s ease, color 0.3s ease;
        margin-left: 10px;
    }

    .logo:hover {
        transform: scale(1.1);
        color: #9e9b76;
    }

    .navbar a {
        color: #c9c9a6;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 10px 18px;
        border-radius: 25px;
        background-color: #9e9b76;
        position: relative;
        transition: all 0.3s ease;
    }

    .navbar a:hover {
        color: #fff;
        background-color: #9e9b76;
    }

    .navbar a::after {
        content: "";
        position: absolute;
        width: 0;
        height: 3px;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #9e9b76;
        transition: width 0.3s ease-in-out;
    }

    .navbar a:hover::after {
        width: 100%;
    }

    .action {
        position: relative;
    }

    .action .profile {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid #9e9b76;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action .profile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
    }

    .action .menu {
        position: absolute;
        top: 70px;
        right: 0;
        left: 20px;
        padding: 10px 20px;
        background: #c9c9a6;
        width: 200px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 200;
    }

    .action .menu.active {
        visibility: visible;
        opacity: 1;
        transform: translateY(10px);
    }

    .action .menu h3 {
        text-align: center;
        font-size: 16px;
        padding: 10px 0;
        color: #555;
    }

    .action .menu ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .action .menu ul li {
        margin-left: 20px;
        padding: 12px 0;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
    }

    .action .menu ul li img {
        width: 20px;
        margin-right: 10px;
    }

    .action .menu ul li a {
        margin-left: 10px;
        text-decoration: none;
        color: #c9c9a6;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .action .menu ul li:hover a {
        color: #fff;
    }
</style>