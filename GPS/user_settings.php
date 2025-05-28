<?php
include 'auth.php';       
include 'db_connect.php'; 
include 'user_theme.php';     

$popup_message = '';

// --- Change Username & Password ---
if (isset($_POST['save_credentials'])) {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];

    if ($new_username === '' || $new_password === '') {
        $popup_message = "Username and password cannot be empty.";
    } else {
        if ($new_username !== $_SESSION['user']) {
            $check = $conn->prepare("SELECT username FROM secondary_users WHERE username = ?");
            $check->bind_param("s", $new_username);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $popup_message = "Username already taken.";
                $check->close();
            } else {
                $check->close();
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $conn->begin_transaction();

                try {
                    $update_user = $conn->prepare("UPDATE secondary_users SET username=?, password=? WHERE username=?");
                    $update_user->bind_param("sss", $new_username, $hashed_password, $_SESSION['user']);
                    $update_user->execute();
                    $update_user->close();

                    $update_settings = $conn->prepare("UPDATE secondary_user_settings SET username=? WHERE username=?");
                    $update_settings->bind_param("ss", $new_username, $_SESSION['user']);
                    $update_settings->execute();
                    $update_settings->close();

                    $conn->commit();

                    $_SESSION['user'] = $new_username;
                    $popup_message = "Username and password updated successfully.";
                } catch (Exception $e) {
                    $conn->rollback();
                    $popup_message = "Error updating credentials: " . $e->getMessage();
                }
            }
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE secondary_users SET password=? WHERE username=?");
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
    /* Base Theme Variables */
    .theme-light {
        --input-bg: #ffffff;
        --input-border: #d1d5db;
        --text-color: #111827;
        background-color: #f9fafb;
        color: var(--text-color);
    }

    .theme-dark {
        --input-bg: #1f2937;
        --input-border: #4b5563;
        --text-color: #f9fafb;
        background-color: #1f2937;
        color: var(--text-color);
    }

    .theme-light .card {
        background-color: #ffffff;
        color: var(--text-color);
    }

    .theme-dark .card {
        background-color: #374151;
        color: var(--text-color);
    }

    input, select, textarea {
        background-color: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-color);
        padding: 0.5rem;
        border-radius: 0.375rem;
        width: 100%;
    }

    /* Popup message styling */
    .popup-message {
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        padding: 12px 20px;
        border-radius: 5px;
        color: #fff;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.5s ease;
        display: none;
    }

    .popup-message.success {
        background-color: #4caf50;
    }

    .popup-message.error {
        background-color: #f44336;
    }
</style>

</head>
<body class="min-h-screen theme-<?= strtolower($theme_pref) ?>">
<?php include 'user_header.php'; ?>

<div id="popupMessage" class="popup-message"></div>

<main class="pt-32 flex justify-center px-4">
    <div class="w-full max-w-4xl space-y-10">
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
