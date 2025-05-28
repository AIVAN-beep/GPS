<?php
include 'auth.php';
include 'db_connect.php';
include 'user_theme.php';

function fetch_assets($conn, $search, $category, $status) {
    $sql = "SELECT * FROM assets WHERE asset_name LIKE ? AND category LIKE ? AND current_status LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%$search%";
    $categoryParam = "%$category%";
    $statusParam = "%$status%";
    $stmt->bind_param('sss', $searchParam, $categoryParam, $statusParam);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function log_asset_action($conn, $asset_id, $action, $details_array) {
    $details = json_encode($details_array);
    $stmt = $conn->prepare("INSERT INTO asset_logs (asset_id, action, details) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("log_asset_action prepare failed: " . $conn->error);
        return false;
    }
    if (!$stmt->bind_param("iss", $asset_id, $action, $details)) {
        error_log("log_asset_action bind_param failed: " . $stmt->error);
        return false;
    }
    if (!$stmt->execute()) {
        error_log("log_asset_action execute failed: " . $stmt->error);
        return false;
    }
    return true;
}

// --- ADD ASSET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_asset'])) {
    $asset_name = $conn->real_escape_string($_POST['asset_name']);
    $serial_num = $conn->real_escape_string($_POST['serial_num']);
    $category = $conn->real_escape_string($_POST['category']);
    $status = $conn->real_escape_string($_POST['status']);
    $location = trim($_POST['location'] ?? '');

    $insert_sql = "INSERT INTO assets (asset_name, serial_num, category, current_status, last_known_location, assigned_to) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param('ssssss', $asset_name, $serial_num, $category, $status, $location, $assigned_user);

    if ($insert_stmt->execute()) {
        $inserted_id = $conn->insert_id;
        $details = [
            'asset_name' => $asset_name,
            'serial_num' => $serial_num,
            'category' => $category,
            'status' => $status,
            'location' => $location,
            'assigned_user' => $assigned_user
        ];
        log_asset_action($conn, $inserted_id, 'Added', $details);

        header("Location: assets.php");
        exit;
    } else {
        die("Error: " . $insert_stmt->error);
    }
}

// --- EDIT ASSET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_asset'])) {
    $asset_id = intval($_POST['asset_id']);

    $stmt = $conn->prepare("SELECT assigned_to FROM assets WHERE asset_id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();

    $asset_name = $conn->real_escape_string($_POST['asset_name']);
    $serial_num = $conn->real_escape_string($_POST['serial_num']);
    $category = $conn->real_escape_string($_POST['category']);
    $status = $conn->real_escape_string($_POST['status']);
    $location = trim($_POST['location'] ?? '');
    $assigned_user = $conn->real_escape_string($_POST['assigned_user']);

    $update_sql = "UPDATE assets 
                   SET asset_name=?, serial_num=?, category=?, current_status=?, last_known_location=?, assigned_to=? 
                   WHERE asset_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ssssssi', $asset_name, $serial_num, $category, $status, $location, $assigned_user, $asset_id);

    if ($update_stmt->execute()) {
        $details = [
            'asset_name' => $asset_name,
            'serial_num' => $serial_num,
            'category' => $category,
            'status' => $status,
            'location' => $location,
            'assigned_user' => $assigned_user
        ];
        log_asset_action($conn, $asset_id, 'Edited', $details);

        header("Location: assets.php");
        exit;
    } else {
        die("Error: " . $update_stmt->error);
    }
}

// --- DELETE ASSET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_asset'])) {
    $asset_id = intval($_POST['asset_id']);

    $stmt = $conn->prepare("SELECT * FROM assets WHERE asset_id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $delete_stmt = $conn->prepare("DELETE FROM assets WHERE asset_id = ?");
    $delete_stmt->bind_param("i", $asset_id);
    if ($delete_stmt->execute()) {
        $details = [
            'asset_name' => $asset['asset_name'],
            'serial_num' => $asset['serial_num'],
            'category' => $asset['category'],
            'status' => $asset['current_status'],
            'location' => $asset['last_known_location'],
            'assigned_user' => $asset['assigned_to']
        ];
        log_asset_action($conn, $asset_id, 'Deleted', $details);

        header("Location: assets.php");
        exit;
    } else {
        die("Error deleting asset: " . $delete_stmt->error);
    }
}

