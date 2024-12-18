<?php
include('connection.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hardcode the admin username (or you could get this value from somewhere else)
$username = "admin"; // Change this to a valid admin username if necessary

// Get admin data from the database
$query = "SELECT * FROM admin_account WHERE username = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc(); // Fetch admin data
} else {
    echo "<script>
            alert('Admin not found!');
            window.location.href = 'login.php';
          </script>";
    exit();
}

if (isset($_POST['change_password'])) {
    $current_password = htmlspecialchars(trim($_POST['current_password']));
    $new_password = htmlspecialchars(trim($_POST['new_password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Trim the stored password to remove any leading/trailing spaces
    $stored_password = trim($admin['passwords']);

    // Verify current password using plain text comparison
    if ($current_password !== $stored_password) {
        echo "<script>alert('Current password is incorrect!');</script>";
    } elseif ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match!');</script>";
    } else {
        // Update plain text password in the database
        $update_query = "UPDATE admin_account SET passwords = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $new_password, $username);

        if ($update_stmt->execute()) {
            echo "<script>alert('Password changed successfully!'); window.location.href = 'adminprofile.php';</script>";
        } else {
            die("Error updating password: " . $update_stmt->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="changepass.css">
</head>
<body>
    <div class="profile-container">
        <!-- Cover Photo Section -->
        <div class="cover-photo">
            <h1 class="title1">Admin Profile</h1>
            <img src="Untitled design (2).png" alt="Cover Photo" class="cover-photo-img">
        </div>

        <!-- Profile Info Section -->
        <div class="profile-info-container">
            <!-- Left Section: Profile Picture -->
            <div class="profile-left">
                <a href="adminprofile.php">
                    <button class="back-btn">Back</button>
                </a>
                <div class="profile-photo">
                    <!-- Display profile picture or default image if not set -->
                    <img src="<?= !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : 'user.png' ?>" alt="Profile Picture" id="profile-image">
                </div>
                <div class="username">
                    <p> @<?= htmlspecialchars($admin['username']) ?></p> <!-- Display username -->
                </div>
            </div>

            <!-- Right Section: Change Password Form -->
            <div class="profile-right">
                <h1><?= htmlspecialchars($admin['Fname']) . ' ' . htmlspecialchars($admin['Lname']) ?></h1>
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
