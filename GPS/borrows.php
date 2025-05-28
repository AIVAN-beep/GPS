<?php
include 'auth.php';
include 'db_connect.php';

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $asset_id = $_POST['asset_id'];
    $borrower_name = $conn->real_escape_string($_POST['borrower_name']);
    $borrow_date = $_POST['borrow_date'];
    $expected_return_date = $_POST['expected_return_date'];

    // Prepare the insert query for the borrowed_assets table
    $insert_query = "INSERT INTO borrowed_assets (asset_id, borrower_name, borrow_date, expected_return_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isss", $asset_id, $borrower_name, $borrow_date, $expected_return_date);

    if ($stmt->execute()) {
        // Update the asset status to 'Borrowed' and set the borrower's name
        $update_asset_query = "UPDATE assets SET borrow_status = 'Borrowed', borrower_name = ? WHERE asset_id = ?";
        $update_stmt = $conn->prepare($update_asset_query);
        $update_stmt->bind_param("si", $borrower_name, $asset_id);
        $update_stmt->execute();

        echo "<script>alert('Asset borrowed successfully.'); window.location.href = 'borrow.php';</script>";
    } else {
        echo "<script>alert('Error borrowing asset. Please try again.');</script>";
    }
}  }
}
?>
