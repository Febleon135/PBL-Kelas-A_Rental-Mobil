<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI HAK AKSES: Izinkan Owner dan Admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'admin')) {
  header("Location: index.php");
  exit;
}

$role_sekarang = $_SESSION['role'];

// ─── SETUP FILTER DINAMIS BULAN & TAHUN ───
$q_tahun = mysqli_query($conn, "SELECT MIN(YEAR(tanggal_sewa)) as tahun_awal FROM transaksi_rental");
$r_tahun = mysqli_fetch_assoc($q_tahun);
$tahun_awal = $r_tahun['tahun_awal'] ?? date('Y');
if (empty($tahun_awal)) $tahun_awal = date('Y');
$tahun_sekarang_sistem = date('Y');

$filter_bulan = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : date('m');
$filter_tahun = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : date('Y');

$where_transaksi = "MONTH(t.tanggal_kembali) = '$filter_bulan' AND YEAR(t.tanggal_kembali) = '$filter_tahun'";
$where_maintenance = "MONTH(mn.tgl_service) = '$filter_bulan' AND YEAR(mn.tgl_service) = '$filter_tahun'";

$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];


// ─── QUERY LOGISTIK & OPERASIONAL REAL-TIME ───
$q_total_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental t WHERE t.status_sewa = 'selesai' AND $where_transaksi");
$r_total_transaksi = mysqli_fetch_assoc($q_total_transaksi);
$total_transaksi = $r_total_transaksi['total'] ?? 0;

$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan");
$r_pelanggan = mysqli_fetch_assoc($q_pelanggan);
$total_pelanggan = $r_pelanggan['total'] ?? 0;

