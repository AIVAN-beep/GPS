<?php
include 'db_connect.php';
include 'theme.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = $conn->real_escape_string($username);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            $user_id = $user['id'];

            $alert_query = "SELECT * FROM alerts WHERE user_id=$user_id AND status='unread'";
            $alerts_result = $conn->query($alert_query);
            $_SESSION['alerts'] = $alerts_result ? $alerts_result->fetch_all(MYSQLI_ASSOC) : [];

            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $query2 = "SELECT * FROM secondary_users WHERE username='$username'";
        $result2 = $conn->query($query2);

        if ($result2 && $result2->num_rows > 0) {
            $user = $result2->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['username'];
                $user_id = $user['id'];

                $alert_query2 = "SELECT * FROM alerts WHERE user_id=$user_id AND status='unread'";
                $alerts_result2 = $conn->query($alert_query2);
                $_SESSION['alerts'] = $alerts_result2 ? $alerts_result2->fetch_all(MYSQLI_ASSOC) : [];

                header("Location: user.php");
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Login</title>
  <style>
    .theme-light {
      --bg-color: #f3f4f6;
      --container-bg: #ffffff;
      --text-color: #000000;
      --input-bg: #ffffff;
      --input-border: #cccccc;
      --input-text: #000000;
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
    input[type="password"],
    select,
    textarea {
      background-color: var(--input-bg);
      color: var(--input-text);
      border: 1px solid var(--input-border);
    }

    input::placeholder,
    textarea::placeholder {
      color: var(--input-text);
      opacity: 0.7;
    }

    @keyframes fade-in-up {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in-up {
      animation: fade-in-up 0.5s ease-out both;
    }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen <?php echo $theme_class; ?> bg-[var(--bg-color)] transition-colors duration-500">

  <div class="bg-white p-8 rounded-lg shadow-lg w-96 animate-fade-in-up">
    <div class="flex justify-center mb-6 animate-fade-in-up delay-100">
      <img src="logo.png" alt="Logo" class="w-24 h-auto animate-pulse" />
    </div>

    <h1 class="text-3xl font-extrabold mb-4 text-center animate-fade-in-up delay-200">
      <span class="bg-gradient-to-r from-blue-500 to-blue-700 text-transparent bg-clip-text">SMART</span>
      <span class="bg-gradient-to-r from-orange-400 to-orange-600 text-transparent bg-clip-text"> GPS </span>
      <span class="bg-gradient-to-r from-blue-500 to-blue-700 text-transparent bg-clip-text">ASSET TRACKER</span>
    </h1>

    <?php if (isset($error_message)): ?>
      <div id="errorMessage" class="bg-red-500 text-white p-4 rounded-lg mb-6 text-center animate-fade-in-up delay-300">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div class="animate-fade-in-up delay-300">
        <label class="block text-gray-700">Username</label>
        <input type="text" name="username" placeholder="Enter Username" required
          class="transition-all duration-300 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="animate-fade-in-up delay-400">
        <label class="block text-gray-700">Password</label>
        <input id="password" type="password" name="password" placeholder="Enter Password" required
          class="transition-all duration-300 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="flex items-center mt-2 animate-fade-in-up delay-500">
        <input type="checkbox" id="togglePassword" class="mr-2" />
        <label for="togglePassword" class="text-gray-700">See Password</label>
      </div>

      <div class="animate-fade-in-up delay-600">
        <button type="submit"
          class="transition-all duration-300 w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 hover:scale-[1.02] active:scale-95">
          Login
        </button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) {
        setTimeout(() => {
          errorMessage.style.display = 'none';
        }, 3000);
      }

      const togglePassword = document.getElementById('togglePassword');
      const passwordField = document.getElementById('password');

      togglePassword.addEventListener('change', function () {
        passwordField.type = this.checked ? 'text' : 'password';
      });
    });
  </script>
</body>
</html>
