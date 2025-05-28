<?php
include 'auth.php';
include 'db_connect.php';
include 'user_theme.php';

// Handle Add Custodian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $firstname = $conn->real_escape_string(trim($_POST['firstname']));
    $lastname = $conn->real_escape_string(trim($_POST['lastname']));
    $middle_initial = isset($_POST['middle_initial']) ? $conn->real_escape_string(trim($_POST['middle_initial'])) : ''; // Optional
    $suffix = isset($_POST['suffix']) ? $conn->real_escape_string(trim($_POST['suffix'])) : ''; // Optional
    $email = $conn->real_escape_string(trim($_POST['email']));
    $contact_number = $conn->real_escape_string(trim($_POST['contact_number']));
    $position = $conn->real_escape_string(trim($_POST['position']));
    $office = $conn->real_escape_string(trim($_POST['office']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO secondary_users (username, firstname, lastname, middle_initial, suffix, email, contact_number, position, office, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $username, $firstname, $lastname, $middle_initial, $suffix, $email, $contact_number, $position, $office, $password);
    $stmt->execute();
    $stmt->close();

    header("Location: user_management.php");
    exit;
}

// Handle Delete Custodian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM secondary_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: user_management.php");
    exit;
}

// Fetch all custodians
$users = $conn->query("SELECT * FROM secondary_users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            --header-bg: #f9fafb;
            --header-text-color: #1f2937;
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
            --header-text-color: #f9fafb;
        }

        input[type="text"],
        input[type="password"],
        select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border: 1px solid var(--input-border);
        }

        input::placeholder {
            color: var(--input-text);
            opacity: 0.7;
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
            color: #000;
        }

        .theme-dark .btn-edit {
            color: #000;
        }

        .modal {
            transition: all 0.3s ease;
        }

        .modal .modal-content {
            width: 85%;
            max-width: 500px;
        }
        /* Responsive media queries */
    @media (max-width: 768px) {
        body {
            padding: 0 0.5rem;
        }
        .container {
            padding: 1rem;
        }
        table th, table td {
            padding: 0.5rem 0.25rem;
            font-size: 0.9rem;
        }
        .btn {
            padding: 6px 12px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 480px) {
        table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        input[type="text"],
        select,
        textarea {
            font-size: 0.9rem;
        }
        .btn {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
    }
    </style>
</head>
<body class="min-h-screen <?php echo $theme_class; ?>">
<?php include 'header.php';  ?>

<main class="min-h-screen flex justify-center items-center">
    <div class="container p-8 rounded-lg shadow-lg w-full max-w-4xl">
        <h2 class="text-2xl font-bold mb-4">CUSTODIANS</h2>

        <button onclick="openAddModal()" class="btn btn-primary mb-4">Add Custodian</button>

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-[var(--header-bg)] text-[var(--header-text-color)]">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Username</th>
                    <th class="border border-gray-300 px-4 py-2">Position</th>
                    <th class="border border-gray-300 px-4 py-2">Office</th>
                    <th class="border border-gray-300 px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['position']) ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['office']) ?></td>
                        <td class="border border-gray-300 px-4 py-2">
                            <button onclick='openViewModal(<?= json_encode($row) ?>)' class="btn btn-edit mb-1">View Info</button>
                            <button onclick="openDeleteModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>')" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>


<!-- Add Custodian Modal -->
<div id="addModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-[var(--container-bg)] text-[var(--text-color)] p-6 rounded-lg shadow-lg w-full max-w-4xl">
        <h3 class="text-lg font-bold mb-4 text-center">Add New Custodian</h3>
        <form method="POST">
            <input type="hidden" name="add_user" value="1">

            <!-- Credentials Section -->
            <h2 class="text-xl font-semibold mt-6 mb-4 text-blue-500">Credentials:</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="firstname" class="block mb-1 font-semibold">First Name</label>
                    <input type="text" name="firstname" id="firstname" class="w-full px-3 py-2 rounded border" placeholder="Enter first name" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="middle_initial" class="block mb-1 font-semibold">Middle Initial (optional)</label>
                    <input type="text" name="middle_initial" id="middle_initial" class="w-full px-3 py-2 rounded border" placeholder="Enter Middle Initial">
                </div>
                <div>
                    <label for="lastname" class="block mb-1 font-semibold">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="w-full px-3 py-2 rounded border" placeholder="Enter last name" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="suffix" class="block mb-1 font-semibold">Suffix (optional)</label>
                    <input type="text" name="suffix" id="suffix" class="w-full px-3 py-2 rounded border" placeholder="Enter suffix (optional)">
                </div>
                <div>
                    <label for="email" class="block mb-1 font-semibold">Email</label>
                    <input type="text" name="email" id="email" class="w-full px-3 py-2 rounded border" placeholder="Enter Email" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="contact_number" class="block mb-1 font-semibold">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" class="w-full px-3 py-2 rounded border" placeholder="Enter contact number" required>
                </div>
                <div>
                    <label for="position" class="block mb-1 font-semibold">Position</label>
                    <input type="text" name="position" id="position" class="w-full px-3 py-2 rounded border" placeholder="Enter position" required>
                </div>
            </div>

            <!-- Account Credentials Section -->
            <h2 class="text-xl font-semibold mt-6 mb-4 text-green-500">Account Credentials:</h2>
            <div>
                <label for="username" class="block mb-1 font-semibold">Username</label>
                <input type="text" name="username" id="username" class="w-full px-3 py-2 rounded border" placeholder="Enter username" required>
            </div>

            <div class="mt-4">
                <label for="password" class="block mb-1 font-semibold">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 rounded border" placeholder="Enter password" required>
            </div>

            <div class="flex justify-center mt-6 gap-4">
                <button type="submit" class="btn btn-primary">Add</button>
                <button type="button" onclick="closeAddModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-[var(--container-bg)] text-[var(--text-color)] p-6 rounded-lg shadow-lg w-full max-w-md text-center">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p>Are you sure you want to delete custodian <span id="deleteUsername" class="font-semibold"></span>?</p>
        <form method="POST" class="mt-4">
            <input type="hidden" name="delete_id" id="deleteId" value="">
            <input type="hidden" name="confirm_delete" value="1">
            <div class="flex justify-center gap-4">
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" onclick="closeDeleteModal()" class="btn btn-primary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Info Modal -->
<div id="viewModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-[var(--container-bg)] text-[var(--text-color)] p-6 rounded-lg shadow-lg w-full max-w-2xl">
        <h3 class="text-lg font-bold mb-4 text-center">Custodian Information</h3>
        <div id="viewModalContent" class="space-y-2 text-left"></div>
        <button type="button" onclick="closeViewModal()" class="mt-6 w-full bg-gray-300 text-black font-semibold py-2 px-4 rounded-md">Close</button>
    </div>
</div>

<script>
    // Add Modal Controls
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    // Delete Modal Controls
    function openDeleteModal(id, username) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteUsername').textContent = username;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // View Info Modal Controls
    function openViewModal(user) {
        const content = `
            <p><strong>Username:</strong> ${user.username}</p>
            <p><strong>Full Name:</strong> ${user.firstname} ${user.middle_initial ? user.middle_initial + ' ' : ''}${user.lastname} ${user.suffix ? user.suffix : ''}</p>
            <p><strong>Email:</strong> ${user.email}</p>
            <p><strong>Contact Number:</strong> ${user.contact_number}</p>
            <p><strong>Position:</strong> ${user.position}</p>
            <p><strong>Office:</strong> ${user.office}</p>
        `;
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').classList.remove('hidden');
    }
    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

</body>
</html>
