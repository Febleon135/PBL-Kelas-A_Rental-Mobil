<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: pengembalian.php");
  exit;
}

$id_transaksi = mysqli_real_escape_string($conn, $_GET['id']);

$query_utama = "SELECT t.*, p.nama_pelanggan, p.no_telepon, m.merk, m.tipe, m.nomor_polisi
                FROM transaksi_rental t
                JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                JOIN mobil m ON t.id_mobil = m.id_mobil
                WHERE t.id_transaksi = '$id_transaksi'";

$result = mysqli_query($conn, $query_utama);

if (mysqli_num_rows($result) == 0) {
  header("Location: pengembalian.php");
  exit;
}

$data = mysqli_fetch_assoc($result);

$q_cek_denda = mysqli_query($conn, "SELECT * FROM denda_kerusakan WHERE id_transaksi = '$id_transaksi'");
$denda_data  = mysqli_fetch_assoc($q_cek_denda);

if ($denda_data) {
    $all_fields = array_values($denda_data);
    $biaya_fisik     = (float)$all_fields[2]; 
    $denda_jam       = (float)$all_fields[3]; 
    $total_akumulasi = (float)$all_fields[4]; 
    
    $string_foto     = isset($all_fields[5]) ? $all_fields[5] : ''; 
    $catatan_lap     = !empty($all_fields[6]) ? $all_fields[6] : 'Pengecekan selesai.';
} else {
    $biaya_fisik     = 0;
    $denda_jam       = 0;
    $total_akumulasi = 0;
    $catatan_lap     = 'Mobil kembali mulus tanpa cacat fisik.';
    $string_foto     = '';
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

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center my-6 gap-4">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
              Berita Acara Pengembalian Mobil #<?php echo $data['id_transaksi']; ?>
            </h2>
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <a href="print_denda.php?id=<?php echo $data['id_transaksi']; ?>" target="_blank" class="px-4 py-2 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-sm inline-flex items-center gap-1.5 transition-colors">
                🖨️ Export Faktur Nota
              </a>
              <a href="pengembalian.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-colors dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                ← Kembali
              </a>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="md:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4 text-xs">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Info Utama</h3>
              <div>
                <p class="text-gray-400 uppercase font-medium">ID Transaksi</p>
                <p class="font-mono font-bold text-purple-600 mt-0.5 text-sm"><?php echo $data['id_transaksi']; ?></p>
              </div>
              <div>
                <p class="text-gray-400 uppercase font-medium">Nama Pelanggan</p>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5"><?php echo htmlspecialchars($data['nama_pelanggan']); ?></p>
              </div>
              <div>
                <p class="text-gray-400 uppercase font-medium">Armada Mobil</p>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5"><?php echo htmlspecialchars($data['merk'] . ' ' . $data['tipe']); ?></p>
                <p class="font-mono text-gray-500 mt-0.5 dark:text-gray-400"><?php echo $data['nomor_polisi']; ?></p>
              </div>
            </div>

            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-xs flex flex-col justify-between">
              <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-4 dark:border-gray-700">Rincian Finansial Denda</h3>
                <div class="space-y-3">
                  <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">1. Denda Keterlambatan Waktu</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Rp <?php echo number_format($denda_jam, 0, ',', '.'); ?></span>
                  </div>
                  <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">2. Klaim Kerusakan Fisik Unit</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Rp <?php echo number_format($biaya_fisik, 0, ',', '.'); ?></span>
                  </div>
                  <div class="flex justify-between items-center pt-2 text-sm font-bold <?php echo ($total_akumulasi > 0) ? 'text-red-600' : 'text-green-600'; ?>">
                    <span>TOTAL AKUMULASI DENDA</span>
                    <span class="text-base">Rp <?php echo number_format($total_akumulasi, 0, ',', '.'); ?></span>
                  </div>
                </div>
              </div>

              <div class="mt-6 bg-amber-50 dark:bg-gray-700 p-4 rounded-lg border border-amber-100 dark:border-gray-600">
                <p class="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide mb-1">Catatan Kondisi Lapangan:</p>
                <p class="text-gray-700 dark:text-gray-200 italic">"<?php echo htmlspecialchars($catatan_lap); ?>"</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-12">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-6 dark:border-gray-700">
              Lampiran Dokumentasi Foto Bukti Kerusakan Fisik
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
              <?php
              if (!empty($string_foto)) {
                $array_foto = explode(',', $string_foto);
                $jumlah_terpajang = 0;

                foreach ($array_foto as $index => $nama_foto) {
                  $nama_foto = trim($nama_foto);
                  if (file_exists('assets/img/bukti/' . $nama_foto) && !empty($nama_foto)) {
                    $jumlah_terpajang++;
              ?>
                    <div class="group relative bg-gray-50 dark:bg-gray-700 rounded-xl overflow-hidden border p-2 shadow-sm hover:shadow transition-shadow dark:border-gray-600">
                      <div class="w-full h-40 flex items-center justify-center bg-white dark:bg-gray-600 rounded-lg overflow-hidden">
                        <img src="assets/img/bukti/<?php echo $nama_foto; ?>" class="max-w-full max-h-full object-contain transition-transform group-hover:scale-105 duration-200" alt="Bukti Kerusakan">
                      </div>
                      <div class="mt-2 text-center text-[11px] text-gray-400 font-mono dark:text-gray-400">
                        Foto_Bukti_<?php echo $index + 1; ?>.jpg
                      </div>
                    </div>
              <?php
                  }
                }
                if ($jumlah_terpajang == 0) {
                  echo '<div class="col-span-full py-6 text-center text-gray-400 italic">Berkas gambar tidak ditemukan di folder assets/img/bukti/.</div>';
                }
              } else {
                echo '<div class="col-span-full py-6 text-center text-gray-400 italic">Tidak ada lampiran berkas foto kerusakan fisik yang diunggah (Unit Kembali Mulus).</div>';
              }
              ?>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</body>
</html>