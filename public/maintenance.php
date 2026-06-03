<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── LOGIKA PROSES BACKEND: SELESAI MAINTENANCE ───
if (isset($_GET['action']) && $_GET['action'] == 'complete') {
  $id_mobil_fix = mysqli_real_escape_string($conn, $_GET['id']);

  // Ubah kembali status mobil menjadi 'tersedia' agar bisa disewa lagi
  $query_fix = "UPDATE mobil SET status_mobil = 'tersedia' WHERE id_mobil = '$id_mobil_fix'";

  if (mysqli_query($conn, $query_fix)) {
    header("Location: maintenance.php?status=success");
    exit;
  } else {
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
              ✓ Unit mobil selesai diperbaiki! Sekarang statusnya aktif kembali dan siap disewakan.
            </div>
          <?php endif; ?>

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
                    <th class="px-4 py-3">Detail Kasus Terakhir</th>
                    <th class="px-4 py-3">Aksi Petugas</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">

                  <?php
                  // Query untuk memanggil mobil yang statusnya HANYA 'maintenance'
                  // Kita gabungkan (JOIN) dengan data denda terbaru untuk melihat catatan kerusakannya
                  $q_maintenance = mysqli_query($conn, "SELECT * FROM mobil WHERE status_mobil = 'maintenance' ORDER BY id_mobil ASC");

                  if (mysqli_num_rows($q_maintenance) > 0) {
                    while ($row = mysqli_fetch_assoc($q_maintenance)) {
                      $catatan_kasus = "Unit masuk masa karantina perbaikan fisik / lecet lapangan.";
                      $biaya_fisik   = 0;
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 w-24">
                          <div class="w-16 h-12 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center border">
                            <img src="assets/img/mobil/<?php echo $row['gambar']; ?>" class="max-w-full max-height-full object-contain" alt="Mobil">
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <p class="font-semibold text-purple-600"><?php echo $row['id_mobil']; ?></p>
                          <p class="font-mono text-xs text-gray-500 mt-0.5"><?php echo $row['nomor_polisi']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                          <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['merk'] . " " . $row['tipe']); ?></p>
                          <p class="text-xs text-gray-500 mt-0.5">Tahun <?php echo $row['tahun']; ?> | Warna <?php echo htmlspecialchars($row['warna']); ?></p>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                          <p class="font-medium text-amber-600 italic">"<?php echo htmlspecialchars($catatan_kasus); ?>"</p>
                          <?php if ($biaya_fisik > 0): ?>
                            <p class="text-xs text-gray-400 mt-1">Alokasi Klaim: <span class="font-semibold text-gray-700 dark:text-gray-300">Rp <?php echo number_format($biaya_fisik, 0, ',', '.'); ?></span></p>
                          <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                          <a href="maintenance.php?action=complete&id=<?php echo $row['id_mobil']; ?>" onclick="return confirm('Apakah unit ini sudah selesai dicuci, diperbaiki, dan siap kembali ke garasi utama?')" class="inline-flex items-center px-3 py-1 text-xs font-bold text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                            ✓ Siap Jalan
                          </a>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 font-medium">✨ Garasi bersih! Tidak ada unit mobil yang sedang rusak atau masuk jadwal maintenance hari ini.</td></tr>';
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