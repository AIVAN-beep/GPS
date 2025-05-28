<?php
include 'auth.php';       
include 'db_connect.php'; 
include 'user_theme.php';      

$popup_message = '';

// -- Handle Form Submission --
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Save System Preferences ---
    if (isset($_POST['save_preferences'])) {
        $theme = $_POST['theme'];
        $refresh_rate = $_POST['refresh_rate'];
        $default_view = $_POST['default_view'];

        $stmt = $conn->prepare("INSERT INTO secondary_user_settings (username, theme, refresh_rate, default_view)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE theme=?, refresh_rate=?, default_view=?");
        $stmt->bind_param("sssssss",
            $_SESSION['user'], $theme, $refresh_rate, $default_view,
            $theme, $refresh_rate, $default_view);
        if ($stmt->execute()) {
            $_SESSION['theme'] = $theme;  // update session theme immediately
            $popup_message = "System preferences saved successfully.";
        } else {
            $popup_message = "Error saving preferences: " . $stmt->error;
        }
        $stmt->close();
    }

    // --- Change Username & Password ---
    if (isset($_POST['save_credentials'])) {
        $new_username = trim($_POST['username']);
        $new_password = $_POST['password'];

        if ($new_username === '' || $new_password === '') {
            $popup_message = "Username and password cannot be empty.";
        } else {
            // Check if username already exists (if changed)
            if ($new_username !== $_SESSION['user']) {
                $check = $conn->prepare("SELECT username FROM users WHERE username = ?");
                $check->bind_param("s", $new_username);
                $check->execute();
                $check->store_result();
                if ($check->num_rows > 0) {
                    $popup_message = "Username already taken.";
                    $check->close();
                } else {
                    $check->close();
                    // Update username and password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Begin transaction to update username references in other tables
                    $conn->begin_transaction();

                    try {
                        // Update users table
                        $update_user = $conn->prepare("UPDATE users SET username=?, password=? WHERE username=?");
                        $update_user->bind_param("sss", $new_username, $hashed_password, $_SESSION['user']);
                        $update_user->execute();
                        $update_user->close();

                        // Update user_settings (username is PK)
                        $update_settings = $conn->prepare("UPDATE user_settings SET username=? WHERE username=?");
                        $update_settings->bind_param("ss", $new_username, $_SESSION['user']);
                        $update_settings->execute();
                        $update_settings->close();

                        // Add any other tables referencing username here!

                        $conn->commit();

                        $_SESSION['user'] = $new_username;
                        $popup_message = "Username and password updated successfully.";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $popup_message = "Error updating credentials: " . $e->getMessage();
                    }
                }
            } else {
                // Username unchanged, update only password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE users SET password=? WHERE username=?");
                $update_pass->bind_param("ss", $hashed_password, $_SESSION['user']);
                if ($update_pass->execute()) {
                    $popup_message = "Password updated successfully.";
                } else {
                    $popup_message = "Error updating password: " . $update_pass->error;
                }
                $update_pass->close();
            }
        }
    }
}

