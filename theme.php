<?php


include('connection.php');


ini_set('display_errors', 1);
error_reporting(E_ALL);





if (isset($_POST['save_logo']) && isset($_FILES['logo'])) {

    $logo = $_FILES['logo'];


    if ($logo['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $logo['tmp_name'];
        $file_name = basename($logo['name']);
        $file_size = $logo['size'];
        $file_type = $logo['type'];


        $upload_dir = 'uploads/logos/';


        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }


        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp_name, $file_path)) {

            $update_logo_query = "UPDATE theme SET logo = ? WHERE id = 1";
            $stmt = $conn->prepare($update_logo_query);
            if ($stmt) {
                $stmt->bind_param("s", $file_path);
                $stmt->execute();
                echo "<p>Logo updated successfully.</p>";
            } else {
                echo "<p>Error updating logo in database.</p>";
            }
        } else {
            echo "<p>Error uploading the logo file.</p>";
        }
    } else {
        echo "<p>There was an error with the file upload.</p>";
    }
}



$query_logo = "SELECT logo FROM theme WHERE id = 1";
$result_logo = $conn->query($query_logo);
if ($result_logo->num_rows > 0) {
    $theme = $result_logo->fetch_assoc();
    $current_logo = $theme['logo'];
} else {
    $current_logo = 'default-logo.png';
}


if (isset($_POST['save_primary']) || isset($_POST['save_font'])) {

    if (isset($_POST['primary_color']) && isset($_POST['secondary_color'])) {
        $primary_color = $_POST['primary_color'];
        $secondary_color = $_POST['secondary_color'];

        $update_query = "UPDATE theme SET primary_color = ?, secondary_color = ? WHERE id = 1";
        $stmt = $conn->prepare($update_query);
        if ($stmt) {
            $stmt->bind_param("ss", $primary_color, $secondary_color);
            $stmt->execute();
            echo "<p>Background colors updated successfully.</p>";
        } else {
            echo "<p>Error updating background colors.</p>";
        }
    }

    if (isset($_POST['font_color'])) {
        $font_color = $_POST['font_color'];


        if (empty($font_color)) {
            $font_color = '#9E9B76';
        }

        $update_query = "UPDATE theme SET font_color = ? WHERE id = 1";
        $stmt = $conn->prepare($update_query);
        if ($stmt) {
            $stmt->bind_param("s", $font_color);
            $stmt->execute();
            echo "<p>Font color updated successfully.</p>";
        } else {
            echo "<p>Error updating font color.</p>";
        }
    }
}


if (isset($_POST['reset_background'])) {
    $default_primary = '#fff';
    $default_secondary = '#C9C9A6';

    $reset_query = "UPDATE theme SET primary_color = ?, secondary_color = ? WHERE id = 1";
    $stmt = $conn->prepare($reset_query);
    if ($stmt) {
        $stmt->bind_param("ss", $default_primary, $default_secondary);
        $stmt->execute();
        echo "<p>Background colors reset to default.</p>";
    } else {
        echo "<p>Error resetting background colors.</p>";
    }
}


if (isset($_POST['reset_font'])) {
    $default_font = '#9E9B76';

    $reset_query = "UPDATE theme SET font_color = ? WHERE id = 1";
    $stmt = $conn->prepare($reset_query);
    if ($stmt) {
        $stmt->bind_param("s", $default_font);
        $stmt->execute();
        echo "<p>Font color reset to default.</p>";
    } else {
        echo "<p>Error resetting font color.</p>";
    }
}


$query = "SELECT * FROM theme WHERE id = 1";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    $theme = $result->fetch_assoc();
    $primary_color = isset($theme['primary_color']) ? $theme['primary_color'] : '#fff';
    $secondary_color = isset($theme['secondary_color']) ? $theme['secondary_color'] : '#C9C9A6';
    $font_color = isset($theme['font_color']) ? $theme['font_color'] : '#9E9B76';
} else {
    echo "<p>Theme not found.</p>";

    $primary_color = '#fff';
    $secondary_color = '#C9C9A6';
    $font_color = '#9E9B76';
}


if (isset($_POST['upload_slideshow']) && isset($_FILES['slideshow_image'])) {

    $slideshow_image = $_FILES['slideshow_image'];


    if ($slideshow_image['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $slideshow_image['tmp_name'];
        $file_name = basename($slideshow_image['name']);
        $file_size = $slideshow_image['size'];
        $file_type = $slideshow_image['type'];


        $upload_dir = 'uploads/slideshow/';


        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }


        $file_path = $upload_dir . $file_name;


        $check_query = "SELECT COUNT(*) FROM slideshow WHERE slideshow_path = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $file_path);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();


        if ($count == 0) {
            if (move_uploaded_file($file_tmp_name, $file_path)) {

                $insert_query = "INSERT INTO slideshow (slideshow_path) VALUES (?)";
                $stmt = $conn->prepare($insert_query);
                if ($stmt) {
                    $stmt->bind_param("s", $file_path);
                    $stmt->execute();
                    echo "<p>Slideshow image uploaded successfully.</p>";
                } else {
                    echo "<p>Error inserting slideshow image into database.</p>";
                }
            } else {
                echo "<p>Error uploading the slideshow image file.</p>";
            }
        } else {
            echo "<p>This image has already been uploaded.</p>";
        }
    } else {
        echo "<p>There was an error with the file upload.</p>";
    }
}


