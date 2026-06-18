<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── MASTER QUERY: AUTO-SYNC STATUS MOBIL BERDASARKAN KALENDER (REAL-TIME) ───
$q_sync = "UPDATE mobil m
SET status_mobil = (
    CASE
        WHEN m.status_mobil = 'Maintenance' THEN 'Maintenance'
        WHEN EXISTS (
            SELECT 1 FROM transaksi_rental t 
            WHERE t.id_mobil = m.id_mobil 
            AND t.status_sewa IN ('Booking', 'Disewakan') 
            AND CURDATE() BETWEEN DATE_SUB(t.tanggal_sewa, INTERVAL 7 DAY) AND t.tanggal_kembali
        ) THEN 'Disewa'
        WHEN EXISTS (
            SELECT 1 FROM transaksi_rental t 
            WHERE t.id_mobil = m.id_mobil 
            AND t.status_sewa = 'Selesai' 
            AND CURDATE() BETWEEN DATE_ADD(t.tanggal_kembali, INTERVAL 1 DAY) AND DATE_ADD(t.tanggal_kembali, INTERVAL 5 DAY)
        ) THEN 'Persiapan Unit'
        ELSE 'Tersedia'
    END
)";
mysqli_query($conn, $q_sync);

// ─── LOGIKA PEMBATALAN BOOKING ───
if (isset($_GET['batal'])) {
  $id_batal = mysqli_real_escape_string($conn, $_GET['batal']);
  mysqli_query($conn, "UPDATE transaksi_rental SET status_sewa = 'Dibatalkan' WHERE id_transaksi = '$id_batal'");
  mysqli_query($conn, $q_sync); // Sync ulang status mobil
  header("Location: penyewaan.php?status=cancelled");
  exit;
}

// ─── LOGIKA PROSES BACKEND: INPUT TRANSAKSI RENTAL BARU ───
if (isset($_POST['save_transaksi'])) {
  $id_transaksi    = mysqli_real_escape_string($conn, $_POST['id_transaksi']);
  $id_pelanggan    = mysqli_real_escape_string($conn, $_POST['id_pelanggan']);
  $id_mobil        = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_pengguna     = $_SESSION['id_pengguna'];
  $tanggal_sewa    = mysqli_real_escape_string($conn, $_POST['tanggal_sewa']);
  $durasi_sewa     = (int)$_POST['durasi_sewa'];
  $diskon          = (float)$_POST['diskon'];

  $tanggal_kembali = date('Y-m-d', strtotime($tanggal_sewa . ' + ' . $durasi_sewa . ' days'));
  $tanggal_kembali_plus_5 = date('Y-m-d', strtotime($tanggal_kembali . ' + 5 days'));

  // CEK TABRAKAN JADWAL EKSKLUSIF
  $q_cek = "SELECT id_transaksi FROM transaksi_rental 
            WHERE id_mobil = '$id_mobil' 
            AND status_sewa IN ('Booking', 'Disewakan') 
            AND ('$tanggal_sewa' <= DATE_ADD(tanggal_kembali, INTERVAL 5 DAY) AND '$tanggal_kembali_plus_5' >= tanggal_sewa)";
  $res_cek = mysqli_query($conn, $q_cek);
  if(mysqli_num_rows($res_cek) > 0) {
      header("Location: penyewaan.php?action=add&status=overbooking");
      exit;
  }

  $q_harga = mysqli_query($conn, "SELECT harga_sewa_per_hari FROM mobil WHERE id_mobil = '$id_mobil'");
  $r_harga = mysqli_fetch_assoc($q_harga);
  $harga_harian = $r_harga['harga_sewa_per_hari'];

  $total_biaya = ($harga_harian * $durasi_sewa) - $diskon;

  $hari_ini = date('Y-m-d');
  if ($tanggal_sewa > $hari_ini) {
    $status_sewa_baru = 'Booking';
  } else {
    $status_sewa_baru = 'Disewakan';
  }

  mysqli_begin_transaction($conn);

  $query_insert = "INSERT INTO transaksi_rental VALUES ('$id_transaksi', '$id_pelanggan', '$id_mobil', '$id_pengguna', '$tanggal_sewa', '$tanggal_kembali', '$durasi_sewa', '$total_biaya', '$status_sewa_baru', '$diskon')";
  $res_insert   = mysqli_query($conn, $query_insert);

  if ($res_insert) {
    mysqli_commit($conn);
    mysqli_query($conn, $q_sync);
    header("Location: penyewaan.php?status=success");
    exit;
  } else {
    mysqli_rollback($conn);
    header("Location: penyewaan.php?status=failed");
    exit;
  }
}

