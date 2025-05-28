<?php
include 'auth.php';
include 'db_connect.php';
include 'user_theme.php';

function set_message($text, $type = 'success') {
    $_SESSION['message'] = $text;
    $_SESSION['message_type'] = $type;
}

// Handle deploying
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deploy'])) {
    $asset_id = $_POST['asset_id'];
    $stmt = $conn->prepare("UPDATE assets SET asset_status = 'Deployed' WHERE asset_id = ?");
    $stmt->bind_param("i", $asset_id);
    if ($stmt->execute()) {
        set_message('Asset successfully deployed.', 'success');
    } else {
        set_message('Failed to deploy asset.', 'error');
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle disposing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispose'])) {
    $asset_id = $_POST['asset_id'];
    $stmt = $conn->prepare("UPDATE assets SET asset_status = 'Disposed' WHERE asset_id = ?");
    $stmt->bind_param("i", $asset_id);
    if ($stmt->execute()) {
        set_message('Asset successfully disposed.', 'success');
    } else {
        set_message('Failed to dispose asset.', 'error');
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Filters
$search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
$category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

$sql = "SELECT * FROM assets WHERE asset_name LIKE ? AND category LIKE ? AND current_status LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$categoryParam = "%$category%";
$statusParam = "%$status%";
$stmt->bind_param('sss', $searchParam, $categoryParam, $statusParam);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Asset Management - Deploy & Dispose</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .theme-light {
            --bg-color: #f9fafb;
            --container-bg: #ffffff;
            --text-color: #111827;
            --input-bg: #ffffff;
            --input-border: #d1d5db;
            --input-text: #111827;
        }
        .theme-dark {
            --bg-color: #1f2937;
            --container-bg: #374151;
            --text-color: #ffffff;
            --input-bg: #4b5563;
            --input-border: #6b7280;
            --input-text: #ffffff;
        }
        body.theme-light,
        body.theme-dark {
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .container {
            background-color: var(--container-bg);
            color: var(--text-color);
            border-radius: 0.5rem;
            padding: 1rem;
        }
        input[type="text"],
        select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border: 1px solid var(--input-border);
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }
        input::placeholder {
            color: var(--input-text);
            opacity: 0.6;
        }
        /* Removed hover effects from table rows */
    </style>
</head>
<body class="min-h-screen <?= htmlspecialchars($theme_class) ?>">
<?php include 'user_header.php'; ?>

<!-- Main Container -->
<div class="flex justify-center mt-8 px-4">
    <div class="w-full max-w-6xl container">
        <h1 class="text-2xl font-normal mb-4 text-center">Asset Management</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-4 p-4 rounded 
                <?= $_SESSION['message_type'] === 'success' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' 
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Assets Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 dark:border-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Asset Name</th>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Category</th>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Current Status</th>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Last Location</th>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Assigned To</th>
                        <th class="px-4 py-2 text-left font-normal border border-gray-300 dark:border-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600"><?= htmlspecialchars($row['asset_name']) ?></td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600"><?= htmlspecialchars($row['category']) ?></td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600"><?= htmlspecialchars($row['current_status']) ?></td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600"><?= htmlspecialchars($row['last_known_location']) ?></td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600"><?= htmlspecialchars($row['assigned_to']) ?></td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">
                                    <div class="flex gap-2">
                                        <?php
                                            $status = strtolower($row['current_status']);
                                            $isDeployed = $status === 'deployed';
                                            $isDisposed = $status === 'disposed';
                                        ?>
                                        <!-- Deploy Button -->
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to deploy this asset?');">
                                            <input type="hidden" name="asset_id" value="<?= htmlspecialchars($row['asset_id']) ?>">
                                            <button type="submit" name="deploy"
                                                class="px-3 py-1 rounded text-white font-normal <?= $isDeployed || $isDisposed ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 dark:hover:bg-green-500' ?>"
                                                <?= $isDeployed || $isDisposed ? 'disabled' : '' ?>>
                                                Deploy
                                            </button>
                                        </form>

                                        <!-- Dispose Button -->
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to dispose this asset?');">
                                            <input type="hidden" name="asset_id" value="<?= htmlspecialchars($row['asset_id']) ?>">
                                            <button type="submit" name="dispose"
                                                class="px-3 py-1 rounded text-white font-normal <?= $isDisposed ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700 dark:hover:bg-red-500' ?>"
                                                <?= $isDisposed ? 'disabled' : '' ?>>
                                                Dispose
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 border border-gray-300 dark:border-gray-600">No assets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const messageBox = document.querySelector('div.mb-4.p-4.rounded');
    if (messageBox) {
      setTimeout(() => {
        messageBox.style.transition = 'opacity 0.8s ease';
        messageBox.style.opacity = '0';
        setTimeout(() => messageBox.remove(), 800);
      }, 5000);
    }
  });
</script>

</body>
</html>
