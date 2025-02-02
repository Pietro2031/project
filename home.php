<?php
session_start();
include('connection.php');
$userLoggedIn = false;
$userImage = 'user.png';
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
        $stmt->close();
    }
}
$query = "SELECT * FROM slideshow";
$result = $conn->query($query);
$slides = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $slides[] = $row['slideshow_path'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<style>
    body {
        min-height: 80vh;
        background-color: <?php echo $primaryColor; ?>;
        color: <?php echo $fontColor; ?>;
    }

    .footer {
        background-color: <?php echo $primaryColor; ?>;
    }

    .menu-item {
        background-color: <?php echo $secondaryColor; ?>;
    }

    .left-column-text h3 {
        color: <?php echo $fontColor; ?>;
    }

    .footer h1 {
        color: <?php echo $secondaryColor; ?>;
    }

    .main-title2 {
        color: <?php echo $fontColor; ?>;
    }

    .menu ul li {
        color: #9E9B76;
    }
</style>

<body>
    <?php include 'header.php'; ?>
    <section class="hero-section">
        <div class="hero-content">
            <div class="left-column-text">
                <h3>Peter Beans</h3>
                <pre>At The Peter Beans, we are passionate about
brewing the finest coffee to brighten
your day and elevate your experience.Our 
mission is to provide you with more than
just a cup of coffee – we aim to create
a moment of joy in every sip.
</pre>
                <a href="#more" class="cta-button">
                    <p>Learn More</p>
                </a>
                <a href="makeourown.php" class="cta-button">
                    <p>Make you own Drink</p>
                </a>
            </div>

            <div class="right-column-icons">

                <div class="feature-box">
                    <i class="fa fa-mug-hot mug-icon"></i>
                    <h4>Delicious Coffee</h4>
                    <p>Experience the rich taste of freshly brewed coffee that will keep you coming back for more.</p>
                </div>

                <div class="feature-box">
                    <i class="fa fa-seedling beans-icon"></i>
                    <h4>Organic Beans</h4>
                    <p>We use only the finest organic beans, carefully sourced to bring you the best flavors.</p>
                </div>
                <div class="feature-box">
                    <i class="fa fa-coffee mug-icon"></i>
                    <h4>Fresh Brews</h4>
                    <p>Our coffee is brewed fresh to order, giving you the perfect cup every time.</p>
                </div>
            </div>
        </div>
    </section>
    <div class="slideshow-container">
        <?php

        foreach ($slides as $index => $slide) {
            $slideNumber = $index + 1;
            echo '<div class="mySlides fade">';
            echo "<div class='numbertext'>$slideNumber / " . count($slides) . "</div>";
            echo "<img src='$slide' style='width:100%'>";
            echo '</div>';
        }
        ?>
    </div>
    <section class="sec-03">
        <div class="container-section">
            <h3 class="main-title2">Our Physical Store</h3>
            <div class="content-02">
                <div class="media-info">
                    <li><a href="https://www.facebook.com/peter.maravilla.39" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
                    <li><a href="https://www.instagram.com/pietroooo.6/" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
                    <li><a href="https://x.com/MaravillaPeter" target="_blank"><i class="fab fa-twitter"></i> Twitter</a></li>
                    <li><a href="https://www.google.com/maps/place/Bulacan+State+University+-+Bustos+Campus+Multi+Purpose+Hall/@14.9546734,120.9081463,17z/data=!3m1!4b1!4m6!3m5!1s0x33970009b05503bb:0xfb07a75d6b077fb4!8m2!3d14.9546734!4d120.9107212!16s%2Fg%2F11c5q_tt9l?entry=ttu&g_ep=EgoyMDI0MTAyMy4wIKXMDSoASAFQAw%3D%3D" target="_blank"><i class="fas fa-map-marker-alt"></i> Maps</a></li>
                    <li><a href="https://www.tiktok.com/@petermaravilla0" target="_blank"><i class="fab fa-tiktok"></i> Tiktok</a></li>
                </div>
                <div class=" image2">
                    <img src="physicalstore.webp" alt="" class="pastashop">
                </div>
            </div>
        </div>
    </section>
    <div class="footer" id="more">
        <h1>Coffee, Bakery, and Desserts Done Right</h1>
        <div class="menu-item coffee">
            <img src="aboutimg4.webp" alt="Coffee">
            <h3>Coffee</h3>
            <p class="desc">Experience our artisan craft and diverse selections of freshly brewed coffee.</p>
        </div>
        <div class="menu-item bakery">
            <img src="aboutimg5.webp" alt="Bakery">
            <h3>Bakery</h3>
            <p class="desc">Step into a world of warm baked memories with our freshly baked goods.</p>
        </div>
        <div class="menu-item breakfast">
            <img src="aboutimg6.webp" alt="Breakfast">
            <h3>Dessert</h3>
            <p class="desc">Enjoy signature dishes and our delicious desserts creations.</p>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let slides = document.getElementsByClassName("mySlides");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1
            }
            slides[slideIndex - 1].style.display = "block";
            setTimeout(showSlides, 2000);
        }
    </script>
</body>

</html>