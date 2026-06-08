<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - WheelsRent Dashboard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/tailwind.output.css" />
  <script
    src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"
    defer></script>
  <script src="assets/js/init-alpine.js"></script>
</head>

<body>
  <div class="flex items-center min-h-screen p-6 bg-gray-50 dark:bg-gray-900">
    <div
      class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800">
      <div class="flex flex-col overflow-y-auto md:flex-row">
        <div class="flex items-center justify-center p-6 md:h-auto md:w-1/2 bg-white dark:bg-gray-800">
          <img
            aria-hidden="true"
            class="object-contain w-4/5 max-w-xs h-auto"
            src="assets/img/wheelsrent-logo.jpeg"
            alt="WheelsRent Logo" />
        </div>
        <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
          <div class="w-full">
            <h1
              class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-200">
              Login - WheelsRent
            </h1>

            <!-- Notifikasi Jika Input Salah -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'gagal'): ?>
              <div style="color: #ef4444; font-size: 0.875rem; margin-bottom: 1rem; font-weight: 600;">
                ❌ Username atau password salah!
              </div>
            <?php endif; ?>

            <!-- FORM UTAMA PROSES MASUK -->
            <form action="proses_login.php" method="POST">

              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Username</span>
                <input
                  type="text"
                  name="username"
                  required
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Masukkan username" />
              </label>

              <label class="block mt-4 text-sm">
                <span class="text-gray-700 dark:text-gray-400">Password</span>
                <input
                  type="password"
                  name="password"
                  required
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="***************" />
              </label>

              <!-- Tombol Submit Form Nyata -->
              <button
                type="submit"
                name="login"
                class="block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Log in
              </button>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>