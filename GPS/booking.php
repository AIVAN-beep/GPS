<?php
include 'auth.php';
include 'db_connect.php';
include 'theme.php';

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset = $_POST['asset'];

    // Validate input
    if (!empty($asset)) {
        $stmt = $conn->prepare("INSERT INTO bookings (username, asset) VALUES (?, ?)");
        $stmt->bind_param("ss", $_SESSION['user'], $asset);
        
        if ($stmt->execute()) {
            $popup_message = "<p class='text-green-500'>Booking Confirmed for: $asset</p>";
        } else {
            $popup_message = "<p class='text-red-500'>Error booking asset. Please try again.</p>";
        }
        
        $stmt->close();
    } else {
        $popup_message = "<p class='text-red-500'>Asset name is required.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Booking</title>
    <style>
        .theme-light {
            background-color: #f4f4f4;
        }

        /* Dark theme styles */
        .theme-dark {
            background-color: #1f2937;
        }

        /* Fixed header at the top of the page */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        body {
            font-family: 'Poppins', sans-serif;
            color: black; /* Set text color to black */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen <?php echo $theme_class; ?>">

 <!-- Header Section with Navigation at the Top -->
    <header class="shadow-md w-full" style="background-color: #1f1f2e;">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <!-- Logo or User Avatar -->
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center text-xl font-semibold">
                    <span><?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?></span> <!-- Initials of user -->
                </div>
                <h1 class="text-3xl font-semibold text-white">GPS TRACKING SYSTEM</h1>
            </div>
            
           <?php include 'nav.php';?>
    </header>

<div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center mt-20">
    <h2 class="text-2xl font-bold mb-4">Book an Asset</h2>

    <?php if (!empty($popup_message)): ?>
        <div class="mb-4"><?php echo $popup_message; ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700">Asset Name</label>
            <input type="text" name="asset" placeholder="Enter Asset Name" required
                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">Book</button>
    </form>
</div>
</body>
</html>
