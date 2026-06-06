<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── LOGIKA PROSES BACKEND: SELESAI MAINTENANCE & UPDATE RIWAYAT ───
if (isset($_POST['proses_selesai_maintenance'])) {
  $id_mobil_fix   = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_maint_fix   = mysqli_real_escape_string($conn, $_POST['id_maintenance']);
  $biaya_service  = (float)$_POST['biaya_service'];
  $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);
  $tgl_selesai    = date('Y-m-d');

  mysqli_begin_transaction($conn);

  // 1. Kembalikan status armada mobil menjadi tersedia
  $u_mobil = mysqli_query($conn, "UPDATE mobil SET status_mobil = 'tersedia' WHERE id_mobil = '$id_mobil_fix'");

  // 2. Perbarui data di tabel maintenance: isi biaya, keterangan akhir, dan ubah status menjadi 'selesai'
  $u_maint = mysqli_query($conn, "UPDATE maintenance 
                                  SET tgl_service = '$tgl_selesai', 
                                      biaya_service = '$biaya_service', 
                                      status_maintenance = 'selesai', 
                                      keterangan = '$keterangan' 
                                  WHERE id_maintenance = '$id_maint_fix'");

  if ($u_mobil && $u_maint) {
    mysqli_commit($conn);
    header("Location: maintenance.php?status=success");
    exit;
  } else {
    mysqli_rollback($conn);
    header("Location: maintenance.php?status=failed");
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
            Bengkel & Maintenance Armada
          </h2>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
              ✓ Unit mobil selesai diperbaiki! Log finansial bengkel terekam dan status armada aktif kembali.
            </div>
          <?php endif; ?>

          <!-- INTERFACE 1: DAFTAR ANTREAN MAITENANCE YANG SEDANG BERJALAN -->
          <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Daftar Antrean Perbaikan Unit (Karantina)</h3>
          </div>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-3">Foto Unit</th>
                    <th class="px-4 py-3">ID / Plat Mobil</th>
                    <th class="px-4 py-3">Spesifikasi Kendaraan</th>
                    <th class="px-4 py-3">Input Data Bengkel / Perbaikan</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                  <?php
                  // Menarik data mobil yang berstatus 'maintenance' dengan log pendaftarannya di tabel maintenance
                  $q_active = mysqli_query($conn, "SELECT m.*, mn.id_maintenance, mn.keterangan as ket_awal 
                                                   FROM mobil m 
                                                   JOIN maintenance mn ON m.id_mobil = mn.id_mobil 
                                                   WHERE m.status_mobil = 'maintenance' AND mn.status_maintenance = 'proses'
                                                   ORDER BY mn.id_maintenance ASC");

                  if (mysqli_num_rows($q_active) > 0) {
                    while ($row = mysqli_fetch_assoc($q_active)) {
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 w-24">
                          <div class="w-16 h-12 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center border">
                            <img src="assets/img/mobil/<?php echo $row['gambar']; ?>" class="max-w-full max-h-full object-contain" alt="Mobil">
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <p class="font-semibold text-purple-600"><?php echo $row['id_mobil']; ?></p>
                          <p class="font-mono text-xs text-gray-500 mt-0.5"><?php echo $row['nomor_polisi']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                          <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['merk'] . " " . $row['tipe']); ?></p>
                          <p class="text-xs text-gray-500 mt-0.5">Warna: <?php echo htmlspecialchars($row['warna']); ?></p>
                        </td>
                        <!-- CARI TAG TD FORM INPUT DI MAINTENANCE.PHP, LALU TIMPA JADI SEPERTI INI: -->
                        <td class="px-4 py-3">
                          <form action="maintenance.php" method="POST" class="flex flex-col md:flex-row flex-wrap items-stretch md:items-center gap-2 py-1 max-w-xl">
                            <input type="hidden" name="id_mobil" value="<?php echo $row['id_mobil']; ?>">
                            <input type="hidden" name="id_maintenance" value="<?php echo $row['id_maintenance']; ?>">

                            <div class="flex items-center gap-2">
                              <input type="number" name="biaya_service" required min="0" placeholder="Biaya (Rp)" class="text-xs p-1.5 rounded border border-gray-300 dark:bg-gray-700 w-24 focus:border-purple-400">
                              <input type="text" name="keterangan" required placeholder="Tindakan mekanik..." class="text-xs p-1.5 rounded border border-gray-300 dark:bg-gray-700 w-40 focus:border-purple-400">
                            </div>

                            <div class="flex items-center">
                              <button type="submit" name="proses_selesai_maintenance" onclick="return confirm('Apakah unit ini benar-benar selesai diperbaiki dan siap masuk garasi?')" class="w-full md:w-auto whitespace-nowrap px-3 py-1.5 text-xs font-bold text-white bg-green-600 rounded hover:bg-green-700 transition-colors shadow-sm text-center">
                                ✓ Selesai Perbaikan
                              </button>
                            </div>
                          </form>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 font-medium">✨ Garasi bersih! Tidak ada unit mobil yang sedang karantina atau maintenance hari ini.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- INTERFACE 2: TABEL LOG RIWAYAT (HISTORY) MAINTENANCE SELESAI -->
          <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-8">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Log Riwayat Maintenance & Bengkel (Selesai)</h3>
          </div>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-12">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-3">ID Maint</th>
                    <th class="px-4 py-3">Armada Mobil</th>
                    <th class="px-4 py-3">Tanggal Keluar</th>
                    <th class="px-4 py-3">Total Biaya Perbaikan</th>
                    <th class="px-4 py-3">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                  <?php
                  $q_history = mysqli_query($conn, "SELECT mn.*, m.merk, m.tipe, m.nomor_polisi 
                                                    FROM maintenance mn
                                                    JOIN mobil m ON mn.id_mobil = m.id_mobil
                                                    WHERE mn.status_maintenance = 'selesai'
                                                    ORDER BY mn.id_maintenance DESC");

                  if (mysqli_num_rows($q_history) > 0) {
                    while ($h_row = mysqli_fetch_assoc($q_history)) {
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $h_row['id_maintenance']; ?></td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                          <?php echo htmlspecialchars($h_row['merk'] . " " . $h_row['tipe']); ?>
                          <span class="block font-mono text-xs text-gray-400 font-normal mt-0.5"><?php echo $h_row['nomor_polisi']; ?></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400"><?php echo date('d-m-Y', strtotime($h_row['tgl_service'])); ?></td>
                        <td class="px-4 py-3 font-bold text-red-600">Rp <?php echo number_format($h_row['biaya_service'], 0, ',', '.'); ?></td>
                        <td class="px-4 py-3">
                          <a href="detail_maintenance.php?id=<?php echo $h_row['id_maintenance']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-bold text-white bg-purple-600 rounded-md hover:bg-purple-700 transition-colors">
                            Detail Kasus
                          </a>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat perbaikan mobil yang selesai dilakukan.</td></tr>';
                  }
                  ?>
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