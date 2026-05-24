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
              Manajemen Pengguna
            </h2>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 w-full">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                
                <div class="md:col-span-7 flex flex-wrap items-center gap-2 w-full">
                  <div class="relative w-full max-w-xs text-gray-500 focus-within:text-purple-600">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                    </div>
                    <input class="w-full pl-9 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-500 bg-gray-50 border border-gray-300 rounded-lg dark:placeholder-gray-500 focus:outline-none" type="text" placeholder="Cari Username" />
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
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                    Sort
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
              DATA PENGGUNA
            </h3>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                  <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Pengguna ↓</th>
                      <th class="px-4 py-3">Nama ↓</th>
                      <th class="px-4 py-3">Username ↓</th>
                      <th class="px-4 py-3">Role ↓</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                    
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">USROOT</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Ramesh Naidu</td>
                      <td class="px-4 py-3">Ramesh36</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-100">Owner</span>
                      </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">USR002</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Keren Kenneth</td>
                      <td class="px-4 py-3">Kren</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-100">Admin</span>
                      </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">USR003</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Eka Pratama</td>
                      <td class="px-4 py-3">EkaPr</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Staff</span>
                      </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">USR004</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Robi Astadi</td>
                      <td class="px-4 py-3">RobyA95</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Staff</span>
                      </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                      <td class="px-4 py-3 font-semibold text-purple-600">USR005</td>
                      <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Joko Budiman</td>
                      <td class="px-4 py-3">Jokowi99</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Staff</span>
                      </td>
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