// -- Load existing user preferences --
$stmt = $conn->prepare("SELECT theme, refresh_rate, default_view FROM secondary_user_settings WHERE username=?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();
$user_settings = $result->fetch_assoc() ?? ['theme'=>'Light', 'refresh_rate'=>'30s', 'default_view'=>'Dashboard'];
$stmt->close();

$theme_pref = $user_settings['theme'];
$refresh_rate_pref = $user_settings['refresh_rate'];
$default_view_pref = $user_settings['default_view'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Settings & Configuration Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .theme-light {
        --bg-color: #f3f4f6;
        --text-color: #1f2937;
        --card-bg: #ffffff;
        --input-bg: #ffffff;
        --input-border: #d1d5db;
        color: black;
    }
    .theme-dark {
        --bg-color: #1f2937;
        --text-color: #f9fafb;
        --card-bg: #374151;
        --input-bg: #4b5563;
        --input-border: #6b7280;
        color: white;
    }
    body {
        background-color: var(--bg-color);
        color: var(--text-color);
    }
    .card {
        background-color: var(--card-bg);
        color: var(--text-color);
    }
    input, select {
        background-color: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-color);
    }
    .popup-message {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        display: none;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }
    .popup-message.success { background-color: #4caf50; }
    .popup-message.error { background-color: #f44336; }
</style>
</head>
<body class="min-h-screen theme-<?= strtolower($theme_pref) ?>">
<?php include 'header.php';  ?>

<div id="popupMessage" class="popup-message"></div>

<main class="pt-32 flex justify-center px-4">
    <div class="w-full max-w-4xl space-y-10">

        <!-- System Preferences -->
        <div class="card p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Settings & Configuration Panel</h2>

            <form method="POST" class="mb-6">
                <h3 class="text-xl font-semibold mb-2">System Preferences</h3>
                <select name="theme" id="themeSelector" class="w-full p-2 border rounded mb-2" onchange="applyTheme(this.value)">
                    <option value="Light" <?= ($theme_pref === 'Light') ? 'selected' : '' ?>>Light</option>
                    <option value="Dark" <?= ($theme_pref === 'Dark') ? 'selected' : '' ?>>Dark</option>
                    <option value="Auto" <?= ($theme_pref === 'Auto') ? 'selected' : '' ?>>Auto</option>
                </select>
                <select name="refresh_rate" class="w-full p-2 border rounded mb-2">
                    <option value="30s" <?= ($refresh_rate_pref === '30s') ? 'selected' : '' ?>>Every 30s</option>
                    <option value="1m" <?= ($refresh_rate_pref === '1m') ? 'selected' : '' ?>>Every 1 min</option>
                    <option value="5m" <?= ($refresh_rate_pref === '5m') ? 'selected' : '' ?>>Every 5 min</option>
                </select>
                <select name="default_view" class="w-full p-2 border rounded mb-4">
                    <option value="Dashboard" <?= ($default_view_pref === 'Dashboard') ? 'selected' : '' ?>>Dashboard</option>
                    <option value="Asset List" <?= ($default_view_pref === 'Asset List') ? 'selected' : '' ?>>Asset List</option>
                    <option value="Reports" <?= ($default_view_pref === 'Reports') ? 'selected' : '' ?>>Reports</option>
                </select>
                <button type="submit" name="save_preferences" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Preferences</button>
            </form>
        </div>

        <!-- Username & Password -->
        <div class="card p-6 rounded-lg shadow-lg">
            <h3 class="text-xl font-semibold mb-4">Change Username & Password</h3>
            <form method="POST">
                <input type="text" name="username" placeholder="New username" value="<?= htmlspecialchars($_SESSION['user']) ?>" class="w-full p-2 border rounded mb-2" required>
                <input type="password" name="password" placeholder="New password" class="w-full p-2 border rounded mb-2" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" class="w-full p-2 border rounded mb-4" required>
                <button type="submit" name="save_credentials" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update Credentials</button>
            </form>
        </div>
    </div>
</main>

<script>
<?php if ($popup_message): ?>
    const msg = document.getElementById('popupMessage');
    msg.textContent = <?= json_encode($popup_message) ?>;
    msg.classList.add(<?= (strpos($popup_message, 'Error') !== false || strpos($popup_message, 'cannot') !== false) ? "'error'" : "'success'" ?>);
    msg.style.display = 'block';
    setTimeout(() => { msg.style.opacity = '0'; setTimeout(() => msg.style.display = 'none', 500); }, 5000);
<?php endif; ?>

function applyTheme(theme) {
    const body = document.body;
    body.classList.remove('theme-light', 'theme-dark');

    if (theme === 'Auto') {
        const hour = new Date().getHours();
        theme = (hour >= 18 || hour < 6) ? 'Dark' : 'Light';
    }

    body.classList.add('theme-' + theme.toLowerCase());
}
</script>

</body>
</html>