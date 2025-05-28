<?php
include 'auth.php';
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asset_id'])) {
    $asset_id = intval($_POST['asset_id']); // sanitize input
    $delete_sql = "DELETE FROM assets WHERE asset_id=?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $asset_id);

    if ($delete_stmt->execute()) {
        // Redirect back to assets page with success message
        header("Location: assets.php?deleted=1");
        exit;
    } else {
        // Error occurred
        header("Location: assets.php?deleted=0&error=" . urlencode($delete_stmt->error));
        exit;
    }
} else {
    // Invalid access
    header("Location: assets.php?deleted=0&error=InvalidRequest");
    exit;
}
?>
