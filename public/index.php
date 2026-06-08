<?php
session_start();
include 'config.php';

/** @var mysqli $conn */ 
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── QUERY UMUM (Bisa Dilihat Semua Role) ───
$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan");
$r_pelanggan = mysqli_fetch_assoc($q_pelanggan);

$q_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental WHERE status_sewa = 'berjalan'");
$r_transaksi = mysqli_fetch_assoc($q_transaksi);

$q_mobil_ready = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'tersedia'");
$r_mobil_ready = mysqli_fetch_assoc($q_mobil_ready);

$q_mobil_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil");
$r_mobil_total = mysqli_fetch_assoc($q_mobil_total);

$q_mobil_mt = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'maintenance'");
$r_mobil_mt = mysqli_fetch_assoc($q_mobil_mt);

// ─── QUERY TAMBAHAN KHUSUS ADMIN/STAFF (NON-SENSITIF) ───
$q_mobil_rented = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'disewa'");
$r_mobil_rented = mysqli_fetch_assoc($q_mobil_rented);

$q_sewa_done = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
$r_sewa_done = mysqli_fetch_assoc($q_sewa_done);


// ─── REVISI POLICY: DATA KAS SEKARANG BISA DIAKSES OLEH OWNER MAUPUN ADMIN ───
$total_denda = 0;
$total_omzet = 0;

