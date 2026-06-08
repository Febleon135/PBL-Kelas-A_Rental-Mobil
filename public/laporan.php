<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI HAK AKSES: Izinkan Owner dan Admin, tendang Staff atau yang belum login
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'admin')) {
  header("Location: index.php");
  exit;
}

$role_sekarang = $_SESSION['role'];

// ─── QUERY LOGISTIK & OPERASIONAL (BISA DIAKSES OWNER & ADMIN) ───
// 1. Hitung total transaksi rental yang sukses dilakukan
$q_total_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
$r_total_transaksi = mysqli_fetch_assoc($q_total_transaksi);
$total_transaksi = $r_total_transaksi['total'] ?? 0;

// 2. Hitung total basis pelanggan terdaftar
$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan");
$r_pelanggan = mysqli_fetch_assoc($q_pelanggan);
$total_pelanggan = $r_pelanggan['total'] ?? 0;

// 3. Cari armada mobil terfavorit (Paling banyak disewa)
$q_fav = mysqli_query($conn, "SELECT m.merk, m.tipe, COUNT(t.id_mobil) as jumlah 
                              FROM transaksi_rental t 
                              JOIN mobil m ON t.id_mobil = m.id_mobil 
                              WHERE t.status_sewa = 'selesai'
                              GROUP BY t.id_mobil ORDER BY jumlah DESC LIMIT 1");
$r_fav = mysqli_fetch_assoc($q_fav);
$mobil_terfavorit = $r_fav ? $r_fav['merk'] . " " . $r_fav['tipe'] . " (" . $r_fav['jumlah'] . "x)" : "Belum ada data";


// ─── REVISI POLICY: KINI DATA FINANSIAL BISA DIAKSES OLEH OWNER MAUPUN ADMIN ───
$total_omzet = 0;
$total_bengkel = 0;
$profit_bersih = 0;

// 1. Hitung akumulasi biaya sewa pokok
$q_duit_sewa = mysqli_query($conn, "SELECT SUM(total_biaya) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
$r_duit_sewa = mysqli_fetch_assoc($q_duit_sewa);

// 2. Hitung denda keseluruhan (waktu + kerusakan) menggunakan kolom BIAYA_DENDA sesuai ERD
$total_denda = 0;
$cek_tabel_denda = mysqli_query($conn, "SHOW TABLES LIKE 'denda_kerusakan'");
if (mysqli_num_rows($cek_tabel_denda) > 0) {
  $q_duit_denda = mysqli_query($conn, "SELECT SUM(biaya_denda) as total FROM denda_kerusakan");
  $r_duit_denda = mysqli_fetch_assoc($q_duit_denda);
  $total_denda = $r_duit_denda['total'] ?? 0;
}

$total_omzet = ($r_duit_sewa['total'] ?? 0) + $total_denda;

// 3. Hitung biaya keluar untuk maintenance armada
$q_duit_maint = mysqli_query($conn, "SELECT SUM(biaya_service) as total FROM maintenance WHERE status_maintenance = 'selesai'");
$r_duit_maint = mysqli_fetch_assoc($q_duit_maint);
$total_bengkel = $r_duit_maint['total'] ?? 0;

// 4. Hitung laba bersih perusahaan WheelsRent
$profit_bersih = $total_omzet - $total_bengkel;

// ─── REVISI POLICY: PROSES TUTUP BUKU BULANAN SEKARANG BISA DIEKSEKUSI OWNER & ADMIN ───
if (isset($_POST['arsip_laporan'])) {
  $periode_aktif = date('Y-m-01'); 

  // Cek apakah periode bulan ini sudah pernah dikunci di database biar tidak ganda
  $cek_arsip = mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE periode_laporan = '$periode_aktif'");
  if (mysqli_num_rows($cek_arsip) > 0) {
    header("Location: laporan.php?status=already-archived");
    exit;
  }

  // Auto-generate ID Laporan baru (LAP001, LAP002, dst)
  $q_id_l   = mysqli_query($conn, "SELECT id_laporan FROM laporan ORDER BY id_laporan DESC LIMIT 1");
  $row_id_l = mysqli_fetch_assoc($q_id_l);
  $id_lap_baru = $row_id_l ? "LAP" . sprintf("%03d", substr($row_id_l['id_laporan'], 3) + 1) : "LAP001";

  $fix_transaksi   = intval($total_transaksi);
  $fix_pendapatan  = doubleval($total_omzet);
  $fix_pengeluaran = doubleval($total_bengkel);
  $fix_bersih      = doubleval($profit_bersih);

  // Eksekusi insert data ke tabel laporan
  $insert_history = mysqli_query($conn, "INSERT INTO laporan (id_laporan, periode_laporan, total_transaksi, total_pendapatan, total_pengeluaran, keuntungan_bersih) 
                                         VALUES ('$id_lap_baru', '$periode_aktif', '$fix_transaksi', '$fix_pendapatan', '$fix_pengeluaran', '$fix_bersih')");

  if ($insert_history) {
    header("Location: laporan.php?status=archive-success");
  } else {
    header("Location: laporan.php?status=archive-failed");
  }
  exit;
}
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
            Laporan Analitik Sistem <span class="text-xs px-2.5 py-1 font-bold rounded-full uppercase bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-100 ml-2"><?php echo $role_sekarang; ?> View</span>
          </h2>

          <div class="mb-6 max-w-xs">
            <label class="block text-sm mb-2 text-gray-600 dark:text-gray-400 font-medium">
              Pilih Periode Analitik:
            </label>
            <select class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 rounded-lg">
              <option>Bulan Ini (Juni 2026)</option>
              <option>Bulan Lalu</option>
              <option>Tahun Ini (2026)</option>
            </select>
          </div>

          <div class="grid gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">

            <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path>
                </svg>
              </div>
              <div>
                <p class="text-xl font-extrabold text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Total Omzet Masuk</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-xl font-extrabold text-red-600 dark:text-red-400">Rp <?php echo number_format($total_bengkel, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Biaya Pemeliharaan Unit</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-purple-600 rounded-lg border border-purple-500 shadow-md md:col-span-2 lg:col-span-1">
              <div class="p-3 mr-4 text-purple-600 bg-purple-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-xl font-black text-white">Rp <?php echo number_format($profit_bersih, 0, ',', '.'); ?></p>
                <p class="text-xs font-bold text-purple-100 uppercase tracking-wider mt-1">Laba Kas Bersih</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-gray-700 dark:text-gray-200"><?php echo $total_transaksi; ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Frekuensi Transaksi Selesai</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-gray-700 dark:text-gray-200"><?php echo $total_pelanggan; ?> Member</p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Konsumen Terdaftar</p>
              </div>
            </div>

            <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($mobil_terfavorit); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Unit Paling Produktif</p>
              </div>
            </div>

          </div>

          <div class="min-w-0 p-6 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 mb-8">
            <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-300 uppercase tracking-wide">
              Grafik Tren Finansial Bulanan & Arus Omzet Perusahaan
            </h4>
            <div class="relative h-64">
              <canvas id="lineLaporan"></canvas>
            </div>
            <div class="flex justify-center mt-4 space-x-3 text-sm text-gray-600 dark:text-gray-400">
              <div class="flex items-center">
                <span class="inline-block w-3 h-3 mr-1 bg-purple-600 rounded-full"></span>
                <span class="font-medium">Pendapatan Selesai Sewa (Rupiah)</span>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 border-t dark:border-gray-700 mb-12 w-full">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
              <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                  Buku Sejarah / History Arsip Bulanan Perusahaan
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Rekam jejak pembukuan finansial statis bulanan yang dikunci permanen.</p>
              </div>
              <form action="laporan.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengunci dan mengarsip data finansial periode bulan ini?')">
                <button type="submit" name="arsip_laporan" class="px-4 py-2 text-xs font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm transition-colors duration-150">
                  💾 Tutup Buku Bulan Ini
                </button>
              </form>
            </div>

            <?php if (isset($_GET['status'])): ?>
              <?php if ($_GET['status'] == 'archive-success'): ?>
                <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-xs font-semibold">✓ Berhasil mengunci pembukuan periode bulan ini ke database!</div>
              <?php elseif ($_GET['status'] == 'already-archived'): ?>
                <div class="mb-4 p-3 bg-amber-500 text-white rounded-lg text-xs font-semibold">⚠ Periode bulan ini sudah dikunci sebelumnya di dalam rekam sejarah.</div>
              <?php endif; ?>
            <?php endif; ?>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap text-xs text-left">
                  <thead>
                    <tr class="font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Lap</th>
                      <th class="px-4 py-3">Periode Laporan</th>
                      <th class="px-4 py-3">Total Rit Jalan</th>
                      <th class="px-4 py-3">Omzet Kotor</th>
                      <th class="px-4 py-3">Biaya Bengkel</th>
                      <th class="px-4 py-3">Profit Bersih</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <?php
                    $q_hist_lap = mysqli_query($conn, "SELECT * FROM laporan ORDER BY periode_laporan DESC");
                    if (mysqli_num_rows($q_hist_lap) > 0) {
                      while ($h_lap = mysqli_fetch_assoc($q_hist_lap)) {
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                          <td class="px-4 py-3 font-mono font-bold text-purple-600"><?php echo $h_lap['id_laporan']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo date('F Y', strtotime($h_lap['periode_laporan'])); ?></td>
                          <td class="px-4 py-3"><?php echo $h_lap['total_transaksi']; ?> Kali</td>
                          <td class="px-4 py-3 text-green-600 font-medium">Rp <?php echo number_format($h_lap['total_pendapatan'], 0, ',', '.'); ?></td>
                          <td class="px-4 py-3 text-red-600">Rp <?php echo number_format($h_lap['total_pengeluaran'], 0, ',', '.'); ?></td>
                          <td class="px-4 py-3 font-extrabold text-purple-600 dark:text-purple-400">Rp <?php echo number_format($h_lap['keuntungan_bersih'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 italic">Belum ada history pembukuan bulanan yang diarsip.</td></tr>';
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

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const ctx = document.getElementById('lineLaporan').getContext('2d');
      
      // FIX SINKRONISASI GRAFIK: Admin kini otomatis melihat visualisasi nominal omzet rupiah
      const dataChart = [18000000, 25000000, 19500000, 22000000];
      const labelY = 'Pendapatan';

      const myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
          datasets: [{
            label: labelY,
            data: dataChart,
            backgroundColor: 'rgba(147, 51, 234, 0.1)',
            borderColor: '#9333ea',
            borderWidth: 3,
            pointBackgroundColor: '#9333ea',
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                callback: function(value) {
                  return 'Rp ' + value.toLocaleString('id-ID');
                }
              }
            }]
          },
          tooltips: {
            callbacks: {
              label: function(tooltipItem, data) {
                return 'Pendapatan: Rp ' + tooltipItem.yLabel.toLocaleString('id-ID');
              }
            }
          }
        }
      });
    });
  </script>
</body>

</html>