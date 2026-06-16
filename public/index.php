<?php
session_start();
include 'config.php';

/** @var mysqli $conn */ 
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

date_default_timezone_set('Asia/Jakarta');

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

          <div class="my-8 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
              Selamat Datang, <span class="font-black text-purple-600 dark:text-purple-400"><?php echo htmlspecialchars($_SESSION['nama_pengguna']); ?></span>
            </h2>
            <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider text-white bg-gray-800 dark:bg-gray-600 rounded-full shadow-sm">
              Role: <?php echo ucfirst($_SESSION['role']); ?>
            </span>
          </div>

          <div class="grid gap-6 mb-10 md:grid-cols-2 xl:grid-cols-4">

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-400 dark:bg-green-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200"><?php echo $r_mobil_ready['total'] . '<span class="text-lg text-gray-400 dark:text-gray-500">/' . $r_mobil_total['total'] . '</span>'; ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Unit Tersedia</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-400 dark:bg-blue-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200"><?php echo $r_transaksi['total']; ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Sewa Berjalan</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-400 dark:bg-teal-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200"><?php echo $r_mobil_mt['total']; ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Maintenance</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-400 dark:bg-orange-900/50">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A6.005 6.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200"><?php echo $r_pelanggan['total']; ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Total Member</p>
              </div>
            </div>

          </div>

          <?php if ($_SESSION['role'] === 'owner' || $_SESSION['role'] === 'admin'): ?>
          <div class="grid gap-6 mb-10 md:grid-cols-2">
            
            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-400 dark:bg-purple-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Total Pendapatan (Bulan Ini)</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-transform duration-200 hover:-translate-y-1">
              <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full dark:text-red-400 dark:bg-red-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_denda, 0, ',', '.'); ?></p>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">Total Denda Ditagih (Bulan Ini)</p>
              </div>
            </div>
            
          </div>
          <?php endif; ?>

          <div class="flex items-center justify-between mb-4 mt-2">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-widest border-l-4 border-blue-500 pl-3">
              Radar Unit Keluar (Belum Kembali)
            </h3>
            <a href="pengembalian.php" class="text-xs font-bold text-blue-600 hover:underline dark:text-blue-400">Lihat Modul Pengembalian &rarr;</a>
          </div>

          <div class="w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-12 bg-white dark:bg-gray-800">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-[10px] font-bold tracking-widest text-left text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 uppercase">
                    <th class="px-5 py-3">Pelanggan / ID</th>
                    <th class="px-5 py-3">Armada Dipakai</th>
                    <th class="px-5 py-3">Jatuh Tempo Kembali</th>
                    <th class="px-5 py-3">Status Peringatan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-600 dark:text-gray-300 text-xs font-medium">
                  <?php
                  $q_belum_kembali = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_kembali 
                                                          FROM transaksi_rental t
                                                          JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                          JOIN mobil m ON t.id_mobil = m.id_mobil
                                                          WHERE t.status_sewa = 'berjalan' 
                                                          ORDER BY t.tanggal_kembali ASC LIMIT 5");

                  if (mysqli_num_rows($q_belum_kembali) > 0) {
                    $hari_ini = date('Y-m-d');
                    while ($bk = mysqli_fetch_assoc($q_belum_kembali)) {
                      $tgl_k = $bk['tanggal_kembali'];
                      
                      if ($tgl_k < $hari_ini) {
                        $peringatan = "<span class='px-2.5 py-1 text-[10px] font-extrabold text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700 rounded-md uppercase animate-pulse'>Terlambat!</span>";
                      } elseif ($tgl_k == $hari_ini) {
                        $peringatan = "<span class='px-2.5 py-1 text-[10px] font-extrabold text-orange-700 bg-orange-100 dark:text-orange-100 dark:bg-orange-700 rounded-md uppercase'>Pulang Hari Ini</span>";
                      } else {
                        $peringatan = "<span class='px-2.5 py-1 text-[10px] font-extrabold text-blue-700 bg-blue-100 dark:text-blue-100 dark:bg-blue-700 rounded-md uppercase'>Masih Aman</span>";
                      }
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <td class="px-5 py-3">
                          <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($bk['nama_pelanggan']); ?></p>
                          <p class="font-mono text-[10px] text-purple-500 mt-0.5"><?php echo $bk['id_transaksi']; ?></p>
                        </td>
                        <td class="px-5 py-3">
                          <p class="font-bold"><?php echo htmlspecialchars($bk['merk'] . ' ' . $bk['tipe']); ?></p>
                          <p class="text-[10px] text-gray-400 mt-0.5"><?php echo $bk['nomor_polisi']; ?></p>
                        </td>
                        <td class="px-5 py-3 font-semibold <?php echo ($tgl_k < $hari_ini) ? 'text-red-500' : ''; ?>">
                          <?php echo date('d M Y', strtotime($tgl_k)); ?>
                        </td>
                        <td class="px-5 py-3"><?php echo $peringatan; ?></td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500 italic">✨ Garasi penuh! Tidak ada unit yang sedang disewa saat ini.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex items-center mb-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-widest border-l-4 border-purple-500 pl-3">
              Arsip Transaksi Terbaru
            </h3>
          </div>

          <div class="w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-8 bg-white dark:bg-gray-800">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-[10px] font-bold tracking-widest text-left text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 uppercase">
                    <th class="px-5 py-3">ID Transaksi</th>
                    <th class="px-5 py-3">Pelanggan</th>
                    <th class="px-5 py-3">Mobil</th>
                    <th class="px-5 py-3">Tanggal Sewa</th>
                    <th class="px-5 py-3">Status Data</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-600 dark:text-gray-300 text-xs font-medium">

                  <?php
                  $q_table = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_sewa, t.status_sewa 
                                                   FROM transaksi_rental t
                                                   JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                   JOIN mobil m ON t.id_mobil = m.id_mobil
                                                   ORDER BY t.tanggal_sewa DESC, t.id_transaksi DESC 
                                                   LIMIT $posisi_awal, $batas_data");

                  if (mysqli_num_rows($q_table) > 0) {
                    while ($row = mysqli_fetch_assoc($q_table)) {
                      
                      // FIX BADGE: Menggunakan warna yang aman di compile Windmill
                      if (strtolower($row['status_sewa']) === 'berjalan') {
                        $badge_class = "text-blue-700 bg-blue-100 dark:text-blue-100 dark:bg-blue-700";
                      } elseif (strtolower($row['status_sewa']) === 'booking') {
                        $badge_class = "text-orange-700 bg-orange-100 dark:text-orange-100 dark:bg-orange-700";
                      } elseif (strtolower($row['status_sewa']) === 'selesai') {
                        $badge_class = "text-green-700 bg-green-100 dark:text-green-100 dark:bg-green-700";
                      } else {
                        $badge_class = "text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700";
                      }
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <td class="px-5 py-3 font-mono font-bold text-purple-600 dark:text-purple-400"><?php echo $row['id_transaksi']; ?></td>
                        <td class="px-5 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                        <td class="px-5 py-3"><?php echo htmlspecialchars($row['merk'] . ' ' . $row['tipe']); ?> <span class="text-[10px] text-gray-400 ml-1">(<?php echo htmlspecialchars($row['nomor_polisi']); ?>)</span></td>
                        <td class="px-5 py-3"><?php echo date('d M Y', strtotime($row['tanggal_sewa'])); ?></td>
                        <td class="px-5 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-extrabold rounded-md uppercase <?php echo $badge_class; ?>">
                            <?php echo $row['status_sewa']; ?>
                          </span>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500 italic">Belum ada riwayat transaksi sewa masuk.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <div class="grid px-5 py-4 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t border-gray-100 dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:bg-gray-800 dark:text-gray-400">
              <span class="flex items-center sm:col-span-3 text-[11px] normal-case text-gray-400 dark:text-gray-500">
                Menampilkan <?php echo ($total_semua_transaksi > 0) ? $posisi_awal + 1 : 0; ?>-<?php echo min($posisi_awal + $batas_data, $total_semua_transaksi); ?> dari <?php echo $total_semua_transaksi; ?> data
              </span>
              <span class="sm:col-span-2"></span>
              
              <span class="flex col-span-4 mt-2 sm:mt-0 sm:justify-end">
                <nav aria-label="Table navigation">
                  <ul class="inline-flex items-center space-x-1">
                    
                    <li>
                      <?php if ($halaman_aktif > 1): ?>
                        <a href="index.php?p_trans=<?php echo $halaman_aktif - 1; ?>" class="px-3 py-1.5 rounded-md focus:outline-none focus:shadow-outline-purple hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center shadow-sm border border-gray-200 dark:border-gray-600" aria-label="Previous">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </a>
                      <?php else: ?>
                        <button disabled class="px-3 py-1.5 rounded-md opacity-30 cursor-not-allowed flex items-center border border-gray-200 dark:border-gray-600">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </button>
                      <?php endif; ?>
                    </li>

                    <li class="px-3 py-1.5 text-gray-500 normal-case text-[11px] font-medium">
                      Hal <span class="text-purple-600 dark:text-purple-400 font-bold text-sm mx-1"><?php echo $halaman_aktif; ?></span> / <?php echo max(1, $total_halaman); ?>
                    </li>

                    <li>
                      <?php if ($halaman_aktif < $total_halaman): ?>
                        <a href="index.php?p_trans=<?php echo $halaman_aktif + 1; ?>" class="px-3 py-1.5 rounded-md focus:outline-none focus:shadow-outline-purple hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center shadow-sm border border-gray-200 dark:border-gray-600" aria-label="Next">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </a>
                      <?php else: ?>
                        <button disabled class="px-3 py-1.5 rounded-md opacity-30 cursor-not-allowed flex items-center border border-gray-200 dark:border-gray-600">
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