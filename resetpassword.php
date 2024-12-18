<?php
session_start();
$message = "";

// Include the database connection file
include('connection.php');

// Default user logged-in status and profile picture
$userLoggedIn = false;
$userImage = 'user.png'; // Default profile image

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $userLoggedIn = false;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $email = $_SESSION['reset_email']; // The email stored during the forgot password process

    if (empty($newPassword) || empty($confirmPassword)) {
        $message = "Both fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        include('connection.php'); // Include your database connection

        // Hash the new password

        // Update the password in the database
        $query = "UPDATE user_account SET passwords = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $newPassword, $email);

        if ($stmt->execute()) {
            $message = "Password reset successful. You can now log in with your new password.";
            unset($_SESSION['reset_email']); // Clear session variables
            unset($_SESSION['reset_token']);
        } else {
            $message = "An error occurred. Please try again later.";
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
    <title>Reset Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;200;300;400;500;600;700&display=swap');
        body {
            
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #C9C9A6;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .reset-password-container {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .reset-password-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background-color: #9E9B76;
            border-radius: 50px;
            margin-top: 20px;
        }

        h2 {
            font-family: "Roboto Slab", serif;
            font-size: 48px; /* Increased font size for better visibility */
            color: #9E9B76; /* Olive Green */
            margin-bottom: 30px; /* More space between title and other elements */
        }

        p {
            font-size: 18px; /* Slightly larger font size for readability */
            color: #777;
            margin-bottom: 30px; /* Added space below instructions */
        }

        .input-field {
            width: 100%;
            height: 50px;
            background: #ffffff; /* Pure White input field background */
            margin: 10px 0;
            border: 2px solid #C9C9A6; /* Warm Light Olive border */
            border-radius: 50px;
            display: flex;
            align-items: center;
        }

        .input-field input {
            flex: 5;
            background: none;
            border: none;
            outline: none;
            width: 100%;
            font-size: 17px;
            font-weight: 600;
            padding-left: 3px;
            color: #9E9B76; /* Olive Green input text */
        }

        .input-field i {
            flex: 1;
            text-align: center;
            color: #9E9B76; /* Olive Green icon color */
            font-size: 17px;
        }

        button {
            margin-top: 3%;
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background: #9E9B76;
            color: #ffffff;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #9E9B76;
            color: #ffffff; /* White text */
            font-size: 19px;
        }

        .message {
            margin-top: 20px;
            font-size: 16px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .footer {
            font-size: 14px;
            color: #777;
            margin-top: 50px;
        }

        .footer a {
            color: #9E9B76;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            color: #9E9B76;
            font-size: 19px;
        }
        .header1 {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Allows repositioning of logo and navbar */
    padding: 15px 30px;
    background-color: #fff;
    border-bottom: 1px solid #ddd;
    position: fixed;
    top: 0;
    height:60px;
    left: 0;
    width: 100%;
    z-index: 100;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    margin-bottom: 20%;
}


/* Navigation in the center */
.navbar {
    margin-left: 125px;
    display: flex;
    justify-content: center;
    gap: 2.5rem; /* Adjusted for better spacing */
    flex: 1; /* Allows it to occupy central space */
    text-align: center;
}

/* Logo container */
.logo-container {
    display: flex;
    align-items: center;
    justify-content: flex-start; /* Aligns logo to the left */
    flex-shrink: 0;
}

/* Logo text and image styles */
.logopic {
    height: 60px; /* Adjusted logo size */
    width: auto;
    margin-left: 15px; /* Space between text and image */
    transition: transform 0.3s ease, filter 0.3s ease;
    filter: grayscale(80%);
}
.logopic:hover {
    transform: rotate(360deg);
    filter: grayscale(0%);
}
.logo {
    font-size: 30px; /* Adjusted logo text size */
    font-weight: bold;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: 'Poppins', sans-serif;
    transition: transform 0.3s ease, color 0.3s ease;
    margin-left: 10px; /* Space between elements */
}
.logo:hover {
    transform: scale(1.1);
    color: #9E9B76;
}

/* Navigation */
.navbar a {
    color: #C9C9A6;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 10px 18px;
    border-radius: 25px;
    background-color: #9E9B76;
    position: relative;
    transition: all 0.3s ease;
}
.navbar a:hover {
    color: #fff;
    background-color: #9E9B76;
}
.navbar a::after {
    content: "";
    position: absolute;
    width: 0;
    height: 3px;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #9E9B76;
    transition: width 0.3s ease-in-out;
}
.navbar a:hover::after {
    width: 100%;
}

/* Profile Dropdown */
.action {
    position: relative;
}
.action .profile {
    margin-right: -60px;
    margin-left: 100px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid #9E9B76;
    border-radius: 50%;
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

/* Profile and Cart on the Right Corner */
.cart-profile-container {
    display: flex;
    align-items: center;
    gap: 15px;
    position: absolute; /* Positioned on the right corner */
    right: 20px;
    top: 20px; /* Adjusted for alignment */
}

.action .menu {
    position: absolute;
    top: 70px;
    right: 0;
    left: 20px;
    padding: 10px 20px;
    background: #C9C9A6;
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
    color: #C9C9A6;
    font-size: 14px;
    transition: color 0.3s ease;
}

.action .menu ul li:hover a {
    color: #fff;
}

.content img{
    height: 600px;
    width: 700px;
}


    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        <p>Enter your new password below.</p>
        <form method="POST" action="">
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" placeholder="New Password" name="password" required>
            </div>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" placeholder="Confirm Password" name="confirm_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'successful') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Remembered your password? <a href="login.php">Log in</a></p>
        </div>
    </div>

    
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