// ─── LOGIKA BACKEND: PAGINATION LIMIT 10 DATA & SEARCH ───
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "";
if ($search != "") {
  $where_clause = "WHERE t.id_transaksi LIKE '%$search%' OR p.nama_pelanggan LIKE '%$search%'";
}

$sql_count = "SELECT COUNT(*) AS total FROM transaksi_rental t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan $where_clause";
$res_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);
if ($total_pages < 1) $total_pages = 1;

$query_select_rental = "SELECT t.id_transaksi, t.id_pelanggan, t.id_mobil, p.nama_pelanggan, p.NIK, p.no_telepon,
                               m.merk, m.tipe, m.nomor_polisi, m.harga_sewa_per_hari, m.gambar,
                               t.tanggal_sewa, t.tanggal_kembali, t.durasi_sewa, t.total_biaya, t.status_sewa, t.diskon
                        FROM transaksi_rental t
                        JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                        JOIN mobil m ON t.id_mobil = m.id_mobil
                        $where_clause
                        ORDER BY t.id_transaksi DESC 
                        LIMIT $start, $limit";
$q_rental = mysqli_query($conn, $query_select_rental);

$arr_rental = [];
while ($row = mysqli_fetch_assoc($q_rental)) {
  $arr_rental[] = [
    'id'         => $row['id_transaksi'],
    'cust_name'  => $row['nama_pelanggan'],
    'nik'        => $row['NIK'],
    'phone'      => $row['no_telepon'],
    'mobil'      => $row['merk'] . ' ' . $row['tipe'],
    'plat'       => $row['nomor_polisi'],
    'gbr'        => !empty($row['gambar']) ? $row['gambar'] : 'default.png',
    'harga_ori'  => number_format($row['harga_sewa_per_hari'], 0, ',', '.'),
    'tgl_sewa'   => date('d-m-Y', strtotime($row['tanggal_sewa'])),
    'tgl_kembali'=> date('d-m-Y', strtotime($row['tanggal_kembali'])),
    'durasi'     => $row['durasi_sewa'],
    'diskon'     => number_format($row['diskon'], 0, ',', '.'),
    'total'      => number_format($row['total_biaya'], 0, ',', '.'),
    'status'     => ucfirst($row['status_sewa']) 
  ];
}
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="Object.assign(data(), { isDetailOpen: false, targetRental: {} })" lang="en">

