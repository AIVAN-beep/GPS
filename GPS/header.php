<header class="shadow-md w-full">
  <div class="max-w-7xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">

    <!-- Left: Logo -->
    <div class="flex items-center space-x-3 flex-shrink-0">
      <h1 class="text-2xl sm:text-3xl font-normal flex flex-wrap gap-x-1 <?php echo ($theme_class === 'theme-dark') ? 'text-gray-200' : 'text-gray-800'; ?>">
        <span class="bg-gradient-to-r from-orange-400 to-yellow-400 bg-clip-text text-transparent leading-none">GPS</span>
        <span class="bg-gradient-to-r from-blue-400 to-blue-700 bg-clip-text text-transparent leading-none">TRACKING SYSTEM</span>
      </h1>
    </div>

    <!-- Center: Navigation -->
    <nav class="w-full md:w-auto order-3 md:order-none <?php echo ($theme_class === 'theme-dark') ? 'text-gray-200' : 'text-gray-800'; ?>">
      <?php include 'nav.php'; ?>
    </nav>

    <!-- Right: Admin -->
    <div class="flex items-center space-x-2 font-poppins flex-shrink-0 <?php echo ($theme_class === 'theme-dark') ? 'text-gray-200' : 'text-gray-800'; ?>">
      <i class="fas fa-user-circle text-2xl sm:text-3xl leading-none" title="Admin Panel"></i>
      <span class="text-base sm:text-lg leading-none font-normal">ADMIN</span>
    </div>
  </div>
</header>