if ($_SESSION['role'] === 'owner' || $_SESSION['role'] === 'admin') {
  $q_denda = mysqli_query($conn, "SELECT SUM(biaya_denda) as total FROM denda_kerusakan");
  $r_denda = mysqli_fetch_assoc($q_denda);
  $total_denda = $r_denda['total'] ?? 0;

  $q_omzet = mysqli_query($conn, "SELECT SUM(total_biaya) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
  $r_omzet = mysqli_fetch_assoc($q_omzet);
  $total_omzet = $r_omzet['total'] ?? 0;
}

// ─── LOGIKA PAGINATION ───
$batas_data = 10;
$halaman_aktif = isset($_GET['p_trans']) ? intval($_GET['p_trans']) : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;

$posisi_awal = ($halaman_aktif - 1) * $batas_data;

$q_hitung_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental");
$r_hitung_total = mysqli_fetch_assoc($q_hitung_total);
$total_semua_transaksi = $r_hitung_total['total'] ?? 0;

$total_halaman = ceil($total_semua_transaksi / $batas_data);
?>
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
            Dashboard - Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_pengguna']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)
          </h2>

          <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">

            <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
              <div class="p-3 mr-4 text-orange-500 bg-orange-100 dark:bg-orange-900/50 dark:text-orange-400 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A6.005 6.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_pelanggan['total']; ?></p>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Pelanggan</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
              <div class="p-3 mr-4 text-green-500 bg-green-100 dark:bg-green-900/50 dark:text-green-400 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_mobil_ready['total'] . '/' . $r_mobil_total['total']; ?></p>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Mobil Tersedia</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
              <div class="p-3 mr-4 text-amber-500 bg-amber-100 dark:bg-amber-900/50 dark:text-amber-400 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_mobil_mt['total']; ?></p>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Mobil Maintenance</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
              <div class="p-3 mr-4 text-blue-500 bg-blue-100 dark:bg-blue-900/50 dark:text-blue-400 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_transaksi['total']; ?></p>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Transaksi Aktif</p>
              </div>
            </div>

            <?php if ($_SESSION['role'] === 'owner' || $_SESSION['role'] === 'admin'): ?>
              <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
                <div class="p-3 mr-4 text-red-500 bg-red-100 dark:bg-red-900/50 dark:text-red-400 rounded-full">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_denda, 0, ',', '.'); ?></p>
                  <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Denda (Bulan Ini)</p>
                </div>
              </div>

              <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
                <div class="p-3 mr-4 text-purple-500 bg-purple-100 dark:bg-purple-900/50 dark:text-purple-400 rounded-full">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></p>
                  <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Pendapatan (Bulan Ini)</p>
                </div>
              </div>

            <?php else: ?>
              <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-150">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 dark:bg-teal-900/50 dark:text-teal-400 rounded-full">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_sewa_done['total']; ?></p>
                  <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Transaksi Selesai</p>
                </div>
              </div>
            <?php endif; ?>

          </div>

          <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">
            Transaksi Terbaru
          </h3>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8 bg-white dark:bg-gray-800">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-bold tracking-wide text-left text-gray-500 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-400 uppercase">
                    <th class="px-4 py-3">ID Transaksi</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Mobil</th>
                    <th class="px-4 py-3">Tanggal Sewa</th>
                    <th class="px-4 py-3">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300 text-xs font-medium">

                  <?php
                  $q_table = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_sewa, t.status_sewa 
                                                   FROM transaksi_rental t
                                                   JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                   JOIN mobil m ON t.id_mobil = m.id_mobil
                                                   ORDER BY t.tanggal_sewa DESC, t.id_transaksi DESC 
                                                   LIMIT $posisi_awal, $batas_data");

                  if (mysqli_num_rows($q_table) > 0) {
                    while ($row = mysqli_fetch_assoc($q_table)) {
                      
                      // FIX BADGE HIGH-CONTRAST SOLID (ANTI-HILANG PAS SIANG, ANTI-BLUR PAS MALAM)
                      if ($row['status_sewa'] == 'berjalan') {
                        $badge_class = "bg-blue-600 text-white dark:bg-blue-700 dark:text-blue-100";
                      } elseif ($row['status_sewa'] == 'selesai') {
                        $badge_class = "bg-green-600 text-white dark:bg-green-700 dark:text-green-100";
                      } else {
                        $badge_class = "bg-red-600 text-white dark:bg-red-700 dark:text-red-100";
                      }
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-purple-600 dark:text-purple-400"><?php echo $row['id_transaksi']; ?></td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['merk'] . ' ' . $row['tipe'] . ' (' . $row['nomor_polisi'] . ')'); ?></td>
                        <td class="px-4 py-3"><?php echo date('d M Y', strtotime($row['tanggal_sewa'])); ?></td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-extrabold rounded-md uppercase <?php echo $badge_class; ?>">
                            <?php echo $row['status_sewa']; ?>
                          </span>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500 italic">Belum ada riwayat transaksi sewa masuk.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t border-gray-200 dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:bg-gray-800 dark:text-gray-400">
              <span class="flex items-center sm:col-span-3 text-[11px] normal-case text-gray-400 dark:text-gray-500">
                Menampilkan <?php echo ($total_semua_transaksi > 0) ? $posisi_awal + 1 : 0; ?>-<?php echo min($posisi_awal + $batas_data, $total_semua_transaksi); ?> dari <?php echo $total_semua_transaksi; ?> data
              </span>
              <span class="sm:col-span-2"></span>
              
              <span class="flex col-span-4 mt-2 sm:mt-0 sm:justify-end">
                <nav aria-label="Table navigation">
                  <ul class="inline-flex items-center space-x-1">
                    
                    <li>
                      <?php if ($halaman_aktif > 1): ?>
                        <a href="index.php?p_trans=<?php echo $halaman_aktif - 1; ?>" class="px-3 py-1 rounded-md rounded-l-lg focus:outline-none focus:shadow-outline-purple hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center" aria-label="Previous">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </a>
                      <?php else: ?>
                        <button disabled class="px-3 py-1 rounded-md rounded-l-lg opacity-30 cursor-not-allowed flex items-center">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </button>
                      <?php endif; ?>
                    </li>

                    <li class="px-3 py-1 text-gray-400 normal-case text-[11px] dark:text-gray-500">
                      Halaman <span class="text-gray-700 dark:text-gray-200 font-bold"><?php echo $halaman_aktif; ?></span> dari <?php echo max(1, $total_halaman); ?>
                    </li>

                    <li>
                      <?php if ($halaman_aktif < $total_halaman): ?>
                        <a href="index.php?p_trans=<?php echo $halaman_aktif + 1; ?>" class="px-3 py-1 rounded-md rounded-r-lg focus:outline-none focus:shadow-outline-purple hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center" aria-label="Next">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </a>
                      <?php else: ?>
                        <button disabled class="px-3 py-1 rounded-md rounded-r-lg opacity-30 cursor-not-allowed flex items-center">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </button>
                      <?php endif; ?>
                    </li>

                  </ul>
                </nav>
              </span>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</body>

</html>