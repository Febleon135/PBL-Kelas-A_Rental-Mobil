<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: maintenance.php");
  exit;
}

$id_maint = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT mn.*, m.merk, m.tipe, m.nomor_polisi, m.tahun, m.warna, m.gambar
          FROM maintenance mn
          JOIN mobil m ON mn.id_mobil = m.id_mobil
          WHERE mn.id_maintenance = '$id_maint'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
  header("Location: maintenance.php");
  exit;
}

$data = mysqli_fetch_assoc($result);
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

          <div class="flex justify-between items-center my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
              Berita Acara & Nota Maintenance #<?php echo $data['id_maintenance']; ?>
            </h2>
            <a href="maintenance.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
              ← Kembali
            </a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- SPESIFIKASI ARMADA YANG MASUK BENGKEL -->
            <div class="md:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col items-center text-center text-xs">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-4 w-full">Detail Armada</h3>
              
              <div class="w-full h-32 bg-gray-50 rounded-lg overflow-hidden border p-2 mb-4 flex items-center justify-center">
                <img src="assets/img/mobil/<?php echo $data['gambar']; ?>" class="max-w-full max-h-full object-contain" alt="Armada WheelsRent">
              </div>

              <div class="space-y-3 w-full text-left">
                <div>
                  <p class="text-gray-400 uppercase font-medium">ID Kendaraan</p>
                  <p class="font-mono font-bold text-purple-600 mt-0.5"><?php echo $data['id_mobil']; ?></p>
                </div>
                <div>
                  <p class="text-gray-400 uppercase font-medium">Merk & Tipe Unit</p>
                  <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5"><?php echo htmlspecialchars($data['merk'] . ' ' . $data['tipe']); ?></p>
                </div>
                <div>
                  <p class="text-gray-400 uppercase font-medium">Nomor Registrasi (Plat)</p>
                  <p class="font-mono font-bold text-gray-900 dark:text-white mt-0.5 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded inline-block"><?php echo $data['nomor_polisi']; ?></p>
                </div>
              </div>
            </div>

            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-xs flex flex-col justify-between">
              <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-4 dark:border-gray-700">Rincian Pengeluaran Finansial</h3>

                <div class="space-y-4">
                  <div class="flex justify-between items-center py-1 border-b dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Status Karantina</span>
                    <?php if ($data['status_maintenance'] === 'selesai'): ?>
                      <span class="px-2 py-0.5 font-bold text-green-700 bg-green-100 rounded uppercase text-[10px]">Selesai / Aktif</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 font-bold text-amber-700 bg-amber-100 rounded uppercase text-[10px]">Sedang Proses</span>
                    <?php endif; ?>
                  </div>

                  <div class="flex justify-between items-center py-1 border-b dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Estimasi Selesai (Awal)</span>
                    <span class="font-bold text-amber-600 dark:text-amber-500">
                      <?php echo empty($data['tgl_estimasi']) ? 'Belum Diatur' : date('d F Y', strtotime($data['tgl_estimasi'])); ?>
                    </span>
                  </div>

                  <div class="flex justify-between items-center py-1 border-b dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Tanggal Aktual (Pencatatan)</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200"><?php echo date('d F Y', strtotime($data['tgl_service'])); ?></span>
                  </div>

                  <div class="flex justify-between items-center pt-2 text-sm font-bold text-red-600">
                    <span>TOTAL PENGELUARAN BENGKEL</span>
                    <span class="text-base">Rp <?php echo number_format($data['biaya_service'], 0, ',', '.'); ?></span>
                  </div>
                </div>
              </div>

              <div class="mt-6 bg-purple-50 dark:bg-gray-700 p-4 rounded-lg border border-purple-100 dark:border-gray-600">
                <p class="font-bold text-purple-800 dark:text-purple-300 uppercase tracking-wide mb-1">Log Tindakan Mekanik / Bengkel:</p>
                <p class="text-gray-700 dark:text-gray-200 italic font-medium">"<?php echo htmlspecialchars($data['keterangan']); ?>"</p>
              </div>
            </div>

          </div>

        </div>
      </main>
    </div>
  </div>
</body>

</html>