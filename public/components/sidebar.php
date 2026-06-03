<aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
  <div class="py-4 text-gray-500 dark:text-gray-400">
    <a class="flex items-center justify-center pt-6 pb-2 w-full text-center" href="index.php">
      <img
        src="assets/img/wheelsrent-logo.jpeg"
        alt="WheelsRent Logo"
        style="height: 80px !important; width: auto !important; max-width: 95%; margin: 0 auto;"
        class="rounded-xl object-contain bg-white p-1 shadow-sm transition-transform hover:scale-102">
    </a>
    <ul class="mt-6">
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="index.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span class="ml-4">Dashboard</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'pelanggan.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'pelanggan.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="pelanggan.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
          <span class="ml-4">Data Pelanggan</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'mobil.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'mobil.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="mobil.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
            <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h4M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
          </svg>
          <span class="ml-4">Data Mobil</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'penyewaan.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'penyewaan.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="penyewaan.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
          </svg>
          <span class="ml-4">Penyewaan Mobil</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'pengembalian.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'pengembalian.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="pengembalian.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
          <span class="ml-4">Pengembalian Mobil</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'maintenance.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'maintenance.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="maintenance.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span class="ml-4">Maintenance</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'laporan.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'laporan.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="laporan.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path>
            <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          <span class="ml-4">Laporan Analitik</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <?php if (basename($_SERVER['PHP_SELF']) == 'pengguna.php') {
          echo '<span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>';
        } ?>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if (basename($_SERVER['PHP_SELF']) == 'pengguna.php') {
                                                                                                                                                      echo 'text-gray-800 dark:text-gray-100';
                                                                                                                                                    } ?>" href="pengguna.php">
          <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          <span class="ml-4">Manajemen Pengguna</span>
        </a>
      </li>
    </ul>
  </div>
</aside>