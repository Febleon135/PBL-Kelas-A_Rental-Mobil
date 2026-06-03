<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID Transaksi dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: penyewaan.php");
    exit;
}

$id_transaksi = mysqli_real_escape_string($conn, $_GET['id']);

// Query JOIN super lengkap untuk menarik detail data transaksi, pelanggan, mobil, dan akun staff kasir
$query_detail = "SELECT t.*, p.nama_pelanggan, p.NIK, p.no_telepon, p.alamat, 
                        m.merk, m.tipe, m.nomor_polisi, m.harga_sewa_per_hari,
                        u.nama_pengguna as nama_kasir
                 FROM transaksi_rental t
                 JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                 JOIN mobil m ON t.id_mobil = m.id_mobil
                 JOIN pengguna u ON t.id_pengguna = u.id_pengguna
                 WHERE t.id_transaksi = '$id_transaksi'";

$result = mysqli_query($conn, $query_detail);

// Jika ID transaksi gadungan atau tidak ditemukan, kembalikan ke log utama
if (mysqli_num_rows($result) == 0) {
    header("Location: penyewaan.php");
    exit;
}

$data = mysqli_fetch_assoc($result);

// Hitung Harga Kotor sebelum diskon untuk keperluan rincian nota
$harga_kotor = ($data['harga_sewa_per_hari'] * $data['durasi_sewa']);
?>

<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
    <?php include 'components/head.php'; ?>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printable-nota,
            #printable-nota * {
                visibility: visible;
            }

            #printable-nota {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
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

                    <div class="flex justify-between items-center my-6 no-print">
                        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                            Detail Transaksi #<?php echo $data['id_transaksi']; ?>
                        </h2>
                        <div class="flex space-x-3">
                            <a href="penyewaan.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm">
                                ← Kembali
                            </a>
                            <button onclick="window.print()" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-sm flex items-center">
                                🖨️ Cetak Nota Struk
                            </button>
                        </div>
                    </div>

                    <div id="printable-nota" class="p-8 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 mb-8 max-w-4xl mx-auto w-full">

                        <div class="flex justify-between items-start border-b pb-6 border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    <img src="assets/img/wheelsrent-logo.jpeg" alt="Logo" style="height: 70px; width: auto;" class="object-contain rounded">
                                    <h1 class="text-xl font-black text-purple-600 tracking-wide"></h1>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Solusi Rental Mobil Digital Terpercaya</p>
                                <p class="text-xs text-gray-400">Bandung, Jawa Barat, Indonesia</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 uppercase mb-2">
                                    <?php echo $data['status_sewa']; ?>
                                </span>
                                <p class="text-xs text-gray-400">ID Transaksi: <span class="font-mono font-bold text-gray-800 dark:text-gray-200"><?php echo $data['id_transaksi']; ?></span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-6 text-xs text-gray-600 dark:text-gray-400">
                            <div>
                                <h4 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-2">DATA PENYEWA / PELANGGAN:</h4>
                                <p class="font-bold text-gray-900 dark:text-white text-sm mb-1"><?php echo htmlspecialchars($data['nama_pelanggan']); ?></p>
                                <p>NIK: <span class="font-mono"><?php echo $data['NIK']; ?></span></p>
                                <p>No. Telp: <?php echo $data['no_telepon']; ?></p>
                                <p class="mt-1">Alamat: <?php echo htmlspecialchars($data['alamat']); ?></p>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-2">WAKTU OPERASIONAL RENTAL:</h4>
                                <table class="w-full text-left">
                                    <tr>
                                        <td class="py-1 pr-4">Tanggal Mulai</td>
                                        <td class="py-1 font-semibold text-gray-800 dark:text-gray-200">: <?php echo date('d M Y', strtotime($data['tanggal_sewa'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 pr-4">Batas Kembali</td>
                                        <td class="py-1 font-semibold text-gray-800 dark:text-gray-200">: <?php echo date('d M Y', strtotime($data['tanggal_kembali'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 pr-4">Total Durasi</td>
                                        <td class="py-1 font-semibold text-purple-600">: <?php echo $data['durasi_sewa']; ?> Hari</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm my-6">
                            <table class="w-full whitespace-no-wrap text-xs text-left">
                                <thead>
                                    <tr class="font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <th class="px-4 py-3">Deskripsi Unit Kendaraan</th>
                                        <th class="px-4 py-3 text-center">Tarif Harian</th>
                                        <th class="px-4 py-3 text-center">Durasi</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <td class="px-4 py-4">
                                            <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($data['merk'] . ' ' . $data['tipe']); ?></p>
                                            <p class="font-mono text-[11px] text-gray-400 mt-0.5">Nomor Polisi: <?php echo $data['nomor_polisi']; ?></p>
                                        </td>
                                        <td class="px-4 py-4 text-center">Rp <?php echo number_format($data['harga_sewa_per_hari'], 0, ',', '.'); ?></td>
                                        <td class="px-4 py-4 text-center"><?php echo $data['durasi_sewa']; ?> Hari</td>
                                        <td class="px-4 py-4 text-right font-medium">Rp <?php echo number_format($harga_kotor, 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-4">
                            <div class="w-full md:w-64 text-xs space-y-2 text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between">
                                    <span>Harga Kotor:</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">Rp <?php echo number_format($harga_kotor, 0, ',', '.'); ?></span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span>Potongan Diskon:</span>
                                    <span class="font-medium">- Rp <?php echo number_format($data['diskon'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="flex justify-between border-t pt-2 text-sm font-bold text-gray-900 dark:text-white">
                                    <span>TOTAL BAYAR:</span>
                                    <span class="text-purple-600 text-base">Rp <?php echo number_format($data['total_biaya'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-6 border-t border-dashed dark:border-gray-700 flex justify-between text-center text-xs text-gray-500">
                            <div>
                                <p>Penerima / Penyewa</p>
                                <div class="h-16"></div>
                                <p class="font-bold text-gray-800 dark:text-gray-200">( <?php echo htmlspecialchars($data['nama_pelanggan']); ?> )</p>
                            </div>
                            <div>
                                <p>Petugas Kasir,</p>
                                <div class="h-16"></div>
                                <p class="font-bold text-gray-800 dark:text-gray-200">( <?php echo htmlspecialchars($data['nama_kasir']); ?> )</p>
                            </div>
                        </div>

                    </div>

                </div>
            </main>
        </div>
    </div>
</body>

</html>