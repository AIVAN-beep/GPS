<?php
session_start();
include('db_connect.php');
include 'theme.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch Data
$query_access_attempts = "SELECT * FROM access_logs WHERE status = 'failed' ORDER BY timestamp DESC";
$result_access_attempts = mysqli_query($conn, $query_access_attempts) or die('Query error: ' . mysqli_error($conn));

$query_missing_assets = "SELECT * FROM assets WHERE status = 'missing' ORDER BY last_seen DESC";
$result_missing_assets = mysqli_query($conn, $query_missing_assets) or die('Query error: ' . mysqli_error($conn));

$query_resolved_incidents = "SELECT * FROM incidents WHERE status = 'resolved' ORDER BY resolved_date DESC";
$result_resolved_incidents = mysqli_query($conn, $query_resolved_incidents) or die('Query error: ' . mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: white;
            font-size: 15px;
        }
        h1{
            color: black;
        }
    </style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">

    <!-- Header at the very top -->
    <header class="shadow-md w-full" style="background-color: #1f1f2e;">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-semibold text-white">GPS TRACKING SYSTEM</h1>
            </div>
            <?php include 'nav.php'; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6 text-center">
        <h1 class="text-3xl font-bold mb-6">Security Report</h1>

        <!-- Unauthorized Access Attempts -->
        <div class="bg-gray-700 p-4 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-semibold mb-4">Unauthorized Access Attempts</h2>
            <?php if (mysqli_num_rows($result_access_attempts) > 0): ?>
                <table class="table-auto w-full text-sm text-left text-gray-400">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">User</th>
                            <th class="px-4 py-2">IP Address</th>
                            <th class="px-4 py-2">Timestamp</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_access_attempts)): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['user']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['ip_address']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['timestamp']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-400">No unauthorized access attempts found.</p>
            <?php endif; ?>
        </div>

        <!-- Missing Assets -->
        <div class="bg-gray-700 p-4 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-semibold mb-4">Missing Assets</h2>
            <?php if (mysqli_num_rows($result_missing_assets) > 0): ?>
                <table class="table-auto w-full text-sm text-left text-gray-400">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Asset ID</th>
                            <th class="px-4 py-2">Asset Name</th>
                            <th class="px-4 py-2">Last Seen</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_missing_assets)): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['asset_id']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['last_seen']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-400">No missing assets found.</p>
            <?php endif; ?>
        </div>

        <!-- Resolved Incidents -->
        <div class="bg-gray-700 p-4 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Resolved Incidents</h2>
            <?php if (mysqli_num_rows($result_resolved_incidents) > 0): ?>
                <table class="table-auto w-full text-sm text-left text-gray-400">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Incident ID</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2">Resolved Date</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_resolved_incidents)): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['incident_id']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['resolved_date']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-400">No resolved incidents found.</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
