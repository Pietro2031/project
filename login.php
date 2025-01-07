<?php
include('connection.php');
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userLoggedIn = false;
$userImage = 'user.png'; // Default profile image
// Fetch theme settings from the database
$themeQuery = "SELECT * FROM theme LIMIT 1"; // Assuming there's only one theme row
$themeResult = $conn->query($themeQuery);
$theme = $themeResult->fetch_assoc();

// Set default values in case no theme is found
$logo = $theme['logo'] ?? 'logo3.png'; // Default logo if none is found

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

// Registration Process
if (isset($_POST['register'])) {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $address = htmlspecialchars(trim($_POST['address']));
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password matching
    if ($password !== $confirm_password) {
        echo "<script>
                alert('Passwords do not match!');
                window.location.href = 'login.php';
              </script>";
        exit();
    }

    // Validate Philippine contact number format
    if (!preg_match('/^(09|\+639|639)\d{9}$/', $contact_number)) {
        echo "<script>
                alert('Invalid Philippine contact number format!');
                window.location.href = 'login.php';
              </script>";
        exit();
    }

    // Check if username or email already exists
    $query = "SELECT * FROM user_account WHERE userName = ? OR email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
                alert('Username or Email already exists!');
                window.location.href = 'login.php';
              </script>";
        exit();
    }

    // Store user information in session (without hashing the password)
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['address'] = $address;
    $_SESSION['contact_number'] = $contact_number;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['password'] = $password; // Store the plain password
    $profile_image = ''; // Default profile image
    $address2 = '';
    $attempt = 4;

    // Insert user data into database
    $query = "INSERT INTO user_account (userName, email, passwords, statuss, attempt, Addresss, ContactNum, Fname, Lname, profile_picture, addresss2, verified) 
VALUES (?, ?, ?, 'notblock', ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'ssssssssss',
        $username,
        $email,
        $password,
        $attempt,
        $address,
        $contact_number,
        $first_name,
        $last_name,
        $profile_image,
        $address2
    );


    if ($stmt->execute()) {
        // Clear session data
        session_unset();
        session_destroy();

        // Redirect to login
        echo "<script>
        alert('Successfully registered! You can now login to your account.');
        window.location.href = 'login.php'; // Redirect to login page
        </script>";
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }

    exit();
}

// Login Process
if (isset($_POST['login'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Check if the user is logging in as an admin
    $query = "SELECT * FROM admin_account WHERE username = ? AND passwords = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if admin is found
    if ($result->num_rows > 0) {
        // Admin login successful
        $admin = $result->fetch_assoc();
        $_SESSION['admin_username'] = $admin['username'];

        // Redirect to admin dashboard
        header("Location: admin.php?dashboard");
        exit();
    }

    // Query to check if the username exists in the user_account table
    $query = "SELECT * FROM user_account WHERE userName = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user is found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the user is blocked
        if ($user['statuss'] == 'blocked') {
            echo "<script>
                    alert('Your account is blocked, please contact support.');
                    window.location.href = 'login.php';
                  </script>";
            exit();
        }

        // Check if the session has failed login attempts stored
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0; // Initialize failed attempts counter
        }

        // Verify the password (without hashing)
        if ($password === $user['passwords']) {
            // Reset failed login attempts on successful login
            $_SESSION['failed_attempts'] = 0;

            // Login successful for regular users
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $user['Fname'];
            $_SESSION['last_name'] = $user['Lname'];
            $_SESSION['profile_image'] = !empty($user['profile_picture']) ? $user['profile_picture'] : 'user.png';

            header("Location: home.php");
            exit();
        } else {
            // Increment the failed attempts
            $_SESSION['failed_attempts']++;

            // Block user after 4 failed login attempts
            if ($_SESSION['failed_attempts'] >= 3) {
                // Block the user and set their verified status to 'pending'
                $status_query = "UPDATE user_account SET statuss = 'blocked', verified = 'pending' WHERE userName = ?";
                $status_stmt = $conn->prepare($status_query);
                $status_stmt->bind_param("s", $username);
                $status_stmt->execute();
                $status_stmt->close();

                echo "<script>
                        alert('Your account is blocked due to multiple failed login attempts.');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            } else {
                echo "<script>
                        alert('Invalid username or password!');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            }
        }
    }

    // If no match found in either table
    echo "<script>
            alert('Invalid username or password!');
            window.location.href = 'login.php';
          </script>";

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-in || Sign-up Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="login.css">
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
    <div class="container">
        <div class="signin-signup">
            <!-- Sign In Form -->
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
                <div class="forgot-pass">
                    <button type="button" class="forgot-pass-btn" onclick="location.href='forgotpass.php'">
                        Forgot Password?
                    </button>
                </div>
                <input type="submit" name="login" value="Login" class="btn">
            </form>

            <!-- Sign Up Form -->
            <form action="" method="POST" class="sign-up-form">
                <h2 class="title">Sign Up</h2>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="First Name" name="first_name" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Last Name" name="last_name" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" placeholder="Address" name="address" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-phone-alt"></i>
                    <input type="text" placeholder="Contact Number" name="contact_number" required>
                </div>
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
                    <center>
                        <h2>Start Your Journey With Peter Beans</h2>
                    </center>
                    <center><button class="btn" id="sign-in-btn">Sign In</button></center>
                </div>

                <a href="home.php"><img src="<?php echo $logo; ?>" alt="" class="image"></a>
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <center>
                        <h2>Are you new to Peter Beans?</h2>
                    </center>
                    <center><button class="btn" id="sign-up-btn">Sign Up</button></center>
                </div>
                <a href="home.php"><img src="<?php echo $logo; ?>" alt="" class="image"></a>
            </div>
        </div>
    </div>

    <script src="app.js"></script>
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