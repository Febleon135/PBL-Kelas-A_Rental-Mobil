<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <?php include 'components/head.php'; ?>
  </head>
  <body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
      <?php include 'components/sidebar.php'; ?>

      <div class="flex flex-col flex-1 w-full">
        <?php include 'components/header.php'; ?>

        <main class="h-full overflow-y-auto">
          <div class="container px-6 mx-auto grid">

            <?php
            // Alur Deteksi Halaman Detail (Halaman 7)
            if (isset($_GET['action']) && $_GET['action'] == 'detail') {
            ?>
              <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Pengembalian Mobil
              </h2>

              <div class="mb-4">
                <a href="pengembalian.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200 dark:text-purple-300 dark:bg-gray-700">
                  ← Kembali ke Daftar Transaksi
                </a>
              </div>

              <h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">
                Detail Transaksi
              </h4>
              <div class="p-6 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 text-sm">
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block font-medium">ID Transaksi</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold text-base">TR001</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block font-medium">Pelanggan</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold text-base">Agus Wijayanto</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block font-medium">Mobil</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold text-base">Avanza B 1234 DF</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block font-medium">Tgl Kembali (Seharusnya)</span>
                    <span class="text-red-600 dark:text-red-400 font-semibold text-base">24 Mei 2026 12:00</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block font-medium">Harga Sewa / Hari</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold text-base">Rp350.000</span>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 mb-8">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                  <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300 text-md border-b pb-2 dark:border-gray-700">
                    Form Pengecekan Akhir
                  </h4>
                  <form action="pengembalian.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal Cek</span>
                      <input type="date" value="2026-05-24" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">KM Mobil Terakhir</span>
                      <input type="number" placeholder="12950" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Body</span>
                      <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                        <option>Baik / Mulus</option>
                        <option>Lecet Ringan</option>
                        <option>Penyok / Rusak Berat</option>
                      </select>
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Mesin</span>
                      <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                        <option>Normal</option>
                        <option>Bermasalah</option>
                      </select>
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Indikator Bensin</span>
                      <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                        <option>Full</option>
                        <option>3/4</option>
                        <option>1/2</option>
                        <option>Empty</option>
                      </select>
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Foto Bukti Fisik Mobil</span>
                      <input type="file" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>
                    <div class="flex items-center justify-end pt-4 border-t dark:border-gray-700">
                      <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        Save Pengecekan
                      </button>
                    </div>
                  </form>
                </div>

                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                  <h4 class="mb-4 font-semibold text-red-600 dark:text-red-400 text-md border-b pb-2 dark:border-gray-700">
                    Form Denda Kerusakan
                  </h4>
                  <form action="pengembalian.php" method="POST" class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Letak Kerusakan</span>
                      <input type="text" placeholder="Contoh: Bumper depan, kaca spion kanan" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-red-400" />
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Biaya Denda (Rp)</span>
                      <input type="number" placeholder="Contoh: 300000" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-red-400" />
                    </label>
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Keterangan / Kronologi</span>
                      <textarea class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-red-400" rows="5" placeholder="Detail kronologi kerusakan aset..."></textarea>
                    </label>
                    <div class="flex items-center justify-end pt-4 border-t dark:border-gray-700">
                      <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Save Denda
                      </button>
                    </div>
                  </form>
                </div>
              </div>

            <?php
            } else {
            ?>
              <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                  
                  <div class="md:col-span-7 flex flex-wrap items-center gap-2 w-full">
                    <div class="relative w-full max-w-xs text-gray-500 focus-within:text-purple-600">
                      <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                          <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                      </div>
                      <input class="w-full pl-9 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-500 bg-gray-50 border border-gray-300 rounded-lg dark:placeholder-gray-500 dark:bg-gray-700 dark:text-gray-200 focus:placeholder-gray-500 focus:bg-white focus:border-purple-300 focus:outline-none focus:shadow-outline-purple form-input" type="text" placeholder="Cari Nama/Nopol Kendaraan" />
                    </div>

                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm transition-colors duration-150">
                      <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.212 8H17"></path>
                      </svg>
                      Refresh
                    </button>
                  </div>

                  <div class="md:col-span-5 flex flex-wrap items-center justify-start md:justify-end gap-2 w-full">
                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm">
                      <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      Delete
                    </button>
                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm">
                      <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                      Filters
                    </button>
                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm">
                      <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                      Edit
                    </button>
                    <button class="inline-flex items-center px-4 py-1.5 text-xs font-semibold tracking-wide text-white bg-black rounded-lg hover:bg-gray-900 dark:bg-gray-100 dark:text-black dark:hover:bg-white shadow transition-colors duration-150">
                      <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                      Add
                    </button>
                  </div>

                </div>
              </div>

              <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 tracking-tight">
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
                        <th class="px-4 py-3 text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                      
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-medium">TR001</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Agus Wijayanto</td>
                        <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Avanza B 1234 DF</td>
                        <td class="px-4 py-3 text-gray-500">19 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold leading-tight text-white bg-blue-600 rounded-full">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-white rounded-full"></span>Berjalan
                          </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                          <a href="pengembalian.php?action=detail" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-wider">
                            Pilih
                          </a>
                        </td>
                      </tr>

                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-medium">TR002</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Budi Kurnia</td>
                        <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Innova D 4567 EF</td>
                        <td class="px-4 py-3 text-gray-500">20 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold leading-tight text-white bg-blue-600 rounded-full">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-white rounded-full"></span>Berjalan
                          </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                          <a href="pengembalian.php?action=detail" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-wider">
                            Pilih
                          </a>
                        </td>
                      </tr>

                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-medium">TR003</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Eka Pratama</td>
                        <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Brio F 8901 DC</td>
                        <td class="px-4 py-3 text-gray-500">21 MEI 2026</td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold leading-tight text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-100 rounded-full border border-red-300 dark:border-red-700">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-red-600 rounded-full"></span>Terlambat
                          </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                          <a href="pengembalian.php?action=detail" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-wider">
                            Pilih
                          </a>
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