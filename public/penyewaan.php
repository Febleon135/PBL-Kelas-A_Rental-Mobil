<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── LOGIKA PROSES BACKEND: INPUT TRANSAKSI RENTAL BARU ───
if (isset($_POST['save_transaksi'])) {
  $id_transaksi    = mysqli_real_escape_string($conn, $_POST['id_transaksi']);
  $id_pelanggan    = mysqli_real_escape_string($conn, $_POST['id_pelanggan']);
  $id_mobil        = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_pengguna     = $_SESSION['id_pengguna']; // Mengambil ID user yang sedang melayani di kasir
  $tanggal_sewa    = mysqli_real_escape_string($conn, $_POST['tanggal_sewa']);
  $durasi_sewa     = (int)$_POST['durasi_sewa'];
  $diskon          = (float)$_POST['diskon'];

  // 1. Hitung otomatis tanggal_kembali berdasarkan durasi hari
  $tanggal_kembali = date('Y-m-d', strtotime($tanggal_sewa . ' + ' . $durasi_sewa . ' days'));

  // 2. Ambil harga sewa per hari mobil tersebut untuk menghitung total biaya
  $q_harga = mysqli_query($conn, "SELECT harga_sewa_per_hari FROM mobil WHERE id_mobil = '$id_mobil'");
  $r_harga = mysqli_fetch_assoc($q_harga);
  $harga_harian = $r_harga['harga_sewa_per_hari'];

  // 3. Rumus Hitung Total Biaya: (Harga * Durasi) - Diskon
  $total_biaya = ($harga_harian * $durasi_sewa) - $diskon;

  // Mulai Database Transaction untuk menjaga validitas data ganda
  mysqli_begin_transaction($conn);

  // Query A: Insert ke tabel transaksi_rental
  $query_insert = "INSERT INTO transaksi_rental VALUES ('$id_transaksi', '$id_pelanggan', '$id_mobil', '$id_pengguna', '$tanggal_sewa', '$tanggal_kembali', '$durasi_sewa', '$total_biaya', 'berjalan', '$diskon')";
  $res_insert   = mysqli_query($conn, $query_insert);

  // Query B: Otomatis ubah status mobil terkait menjadi 'disewa' agar tidak bisa dirental orang lain
  $query_update_mobil = "UPDATE mobil SET status_mobil = 'disewa' WHERE id_mobil = '$id_mobil'";
  $res_update_mobil   = mysqli_query($conn, $query_update_mobil);

  if ($res_insert && $res_update_mobil) {
    mysqli_commit($conn); // Sukses, simpan permanen ke DB
    header("Location: penyewaan.php?status=success");
    exit;
  } else {
    mysqli_rollback($conn); // Gagal, batalkan semua perubahan
    header("Location: penyewaan.php?status=failed");
    exit;
  }
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
            Penyewaan Mobil
          </h2>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
              ✓ Transaksi rental baru berhasil dicatat! Status mobil otomatis berubah menjadi 'Disewa'.
            </div>
          <?php endif; ?>

          <?php
          // KONTROL ALUR TAMPILAN (FORM TRANSAKSI VS LIST VIEW TRANSAKSI)
          if (isset($_GET['action']) && $_GET['action'] == 'add') {

            // OTO-GENERATE ID TRANSAKSI (Contoh: TR001, TR002)
            $q_id   = mysqli_query($conn, "SELECT id_transaksi FROM transaksi_rental ORDER BY id_transaksi DESC LIMIT 1");
            $row_id = mysqli_fetch_assoc($q_id);
            $form_id = $row_id ? "TR" . sprintf("%03d", substr($row_id['id_transaksi'], 2) + 1) : "TR001";
          ?>
            <div class="mb-4">
              <a href="penyewaan.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200">
                ← Kembali ke Daftar Transaksi
              </a>
            </div>

            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-8 max-w-2xl">
              <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide border-b pb-2 dark:border-gray-700">
                INPUT TRANSAKSI RENTAL BARU
              </h4>

              <form action="penyewaan.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_transaksi" value="<?php echo $form_id; ?>">

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">ID Transaksi</span>
                  <input type="text" value="<?php echo $form_id; ?>" readonly class="block w-full mt-1 text-sm bg-gray-100 dark:bg-gray-700 dark:text-gray-300 form-input" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Pilih Pelanggan</span>
                  <select name="id_pelanggan" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php
                    $pel = mysqli_query($conn, "SELECT id_pelanggan, nama_pelanggan, NIK FROM pelanggan");
                    while ($p = mysqli_fetch_assoc($pel)) {
                      echo "<option value='" . $p['id_pelanggan'] . "'>" . $p['nama_pelanggan'] . " (" . $p['NIK'] . ")</option>";
                    }
                    ?>
                  </select>
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Pilih Unit Mobil</span>
                  <select name="id_mobil" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                    <option value="">-- Pilih Mobil Tersedia --</option>
                    <?php
                    $mob = mysqli_query($conn, "SELECT id_mobil, merk, tipe, nomor_polisi, harga_sewa_per_hari FROM mobil WHERE status_mobil = 'tersedia'");
                    while ($m = mysqli_fetch_assoc($mob)) {
                      echo "<option value='" . $m['id_mobil'] . "'>" . $m['merk'] . " " . $m['tipe'] . " [" . $m['nomor_polisi'] . "] - Rp " . number_format($m['harga_sewa_per_hari'], 0, ',', '.') . "/hari</option>";
                    }
                    ?>
                  </select>
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal Mulai Sewa</span>
                  <input type="date" name="tanggal_sewa" value="<?php echo date('Y-m-d'); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Durasi Sewa (Hari)</span>
                  <input type="number" name="durasi_sewa" min="1" required placeholder="Contoh: 3" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Potongan Diskon (Rp)</span>
                  <input type="number" name="diskon" min="0" value="0" placeholder="Contoh: 50000" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                </label>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                  <a href="penyewaan.php" class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">Cancel</a>
                  <button type="submit" name="save_transaksi" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan Sewa</button>
                </div>
              </form>
            </div>

          <?php
          } else {
          ?>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4 flex justify-between items-center w-full">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">LOG PENYEWAAAN BERJALAN</h3>
              <a href="penyewaan.php?action=add" class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 shadow">
                + Tambah Transaksi Baru
              </a>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
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
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">

                    <?php
                    $q_rental = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_sewa, t.tanggal_kembali, t.total_biaya, t.status_sewa 
                                                       FROM transaksi_rental t
                                                       JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                       JOIN mobil m ON t.id_mobil = m.id_mobil
                                                       ORDER BY t.id_transaksi DESC");

                    if (mysqli_num_rows($q_rental) > 0) {
                      while ($r = mysqli_fetch_assoc($q_rental)) {
                        $badge = "bg-blue-600 text-white";
                        if ($r['status_sewa'] == 'selesai') $badge = "bg-green-600 text-white";
                        if ($r['status_sewa'] == 'terlambat') $badge = "bg-red-600 text-white";
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                          <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $r['id_transaksi']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($r['nama_pelanggan']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($r['merk'] . " " . $r['tipe'] . " [ " . $r['nomor_polisi'] . " ]"); ?></td>
                          <td class="px-4 py-3"><?php echo date('d-m-Y', strtotime($r['tanggal_sewa'])); ?></td>
                          <td class="px-4 py-3 font-medium text-amber-600"><?php echo date('d-m-Y', strtotime($r['tanggal_kembali'])); ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Rp <?php echo number_format($r['total_biaya'], 0, ',', '.'); ?></td>
                          <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-full <?php echo $badge; ?>">
                              <?php echo ucfirst($r['status_sewa']); ?>
                            </span>
                          </td>
                          <td class="px-4 py-3">
                            <a href="detail_penyewaan.php?id=<?php echo $r['id_transaksi']; ?>" class="px-3 py-1 text-xs font-bold text-white bg-purple-600 rounded-md hover:bg-purple-700 transition-colors">
                              Detail
                            </a>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi rental berjalan.</td></tr>';
                    }
                    ?>

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