$q_fav = mysqli_query($conn, "SELECT m.merk, m.tipe, COUNT(t.id_mobil) as jumlah 
                              FROM transaksi_rental t 
                              JOIN mobil m ON t.id_mobil = m.id_mobil 
                              WHERE t.status_sewa = 'selesai' AND $where_transaksi
                              GROUP BY t.id_mobil ORDER BY jumlah DESC LIMIT 1");
$r_fav = mysqli_fetch_assoc($q_fav);
$mobil_terfavorit = $r_fav ? $r_fav['merk'] . " " . $r_fav['tipe'] . " (" . $r_fav['jumlah'] . "x)" : "Belum ada transaksi";


// ─── QUERY FINANSIAL REAL-TIME ───
$q_duit_sewa = mysqli_query($conn, "SELECT SUM(t.total_biaya) as total FROM transaksi_rental t WHERE t.status_sewa = 'selesai' AND $where_transaksi");
$r_duit_sewa = mysqli_fetch_assoc($q_duit_sewa);
$duit_sewa = $r_duit_sewa['total'] ?? 0;

$total_denda = 0;
$cek_tabel_denda = mysqli_query($conn, "SHOW TABLES LIKE 'denda_kerusakan'");
if (mysqli_num_rows($cek_tabel_denda) > 0) {
  $q_duit_denda = mysqli_query($conn, "SELECT SUM(d.biaya_denda) as total 
                                      FROM denda_kerusakan d
                                      JOIN transaksi_rental t ON d.id_transaksi = t.id_transaksi
                                      WHERE t.status_sewa = 'selesai' AND $where_transaksi");
  $r_duit_denda = mysqli_fetch_assoc($q_duit_denda);
  $total_denda = $r_duit_denda['total'] ?? 0;
}

$total_omzet = $duit_sewa + $total_denda;

$q_duit_maint = mysqli_query($conn, "SELECT SUM(mn.biaya_service) as total FROM maintenance mn WHERE mn.status_maintenance = 'selesai' AND $where_maintenance");
$r_duit_maint = mysqli_fetch_assoc($q_duit_maint);
$total_bengkel = $r_duit_maint['total'] ?? 0;

$profit_bersih = $total_omzet - $total_bengkel;
?>

<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
  <?php include 'components/head.php'; ?>
  <style>
    /* MAGIC CSS UNTUK PRINT/CETAK PDF YANG SUDAH DI-FIX TOTAL */
    @media print {
      @page { margin: 1cm; size: auto; }
      
      /* Reset Warna Background Kertas */
      body { background-color: white !important; }
      
      /* Hapus Elemen yang Gak Perlu Diprint dengan display:none biar gak ninggalin ruang kosong */
      aside, header, #form-filter, #btn-cetak { display: none !important; }
      
      /* Bongkar Struktur Flexbox Dashboard yang bikin margin nyangkut */
      .flex.h-screen { display: block !important; height: auto !important; overflow: visible !important; }
      .flex-col.flex-1 { display: block !important; width: 100% !important; }
      main { overflow: visible !important; height: auto !important; padding: 0 !important; margin: 0 !important; }
      
      /* Paksa Container Utama Jadi 100% Lebar Kertas */
      .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
      
      /* Paksa Grid Box Card Menjadi 2 Kolom Sejajar (Biar Gak Memanjang ke Bawah) */
      .grid.gap-6.mb-8 {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 1rem !important;
      }
      
      /* Kalibrasi Ulang Warna Text & Border untuk Mode Print */
      .dark\:bg-gray-900, .dark\:bg-gray-800, .bg-white { background-color: white !important; }
      .dark\:text-white, .dark\:text-gray-200, .text-gray-800, .text-gray-900 { color: black !important; }
      .shadow-sm, .shadow-md { box-shadow: none !important; border: 1px solid #d1d5db !important; }
      
      /* Render Background Berwarna (Badge & Icon) Secara Presisi */
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
  </style>
</head>

<body>
  <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
    <?php include 'components/sidebar.php'; ?>

    <div class="flex flex-col flex-1 w-full min-w-0">
      <?php include 'components/header.php'; ?>

      <main class="h-full overflow-y-auto">
        <div class="container px-6 mx-auto grid">

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center my-6 gap-4 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white uppercase tracking-wider">
              Laporan Analitik Finansial
            </h2>
            <button onclick="window.print()" id="btn-cetak" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition-colors flex items-center gap-2">
              🖨️ Cetak Laporan PDF
            </button>
          </div>

          <div id="form-filter" class="mb-8 bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-4 items-end sm:items-center w-full">
            <form action="laporan.php" method="GET" class="flex flex-wrap items-end gap-4 w-full">
              <div class="w-full sm:w-auto">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Filter Bulan</label>
                <select name="filter_bulan" class="block w-full sm:w-48 text-sm dark:text-gray-200 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 rounded-lg font-semibold cursor-pointer">
                  <?php for($i=1; $i<=12; $i++): 
                    $val_bulan = str_pad($i, 2, "0", STR_PAD_LEFT);
                  ?>
                    <option value="<?php echo $val_bulan; ?>" <?php echo ($filter_bulan == $val_bulan) ? 'selected' : ''; ?>>
                      <?php echo $nama_bulan[$i]; ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>

              <div class="w-full sm:w-auto">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Filter Tahun</label>
                <select name="filter_tahun" class="block w-full sm:w-40 text-sm dark:text-gray-200 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 rounded-lg font-semibold cursor-pointer">
                  <?php for($th = $tahun_sekarang_sistem; $th >= $tahun_awal; $th--): ?>
                    <option value="<?php echo $th; ?>" <?php echo ($filter_tahun == $th) ? 'selected' : ''; ?>>
                      <?php echo $th; ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>

              <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-sm w-full sm:w-auto">
                Terapkan Filter
              </button>
            </form>
          </div>

          <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest text-center sm:text-left border-l-4 border-purple-500 pl-3">
              Ringkasan Data Periode: <span class="text-purple-600 dark:text-purple-400"><?php echo $nama_bulan[intval($filter_bulan)] . " " . $filter_tahun; ?></span>
            </h3>
          </div>

          <div class="grid gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
            <div class="flex items-center p-5 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-green-600 bg-green-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path></svg>
              </div>
              <div>
                <p class="text-xl font-extrabold text-gray-800 dark:text-gray-100">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Total Omzet Masuk</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-red-600 bg-red-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              </div>
              <div>
                <p class="text-xl font-extrabold text-red-600 dark:text-red-400">Rp <?php echo number_format($total_bengkel, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Biaya Bengkel Unit</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-purple-600 rounded-xl border border-purple-500 shadow-md md:col-span-2 lg:col-span-1">
              <div class="p-3 mr-4 text-purple-700 bg-white rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-black text-white">Rp <?php echo number_format($profit_bersih, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-purple-200 uppercase tracking-wider mt-1">Profit Laba Bersih</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-blue-600 bg-blue-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?php echo $total_transaksi; ?> Rit</p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Transaksi Selesai</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-teal-600 bg-teal-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?php echo $total_pelanggan; ?> Org</p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Total Konsumen</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-amber-600 bg-amber-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path></svg>
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($mobil_terfavorit); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Armada Produktif</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 w-full mb-12">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 border-b pb-3 dark:border-gray-700">
              Rincian Nota Transaksi Masuk (<?php echo $nama_bulan[intval($filter_bulan)] . " " . $filter_tahun; ?>)
            </h3>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap text-xs text-left">
                  <thead>
                    <tr class="font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">Tgl Kembali</th>
                      <th class="px-4 py-3">ID / Pelanggan</th>
                      <th class="px-4 py-3">Unit Mobil</th>
                      <th class="px-4 py-3">Sewa Pokok</th>
                      <th class="px-4 py-3">Denda (Jika Ada)</th>
                      <th class="px-4 py-3">Total Sub-Omzet</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <?php
                    $q_detail_trans = mysqli_query($conn, "SELECT t.*, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi 
                                                           FROM transaksi_rental t
                                                           JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                           JOIN mobil m ON t.id_mobil = m.id_mobil
                                                           WHERE t.status_sewa = 'selesai' AND $where_transaksi
                                                           ORDER BY t.tanggal_kembali DESC");
                    
                    if (mysqli_num_rows($q_detail_trans) > 0) {
                      while ($dt = mysqli_fetch_assoc($q_detail_trans)) {
                        $denda_per_transaksi = 0;
                        if (mysqli_num_rows($cek_tabel_denda) > 0) {
                          $id_t = $dt['id_transaksi'];
                          $q_cek_d = mysqli_query($conn, "SELECT biaya_denda FROM denda_kerusakan WHERE id_transaksi = '$id_t'");
                          if ($r_cek_d = mysqli_fetch_assoc($q_cek_d)) {
                            $denda_per_transaksi = $r_cek_d['biaya_denda'];
                          }
                        }
                        
                        $sub_omzet = $dt['total_biaya'] + $denda_per_transaksi;
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                          <td class="px-4 py-3 font-semibold"><?php echo date('d-m-Y', strtotime($dt['tanggal_kembali'])); ?></td>
                          <td class="px-4 py-3">
                            <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($dt['nama_pelanggan']); ?></p>
                            <p class="font-mono text-[10px] text-purple-500 mt-0.5"><?php echo $dt['id_transaksi']; ?></p>
                          </td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($dt['merk'] . " " . $dt['tipe']); ?></td>
                          <td class="px-4 py-3 text-green-600 font-medium">Rp <?php echo number_format($dt['total_biaya'], 0, ',', '.'); ?></td>
                          <td class="px-4 py-3 <?php echo ($denda_per_transaksi > 0) ? 'text-amber-600 font-bold' : 'text-gray-400'; ?>">
                            <?php echo ($denda_per_transaksi > 0) ? 'Rp ' . number_format($denda_per_transaksi, 0, ',', '.') : '-'; ?>
                          </td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Rp <?php echo number_format($sub_omzet, 0, ',', '.'); ?></td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Belum ada transaksi selesai pada periode ini.</td></tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</body>

</html>