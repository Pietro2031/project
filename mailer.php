<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/Exception.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/PHPMailer.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 0;                // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                    // Enable SMTP authentication
    $mail->Username   = 'peterbeans138@gmail.com';                // SMTP username (your Gmail address)
    $mail->Password   = 'rdpz hulu qimj zril';                   // SMTP password (use App Password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable implicit TLS encryption
    $mail->Port       = 587;                                     // TCP port to connect to (587 for TLS)

    // Recipients
    $mail->setFrom('maravillapeter123@gmail.com', 'The Peter Beans');
    $mail->addAddress('maravillapeter123@gmail.com', 'Peter Beans'); // Add a recipient

    // Generate a random 6-letter code

    // Content
    $mail->isHTML(true);                                         // Set email format to HTML
    $mail->Subject = 'OTP';
    $mail->Body    = "Thank you for Registering to our website. Please use this code to proceed to our website: <b></b>";
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    // Send the message
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>


?php
session_start();

// Include PHPMailer files

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/Exception.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/PHPMailer.php';
require '/Applications/XAMPP/htdocs/PHPMailer/PHPMailer/src/SMTP.php';

// Database connection details
$servername = "localhost";
$username = "root"; // Your database username
$password = "";     // Your database password
$dbname = "project"; // Your database name

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

// Function to generate OTP
function generateOTP($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}


function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 2;  // Enable verbose debug output (level 2 for more detailed output)
        $mail->isSMTP();  // Send using SMTP
        $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through
        $mail->SMTPAuth = true;  // Enable SMTP authentication
        $mail->Username = 'peterbeans385@gmail.com';  // SMTP username (your Gmail address)
        $mail->Password = 'rytr vlqg wbxc izcx';  // SMTP password (use App Password if 2FA is enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable implicit TLS encryption
        $mail->Port = 587;  // TCP port to connect to (587 for TLS)

        // Recipients
        $mail->setFrom('peterbeans385@gmail.com', 'The Peter Beans');
        $mail->addAddress('maravillapeter123@gmail.com', 'Peter Beans'); // Add a recipient

        // Content
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = 'Your OTP for Account Verification';
        $mail->Body = "Use the following OTP to verify your account: <b>$otp</b>";

        // Send the email
        if ($mail->send()) {
            return "OTP sent successfully to $email.";  // OTP sent successfully
        } else {
            return "Error: OTP could not be sent.";
        }
    } catch (Exception $e) {
        // More detailed debug output
        echo "Mailer Error: " . $mail->ErrorInfo;
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}



// Function to register a new user
function registerUser($conn, $username, $email, $password, $confirm_password) {
    // Check if the passwords match
    if ($password !== $confirm_password) {
        return "Passwords do not match.";
    }

    // Check if the username or email already exists
    $check_query = "SELECT * FROM user_account WHERE userName = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Username or email already exists.";
    } else {
        // Hash the password and insert new user (inactive status)
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['password'] = password_hash($password, PASSWORD_BCRYPT);

        // Generate OTP and send it to the user's email
        $otp = generateOTP();
        $email_status = sendOTP($email, $otp);

        if ($email_status !== "OTP sent successfully to $email.") {
            return "Error sending OTP email.";
        }

        // Store OTP in session for verification
        $_SESSION['otp'] = $otp;

        // Redirect to OTP page
        header("Location: otp.php");
        exit();
    }
}

// Handle form submissions and set message
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $message = registerUser($conn, $username, $email, $password, $confirm_password);
    }

    // Pass the message to JavaScript as an alert if there was an error or success
    if ($message) {
        echo "<script>alert('$message');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-in || Sign-up Page</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="signin-signup">
            <form action="" method="POST" class="sign-in-form">
                <h2 class="title">Sign In</h2>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <input type="submit" name="login" value="Login" class="btn">
            </form>

            <form action="" method="POST" class="sign-up-form">
                <h2 class="title">Sign Up</h2>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email" name="email" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Confirm Password" name="confirm_password" required>
                </div>
                <input type="submit" name="register" value="Sign Up" class="btn">
            </form>
        </div>

        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <center><h2>Are you new to the Peter Beans?</h2></center>
                    <center><button class="btn" id="sign-in-btn">Sign In</button></center>
                </div>
                <img src="logo3.png" alt="" class="image">
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <center><h2>Start Your Journey With Peter Beans</h2></center>
                    <center><button class="btn" id="sign-up-btn">Sign Up</button></center>
                </div>
                <img src="logo3.png" alt="" class="image">
            </div>
        </div>
    </div>
    <script src="app.js"></script>
</body>

</html>


<div class="content">
    <!-- Coffee Image -->
    <img src="coffeehome.png" alt="Coffee cup" class="coffee-image">

    <!-- Icons on the far left and right of the shop name -->
    <div class="icons-line-container">
        <!-- Left Coffee icon -->
        <div class="icon icon-left">
            <i class="fas fa-coffee"></i>
        </div>
        <!-- Right Coffee icon -->
        <div class="icon icon-right">
            <i class="fas fa-coffee"></i>
        </div>
    </div>

    <!-- Coffee Shop Name -->
    <center><h1 class="shop-name">The Peter Bean</h1></center>

    <!-- Tagline -->
    <center><p class="tagline">Brewed to Perfection</p></center>
</div>

<!-- Wave background color under the image -->
<div class="coffee-wave"></div>
<script>
        ScrollReveal({
            distance: '60px',
            duration: 2000,
            delay: 300
        });

        ScrollReveal().reveal('.dream-clients2 li', { delay: 400, origin: 'left' ,setInterval:500});
        ScrollReveal().reveal('.content img', { delay: 100, origin: 'top' });
        ScrollReveal().reveal('.content .icon-left i', { delay: 200, origin: 'bottom' });
        ScrollReveal().reveal('.content .icon-right i', { delay: 200, origin: 'bottom' });
        ScrollReveal().reveal('.content h1', { delay: 200, origin: 'right' });
        ScrollReveal().reveal('.content p', { delay: 200, origin: 'left' });




      
    </script>