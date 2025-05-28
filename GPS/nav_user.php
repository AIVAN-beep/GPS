<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.tailwindcss.com"></script>
<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    transition: background-color 0.3s, color 0.3s;
  }
</style>
</head>
<body class="<?php echo $theme_class; ?>">

<!-- Hamburger button (visible on small screens only) -->
<button id="hamburgerBtn" 
        class="fixed top-4 left-4 z-50 p-2 rounded-md bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-md lg:hidden"
        aria-label="Toggle sidebar">
  <i class="fas fa-bars text-lg"></i>
</button>

<!-- Sidebar -->
<aside id="sidebar" class="w-64 h-screen p-4 fixed top-0 left-0 shadow-md flex flex-col space-y-2 border
  <?php 
    echo ($theme_class === 'theme-dark') 
      ? 'bg-gray-900 text-gray-100 border-gray-700' 
      : 'bg-white text-gray-800 border-gray-300'; 
  ?> text-sm font-bold

  hidden lg:flex
  ">
  
  <div class="flex justify-center mb-6">
    <img src="logo.png" alt="Logo" class="w-24 h-auto logo-animate">
  </div>

  <!-- Dashboard -->
  <a href="user.php" class="flex items-center space-x-4 px-4 py-3 transition-colors
    <?php 
      $is_active = ($current_page === 'user.php');
      echo ($theme_class === 'theme-dark') 
        ? ($is_active ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 text-gray-300') 
        : ($is_active ? 'bg-gray-200 text-gray-900' : 'hover:bg-gray-200 text-gray-600'); 
    ?>">
    <i class="fas fa-map-marker-alt text-xl w-5"></i>
    <span>Dashboard</span>
  </a>

  <!-- Assets -->
  <a href="asset_available.php" class="flex items-center space-x-4 px-4 py-3 transition-colors
    <?php 
      $is_active = ($current_page === 'asset_available.php');
      echo ($theme_class === 'theme-dark') 
        ? ($is_active ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 text-green-400') 
        : ($is_active ? 'bg-gray-200 text-green-800' : 'hover:bg-gray-200 text-green-600'); 
    ?>">
    <i class="fas fa-calendar-check text-lg w-5"></i>
    <span>Assets</span>
  </a>

  <!-- Settings -->
  <a href="user_settings.php" class="flex items-center space-x-4 px-4 py-3 transition-colors
    <?php 
      $is_active = ($current_page === 'user_settings.php');
      echo ($theme_class === 'theme-dark') 
        ? ($is_active ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 text-yellow-400') 
        : ($is_active ? 'bg-gray-200 text-yellow-800' : 'hover:bg-gray-200 text-yellow-600'); 
    ?>">
    <i class="fas fa-cogs text-lg w-5"></i>
    <span>Settings</span>
  </a>

  <!-- Logout -->
  <button onclick="openLogoutModal()" class="flex items-center space-x-4 px-4 py-3 transition-colors text-left
    <?php
      echo ($theme_class === 'theme-dark')
        ? 'hover:bg-gray-700 text-red-400'
        : 'hover:bg-gray-200 text-red-600';
    ?>">
    <i class="fas fa-sign-out-alt text-lg w-5"></i>
    <span>Logout</span>
  </button>
</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="<?php 
    echo ($theme_class === 'theme-dark') 
      ? 'bg-gray-800 text-gray-100' 
      : 'bg-white text-gray-800'; 
  ?> shadow-lg p-6 w-full max-w-sm text-sm font-bold rounded-xl">
    <h2 class="mb-2 text-lg">Sign out?</h2>
    <p class="mb-6 <?php echo ($theme_class === 'theme-dark') ? 'text-gray-400' : 'text-gray-600'; ?>">
      Are you sure you want to log out of your account?
    </p>
    <div class="flex justify-end space-x-2">
      <button onclick="closeLogoutModal()" class="<?php
        echo ($theme_class === 'theme-dark')
          ? 'bg-gray-700 hover:bg-gray-600 text-gray-100'
          : 'bg-gray-200 hover:bg-gray-300 text-gray-800';
      ?> px-4 py-2">Cancel</button>
      <a href="logout.php" class="px-4 py-2 bg-red-500 text-white hover:bg-red-600">Logout</a>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
  function openLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
  }
  function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
  }

  // Hamburger toggle sidebar
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');

  hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
  });
</script>

</body>
</html>
