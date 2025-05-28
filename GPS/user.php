<?php
include 'auth.php';
include 'db_connect.php';
include 'user_theme.php';

// Get search and filters safely
$search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
$category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
$asset_stat = isset($_POST['asset_status']) ? $conn->real_escape_string($_POST['asset_status']) : '';

// Prepare SQL with filters
$sql = "SELECT category, current_status, asset_status, COUNT(*) AS asset_count
        FROM assets 
        WHERE asset_name LIKE ? AND category LIKE ? AND current_status LIKE ? AND asset_status LIKE ?
        GROUP BY category, current_status, asset_status";

$stmt = $conn->prepare($sql);

$searchParam = "%$search%";
$categoryParam = "%$category%";
$statusParam = "%$status%";
$asset_statusParam = "%$asset_stat%";

$stmt->bind_param('ssss', $searchParam, $categoryParam, $statusParam, $asset_statusParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Asset Management</title>
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
        }
        .theme-dark {
            background-color: #1f2937;
            --bg-color: #1f2937;
            --container-bg: #374151;
            --text-color: white;
            --input-bg: #4b5563;
            --input-border: #6b7280;
            --input-text: #ffffff;
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
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
        }
        .card-count {
            font-size: 2rem;
            font-weight: bold;
        }
        .card-category-1 { background-color: #dbeafe; }
        .card-category-2 { background-color: #bfdbfe; }
        .card-category-3 { background-color: #93c5fd; }
        .card-category-4 { background-color: #60a5fa; }
        .card-category-5 { background-color: #3b82f6; }
    </style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">
<?php include 'user_header.php'; ?>
<!-- Main Container -->
<div class="bg-[var(--bg-color)] text-[var(--text-color)] min-h-[80vh] p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Search & Filters -->
        <form method="POST" class="mb-8 flex flex-col md:flex-row gap-4 items-center justify-center">
            <input type="text" name="search" placeholder="Search asset name..." value="<?= htmlspecialchars($search) ?>" class="w-64">

            <select name="category" class="w-48">
                <option value="">All Categories</option>
                <option value="">All Categories</option>
            <option value="IT Equipment" <?= $category == 'IT Equipment' ? 'selected' : '' ?>>IT Equipment</option>
            <option value="Vehicles" <?= $category == 'Vehicles' ? 'selected' : '' ?>>Vehicles</option>
            <option value="Lab Equipment" <?= $category == 'Lab Equipment' ? 'selected' : '' ?>>Lab Equipment</option>
            </select>

            <select name="asset_status" class="w-48">
                <option value="">All Statuses</option>
                <option value="Available" <?= $status == "Available" ? "selected" : "" ?>>Available</option>
                <option value="Deployed" <?= $status == "Deployed" ? "selected" : "" ?>>Deployed</option>
                <option value="Disposed" <?= $status == "Disposed" ? "selected" : "" ?>>Disposed</option>
                <!-- <option value="Under Maintenance" <?= $status == "Under Maintenance" ? "selected" : "" ?>>Under Maintenance</option>
                <option value="For Repair" <?= $status == "For Repair" ? "selected" : "" ?>>For Repair</option> -->
            </select>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">Filter</button>
        </form>

        <!-- Cards Grid -->
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card card-category-<?php echo rand(1, 5); ?> text-white p-6 rounded-xl shadow-lg">
                        <h3 class="card-title mb-2"><?= htmlspecialchars($row['category']) ?> - <?= htmlspecialchars($row['current_status']) ?></h3>
                        <p class="card-count"><?= $row['asset_count'] ?> Assets</p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-500">
                    No assets found for the selected filters.
                </div>
            <?php endif; ?>
        </div>
    </div>
<!-- Green Google Maps Card -->
<div class="card bg-gray-500 text-white-900 p-5 rounded-xl shadow-lg mt-8 max-w-5xl mx-auto">
    <h3 class="card-title mb-3 text-lg">Asset Location Map</h3>
    <div class="w-full h-[500px] rounded-md overflow-hidden">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d6038.803705214779!2d122.0606!3d18.2709!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1712990000000!5m2!1sen!2sph"
            width="100%"
            height="100%"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>




</div>


</body>
</html>

<?php
$conn->close();
?>
