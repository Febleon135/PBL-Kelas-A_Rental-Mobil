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
              Data Penyewaan
            </h2>

            <?php
            // Alur Deteksi: Tampilkan Form Tambah Transaksi Baru (Halaman 7 PDF) jika parameter URL aktif
            if (isset($_GET['action']) && $_GET['action'] == 'add') {
            ?>
              <div class="mb-4">
                <a href="penyewaan.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200 dark:text-purple-300 dark:bg-gray-700">
                  ← Kembali ke Daftar Penyewaan
                </a>
              </div>

              <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-8">
                <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide border-b pb-2 dark:border-gray-700">
                  Tambah Data Transaksi
                </h4>

                <div class="flex gap-4 mb-6">
                  <button class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg">Pilih dari data yang sudah ada</button>
                  <a href="pelanggan.php?action=add" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Masukkan data baru</a>
                </div>

                <form action="penyewaan.php" method="POST" class="space-y-4 max-w-2xl">
                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Pilih Pelanggan</span>
                    <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                      <option>— Pilih Pelanggan —</option>
                      <option>Agus Wijayanto</option>
                      <option>Budi Kurnia</option>
                      <option>Eka Pratama</option>
                    </select>
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Pilih Mobil</span>
                    <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                      <option>— Pilih Mobil —</option>
                      <option>Avanza (B 1234 DF)</option>
                      <option>Innova (D 4567 EF)</option>
                      <option>Brio (F 8901 DC)</option>
                    </select>
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal Sewa</span>
                    <input type="date" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Durasi Sewa (Hari)</span>
                    <input type="number" placeholder="Masukkan Durasi Sewa (Hari)" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Status Awal Transaksi</span>
                    <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                      <option value="Berjalan">Berjalan</option>
                    </select>
                  </label>

                  <div class="flex items-center justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                      Save
                    </button>
                  </div>
                </form>
              </div>

            <?php
            } else {
            ?>
              <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
                <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                  <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                  </div>
                  <div>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">12</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Hari Ini</p>
                  </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                  <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                  </div>
                  <div>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">7</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mobil Sedang Berjalan</p>
                  </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                  <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                  </div>
                  <div>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">5</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Selesai</p>
                  </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                  <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  </div>
                  <div>
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">2</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mobil Terlambat</p>
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
                      <input class="w-full pl-9 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-500 bg-gray-50 border border-gray-300 rounded-lg dark:placeholder-gray-500 focus:outline-none" type="text" placeholder="Cari ID Transaksi / Nama" />
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
                    <a href="penyewaan.php?action=add" class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-black shadow transition-colors duration-150 ml-1">
                      <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                      Add
                    </a>
                  </div>

                </div>
              </div>

              <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">
                Transaksi Terbaru
              </h3>

              <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
                <div class="w-full overflow-x-auto">
                  <table class="w-full whitespace-no-wrap">
                    <thead>
                      <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        <th class="px-4 py-3">ID Transaksi ↓</th>
                        <th class="px-4 py-3">Pelanggan ↓</th>
                        <th class="px-4 py-3">Mobil ↓</th>
                        <th class="px-4 py-3">Tanggal Sewa ↓</th>
                        <th class="px-4 py-3">Status ↓</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600">TR001</td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Agus Wijayanto</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">Avanza B 1234 DF</td>
                        <td class="px-4 py-3">19 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-white bg-blue-600 rounded-full">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-white rounded-full"></span>Berjalan
                          </span>
                        </td>
                      </tr>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600">TR002</td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Budi Kurnia</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">Innova D 4567 EF</td>
                        <td class="px-4 py-3">20 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-white bg-green-600 rounded-full">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-white rounded-full"></span>Selesai
                          </span>
                        </td>
                      </tr>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600">TR003</td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Eka Pratama</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">Brio F 8901 DC</td>
                        <td class="px-4 py-3">21 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-100 rounded-full border border-red-300">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-red-600 rounded-full"></span>Terlambat
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php
            }
            ?>

          </div>
        </main>
      </div>
    </div>
  </body>
</html>