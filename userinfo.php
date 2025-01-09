<?php

$search_query = "";
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search']);
    $query = "SELECT userName, email, statuss, passwords, Addresss, ContactNum, profile_picture, Fname, Lname 
              FROM user_account 
              WHERE userName LIKE '%$search_query%' OR email LIKE '%$search_query%'";
} else {
    $query = "SELECT userName, email, statuss, passwords, Addresss, ContactNum, profile_picture, Fname, Lname 
              FROM user_account";
}
$result = mysqli_query($conn, $query);

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $admin_password = $_POST['admin_password'];
    $usernames = $_POST['usernames'];

    $password_query = "SELECT * FROM admin_account WHERE passwords = '$admin_password'";
    $password_result = mysqli_query($conn, $password_query);

    if (mysqli_num_rows($password_result) > 0) {
        if ($action == 'block') {
            $status = 'blocked';
        } elseif ($action == 'unblock') {
            $status = 'notBlock';
        } elseif ($action == 'delete') {
            foreach ($usernames as $username) {
                $delete_query = "DELETE FROM user_account WHERE userName = '$username'";
                mysqli_query($conn, $delete_query);
            }
            echo "User(s) deleted successfully!";
            exit();
        }

        if ($action != 'delete') {
            foreach ($usernames as $username) {
                $update_query = "UPDATE user_account SET statuss = '$status' WHERE userName = '$username'";
                mysqli_query($conn, $update_query);
            }
            echo ucfirst($action) . " successful!";
        }
    } else {
        echo "Invalid admin password!";
    }
    exit();
}
?>
<link rel="stylesheet" href="userinfo.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<style>
    .popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .popup-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
    }
</style>


<div class="popup" id="popup">
    <div class="popup-content">
        <h2>Admin Validation</h2>
        <p>Please enter the admin password to proceed:</p>
        <input type="password" id="adminPassword" placeholder="Enter Admin Password" />
        <button onclick="confirmAction()">Confirm</button>
        <button onclick="closePopup()">Cancel</button>
    </div>
</div>

<div class="content">
<div class="search-bar">
    <form method="POST">
        <input type="text" name="search" placeholder="Search by Username or Email" value="<?= htmlspecialchars($search_query); ?>">
        <button type="submit">Search</button>
    </form>
    <form action="generate_report.php" method="POST" style="display:inline;" target="_blank">
        <button type="submit" name="generate_report">Print Report</button> <!-- Print Report button -->
    </form>
</div>



    <form id="bulkActionForm">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Select</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['userName']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['statuss']); ?></td>
                        <td>
                            <button type="button" onclick="openPopup('<?= $row['userName']; ?>', 'delete')">Delete</button>
                            <button type="button" onclick="openPopup('<?= $row['userName']; ?>', '<?= $row['statuss'] === 'blocked' ? 'unblock' : 'block'; ?>')">
                                <?= $row['statuss'] === 'blocked' ? 'Unblock' : 'Block'; ?>
                            </button>
                        </td>
                        <td><input type="checkbox" name="usernames[]" value="<?= $row['userName']; ?>"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="button" onclick="handleBulkAction('block')">Block Selected</button>
        <button type="button" onclick="handleBulkAction('unblock')">Unblock Selected</button>
        <button type="button" onclick="handleBulkAction('delete')">Delete Selected</button>
    </form>
</div>

<script>
    let selectedAction = '';
    let selectedUsernames = [];

    function openPopup(username, action) {
        selectedUsernames = [username];
        selectedAction = action;
        document.getElementById('popup').style.display = 'flex';
    }

    function handleBulkAction(action) {
        const checkboxes = document.querySelectorAll('input[name="usernames[]"]:checked');
        selectedUsernames = Array.from(checkboxes).map(cb => cb.value);
        if (selectedUsernames.length > 0) {
            selectedAction = action;
            document.getElementById('popup').style.display = 'flex';
        } else {
            alert('No users selected.');
        }
    }

    function confirmAction() {
        const adminPassword = document.getElementById('adminPassword').value;

        if (!adminPassword) {
            alert('Admin password is required.');
            return;
        }

        const formData = new FormData();
        formData.append('action', selectedAction);
        formData.append('admin_password', adminPassword);
        selectedUsernames.forEach(username => formData.append('usernames[]', username));

        fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(response => {
                alert(response);
                window.location.reload();
            });
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
    }
</script>