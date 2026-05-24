<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <?php include 'components/head.php'; ?>
  </head>
  <body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
      <?php include 'components/sidebar.php'; ?>

      <div class="flex flex-col flex-1 w-full min-w-0">
        <?php include 'components/header.php'; ?>

        <main class="h-full overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            
            <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
              Maintenance
            </h2>

            <div class="mb-4 max-w-xs">
              <select class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                <option>Bulan Ini</option>
                <option>Bulan Lalu</option>
                <option>Tahun Ini</option>
              </select>
            </div>

            <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">25</p>
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Maintenance</p>
                </div>
              </div>
              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-amber-500 bg-amber-100 rounded-full dark:text-amber-100 dark:bg-amber-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">3</p>
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sedang Maintenance</p>
                </div>
              </div>
              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">8</p>
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Selesai Maintenance</p>
                </div>
              </div>
              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path></svg>
                </div>
                <div>
                  <p class="text-xl font-bold text-gray-700 dark:text-gray-200">Rp12.000.000</p>
                  <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Biaya Service</p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 w-full">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                
                <div class="md:col-span-7 flex flex-wrap items-center gap-2 w-full">
                  <div class="relative w-full max-w-xs text-gray-500 focus-within:text-purple-600">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                    </div>
                    <input class="w-full pl-9 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-500 bg-gray-50 border border-gray-300 rounded-lg dark:placeholder-gray-500 focus:outline-none" type="text" placeholder="Cari Nama/Nopol Kendaraan" />
                  </div>

                  <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.212 8H17"></path></svg>
                    Refresh
                  </button>
                </div>

                <div class="md:col-span-5 flex flex-wrap items-center justify-start md:justify-end gap-2 w-full">
                  <button class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete
                  </button>
                  <button class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filters
                  </button>
                  <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm ml-2">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                  </button>
                  <button class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-black shadow transition-colors duration-150 ml-1">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                    Add
                  </button>
                </div>

              </div>
            </div>

            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">
              Daftar Maintenance
            </h3>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                  <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Maintenance</th>
                      <th class="px-4 py-3">Mobil</th>
                      <th class="px-4 py-3">Nomor Polisi</th>
                      <th class="px-4 py-3">Tanggal Service</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">MT001</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Avanza</td>
                      <td class="px-4 py-3 font-semibold">B 1234 DF</td>
                      <td class="px-4 py-3 text-gray-500">20 MEI 2026</td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">MT002</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Innova</td>
                      <td class="px-4 py-3 font-semibold">D 4567 EF</td>
                      <td class="px-4 py-3 text-gray-500">21 MEI 2026</td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">MT003</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Brio</td>
                      <td class="px-4 py-3 font-semibold">F 8901 DC</td>
                      <td class="px-4 py-3 text-gray-500">22 MEI 2026</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  </body>
</html>