// --- AJAX fetch assets ---
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
    $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

    $assets = fetch_assets($conn, $search, $category, $status);

    header('Content-Type: application/json');
    echo json_encode($assets);
    exit;
}

// Default fetch for page load
$search = '';
$category = '';
$status = '';
$assets = fetch_assets($conn, $search, $category, $status);
?>





<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
<title>Asset Management</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        color: black;
    }
    .container {
        background-color: var(--container-bg);
        color: var(--text-color);
    }
    .theme-light {
        --bg-color: #f3f4f6;
        --container-bg: #ffffff;
        --text-color: #000000;
        --input-bg: #ffffff;
        --input-border: #cccccc;
        --input-text: #000000;
        --header-bg: #f3f4f6;
        --header-text-color: #000000;
    }
    .theme-dark {
        background-color: #1f2937;
        --bg-color: #1f2937;
        --container-bg: #374151;
        --text-color: white;
        --input-bg: #4b5563;
        --input-border: #6b7280;
        --input-text: #ffffff;
        --header-bg: #374151;
        --header-text-color: #ffffff;
    }
    input[type="text"],
    select,
    textarea {
        background-color: var(--input-bg);
        color: var(--input-text);
        border: 1px solid var(--input-border);
    }
    input[type="text"]::placeholder,
    textarea::placeholder {
        color: var(--input-text);
        opacity: 0.7;
    }
    table th {
        background-color: var(--header-bg);
        color: var(--header-text-color);
    }
    .btn {
        border-radius: 5px;
        padding: 8px 16px;
        text-align: center;
        font-weight: bold;
        cursor: pointer;
    }
    .btn-primary {
        background-color: #4CAF50;
        color: white;
    }
    .btn-danger {
        background-color: #F44336;
        color: white;
    }
    .btn-edit {
        background-color: #FFD700;
    }
    .theme-dark .btn-edit {
        color: #000;
    }
</style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">
<?php include 'header.php';  ?>

<div class="container p-8 pr-8 rounded-lg shadow-lg w-full max-w-7xl mx-auto mt-8">
    <div class="mb-8 flex items-center space-x-4">
        <input type="text" id="search" placeholder="Search by Asset Name" class="px-4 py-2 border rounded-md w-1/3" />
        <select id="category" class="px-4 py-2 border rounded-md">
            <option value="">All Categories</option>
            <option value="IT Equipment">IT Equipment</option>
            <option value="Vehicles">Vehicles</option>
            <option value="Lab Equipment">Lab Equipment</option>
        </select>
        <select id="status" class="px-4 py-2 border rounded-md">
            <option value="">All Statuses</option>
            <option value="Working">Working</option>
            <option value="Under Maintenance">Under Maintenance</option>
            <option value="For Repair">For Repair</option>
        </select>
    </div>

    <button class="px-4 py-2 bg-green-500 text-white rounded-md mb-4" onclick="openAddAssetModal()">Add New Asset</button>

    <table class="min-w-full table-auto border border-gray-300 border-collapse" id="assetsTable">
    <thead>
        <tr class="border-b border-gray-300">
            <th class="px-4 py-2 text-center border-r border-gray-300">Asset Name/ID</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Serial Number</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Category</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Status</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Last Known Location</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Assigned Office</th>
            <th class="px-4 py-2 text-center border-r border-gray-300">Asset Standing</th>
            <th class="px-4 py-2 text-center">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($assets as $asset): ?>
            <tr class="border-b border-gray-200">
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['serial_num']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['category']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['current_status']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['last_known_location']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['assigned_to']); ?></td>
                <td class="px-4 py-2 text-center border-r border-gray-200"><?php echo htmlspecialchars($asset['asset_status'] ?? ''); ?></td>
                <td class="px-4 py-2 text-center flex space-x-2">
                <button class="btn btn-edit btn-sm" onclick="openEditAssetModal(<?php echo $asset['asset_id']; ?>,
                    '<?php echo addslashes($asset['asset_name']); ?>',
                    '<?php echo addslashes($asset['serial_num']); ?>',
                    '<?php echo addslashes($asset['category']); ?>',
                    '<?php echo addslashes($asset['current_status']); ?>',
                    '<?php echo addslashes($asset['last_known_location']); ?>',
                    '<?php echo addslashes($asset['assigned_to']); ?>')">Edit</button>
                <button class="btn btn-danger btn-sm" onclick="showDeleteModal(<?php echo $asset['asset_id']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>

