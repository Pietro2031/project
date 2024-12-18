<?php
session_start(); // Start the session to access session variables

// Include the database connection file
include('connection.php');

// Default user logged-in status and profile picture
$userLoggedIn = false;
$userImage = 'user.png'; // Default profile image

// Check if the user is logged in by verifying the session
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
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

            // Set the user's email from the database
            $_SESSION['email'] = $user['email']; // Save email to session

            // Check if the user is already verified
            if ($user['verified'] == 'verified') {
                $_SESSION['already_verified'] = true; // Set session variable to indicate user is verified
            }
        }
        $stmt->close();
    }
} else {
    // If the user is not logged in, redirect them to the login page
    header("Location: login.php");
    exit();
}

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/Exception.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/PHPMailer.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/SMTP.php';

// Function to generate a 6-character random OTP
function generateOTP() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $otp = '';
    for ($i = 0; $i < 6; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}

// Generate a new OTP every time the page is loaded and store it in the session, if not already verified
if (!isset($_SESSION['otp']) && !isset($_SESSION['already_verified'])) {
    $_SESSION['otp'] = generateOTP(); // Store the newly generated OTP in session
}

// Send OTP email immediately after generation (only if OTP has just been generated and the user is not already verified)
if (isset($_SESSION['otp']) && isset($_SESSION['email']) && !isset($_SESSION['already_verified'])) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'petermaravilla522@gmail.com'; // Use your email
        $mail->Password = 'dbyj cdfb evov mede'; // Use a secure app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('petermaravilla522@gmail.com', 'The Peter Beans');
        $mail->addAddress($_SESSION['email']); // Send OTP to user's email
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';

        // Retrieve the username from the session
        $username = $_SESSION['username'];

        // Styled email body with username and OTP
        $mail->Body = "
        <html>
        <head>
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    background-color: #f9f9f9;
                    color: #333;
                    padding: 20px;
                }
                .container {
                    background-color: #ffffff;
                    border-radius: 10px;
                    padding: 30px;
                    text-align: center;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                }
                .otp-code {
                    font-size: 24px;
                    font-weight: bold;
                    color: #9E9B76;
                    margin: 20px 0;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 14px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
            <h2>Thank You for Registering to our website, $username!</h2>
            <p>Your OTP code is:</p>
            <div class='otp-code'>" . $_SESSION['otp'] . "</div>
            <p>Please enter this code to verify your account on Peter Beans.</p>
            <div class='footer'>Thank you for verifying your account with Peter Beans!</div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        die("OTP email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

// Handle OTP form submission
if (isset($_POST["submit"])) {
    // Get the OTP entered by the user from the form
    $otp_input = $_POST["otp1"] . $_POST["otp2"] . $_POST["otp3"] . $_POST["otp4"] . $_POST["otp5"] . $_POST["otp6"];

    // Compare the entered OTP with the one in the session
    if ($otp_input === $_SESSION['otp']) {
        // OTP matches, update the user's verification status
        $query = "UPDATE user_account SET verified = 'verified' WHERE userName = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_SESSION['username']);
        
        if ($stmt->execute()) {
            // Successfully updated the user's status
            unset($_SESSION['otp']); 
            echo "<script>
                alert('Verified successfully!');
                window.location.href = 'home.php';
            </script>";
            exit();
        } else {
            echo "<script>alert('Error updating verification status.');</script>";
        }
    } else {
        // OTP does not match
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}

// Display an alert if the user is already verified
if (isset($_SESSION['already_verified']) && $_SESSION['already_verified']) {
    // Alert message
    echo "<script>alert('You are already verified!');</script>";

    // Clear the session variable for already verified
    unset($_SESSION['already_verified']); 

    // Redirect to home page after a brief delay
    echo "<script>window.location.href = 'home.php';</script>";
    exit(); // Ensure no further code is executed
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Identity</title>
    <link rel="stylesheet" href="otp.css">
</head>
<body>
<header class="header1">
    <img src="logo3.png" alt="Peter Beans Logo" class="logopic">
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

<div class="otp-container">
    <h2 class="title">Verify Your Identity</h2>
    <p class="instructions">Enter the OTP sent to your email to proceed.</p>
    <form method="POST" action="">
        <div class="otp-input">
            <input type="text" maxlength="1" name="otp1" required>
            <input type="text" maxlength="1" name="otp2" required>
            <input type="text" maxlength="1" name="otp3" required>
            <input type="text" maxlength="1" name="otp4" required>
            <input type="text" maxlength="1" name="otp5" required>
            <input type="text" maxlength="1" name="otp6" required>
        </div>
        <button type="submit" name="submit" class="submit-btn">Verify OTP</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input input[type="text"]');
        const submitBtn = document.querySelector('.submit-btn');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();  // Move to next input
                }
                checkOTPFields();  // Check if all OTP fields are filled
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && index > 0 && input.value === '') {
                    otpInputs[index - 1].focus();  // Move to previous input when backspace is pressed
                }
            });
        });

        function checkOTPFields() {
            const allFilled = Array.from(otpInputs).every(input => input.value.length === 1);
            submitBtn.disabled = !allFilled;  // Enable submit button only if all OTP fields are filled
        }
    });
</script>

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
