<?php
session_start();
include 'config.php';

// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID Transaksi dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pengembalian.php");
    exit;
}

$id_transaksi = mysqli_real_escape_string($conn, $_GET['id']);

// Query untuk mengambil data gabungan dari tabel transaksi, denda, pelanggan, dan mobil
$query_denda = "SELECT t.*, d.biaya_kerusakan, d.denda_waktu, d.total_denda, d.keterangan_kerusakan,
                       p.nama_pelanggan, p.no_telepon,
                       m.merk, m.tipe, m.nomor_polisi
                FROM transaksi_rental t
                JOIN denda_kerusakan d ON t.id_transaksi = d.id_transaksi
                JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                JOIN mobil m ON t.id_mobil = m.id_mobil
                WHERE t.id_transaksi = '$id_transaksi'";

$result = mysqli_query($conn, $query_denda);

// Jika tidak ada riwayat denda pada transaksi ini, kembalikan ke log utama
if (mysqli_num_rows($result) == 0) {
    header("Location: pengembalian.php");
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
                Berita Acara Pengembalian & Klaim Denda
              </h2>
              <a href="pengembalian.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
                ← Kembali
              </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              
              <!-- KOTAK KIRI: RINGKASAN TRANSAKSI & UNIT -->
              <div class="md:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4 text-xs">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Info Utama</h3>
                
                <div>
                  <p class="text-gray-400 uppercase font-medium">ID Transaksi</p>
                  <p class="font-mono font-bold text-gray-900 dark:text-white mt-0.5 text-sm"><?php echo $data['id_transaksi']; ?></p>
                </div>
                <div>
                  <p class="text-gray-400 uppercase font-medium">Nama Pelanggan</p>
                  <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5"><?php echo htmlspecialchars($data['nama_pelanggan']); ?></p>
                </div>
                <div>
                  <p class="text-gray-400 uppercase font-medium">Armada Mobil</p>
                  <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5"><?php echo htmlspecialchars($data['merk'] . ' ' . $data['tipe']); ?></p>
                  <p class="font-mono text-gray-500 mt-0.5"><?php echo $data['nomor_polisi']; ?></p>
                </div>
              </div>

              <!-- KOTAK KANAN: RINCIAN AKUMULASI KLAIM DENDA -->
              <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-xs flex flex-col justify-between">
                <div>
                  <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-4 dark:border-gray-700">Rincian Finansial Denda</h3>
                  
                  <div class="space-y-3">
                    <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700">
                      <span class="text-gray-600 dark:text-gray-400">1. Denda Keterlambatan Waktu (Jam)</span>
                      <span class="font-semibold text-gray-800 dark:text-gray-200">Rp <?php echo number_format($data['denda_waktu'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-700">
                      <span class="text-gray-600 dark:text-gray-400">2. Klaim Kerusakan Fisik Unit</span>
                      <span class="font-semibold text-gray-800 dark:text-gray-200">Rp <?php echo number_format($data['biaya_kerusakan'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-center pt-2 text-sm font-bold text-red-600">
                      <span>TOTAL AKUMULASI DENDA</span>
                      <span class="text-base">Rp <?php echo number_format($data['total_denda'], 0, ',', '.'); ?></span>
                    </div>
                  </div>
                </div>

                <div class="mt-6 bg-amber-50 dark:bg-gray-700 p-4 rounded-lg border border-amber-100 dark:border-gray-600">
                  <p class="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide mb-1">Catatan Kondisi Lapangan:</p>
                  <p class="text-gray-700 dark:text-gray-200 italic">"<?php echo htmlspecialchars($data['keterangan_kerusakan']); ?>"</p>
                </div>
              </div>

            </div>

            <!-- CARD APAPUN DI BAWAH: GALERI BANYAK FOTO BUKTI FISIK -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-12">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider border-b pb-2 mb-6 dark:border-gray-700">
                Lampiran Dokumentasi Foto Bukti Kerusakan Fisik
              </h3>

              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <?php
                // AMBIL DATA STRING FOTO DARI DATABASE, LALU PECAH JADI ARRAY BERDASARKAN TANDA KOMA
                $string_foto = $data['keterangan_kerusakan']; // Di kolom ini kita simpan string fotonya kemarin
                
                if (!empty($string_foto)) {
                    $array_foto = explode(',', $string_foto);
                    
                    foreach ($array_foto as $index => $nama_foto) {
                        $nama_foto = trim($nama_foto);
                        // Pastikan file fisiknya benar-benar ada di folder local proyekmu
                        if (file_exists('assets/img/mobil/' . $nama_foto) && !empty($nama_foto)) {
                ?>
                            <div class="group relative bg-gray-50 dark:bg-gray-700 rounded-xl overflow-hidden border p-2 shadow-sm hover:shadow transition-shadow">
                              <div class="w-full h-40 flex items-center justify-center bg-white rounded-lg overflow-hidden">
                                <img src="assets/img/mobil/<?php echo $nama_foto; ?>" class="max-w-full max-h-full object-contain transition-transform group-hover:scale-105 duration-200" alt="Bukti Kerusakan">
                              </div>
                              <div class="mt-2 text-center text-[11px] text-gray-400 font-mono">
                                Foto_Bukti_<?php echo $index + 1; ?>.jpg
                              </div>
                            </div>
                <?php
                        }
                    }
                } else {
                    echo '<div class="col-span-full py-6 text-center text-gray-400 italic">Tidak ada lampiran foto fisik yang diunggah untuk transaksi ini.</div>';
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