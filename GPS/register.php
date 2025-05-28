<?php
include 'db_connect.php';
$message = ''; // Variable to store the message (success or error)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $office = $_POST['office'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // Check if passwords match
    if ($password !== $confirm_password) {
        $message = "<p class='text-red-500'>Error: Passwords do not match.</p>";
    } else {
        // Hash the password before storing it in the database
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO secondary_users (username,office, password) VALUES ('$username', '$office','$hashed_password')";
        if ($conn->query($query) === TRUE) {
            $message = "<p class='text-green-500'>Registration successful! Redirecting to login page...</p>";
            // Redirect after 2 seconds (message will display first)
            header("refresh:2;url=index.php");
            exit();
        } else {
            $message = "<p class='text-red-500'>Error: " . $query . "<br>" . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Register</title>
    <script>
        // JavaScript function to toggle password visibility
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var confirmPasswordField = document.getElementById("confirm_password");
            var passwordCheckbox = document.getElementById("show_password");

            // Toggle password visibility based on checkbox state
            if (passwordCheckbox.checked) {
                passwordField.type = "text";
                confirmPasswordField.type = "text";
            } else {
                passwordField.type = "password";
                confirmPasswordField.type = "password";
            }
        }

        // JavaScript to show and hide the message popup
        window.onload = function() {
            <?php if ($message != '') { ?>
                document.getElementById("message-popup").classList.remove("hidden");
            <?php } ?>
        };
        
        function closePopup() {
            document.getElementById("message-popup").classList.add("hidden");
        }
    </script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

    <!-- Message Popup -->
    <?php if ($message != ''): ?>
        <div id="message-popup" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
                <div class="text-xl font-bold mb-4">Notification</div>
                <div class="mb-4"><?php echo $message; ?></div>
                <button onclick="closePopup()" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Close</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-4 text-center">Register</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" placeholder="Enter Username" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700">Office</label>
                <select name="office" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Select an office</option>
                    <option>CA</option>
                    <option>CICS</option>
                    <option>CBEA</option>
                    <option>CCJE</option>
                    <option>CTED</option>
                    <option>CHM</option>
                    <option>SUPPLY OFFICE</option>
                    <option>ADMIN</option>
                    <option>BAMBOO CAFE</option>
                    <option>SPORTS OFFICE</option>
                    <option>CAMPUS CLINIC</option>
                    <option>ROTC</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <!-- Show Password Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" id="show_password" onclick="togglePassword()"
                    class="mr-2">
                <label for="show_password" class="text-gray-600">Show Password</label>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">Register</button>
            <a href="index.php" style="color: green;font-size: 15px;text-decoration: underline;">Back to Login</a>
        </form>
    </div>
</body>
</html>