if (isset($_POST['delete_slideshow']) && isset($_POST['selected_slideshow'])) {
    $selected_slideshow_id = $_POST['selected_slideshow'];


    $delete_query = "SELECT slideshow_path FROM slideshow WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    if ($stmt) {
        $stmt->bind_param("i", $selected_slideshow_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $slideshow = $result->fetch_assoc();
            $slideshow_path = $slideshow['slideshow_path'];


            if (file_exists($slideshow_path)) {
                unlink($slideshow_path);
            }


            $delete_query = "DELETE FROM slideshow WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $selected_slideshow_id);
            $stmt->execute();
            echo "<p>Slideshow deleted successfully.</p>";
        } else {
            echo "<p>Slideshow not found.</p>";
        }
    }
}


$query_slideshow = "SELECT * FROM slideshow";
$result_slideshow = $conn->query($query_slideshow);
$slideshows = [];
if ($result_slideshow->num_rows > 0) {
    while ($row = $result_slideshow->fetch_assoc()) {
        $slideshows[] = $row;
    }
} else {
    $slideshows = [];
}


?>
    <link rel="stylesheet" href="theme.css">


    <div class="theme-editor">
        <!-- Background Color Section -->
        <div class="theme-section">
            <h2>Background Color</h2>
            <form method="POST">
                <div class="color-picker">
                    <label for="primary-color">Primary Color:</label>
                    <input type="color" id="primary-color" name="primary_color" value="<?= htmlspecialchars($primary_color) ?>">
                </div>
                <div class="color-picker">
                    <label for="secondary-color">Secondary Color:</label>
                    <input type="color" id="secondary-color" name="secondary_color" value="<?= htmlspecialchars($secondary_color) ?>">
                </div>
                <div class="save-button">
                    <button type="submit" name="save_primary">Save Background Colors</button>
                </div>
                <!-- Reset Background Color -->
                <div class="reset-button">
                    <button type="submit" name="reset_background">Reset Background to Default</button>
                </div>
            </form>
        </div>

        <!-- Font Color Section -->
        <div class="theme-section">
            <h2>Font Color</h2>
            <form method="POST">
                <div class="color-picker">
                    <label for="font-color">Font Color:</label>
                    <input type="color" id="font-color" name="font_color" value="<?= htmlspecialchars($font_color) ?>">
                </div>
                <div class="save-button">
                    <button type="submit" name="save_font">Save Font Color</button>
                </div>
                <!-- Reset Font Color -->
                <div class="reset-button">
                    <button type="submit" name="reset_font">Reset Font Color to Default</button>
                </div>
            </form>
        </div>

        <!-- Logo Section (placed below the font color section) -->
        <div class="theme-section">
            <h2>Current Logo</h2>
            <div class="current-logo">
                <img src="<?= !empty($current_logo) ? htmlspecialchars($current_logo) : 'default-logo.png' ?>" alt="Current Logo" class="logo-image">
            </div>
            <h3>Upload New Logo</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="logo-upload">
                    <label for="logo" class="file-upload-label"></label>
                    <input type="file" id="logo" name="logo" accept="image/*" class="file-upload-input">
                </div>

                <div class="save-button">
                    <button type="submit" name="save_logo">Save Logo</button>
                </div>
            </form>
        </div>
        <!-- Slideshow Section -->
        <div class="theme-section">
            <h2>Slideshow</h2>

            <!-- Display Current Slideshows -->
            <div class="current-slides">
                <h3>Current Slideshows</h3>
                <div class="slideshow-container">
                    <?php foreach ($slideshows as $slideshow) : ?>
                        <div class="slideshow-item">
                            <img src="<?= htmlspecialchars($slideshow['slideshow_path']) ?>" alt="Slideshow Image" class="slideshow-image">
                            <form method="POST">
                                <input type="hidden" name="selected_slideshow" value="<?= $slideshow['id'] ?>">
                                <button type="submit" name="delete_slideshow" class="delete-button">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Upload New Slideshow Image -->
            <h3>Upload New Slideshow Image</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="slideshow-upload">
                    <label for="slideshow_image" class="file-upload-label"></label>
                    <input type="file" id="slideshow_image" name="slideshow_image" accept="image/*" class="file-upload-input">
                </div>

                <div class="save-button">
                    <button type="submit" name="upload_slideshow">Create Slideshow</button>
                </div>
            </form>
        </div>

    </div>
