<?php
include('connection.php');

// Get admin data from the database (no session check required)
$username = "admin"; // Hardcode the username if no login session is checked
$query = "SELECT * FROM admin_account WHERE username = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "<script>
            alert('Admin not found!');
            window.location.href = 'login.php';
          </script>";
    exit();
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address'])); // Update the address variable name to match the form field name

    // Check for duplicate email
    $check_email_query = "SELECT * FROM admin_account WHERE email = ? AND username != ?";
    $check_email_stmt = $conn->prepare($check_email_query);
    $check_email_stmt->bind_param("ss", $email, $username);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();

    if ($email_result->num_rows > 0) {
        echo "<script>alert('Email is already in use by another admin!');</script>";
    } else {
        // Update admin details
        $update_query = "UPDATE admin_account SET Fname = ?, Lname = ?, contactNum = ?, email = ?, addresss = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssss", $first_name, $last_name, $contact_number, $email, $address, $username);

        if ($update_stmt->execute()) {
            echo "<script>
                    alert('Profile updated successfully!');
                    window.location.href = 'adminprofile.php';
                  </script>";
        } else {
            echo "<script>alert('Failed to update profile!');</script>";
        }
    }
}

// Handle profile picture update
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "admin/"; // Directory to store uploaded files
    $file_name = uniqid() . "_" . basename($_FILES['profile_picture']['name']); // Unique file name
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type (only jpg, jpeg, png, gif allowed)
    $valid_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $valid_types)) {
        // Try to move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Update the database with the new profile picture path
            $update_picture_query = "UPDATE admin_account SET profile_picture = ? WHERE username = ?";
            $update_picture_stmt = $conn->prepare($update_picture_query);
            $update_picture_stmt->bind_param("ss", $target_file, $admin['username']);
            if ($update_picture_stmt->execute()) {
                echo "<script>
                        alert('Profile picture updated successfully!');
                        window.location.href = 'adminprofile.php';
                      </script>";
            } else {
                echo "<script>alert('Error updating profile picture in the database.');</script>";
            }
        } else {
            echo "<script>alert('Error uploading the file.');</script>";
        }
    } else {
        echo "<script>alert('Invalid file type. Please upload JPG, JPEG, PNG, or GIF.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="profile1.css">
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
                <div class="profile-photo">
                    <!-- Display profile picture or default image if not set -->
                    <img src="<?= !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : 'user.png' ?>?t=<?= time() ?>" alt="Profile Picture" id="profile-image">
                </div>
                <!-- Username Display Below Profile Picture -->
                <div class="username">
                    <p>@<?= htmlspecialchars($admin['username']) ?></p> <!-- Display username -->
                </div>
                <a href="admin.php">
                    <button class="back-btn">Back</button>
                </a>
                <form method="POST" enctype="multipart/form-data">
                    <label for="profile_picture" class="change-picture-btn">Change Profile Picture</label>
                    <input type="file" name="profile_picture" id="profile_picture" class="profile-picture-input" onchange="this.form.submit()">
                </form>
            </div>

            <!-- Right Section: Profile Information Form -->
            <div class="profile-right">
                <h1><?= htmlspecialchars($admin['Fname']) . ' ' . htmlspecialchars($admin['Lname']) ?></h1>
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-column">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($admin['Fname']) ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($admin['Lname']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($admin['contactNum']) ?>" required>
                        </div>
                    </div>

                    <!-- New Row for Address -->
                    <div class="form-row">
                        <div class="form-column">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($admin['addresss']) ?>" required>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="update-btn">Update Profile</button>
                    <a href="adminchangepass.php">
                        <button type="button" class="change-password-btn">Change Password</button>
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