<!-- Add Asset Modal -->
<div id="addAssetModal" class="addAsset modal hidden fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center">
    <div class="modal-content bg-[var(--container-bg)] text-[var(--text-color)] p-8 rounded-lg w-1/2 shadow-xl">
        <h2 class="text-2xl font-semibold mb-4">Add New Asset</h2>
        <form id="addAssetForm" method="POST">
            <input type="hidden" name="add_asset" value="1" />
            <div class="mb-4">
                <label for="add_asset_name" class="block mb-1">Asset Name/ID</label>
                <input id="add_asset_name" name="asset_name" type="text" required class="w-full px-3 py-2 border rounded-md" />
            </div>
            <div class="mb-4">
                <label for="add_serial_num" class="block mb-1">Serial Number</label>
                <input id="add_serial_num" name="serial_num" type="text" required class="w-full px-3 py-2 border rounded-md" />
            </div>
            <div class="mb-4">
                <label for="add_category" class="block mb-1">Category</label>
                <select id="add_category" name="category" required class="w-full px-3 py-2 border rounded-md">
                    <option value="" disabled selected>Select Category</option>
                    <option value="IT Equipment">IT Equipment</option>
                    <option value="Vehicles">Vehicles</option>
                    <option value="Lab Equipment">Lab Equipment</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="add_status" class="block mb-1">Status</label>
                <select id="add_status" name="status" required class="w-full px-3 py-2 border rounded-md">
                    <option value="" disabled selected>Select Status</option>
                    <option value="Working">Working</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="For Repair">For Repair</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="add_location" class="block mb-1">Last Known Location (Latitude, Longitude)</label>
                <input id="add_location" name="location" type="text" placeholder="e.g. 37.7749,-122.4194" class="w-full px-3 py-2 border rounded-md" />
                <div class="mb-4">
                <a 
                  href="https://www.google.com/maps/@18.2503027,122.0034572,4047m/data=!3m1!1e3!5m1!1e1?entry=ttu&g_ep=EgoyMDI1MDUxNS4xIKXMDSoASAFQAw%3D%3D" 
                  target="_blank" 
                  class="inline-block px-3 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-md shadow-md hover:bg-blue-700 transition-colors duration-300"
                >
                  Go to Google Maps
                </a>
                </div>
                <div id="addMapPreview" class="w-full h-48 mt-4 border rounded-md"></div>
            </div>

            <div class="mb-4">
                <label for="add_assigned_user" class="block mb-1">Assigned Office</label>
                <select name="assigned_user" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Select an office</option>
                    <option>CICS TECH ROOM</option>
                    <option>SUPPLY OFFICE</option>
                </select>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" class="btn btn-danger" onclick="closeAddAssetModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Asset</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Asset Modal -->
