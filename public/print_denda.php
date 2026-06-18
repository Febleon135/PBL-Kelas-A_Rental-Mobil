<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
    exit("Akses ditolak.");
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// SOLUSI AMAN: Kueri hanya mengambil data dari tabel master transaksi, pelanggan, dan mobil
$query = mysqli_query($conn, "SELECT t.*, p.nama_pelanggan, p.no_telepon, m.merk, m.tipe, m.nomor_polisi
                              FROM transaksi_rental t
                              JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                              JOIN mobil m ON t.id_mobil = m.id_mobil
                              WHERE t.id_transaksi = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    exit("Data transaksi tidak ditemukan.");
}

// Ambil data denda secara terpisah menggunakan metode urutan fisik array_values
$q_cek_denda = mysqli_query($conn, "SELECT * FROM denda_kerusakan WHERE id_transaksi = '$id'");
$denda_data  = mysqli_fetch_assoc($q_cek_denda);

if ($denda_data) {
    $all_fields = array_values($denda_data);
    $total_akumulasi = (float)$all_fields[4]; // Indeks ke-4: total akumulasi keseluruhan denda
    $catatan_lap     = !empty($all_fields[6]) ? $all_fields[6] : 'Pengecekan selesai.'; // Indeks ke-6: catatan teks
} else {
    $total_akumulasi = 0;
    $catatan_lap     = 'Unit dikembalikan tepat waktu tanpa kerusakan.';
}

// FIX: Hilangkan jam 00:00 dan set jatuh tempo jadi +1 hari (24 Jam) dari tanggal kembali
$tgl_jatuh_tempo = date('d-m-Y', strtotime($data['tanggal_kembali'] . ' +1 day'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Faktur Denda - <?= $data['id_transaksi']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .border-dashed {
            border-bottom: 1px dashed #000;
            margin: 12px 0;
        }

        .flex-box {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }

        .bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="text-center">
        <h2 style="margin:0;">WHEELSRENT</h2>
        <p style="margin:4px 0;">Jalan Terusan Sutami, Bandung, Jawa Barat</p>
        <p style="margin:0;">Faktur Resmi Denda Keterlambatan Armada</p>
    </div>

    <div class="border-dashed"></div>

    <div class="flex-box"><span>ID Transaksi : <?= $data['id_transaksi']; ?></span><span>Tanggal: <?= date('d/m/Y H:i'); ?></span></div>
    <div class="flex-box"><span>Pelanggan : <?= htmlspecialchars($data['nama_pelanggan']); ?></span><span>Telp: <?= $data['no_telepon']; ?></span></div>
    <div class="flex-box"><span>Unit Mobil : <?= htmlspecialchars($data['merk'] . ' ' . $data['tipe'] . ' [' . $data['nomor_polisi'] . ']'); ?></span></div>

    <div class="border-dashed"></div>

    <div class="flex-box">
        <span>Kontrak Jatuh Tempo :</span>
        <span><?= $tgl_jatuh_tempo; ?></span>
    </div>
    <div class="border-dashed"></div>

    <p class="bold" style="margin: 6px 0 2px 0;">REKAP PENGECEKAN PETUGAS KASIR:</p>
    <p style="margin: 0 0 12px 0; font-style: italic; color:#555;">"<?= htmlspecialchars($catatan_lap); ?>"</p>

    <div class="flex-box bold" style="font-size: 14px;">
        <span>TOTAL TAGIHAN DENDA (NET) :</span>
        <span>Rp <?= number_format($total_akumulasi, 0, ',', '.'); ?></span>
    </div>

    <div class="border-dashed"></div>
    <p class="text-center" style="margin-top: 24px; font-style: italic;">-- Harap simpan resi denda ini sebagai bukti pembayaran sah --</p>

    <div class="text-center no-print" style="margin-top: 30px;">
        <button onclick="window.print()" style="padding: 6px 16px; font-weight: bold; cursor: pointer;">Cetak Faktur</button>
        <button onclick="window.close()" style="padding: 6px 16px; margin-left: 6px;">Tutup</button>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>