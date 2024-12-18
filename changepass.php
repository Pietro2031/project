<?php
include('connection.php');
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if session is set
if (!isset($_SESSION['username'])) {
    die("Error: Session variable 'username' not set.");
}

// Get user data from the session or database
$username = $_SESSION['username'];
$query = "SELECT * FROM user_account WHERE userName = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>
            alert('User not found!');
            window.location.href = 'login.php';
          </script>";
    exit();
}

if (isset($_POST['change_password'])) {
    $current_password = htmlspecialchars(trim($_POST['current_password']));
    $new_password = htmlspecialchars(trim($_POST['new_password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Directly compare plain text passwords
    if ($current_password === $user['passwords']) {
        if ($new_password === $confirm_password) {
            // Update password in the database
            $update_query = "UPDATE user_account SET passwords = ? WHERE userName = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_password, $username);

            if ($update_stmt->execute()) {
                echo "<script>alert('Password changed successfully!'); window.location.href = 'profile.php';</script>";
            } else {
                echo "<script>alert('Error updating password: " . $update_stmt->error . "');</script>";
            }
        } else {
            echo "<script>alert('New password and confirm password do not match!');</script>";
        }
    } else {
        echo "<script>alert('Current password is incorrect!');</script>";
    }
}
$query = "SELECT * FROM user_account WHERE userName = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Debugging: Output the value of 'verified'
} else {
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="changepass.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <div class="profile-container">
        <!-- Cover Photo Section -->
        <div class="cover-photo">
            <h1 class="title1">My Profile</h1>
            <img src="coverimg.jpeg" alt="Cover Photo" class="cover-photo-img">
        </div>

        <!-- Profile Info Section -->
        <div class="profile-info-container">
            <!-- Left Section: Profile Picture -->
            <div class="profile-left">
                <a href="profile.php">
                    <button class="back-btn">Back</button>
                </a>
                <div class="profile-photo">
                    <!-- Display profile picture or default image if not set -->
                    <img src="<?= !empty($user['profile_picture']) ? $user['profile_picture'] : 'user.png' ?>" alt="Profile Picture" id="profile-image">
                </div>
                <div class="username">
    <p>@<?= htmlspecialchars($user['userName']) ?>
    <?php 
    if ($user['verified'] === 'verified'): 
        echo "<i class='fas fa-check-circle' style='color: #0056D2;'></i>"; 
    endif;
    ?>
    </p>
</div>

            </div>

            <!-- Right Section: Change Password Form -->
            <div class="profile-right">
                <h1><?= htmlspecialchars($user['Fname']) . ' ' . htmlspecialchars($user['Lname']) ?></h1>
                <form method="POST" class="change-password-form">
                    <div class="change-password-row">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="change-password-row">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="change-password-row">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="update-btn">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