<div id="editAssetModal" class="editAsset modal hidden fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center">
    <div class="bg-[var(--container-bg)] text-[var(--text-color)] p-8 rounded-lg w-1/2 shadow-xl">
        <h2 class="text-2xl font-semibold mb-4">Edit Asset</h2>
        <form id="editAssetForm" method="POST">
            <input type="hidden" name="edit_asset" value="1" />
            <input type="hidden" id="edit_asset_id" name="asset_id" />
            
            <div class="mb-4">
                <label for="edit_asset_name" class="block mb-1">Asset Name/ID</label>
                <input id="edit_asset_name" name="asset_name" type="text" required class="w-full px-3 py-2 border rounded-md" />
            </div>
            
            <div class="mb-4">
                <label for="edit_serial_num" class="block mb-1">Serial Number</label>
                <input id="edit_serial_num" name="serial_num" type="text" required class="w-full px-3 py-2 border rounded-md" />
            </div>
            
            <div class="mb-4">
                <label for="edit_category" class="block mb-1">Category</label>
                <select id="edit_category" name="category" required class="w-full px-3 py-2 border rounded-md">
                    <option value="IT Equipment">IT Equipment</option>
                    <option value="Vehicles">Vehicles</option>
                    <option value="Lab Equipment">Lab Equipment</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="edit_status" class="block mb-1">Status</label>
                <select id="edit_status" name="status" required class="w-full px-3 py-2 border rounded-md">
                    <option value="Working">Working</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="For Repair">For Repair</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="edit_location" class="block mb-1">Last Known Location(Latitude, Longitude)</label>
                <input id="edit_location" name="location" type="text" placeholder="e.g. 37.7749,-122.4194" class="w-full px-3 py-2 border rounded-md" />
                <div class="mb-4">
                <a 
                  href="https://www.google.com/maps/@18.2503027,122.0034572,4047m/data=!3m1!1e3!5m1!1e1?entry=ttu&g_ep=EgoyMDI1MDUxNS4xIKXMDSoASAFQAw%3D%3D" 
                  target="_blank" 
                  class="inline-block px-3 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-md shadow-md hover:bg-blue-700 transition-colors duration-300"
                >
                  Go to Google Maps
                </a>
                </div>
                <div id="editMapPreview" class="w-full h-48 mt-4 border rounded-md"></div>
            </div>

            <div class="mb-4">
                <label for="edit_assigned_office" class="block mb-1">Assigned Office</label>
                <select id="edit_assigned_office" name="assigned_user" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Select an office</option>
                    <option value="CICS TECH ROOM">CICS TECH ROOM</option>
                    <option value="SUPPLY OFFICE">SUPPLY OFFICE</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" class="btn btn-danger" onclick="closeEditAssetModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="delAsset modal hidden fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-[var(--container-bg)] text-[var(--text-color)] p-6 rounded-lg w-96 shadow-lg text-center">

        <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
        <p>Are you sure you want to delete this asset?</p>
        <form method="POST" action="assets.php" class="mt-6">
            <input type="hidden" name="asset_id" id="delete_asset_id">
            <input type="hidden" name="delete_asset" value="1">
            <div class="flex justify-center gap-4">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                <button type="button" class="btn btn-edit" onclick="closeDeleteModal()">Cancel</button>
            </div>
            </form>

    </div>
</div>

