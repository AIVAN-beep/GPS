<?php
// Include user theme settings (ensure $conn and session are set correctly)
include 'user_theme.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sidebar Navigation</title>

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      transition: background-color 0.3s, color 0.3s;
    }

    /* Light Theme */
    .theme-light body {
      background-color: #f9fafb;
      color: #111827;
    }
    .theme-light aside {
      background-color: #f3f4f6;
      color: #374151;
      border-right: 1px solid #d1d5db;
    }
    .theme-light .sidebar-link {
      color: #4b5563;
      font-weight: 600;
    }
    .theme-light .sidebar-link:hover {
      background-color: #e5e7eb;
      color: #1f2937;
    }
    .theme-light .sidebar-link.active {
      color: #1d4ed8;
      font-weight: 700;
    }
    .theme-light .sidebar-link.active .fas {
      color: #2563eb;
    }
    .theme-light #hamburgerBtn {
      color: #111827;
    }
    .theme-light .logout-btn {
      color: #dc2626;
    }
    .theme-light .logout-btn:hover {
      background-color: #d2d3d6;
      color: #b91c1c;
    }
    .theme-light #logoutModal .modal-content {
      background-color: #f3f4f6;
      color: #111827;
    }
    .theme-light #logoutModal button {
      background-color: #e5e7eb;
      color: #111827;
    }
    .theme-light #logoutModal button:hover {
      background-color: #d1d5db;
    }
    .theme-light #logoutModal a {
      background-color: #dc2626;
      color: white;
    }
    .theme-light #logoutModal a:hover {
      background-color: #b91c1c;
    }

    /* Dark Theme */
    .theme-dark body {
      background-color: #1f2937;
      color: #e5e7eb;
    }
    .theme-dark aside {
      background-color: #111827;
      color: #d1d5db;
      border-right: 1px solid #374151;
    }
    .theme-dark .sidebar-link {
      color: #a5b4fc;
      font-weight: 600;
    }
    .theme-dark .sidebar-link:hover {
      background-color: #374151;
      color: #f3f4f6;
    }
    .theme-dark .sidebar-link.active {
      color: #4338ca;
      font-weight: 700;
    }
    .theme-dark .sidebar-link.active .fas {
      color: #3730a3;
    }
    .theme-dark #hamburgerBtn {
      color: #e5e7eb;
    }
    .theme-dark .logout-btn {
      color: #f87171;
    }
    .theme-dark .logout-btn:hover {
      background-color: #374151;
      color: #fca5a5;
    }
    .theme-dark #logoutModal .modal-content {
      background-color: #1f2937;
      color: #e5e7eb;
    }
    .theme-dark #logoutModal button {
      background-color: #374151;
      color: #e5e7eb;
    }
    .theme-dark #logoutModal button:hover {
      background-color: #4b5563;
    }
    .theme-dark #logoutModal a {
      background-color: #dc2626;
      color: white;
    }
    .theme-dark #logoutModal a:hover {
      background-color: #991b1b;
    }

    /* Other Styling */
    @keyframes logoPulse {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 0 transparent); }
      50% { transform: scale(1.1); filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.7)); }
    }

    .logo-animate {
      animation: logoPulse 3s ease-in-out infinite;
      transform-origin: center;
    }

    #hamburgerBtn {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 60;
      border: none;
      padding: 0.5rem 0.75rem;
      border-radius: 0.375rem;
      cursor: pointer;
      font-size: 1.25rem;
    }

    @media (max-width: 768px) {
      aside {
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
      }
      aside.open {
        transform: translateX(0);
      }
      #hamburgerBtn {
        display: block;
      }
    }

    #sidebarOverlay {
      display: none;
    }
    #sidebarOverlay.active {
      display: block;
      position: fixed;
      inset: 0;
      background-color: rgba(0,0,0,0.5);
      z-index: 30;
    }

    #logoutModal .relative {
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>

  <?php
  function isActive($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
  }
  ?>
</head>

<body class="<?php echo htmlspecialchars($theme_class); ?>">