<head>
  <?php include 'components/head.php'; ?>
  
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    
    /* CUSTOM CSS TOMSELECT AGAR MENYATU DENGAN WINDMILL TAILWIND (LIGHT & DARK MODE) */
    .ts-wrapper { padding: 0 !important; border: none !important; }
    .ts-control {
      border-radius: 0.375rem !important; /* rounded-md */
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important; /* text-sm */
      border: 1px solid #d1d5db !important; /* border-gray-300 */
      background-color: #fff !important; 
      box-shadow: none !important;
      min-height: 42px !important;
      display: flex !important;
      align-items: center !important;
    }
    .ts-control > input { font-size: 0.875rem !important; }
    .ts-dropdown { font-size: 0.875rem !important; border-radius: 0.375rem !important; }
    
    /* DARK MODE OVERRIDES */
    .theme-dark .ts-control {
      background-color: #374151 !important; /* dark:bg-gray-700 */
      border-color: #4b5563 !important; /* dark:border-gray-600 */
      color: #e5e7eb !important; /* dark:text-gray-200 */
    }
    .theme-dark .ts-control input { color: #e5e7eb !important; }
    .theme-dark .ts-dropdown {
      background-color: #374151 !important;
      border-color: #4b5563 !important;
      color: #e5e7eb !important;
    }
    .theme-dark .ts-dropdown .option:hover,
    .theme-dark .ts-dropdown .option.active {
      background-color: #4b5563 !important;
      color: #fff !important;
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

          <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Penyewaan Mobil
          </h2>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold shadow-sm">
              ✓ Transaksi rental/booking baru berhasil dicatat!
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'cancelled'): ?>
            <div class="mb-4 p-3 bg-gray-600 text-white rounded-lg text-sm font-semibold shadow-sm">
              ✓ Transaksi booking telah dibatalkan. Mobil kembali tersedia.
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'overbooking'): ?>
            <div class="mb-4 p-3 bg-red-600 text-white rounded-lg text-sm font-semibold animate-pulse shadow-sm">
              ⚠ Gagal Simpan! Jadwal bentrok dengan Booking/Sewa lain atau masa 'Persiapan Unit' (Spare 5 Hari).
            </div>
          <?php endif; ?>

          <?php
          if (isset($_GET['action']) && $_GET['action'] == 'add') {
            $q_id   = mysqli_query($conn, "SELECT id_transaksi FROM transaksi_rental ORDER BY id_transaksi DESC LIMIT 1");
            $row_id = mysqli_fetch_assoc($q_id);
            $form_id = $row_id ? "TR" . sprintf("%03d", substr($row_id['id_transaksi'], 2) + 1) : "TR001";
          ?>
            <div class="mb-4">
              <a href="penyewaan.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200 transition-colors shadow-sm">
                ← Kembali ke Daftar Transaksi
              </a>
            </div>

            <div class="p-8 bg-white rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 dark:bg-gray-800 mb-8 max-w-4xl mx-auto w-full">
              <h4 class="mb-8 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-3 dark:border-gray-700 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Form Input Transaksi Rental Terjadwal
              </h4>

              <form action="penyewaan.php" method="POST">
                <input type="hidden" name="id_transaksi" value="<?php echo $form_id; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">ID Transaksi (Auto-Generate)</span>
                      <input type="text" value="<?php echo $form_id; ?>" readonly class="block w-full text-sm bg-gray-100 border border-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md form-input cursor-not-allowed font-mono text-purple-600 font-bold" />
                    </label>

                    <label class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">Pilih Pelanggan</span>
                      <select name="id_pelanggan" required class="searchable-select block w-full text-sm">
                        <option value="">-- Ketik / Pilih Pelanggan --</option>
                        <?php
                        $pel = mysqli_query($conn, "SELECT id_pelanggan, nama_pelanggan, NIK FROM pelanggan");
                        while ($p = mysqli_fetch_assoc($pel)) {
                          echo "<option value='" . $p['id_pelanggan'] . "'>" . $p['nama_pelanggan'] . " (" . $p['NIK'] . ")</option>";
                        }
                        ?>
                      </select>
                    </label>

                    <label class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">Pilih Unit Mobil Armada</span>
                      <select name="id_mobil" required class="searchable-select block w-full text-sm">
                        <option value="">-- Ketik / Pilih Mobil --</option>
                        <?php
                        $query_filter_mobil = "SELECT id_mobil, merk, tipe, nomor_polisi, harga_sewa_per_hari, status_mobil 
                                               FROM mobil 
                                               WHERE status_mobil != 'Maintenance'";

                        $mob = mysqli_query($conn, $query_filter_mobil);
                        while ($m = mysqli_fetch_assoc($mob)) {
                          $sts = strtoupper($m['status_mobil']);
                          echo "<option value='" . $m['id_mobil'] . "'>" . $m['merk'] . " " . $m['tipe'] . " [" . $m['nomor_polisi'] . "] - Rp " . number_format($m['harga_sewa_per_hari'], 0, ',', '.') . "/hari (Sts: $sts)</option>";
                        }
                        ?>
                      </select>
                    </label>
                  </div>

                  <div>
                    <label class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">Tanggal Mulai Berangkat / Booking</span>
                      <input type="date" name="tanggal_sewa" value="<?php echo date('Y-m-d'); ?>" required class="block w-full text-sm dark:bg-gray-700 dark:text-gray-200 form-input focus:border-purple-400 focus:ring-purple-300 rounded-md border-gray-300 dark:border-gray-600" />
                    </label>

                    <label class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">Lama Sewa (Durasi Hari)</span>
                      <input type="number" name="durasi_sewa" min="1" required placeholder="Contoh: 3" class="block w-full text-sm dark:bg-gray-700 dark:text-gray-200 form-input focus:border-purple-400 focus:ring-purple-300 rounded-md border-gray-300 dark:border-gray-600" />
                    </label>

                    <div class="block text-sm mb-6">
                      <span class="text-gray-700 dark:text-gray-400 font-semibold mb-2 block">Potongan Harga / Diskon (Rp)</span>
                      <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-gray-500 dark:text-gray-400 sm:text-sm font-semibold">Rp</span>
                        </div>
                        <input type="number" name="diskon" min="0" value="0" placeholder="Contoh: 50000" class="block w-full pl-10 text-sm dark:bg-gray-700 dark:text-gray-200 form-input focus:border-purple-400 focus:ring-purple-300 rounded-md border-gray-300 dark:border-gray-600" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-6 mt-4 border-t border-gray-100 dark:border-gray-700">
                  <a href="penyewaan.php" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors shadow-sm">Batal</a>
                  <button type="submit" name="save_transaksi" class="px-5 py-2.5 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-md">Simpan Data Sewa</button>
                </div>
              </form>
            </div>

          <?php
          } else {
          ?>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4 flex flex-col sm:flex-row justify-between items-center gap-4 w-full">
              <div class="flex items-center w-full sm:w-auto">
                <h3 class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-wider">LOG PENYEWAAAN & BOOKING</h3>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end w-full sm:w-auto gap-3">
                <form method="GET" action="penyewaan.php" class="m-0 p-0 flex items-center gap-2">
                  <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari Nama / ID Transaksi..."
                    class="w-48 pl-3 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:border-purple-400">
                  <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-sm whitespace-nowrap">
                    Search
                  </button>
                </form>
                <a href="penyewaan.php?action=add" class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 shadow transition-colors whitespace-nowrap">
                  + Tambah Transaksi Baru
                </a>
              </div>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-4">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                  <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Transaksi</th>
                      <th class="px-4 py-3">Nama Pelanggan</th>
                      <th class="px-4 py-3">Armada Mobil</th>
                      <th class="px-4 py-3">Tgl Sewa</th>
                      <th class="px-4 py-3">Tgl Kembali Seharusnya</th>
                      <th class="px-4 py-3">Total Biaya</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                    <?php if (count($arr_rental) > 0): ?>
                      <?php foreach ($arr_rental as $index => $item): ?>
                        <?php 
                        $status_clean = isset($item['status']) ? strtolower(trim($item['status'])) : ''; 
                        
                        if ($status_clean === 'disewakan' || $status_clean === 'berjalan') {
                          $badge_class = "text-blue-700 bg-blue-100 dark:text-blue-100 dark:bg-blue-700";
                        } elseif ($status_clean === 'booking') {
                          $badge_class = "text-yellow-700 bg-yellow-100 dark:text-yellow-100 dark:bg-yellow-600";
                        } elseif ($status_clean === 'selesai') {
                          $badge_class = "text-green-700 bg-green-100 dark:text-green-100 dark:bg-green-700";
                        } elseif ($status_clean === 'dibatalkan') {
                          $badge_class = "text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700";
                        } else {
                          $badge_class = "text-teal-700 bg-teal-100 dark:text-teal-100 dark:bg-teal-700";
                        }
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                          <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $item['id']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['cust_name']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($item['mobil'] . " [ " . $item['plat'] . " ]"); ?></td>
                          <td class="px-4 py-3"><?php echo $item['tgl_sewa']; ?></td>
                          <td class="px-4 py-3 font-medium text-amber-600 dark:text-amber-400"><?php echo $item['tgl_kembali']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Rp <?php echo $item['total']; ?></td>
                          <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-extrabold rounded-md uppercase <?php echo $badge_class; ?>">
                              <?php echo $item['status']; ?>
                            </span>
                          </td>
                          <td class="px-4 py-3 text-center flex items-center justify-center gap-2">
                            <?php if ($status_clean === 'booking'): ?>
                              <a href="penyewaan.php?batal=<?php echo $item['id']; ?>" onclick="return confirm('Yakin ingin membatalkan jadwal booking ini? Mobil akan kembali ke garasi.')" class="px-3 py-1 text-xs font-bold text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                                Batal
                              </a>
                            <?php endif; ?>
                            
                            <button type="button" @click.prevent.stop='targetRental = <?php echo htmlspecialchars(json_encode($arr_rental[$index])); ?>; setTimeout(() => { isDetailOpen = true; }, 150)'
                              class="px-3 py-1 text-xs font-bold text-white bg-purple-600 rounded-md hover:bg-purple-700 transition-colors">
                              Detail
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Data log transaksi rental tidak ditemukan.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t bg-gray-50 sm:grid-cols-4 dark:bg-gray-800 dark:text-gray-400 mb-8 rounded-lg">
              <span class="flex items-center col-span-2">Showing <?php echo min($start + 1, $total_data); ?>-<?php echo min($start + $limit, $total_data); ?> of <?php echo $total_data; ?> Transaksi</span>
              <span class="col-span-2 sm:mt-0 sm:justify-end flex">
                <nav aria-label="Table navigation">
                  <ul class="inline-flex items-center space-x-1">
                    <li><a href="penyewaan.php?page=<?php echo max(1, $page - 1); ?>&search=urlencode($search)" class="px-3 py-1 rounded-md rounded-l-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page <= 1) echo 'pointer-events-none opacity-50'; ?>">Prev</a></li>
                    <li><span class="px-3 py-1 text-white bg-purple-600 border border-purple-600 rounded-md"><?php echo $page; ?> / <?php echo $total_pages; ?></span></li>
                    <li><a href="penyewaan.php?page=<?php echo min($total_pages, $page + 1); ?>&search=urlencode($search)" class="px-3 py-1 rounded-md rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page >= $total_pages) echo 'pointer-events-none opacity-50'; ?>">Next</a></li>
                  </ul>
                </nav>
              </span>
            </div>
          <?php
          }
          ?>

        </div>
      </main>
    </div>
  </div>

  <div x-show="isDetailOpen" x-cloak x-transition
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div @click.away="isDetailOpen = false" class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl border border-gray-100 dark:border-gray-700 text-xs text-left cursor-default" role="dialog">
      
      <header class="flex justify-between items-center border-b pb-3 mb-4 dark:border-gray-700">
        <div>
          <p class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-xs">Informasi Lengkap Transaksi</p>
          <span class="font-mono text-[10px] text-purple-600 dark:text-purple-400 font-extrabold" x-text="'ID REFERENSI: ' + targetRental.id"></span>
        </div>
        <button type="button" @click.stop="isDetailOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold text-sm transition-colors">✕</button>
      </header>

      <div class="flex flex-col sm:flex-row gap-6 items-stretch">
        <div class="w-full sm:w-1/2 space-y-3.5 border-r pr-3 border-gray-100 dark:border-gray-700">
          <h5 class="font-black text-purple-600 uppercase tracking-tight text-[10px]">A. Entitas Penyewa & Unit</h5>
          
          <label class="block">
            <span class="text-gray-400 dark:text-gray-500 font-medium">Nama Member Utama</span>
            <input type="text" :value="targetRental.cust_name" readonly class="block w-full mt-1 px-3 py-2 text-xs font-bold rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-white border border-gray-100 dark:border-gray-600 focus:outline-none" />
          </label>
          
          <div class="grid grid-cols-2 gap-2">
            <label class="block">
              <span class="text-gray-400 dark:text-gray-500 font-medium">Nomor NIK KTP</span>
              <input type="text" :value="targetRental.nik" readonly class="block w-full mt-1 px-3 py-1.5 font-mono text-gray-700 dark:text-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 focus:outline-none" />
            </label>
            <label class="block">
              <span class="text-gray-400 dark:text-gray-500 font-medium">No Kontak HP</span>
              <input type="text" :value="targetRental.phone" readonly class="block w-full mt-1 px-3 py-1.5 text-gray-700 dark:text-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 focus:outline-none" />
            </label>
          </div>
          
          <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 flex items-center gap-3">
            <div class="w-16 h-12 bg-white dark:bg-gray-600 p-1 rounded-md flex items-center justify-center overflow-hidden border">
              <img :src="'assets/img/mobil/' + targetRental.gbr" class="max-h-full max-w-full object-contain">
            </div>
            <div>
              <p class="font-extrabold text-gray-800 dark:text-white text-xs" x-text="targetRental.mobil"></p>
              <p class="font-mono text-[10px] text-purple-600 dark:text-purple-400 font-bold mt-0.5" x-text="targetRental.plat"></p>
            </div>
          </div>
        </div>

        <div class="w-full sm:w-1/2 space-y-3.5 flex flex-col justify-between">
          <div>
            <h5 class="font-black text-purple-600 uppercase tracking-tight text-[10px] mb-3">B. Rincian Durasi & Financial</h5>
            <div class="grid grid-cols-2 gap-3 mb-2">
              <div>
                <b class="text-gray-400 dark:text-gray-500 block text-[10px]">TGL MULAI SEWA</b>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetRental.tgl_sewa"></p>
              </div>
              <div>
                <b class="text-gray-400 dark:text-gray-500 block text-[10px]">BATAS KEMBALI</b>
                <p class="font-bold text-amber-600 mt-0.5" x-text="targetRental.tgl_kembali"></p>
              </div>
            </div>
            
            <div class="grid grid-cols-3 gap-2 border-t pt-2 dark:border-gray-700">
              <div>
                <b class="text-gray-400 dark:text-gray-500 text-[9px]">DURASI</b>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetRental.durasi + ' Hari'"></p>
              </div>
              <div>
                <b class="text-gray-400 dark:text-gray-500 text-[9px]">POT. DISKON</b>
                <p class="font-bold text-red-600 mt-0.5" x-text="'Rp ' + targetRental.diskon"></p>
              </div>
              <div>
                <b class="text-gray-400 dark:text-gray-500 text-[9px]">STATUS</b>
                <p class="font-bold text-blue-600 mt-0.5 uppercase text-[10px]" x-text="targetRental.status"></p>
              </div>
            </div>

            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-900/50">
              <b class="text-green-700 dark:text-green-400 text-[10px] uppercase block tracking-wider">Total Biaya Akhir (Net)</b>
              <p class="text-green-600 dark:text-green-400 font-black text-lg mt-0.5" x-text="'Rp ' + targetRental.total"></p>
            </div>
          </div>
          
          <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
            <button type="button" @click.stop="isDetailOpen = false" class="px-4 py-2 font-bold text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 transition-colors shadow-sm">Kembali</button>
            <a :href="'detail_penyewaan.php?id=' + targetRental.id" target="_blank" class="px-4 py-2 font-bold text-xs text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm inline-flex items-center gap-1">🖨️ Export Faktur Nota</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll('.searchable-select').forEach((el) => {
        new TomSelect(el, {
          create: false,
          sortField: {
            field: "text",
            direction: "asc"
          }
        });
      });
    });
  </script>
</body>
</html>