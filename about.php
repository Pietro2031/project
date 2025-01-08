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
    <title>Document</title>
    <link rel="stylesheet" href="about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    body {
        min-height: 80vh;
        background-color: <?php echo $primaryColor; ?>;
    }

    .header .title1 {
        color: <?php echo $fontColor; ?>;
    }

    .our-story h2 {
        color: <?php echo $fontColor; ?>;
    }

    .our-story {
        background-color: <?php echo $secondaryColor; ?>;
    }

    .image-desc-container h3 {
        color: <?php echo $fontColor; ?>;

    }

    .menu ul li {
        color: #9E9B76;
    }
</style>

<body>
        <?php include 'header.php'; ?>

    <!-- Header Section -->
    <div class="header3">
        <h1 class="title1">Peter Beans finest independent coffee house</h1>
        <button class="view-shop-btn">View Shop</button>
    </div>

    <section class="our-story">
        <h2>Our Story</h2>
        <div class="story-content">
            <div class="story-images">
                <img src="aboutimg3.jpg" class="top-left" alt="Coffee beans">
                <img src="aboutimg1.jpg" class="center" alt="Barista at work">
                <img src="aboutimg2.jpg" class="bottom-right" alt="Pouring coffee">
            </div>
        </div>
    </section>

    <pre class="left-text"> The Peter Beans is a welcoming coffee 
 shop dedicated to crafting the perfect 
 cup. With a focus on quality and community,
 we source the finest beans, prepare them
 with care, and serve them in a cozy, inviting
 atmosphere. Whether you’re here to catch
 up with friends, find a quiet moment, or 
 fuel your day, The Peter Beans is your
 go-to destination for fresh coffee,
 delicious bites, and genuine connections.
  </pre>

    <pre class="right-text"> The Peter Beans is a cozy coffee 
shop dedicated to quality and 
community. We source the finest
beans, prepare them with care, and
serve them in an inviting space
designed for connection and
comfort. Whether you’re here for
a quiet moment or a lively chat,
our expertly crafted drinks and
treats make every visit special.
At The Peter Beans, your story
becomes part of ours.
  </pre>

    <main>
        <section class="messy-grid">
            <div class="image-block" style="--offset-x: -10px; --offset-y: 20px; --rotate: -5deg;">
                <img src="aboutimg13.jpg" alt="Coffee brewing">
                <div class="image-desc-container">
                    <h3>Coffee Brewing</h3>
                    <p class="image-desc">"Experience the art of brewing with our handcrafted pour-over coffee, made fresh just for you."</p>
                </div>
            </div>
            <div class="image-block" style="--offset-x: 30px; --offset-y: -10px; --rotate: 3deg;">
                <img src="aboutimg16.png" alt="Delicious breakfast">
                <div class="image-desc-container">
                    <h3>Delicious Pastries</h3>
                    <p class="image-desc">"Pair your coffee with our freshly baked croissants and pastries, crafted daily to perfection."</p>
                </div>
            </div>
            <div class="image-block" style="--offset-x: -30px; --offset-y: 40px; --rotate: -7deg;">
                <img src="aboutimg10.webp" alt="Coffee cup">
                <div class="image-desc-container">
                    <h3>Cozy Cafe Ambiance</h3>
                    <p class="image-desc">"Take a break and relax in our cozy, welcoming space, where every corner invites you to stay a little longer."</p>
                </div>
            </div>
            <div class="image-block" style="--offset-x: 10px; --offset-y: -20px; --rotate: 5deg;">
                <img src="aboutimg11.jpeg" alt="Cozy cafe">
                <div class="image-desc-container">
                    <h3>Dog Friendly Space</h3>
                    <p class="image-desc">"Your furry friends are always welcome here! Enjoy coffee while your pup enjoys the vibe. We’re proud to be a dog-friendly cafe, so feel free to bring your pet along."</p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="footerContainer">
            <div class="socialIcons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
                <a href=""><i class="fa-brands fa-twitter"></i></a>
                <a href=""><i class="fa-brands fa-youtube"></i></a>
            </div>
            <div class="footerNav">
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>

        </div>
        <div class="footerBottom">
            <p>Copyright &copy;2024 <strong><span class="designer">Peter Beans</span> </strong>All Rights Reserved</p>
        </div>
    </footer>


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