<!-- Hamburger Button -->
<button id="hamburgerBtn" aria-label="Toggle sidebar">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<aside class="w-64 h-screen p-5 fixed top-0 left-0 shadow-lg flex flex-col space-y-4 z-40 border-r font-sans font-semibold">
  <div class="flex justify-center mb-6 font-bold">
    <img src="logo.png" alt="Logo" class="w-24 h-auto logo-animate" />
  </div>

  <a href="dashboard.php" class="sidebar-link block w-full flex items-center space-x-3 py-2 px-3 <?php echo isActive('dashboard.php'); ?>">
    <i class="fas fa-map-marker-alt text-xl"></i><span>Dashboard</span>
  </a>
  <a href="assets.php" class="sidebar-link block w-full flex items-center space-x-3 py-2 px-3">
    <i class="fas fa-boxes text-xl"></i><span>Assets</span>
  </a>
  <a href="user_management.php" class="sidebar-link block w-full flex items-center space-x-3 py-2 px-3">
    <i class="fas fa-users text-xl"></i><span>Custodians</span>
  </a>
  <a href="settings.php" class="sidebar-link block w-full flex items-center space-x-3 py-2 px-3">
    <i class="fas fa-cogs text-xl"></i><span>Settings</span>
  </a>
  <a href="asset_logs.php" class="sidebar-link block w-full flex items-center space-x-3 py-2 px-3">
    <i class="fas fa-history text-xl"></i><span>Asset Logs</span>
  </a>

  <!-- Reports Dropdown -->
<div class="relative">
  <button id="reportsToggleBtn" class="w-full flex items-center justify-between py-2 px-3 sidebar-link cursor-pointer">
    <span class="flex items-center space-x-3">
      <i class="fas fa-file-alt text-xl"></i><span>Reports</span>
    </span>
    <svg id="reportsArrow" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
    </svg>
  </button>

  <div id="reportsDropdown" class="hidden flex flex-col space-y-1 mt-1">
    <a href="asset_usage_report.php" class="sidebar-link block px-4 py-2 hover:bg-gray-700">
      <i class="fas fa-chart-line text-s"></i> <span>Asset Usage Report</span>
    </a>
    <a href="security_report.php" class="sidebar-link block px-4 py-2 hover:bg-gray-700">
      <i class="fas fa-shield-alt text-s"></i> <span>Security Report</span>
    </a>
    <a href="maintenance_report.php" class="sidebar-link block px-4 py-2 hover:bg-gray-700">
      <i class="fas fa-tools text-s"></i> <span>Maintenance Report</span>
    </a>
    <a href="custom_report.php" class="sidebar-link block px-4 py-2 hover:bg-gray-700">
      <i class="fas fa-pen-nib text-s"></i> <span>Custom Report</span>
    </a>
  </div>
</div>


  <!-- Logout -->
  <button onclick="openLogoutModal()" class="logout-btn block w-full flex items-center space-x-3 py-2 px-3 hover:bg-gray-700 transition">
    <i class="fas fa-sign-out-alt text-xl"></i><span>Logout</span>
  </button>
</aside>

<!-- Overlay -->
<div id="sidebarOverlay"></div>

<!-- Logout Modal -->
<div id="logoutModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
  <div class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="modal-content relative z-10 max-w-sm w-full mx-4 rounded-xl shadow-lg p-6 transition-all duration-300">
    <h2 class="text-xl font-bold mb-4">Confirm Logout</h2>
    <p class="mb-6">Are you sure you want to log out?</p>
    <div class="flex justify-end gap-3">
      <button onclick="closeLogoutModal()" class="px-4 py-2 rounded-md transition">Cancel</button>
      <a href="logout.php" class="px-4 py-2 rounded-md transition">Logout</a>
    </div>
  </div>
</div>

<script>
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.querySelector('aside');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    sidebarOverlay.classList.toggle('active');
  });

  sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    sidebarOverlay.classList.remove('active');
  });
  const reportsToggleBtn = document.getElementById('reportsToggleBtn');
const reportsDropdown = document.getElementById('reportsDropdown');
const reportsArrow = document.getElementById('reportsArrow');

reportsToggleBtn.addEventListener('click', () => {
  const isHidden = reportsDropdown.classList.contains('hidden');
  if (isHidden) {
    reportsDropdown.classList.remove('hidden');
    reportsArrow.style.transform = 'rotate(180deg)';
  } else {
    reportsDropdown.classList.add('hidden');
    reportsArrow.style.transform = 'rotate(0deg)';
  }
});


  function openLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
  }

  function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
  }
</script>

</body>
</html>