<script>
    let map, marker;

    function openMapSelector() {
        document.getElementById('mapSelectorModal').classList.remove('hidden');

        setTimeout(() => {
            if (!map) {
                map = L.map('mapContainer').setView([14.5995, 120.9842], 13); // default to Manila
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                map.on('click', function (e) {
                    const { lat, lng } = e.latlng;
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng]).addTo(map);
                    }
                });
            }

            map.invalidateSize(); // fix display if container was hidden before
        }, 200);
    }

    function closeMapSelector() {
        document.getElementById('mapSelectorModal').classList.add('hidden');
    }

    function confirmMapLocation() {
        if (marker) {
            const { lat, lng } = marker.getLatLng();
            document.getElementById('add_location').value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
            updateAddMapPreview();
        }
        closeMapSelector();
    }
    // Utility to escape string for JS inline use
    function escapeJs(str) {
        if (!str) return '';
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
    }

    // Open/close modal handlers
    function openAddAssetModal() {
        document.getElementById('addAssetModal').classList.remove('hidden');
        resetAddAssetForm();
    }
    function closeAddAssetModal() {
        document.getElementById('addAssetModal').classList.add('hidden');
    }
    function openEditAssetModal(id, name, serial, category, status, location, assigned) {
    document.getElementById('editAssetModal').classList.remove('hidden');
    document.getElementById('edit_asset_id').value = id;
    document.getElementById('edit_asset_name').value = name;
    document.getElementById('edit_serial_num').value = serial;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_location').value = location || '';
    document.getElementById('edit_assigned_office').value = assigned || '';

    updateEditMapPreview();
}
    function closeEditAssetModal() {
         document.getElementById('editAssetModal').classList.add('hidden');
    }
    function showDeleteModal(assetId) {
        document.getElementById('delete_asset_id').value = assetId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Reset Add Asset form and map preview
    function resetAddAssetForm() {
        document.getElementById('addAssetForm').reset();
        updateAddMapPreview();
    }

    // Map previews for Add form
    function updateAddMapPreview() {
    const locationStr = document.getElementById('add_location').value.trim();
    const container = document.getElementById('addMapPreview');
    const coords = locationStr.split(',');
    if (coords.length === 2) {
        const lat = parseFloat(coords[0]);
        const lng = parseFloat(coords[1]);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            container.innerHTML = `<iframe width="100%" height="100%" frameborder="0" style="border:0"
                src="https://maps.google.com/maps?q=${lat},${lng}&hl=en&z=15&output=embed" allowfullscreen></iframe>`;
            return;
        }
    }
    container.innerHTML = '<p class="text-center text-gray-500 mt-12">Enter valid latitude and longitude to preview map</p>';
}


    function updateEditMapPreview() {
    const locationStr = document.getElementById('edit_location').value.trim();
    const container = document.getElementById('editMapPreview');
    const coords = locationStr.split(',');
    if (coords.length === 2) {
        const lat = parseFloat(coords[0]);
        const lng = parseFloat(coords[1]);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            container.innerHTML = `<iframe width="100%" height="100%" frameborder="0" style="border:0"
                src="https://maps.google.com/maps?q=${lat},${lng}&hl=en&z=15&output=embed" allowfullscreen></iframe>`;
            return;
        }
    }
    container.innerHTML = '<p class="text-center text-gray-500 mt-12">Enter valid latitude and longitude to preview map</p>';
}

    function initAssetFormMapListeners() {
    // Auto-update map preview when typing coordinates
    document.getElementById('add_location').addEventListener('input', updateAddMapPreview);
    document.getElementById('edit_location').addEventListener('input', updateEditMapPreview);
}

document.addEventListener('DOMContentLoaded', function () {
    initAssetFormMapListeners();
});
    // Live filter with AJAX
    async function fetchFilteredAssets() {
        const search = document.getElementById('search').value;
        const category = document.getElementById('category').value;
        const status = document.getElementById('status').value;

        const params = new URLSearchParams({ ajax: 1, search, category, status });
        try {
            const response = await fetch('assets.php?' + params.toString());
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            const tbody = document.querySelector('#assetsTable tbody');
            tbody.innerHTML = '';
            data.forEach(asset => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                                    <td class="px-4 py-2 border border-gray-300">${asset.asset_name}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.serial_num}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.category}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.current_status}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.last_known_location}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.assigned_to}</td>
                <td class="px-4 py-2 border border-gray-300">${asset.asset_status || ''}</td>
                <td class="px-4 py-2 border border-gray-300">
                    <button class="btn btn-edit btn-sm" onclick="openEditAssetModal(${asset.asset_id},
                        '${escapeJs(asset.asset_name)}',
                        '${escapeJs(asset.serial_num)}',
                        '${escapeJs(asset.category)}',
                        '${escapeJs(asset.current_status)}',
                        '${escapeJs(asset.last_known_location)}',
                        '${escapeJs(asset.assigned_to)}')">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="showDeleteModal(${asset.asset_id})">Delete</button>
                </td>

                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Error fetching assets:', error);
        }
    }

    // Event listeners for live filtering
    document.getElementById('search').addEventListener('input', fetchFilteredAssets);
    document.getElementById('category').addEventListener('change', fetchFilteredAssets);
    document.getElementById('status').addEventListener('change', fetchFilteredAssets);

    // Event listeners for map preview update
    document.getElementById('add_latitude').addEventListener('input', updateAddMapPreview);
    document.getElementById('add_longitude').addEventListener('input', updateAddMapPreview);
    document.getElementById('edit_latitude').addEventListener('input', updateEditMapPreview);
    document.getElementById('edit_longitude').addEventListener('input', updateEditMapPreview);

    // Initialize map previews
    updateAddMapPreview();
</script>
</body>
</html>
