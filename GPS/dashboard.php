<?php 
include 'auth.php'; 
include 'db_connect.php';
include 'user_theme.php'; // Provides $theme_class

// Fetch all assets with valid locations
$asset_locations = [];
$sql = "SELECT asset_name, last_known_location FROM assets WHERE last_known_location IS NOT NULL AND last_known_location != ''";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $loc = explode(',', $row['last_known_location']);
        if (count($loc) == 2) {
            $lat = floatval(trim($loc[0]));
            $lng = floatval(trim($loc[1]));
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $asset_locations[] = [
                    'name' => $row['asset_name'],
                    'lat' => $lat,
                    'lng' => $lng
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Asset Locations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Enable Tailwind dark mode class strategy
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .theme-light {
            --bg-color: #f9fafb;
            --container-bg: #ffffff;
            --text-color: #111827;
            --input-bg: #ffffff;
            --input-border: #d1d5db;
            --input-text: #111827;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .theme-dark {
            --bg-color: #1f2937;
            --container-bg: #374151;
            --text-color: white;
            --input-bg: #4b5563;
            --input-border: #6b7280;
            --input-text: #ffffff;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars($theme_class); ?>">

<?php include 'header.php'; ?>

<div class="max-w-3xl mx-auto mt-8 p-4 rounded shadow"
     style="background-color: var(--container-bg); color: var(--text-color);">

    <h1 class="text-2xl font-bold mb-4">Assets with Last Known Location</h1>

    <?php if (count($asset_locations) > 0): ?>
        <button
            onclick="openAllInGoogleMaps()"
            class="w-full px-4 py-2 mb-4 bg-green-600 text-white rounded hover:bg-green-700 transition"
        >
            View All Asset Pins on Google Maps
        </button>
    <?php else: ?>
        <p style="color: var(--text-color);" class="text-gray-700 dark:text-gray-300">No assets with location data found.</p>
    <?php endif; ?>
</div>

<script>
    function openAllInGoogleMaps() {
        const locations = <?php echo json_encode($asset_locations); ?>;
        if (locations.length === 0) return;

        const path = locations.map(loc => `${loc.lat},${loc.lng}`).join('/');
        const url = `https://www.google.com/maps/dir/${path}`;
        window.open(url, '_blank');
    }
</script>

</body>
</html>
