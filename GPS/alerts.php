<?php 
include 'auth.php'; 
include 'db_connect.php';
include 'theme.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome CDN -->
    <title>Alerts & Notifications</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .theme-light {
            background-color: #f4f4f4;
        }

        /* Dark theme styles */
        .theme-dark {
            background-color: #1f2937;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

    </style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">
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
    <!-- Main Content -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-7xl mx-auto mt-8">
        <h2 class="text-2xl font-semibold text-gray-700">Automated Alerts Panel</h2>

        <!-- Automated Alerts Section -->
        <div class="mt-6">
            <ul class="space-y-4">
                <li class="flex items-center text-lg text-gray-800">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                    Unauthorized asset movements
                </li>
                <li class="flex items-center text-lg text-gray-800">
                    <i class="fas fa-map-marker-alt text-red-500 mr-3"></i>
                    Assets leaving geofenced areas
                </li>
                <li class="flex items-center text-lg text-gray-800">
                    <i class="fas fa-battery-quarter text-blue-500 mr-3"></i>
                    Low battery or device malfunction
                </li>
                <li class="flex items-center text-lg text-gray-800">
                    <i class="fas fa-tachometer-alt text-green-500 mr-3"></i>
                    Unusual activity patterns
                </li>
            </ul>
        </div>

        <!-- Alert History & Log Section -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-700">Alert History & Log</h3>
            <p class="text-gray-600">A history of past alerts for security review can be accessed here.</p>
            <table class="w-full mt-4 table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Alert Type</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-3">2025-04-01</td>
                        <td class="p-3">Unauthorized asset movement</td>
                        <td class="p-3 text-yellow-500">Triggered</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-3">2025-03-29</td>
                        <td class="p-3">Geofence breach</td>
                        <td class="p-3 text-red-500">Resolved</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Alert Settings Section -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-700">Alert Settings</h3>
            <p class="text-gray-600">Configure your alert preferences (email, SMS, or in-app notifications).</p>
            <form action="#" method="post">
                <div class="mt-4">
                    <label for="notification-method" class="block text-gray-700">Notification Method</label>
                    <select id="notification-method" name="notification-method" class="mt-2 w-full border border-gray-300 p-2 rounded-lg">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="in-app">In-App</option>
                    </select>
                </div>
                <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Save Settings</button>
            </form>
        </div>
    </div>

</body>
</html>
