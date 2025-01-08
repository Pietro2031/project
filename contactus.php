<?php
session_start();

include('connection.php');

$userLoggedIn = false;
$userImage = 'user.png';

$themeQuery = "SELECT * FROM theme LIMIT 1";
$themeResult = $conn->query($themeQuery);
$theme = $themeResult->fetch_assoc();

$primaryColor = $theme['primary_color'] ?? '#ffffff';
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="contactus.css">
</head>
<style>
  body {
    min-height: 80vh;
    background-color: <?php echo $primaryColor; ?>;
  }

  .contact-section {
    background-color: <?php echo $primaryColor; ?>;

  }

  .menu ul li {
    color: #9E9B76;
  }
</style>

<body>
  <?php include 'header.php'; ?>


  <center>
    <div class="contact-section">
      <!-- Content -->
      <div class="content">
        <!-- Form Section -->
        <div class="form">
          <form>
            <h2>Get In Touch</h2>
            <p>Feel free to reach out to us for inquiries, suggestions, or just to say hello. We're here to help!</p>

            <!-- Name Field -->
            <div class="form-group">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="Your Name" required>
            </div>

            <!-- Email Field -->
            <div class="form-group">
              <i class="fas fa-envelope"></i>
              <input type="email" placeholder="Your Email" required>
            </div>

            <!-- Subject Field -->
            <div class="form-group">
              <i class="fas fa-tag"></i>
              <input type="text" placeholder="Subject" required>
            </div>

            <!-- Message Field -->
            <div class="form-group">
              <i class="fas fa-comment-dots"></i>
              <textarea rows="5" placeholder="Message" required></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit">Send Now</button>
          </form>
        </div>

        <!-- Contact Info Section -->
        <div class="contact-info">
          <div class="info-box">
            <i class="fas fa-phone"></i>
            <p>Phone</p>
            <span>09602558220</span>
          </div>
          <div class="info-box">
            <i class="fab fa-instagram"></i>
            <p>Instagram</p>
            <span>@pietro</span>
          </div>
          <div class="info-box">
            <i class="fas fa-envelope"></i>
            <p>Email</p>
            <span>petermaravilla522@gmail.com</span>
          </div>
          <div class="info-box">
            <i class="fab fa-facebook"></i>
            <p>Facebook</p>
            <span>Peter Maravilla</span>
          </div>
        </div>
      </div>

      <!-- Map Section -->
      <div class="map">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3857.587195204868!2d120.91370227510109!3d14.947537373941933!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ab4281977a23%3A0x4d7d6e3094e3b85d!2sBulacan%20State%20University%20Bustos%20Campus!5e0!3m2!1sen!2sph!4v1700793408577!5m2!1sen!2sph"
          width="600"
          height="450"
          style="border:0;"
          allowfullscreen=""
          loading="lazy">
        </iframe>

      </div>
    </div>
  </center>
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