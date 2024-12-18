<?php
include('connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>
            alert('You must be logged in to view this page!');
            window.location.href = 'login.php';
          </script>";
    exit();
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
// Fetch user data
$query = "SELECT * FROM user_account WHERE userName = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Debugging: Output the value of 'verified'
    echo "<p>Verified: " . htmlspecialchars($user['verified']) . "</p>";
} else {
    echo "<script>alert('User not found!'); window.location.href = 'login.php';</script>";
    exit();
}


// Handle profile update
if (isset($_POST['update_profile'])) {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $address2 = !empty($_POST['addresss2']) ? htmlspecialchars(trim($_POST['addresss2'])) : ''; // Handle optional address2

    // Check for duplicate email
    $check_email_query = "SELECT * FROM user_account WHERE email = ? AND userName != ?";
    $check_email_stmt = $conn->prepare($check_email_query);
    $check_email_stmt->bind_param("ss", $email, $username);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();

    if ($email_result->num_rows > 0) {
        echo "<script>alert('Email is already in use by another user!');</script>";
    } else {
        // Update user details
        $update_query = "UPDATE user_account SET Fname = ?, Lname = ?, ContactNum = ?, email = ?, Addresss = ?, addresss2 = ? WHERE userName = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssss", $first_name, $last_name, $contact_number, $email, $address, $address2, $username);

        if ($update_stmt->execute()) {
            echo "<script>
                    alert('Profile updated successfully!');
                    window.location.href = 'profile.php';
                  </script>";
        } else {
            echo "<script>alert('Failed to update profile!');</script>";
        }
    }
}
$userLoggedIn = false;
$userImage = 'user.png'; // Default profile image

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

// Handle profile picture update
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "uploads/"; // Directory to store uploaded files
    $file_name = uniqid() . "_" . basename($_FILES['profile_picture']['name']); // Unique file name
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type (only jpg, jpeg, png, gif allowed)
    $valid_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $valid_types)) {
        // Try to move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Update the database with the new profile picture path
            $update_picture_query = "UPDATE user_account SET profile_picture = ? WHERE userName = ?";
            $update_picture_stmt = $conn->prepare($update_picture_query);
            $update_picture_stmt->bind_param("ss", $target_file, $username);
            if ($update_picture_stmt->execute()) {
                // Success: Update the session with the new image path
                $_SESSION['profile_image'] = $target_file;
                echo "<script>alert('Profile picture updated successfully!'); window.location.href = 'profile.php';</script>";
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
    <title>User Profile</title>
    <link rel="stylesheet" href="profile.css">
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
    <i class="fas fa-check-circle"></i>
    <a href="otp.php">verification</a> 
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
                <div class="profile-photo">
                    <!-- Display profile picture or default image if not set -->
                    <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'user.png' ?>" alt="Profile Picture" id="profile-image">
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



                <form method="POST" enctype="multipart/form-data">
                    <label for="profile_picture" class="change-picture-btn">Change Profile Picture</label>
                    <input type="file" name="profile_picture" id="profile_picture" class="profile-picture-input" onchange="this.form.submit()">
                </form>
            </div>

            <!-- Right Section: Profile Information Form -->
            <div class="profile-right">
                <h1><?= htmlspecialchars($user['Fname']) . ' ' . htmlspecialchars($user['Lname']) ?></h1>
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-column">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['Fname']) ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['Lname']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($user['ContactNum']) ?>" required>
                        </div>
                    </div>

                    <!-- New Row for Address -->
                    <div class="form-row">
                        <div class="form-column">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['Addresss']) ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="address2">Address 2</label>
                            <input type="text" id="address2" name="addresss2" value="<?= htmlspecialchars($user['addresss2']) ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="update-btn">Update Profile</button>
                    <a href="changepass.php">
                        <button type="button" class="change-password-btn">Change Password</button>
                    </a>
                </form>
            </div>
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
