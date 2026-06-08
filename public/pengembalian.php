<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

define('TARIF_DENDA_PER_JAM', 50000);

if (isset($_POST['proses_kembali'])) {
  $id_transaksi = mysqli_real_escape_string($conn, $_POST['id_transaksi']);
  $tanggal_kembali_aktual = mysqli_real_escape_string($conn, $_POST['tanggal_kembali_aktual']);
  $kondisi_mobil = mysqli_real_escape_string($conn, $_POST['kondisi_mobil']);
  $kondisi_mesin = mysqli_real_escape_string($conn, $_POST['kondisi_mesin']);
  $kondisi_bensin = mysqli_real_escape_string($conn, $_POST['kondisi_bensin']);
  $km_mobil = intval($_POST['km_mobil']);
  $biaya_kerusakan = (float)$_POST['biaya_kerusakan'];
  $keterangan_kerusakan = isset($_POST['keterangan_kerusakan']) ? mysqli_real_escape_string($conn, $_POST['keterangan_kerusakan']) : '';

  $array_nama_foto = [];
  if (isset($_FILES['foto_bukti']['name']) && !empty(array_filter($_FILES['foto_bukti']['name']))) {
    $total_files = count($_FILES['foto_bukti']['name']);
    $ekstensi_ok = array('png', 'jpg', 'jpeg', 'webp');

    for ($i = 0; $i < $total_files; $i++) {
      $nama_file_asli = $_FILES['foto_bukti']['name'][$i];
      $file_tmp = $_FILES['foto_bukti']['tmp_name'][$i];
      $x = explode('.', $nama_file_asli);
      $ekstensi = strtolower(end($x));

      if (in_array($ekstensi, $ekstensi_ok) === true) {
        $nama_foto_unik = "BUKTI-" . $id_transaksi . "-" . ($i + 1) . "-" . time() . "." . $ekstensi;
        if (move_uploaded_file($file_tmp, 'assets/img/bukti/' . $nama_foto_unik)) {
          $array_nama_foto[] = $nama_foto_unik;
        }
      }
    }
  }
  $string_foto_gabungan = implode(',', $array_nama_foto);

  $q_trans = mysqli_query($conn, "SELECT id_mobil, tanggal_sewa, tanggal_kembali FROM transaksi_rental WHERE id_transaksi = '$id_transaksi'");
  $r_trans = mysqli_fetch_assoc($q_trans);
  $id_mobil = $r_trans['id_mobil'];

  $jam_kontrak_awal = date('H:i:s', strtotime($r_trans['tanggal_sewa']));
  $tgl_seharusnya = $r_trans['tanggal_kembali'] . ' ' . $jam_kontrak_awal;

  $selisih_detik = strtotime($tanggal_kembali_aktual) - strtotime($tgl_seharusnya);
  $jam_terlambat = max(0, ceil($selisih_detik / 3600));
  $total_denda_waktu = $jam_terlambat * TARIF_DENDA_PER_JAM;
  $total_denda_keseluruhan = $total_denda_waktu + $biaya_kerusakan;

  mysqli_begin_transaction($conn);

  $q_id_c = mysqli_query($conn, "SELECT id_cek FROM pengecekan ORDER BY id_cek DESC LIMIT 1");
  $row_id_c = mysqli_fetch_assoc($q_id_c);
  $id_cek = $row_id_c ? "CEK" . sprintf("%03d", substr($row_id_c['id_cek'], 3) + 1) : "CEK001";

  $ins_pengecekan = "INSERT INTO pengecekan (id_cek, id_transaksi, tgl_cek, kondisi_body, kondisi_mesin, indikator_bensin, km_mobil) 
                     VALUES ('$id_cek', '$id_transaksi', '$tanggal_kembali_aktual', '$kondisi_mobil', '$kondisi_mesin', '$kondisi_bensin', '$km_mobil')";
  $u_pengecekan = mysqli_query($conn, $ins_pengecekan);

  $u_transaksi = mysqli_query($conn, "UPDATE transaksi_rental SET status_sewa = 'selesai' WHERE id_transaksi = '$id_transaksi'");

  $status_mobil_baru = ($kondisi_mobil === 'rusak' || $kondisi_mesin === 'bermasalah') ? 'maintenance' : 'tersedia';
  $u_mobil = mysqli_query($conn, "UPDATE mobil SET status_mobil = '$status_mobil_baru' WHERE id_mobil = '$id_mobil'");

  $res_denda = true;
  if ($total_denda_keseluruhan > 0) {
    $q_id_d = mysqli_query($conn, "SELECT id_denda FROM denda_kerusakan ORDER BY id_denda DESC LIMIT 1");
    $row_id_d = mysqli_fetch_assoc($q_id_d);
    $id_denda = $row_id_d ? "DND" . sprintf("%03d", substr($row_id_d['id_denda'], 3) + 1) : "DND001";

    $ins_denda = "INSERT INTO denda_kerusakan VALUES ('$id_denda', '$id_transaksi', '$biaya_kerusakan', '$total_denda_waktu', '$total_denda_keseluruhan', '$string_foto_gabungan', '$keterangan_kerusakan')";
    $res_denda = mysqli_query($conn, $ins_denda);
  }

  $res_maint_ticket = true;
  if ($status_mobil_baru === 'maintenance') {
    $q_id_m = mysqli_query($conn, "SELECT id_maintenance FROM maintenance ORDER BY id_maintenance DESC LIMIT 1");
    $row_id_m = mysqli_fetch_assoc($q_id_m);
    $id_maint_baru = $row_id_m ? "MNT" . sprintf("%03d", substr($row_id_m['id_maintenance'], 3) + 1) : "MNT001";

    $tgl_sekarang = date('Y-m-d');
    $ins_maint = "INSERT INTO maintenance VALUES ('$id_maint_baru', '$id_mobil', '$tgl_sekarang', 0, 'proses', '$keterangan_kerusakan')";
    $res_maint_ticket = mysqli_query($conn, $ins_maint);
  }

  if ($u_pengecekan && $u_transaksi && $u_mobil && $res_denda && $res_maint_ticket) {
    mysqli_commit($conn);
    header("Location: pengembalian.php?status=success");
    exit;
  } else {
    mysqli_rollback($conn);
    header("Location: pengembalian.php?status=failed");
    exit;
  }
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
  $where_clause = "AND (t.id_transaksi LIKE '%$search%' OR p.nama_pelanggan LIKE '%$search%')";
}

$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.status_sewa = 'selesai' $where_clause");
$r_total = mysqli_fetch_assoc($q_total);
$total_data = $r_total['total'] ?? 0;
$total_pages = ceil($total_data / $limit);
if ($total_pages < 1) $total_pages = 1;
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
  <?php include 'components/head.php'; ?>
  <style>
    [x-cloak] {
      display: none !important;
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
            Pengembalian Mobil
          </h2>

          <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
              ✓ Mobil berhasil dikembalikan! Data denda fisik & durasi serta status armada otomatis diperbarui.
            </div>
          <?php endif; ?>

          <?php
          if (isset($_GET['action']) && $_GET['action'] == 'proses') {
            $id_t = mysqli_real_escape_string($conn, $_GET['id']);

            $q_data = mysqli_query($conn, "SELECT t.*, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, m.harga_sewa_per_hari 
                                           FROM transaksi_rental t
                                           JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                           JOIN mobil m ON t.id_mobil = m.id_mobil
                                           WHERE t.id_transaksi = '$id_t'");
            $dt = mysqli_fetch_assoc($q_data);

            $waktu_sekarang = date('Y-m-d H:i:s');
            $jam_kontrak_simulasi = date('H:i:s', strtotime($dt['tanggal_sewa']));
            $tgl_jatuh_tempo_aktual = $dt['tanggal_kembali'] . ' ' . $jam_kontrak_simulasi;

            $selisih_detik = strtotime($waktu_sekarang) - strtotime($tgl_jatuh_tempo_aktual);
            $jam_terlambat = max(0, ceil($selisih_detik / 3600));
            $denda_waktu_simulasi = $jam_terlambat * TARIF_DENDA_PER_JAM;
          ?>
            <div class="mb-6">
              <a href="pengembalian.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200">
                ← Kembali ke Daftar Antrean
              </a>
            </div>

            <form action="pengembalian.php" method="POST" enctype="multipart/form-data" class="space-y-6 mb-12">
              <input type="hidden" name="id_transaksi" value="<?php echo $dt['id_transaksi']; ?>">

              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Detail Transaksi</h3>

                <div style="display: flex; flex-wrap: wrap; gap: 16px; width: 100%;" class="text-xs">
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">ID Transaksi</p>
                      <p class="font-bold text-purple-600 dark:text-purple-400 font-mono mt-1"><?php echo $dt['id_transaksi']; ?></p>
                    </div>
                    <div class="w-full pt-2 border-t border-gray-100 dark:border-gray-700">
                      <p class="text-gray-400 font-medium uppercase">Durasi Sewa</p>
                      <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1"><?php echo $dt['durasi_sewa']; ?> Hari</p>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Pelanggan</p>
                      <p class="font-bold text-gray-900 dark:text-white mt-1"><?php echo htmlspecialchars($dt['nama_pelanggan']); ?></p>
                    </div>
                    <div class="w-full pt-2 border-t border-gray-100 dark:border-gray-700">
                      <p class="text-red-600 font-bold uppercase">Keterlambatan</p>
                      <p class="font-bold text-red-600 mt-1"><?php echo $jam_terlambat; ?> Jam</p>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Mobil</p>
                      <p class="font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo htmlspecialchars($dt['merk'] . ' ' . $dt['tipe']); ?> <span class="font-mono text-purple-500">(<?php echo htmlspecialchars($dt['nomor_polisi']); ?>)</span></p>
                    </div>
                    <div class="w-full pt-2 border-t border-gray-100 dark:border-gray-700">
                      <p class="text-red-600 font-bold uppercase">Tarif Keterlambatan</p>
                      <p class="font-bold text-red-600 mt-1">Rp <?php echo number_format(TARIF_DENDA_PER_JAM, 0, ',', '.'); ?>/Jam</p>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: flex-start; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Sewa</p>
                      <p class="font-semibold text-gray-700 dark:text-gray-300 mt-1"><?php echo date('d M Y H:i', strtotime($dt['tanggal_sewa'])); ?></p>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Kembali (Seharusnya)</p>
                      <p class="font-bold text-amber-600 mt-1"><?php echo date('d M Y H:i', strtotime($tgl_jatuh_tempo_aktual)); ?></p>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: flex-start; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Pengembalian Unit</p>
                      <p class="font-bold text-green-600 dark:text-green-400 mt-1"><?php echo date('d M Y H:i', strtotime($waktu_sekarang)); ?></p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Form Pengecekan Akhir</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal & Jam Cek Masuk Pool</span>
                      <input type="text" name="tanggal_kembali_aktual" value="<?php echo date('Y-m-d H:i:s'); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-input focus:border-purple-400 rounded-lg border p-2" />
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Fisik Body Mobil</span>
                      <select name="kondisi_mobil" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-select focus:border-purple-400 rounded-lg border p-2">
                        <option value="baik">Mulus / Baik</option>
                        <option value="rusak">Lecet / Rusak</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Kelayakan Mesin</span>
                      <select name="kondisi_mesin" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-select focus:border-purple-400 rounded-lg border p-2">
                        <option value="baik">Baik / Normal</option>
                        <option value="bermasalah">Bermasalah / Perlu Service</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Indikator Sisa Bensin Tangki</span>
                      <select name="kondisi_bensin" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-select focus:border-purple-400 rounded-lg border p-2">
                        <option value="full">Full / Penuh</option>
                        <option value="half">Half / Setengah</option>
                      </select>
                    </label>
                  </div>

                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">KM Spidometer Terakhir</span>
                      <div class="relative mt-1 rounded-md shadow-sm">
                        <input type="number" name="km_mobil" required placeholder="Contoh: 12950" class="block w-full text-sm dark:bg-gray-700 dark:text-gray-100 form-input pr-12 focus:border-purple-400 rounded-lg border p-2" />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400 text-xs font-semibold">KM</div>
                      </div>
                    </label>

                    <div>
                      <span class="text-gray-700 dark:text-gray-400 text-sm font-medium">Foto Bukti Fisik Kerusakan (Bisa Banyak Foto)</span>
                      <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <input
                          type="file"
                          name="foto_bukti[]"
                          accept="image/*"
                          multiple
                          class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" />
                        <p class="text-[11px] text-gray-400 mt-1.5 italic">💡 Kamu bisa memilih banyak foto sekaligus sambil menekan tombol Ctrl / Shift.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Form Denda Kerusakan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Jenis Kerusakan Fisik</span>
                      <select name="jenis_kerusakan" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-select focus:border-purple-400 rounded-lg border p-2">
                        <option value="none">Tidak Ada Kerusakan (Rp 0)</option>
                        <option value="lecet">Lecet / Goresan Ringan</option>
                        <option value="penyok">Penyok / Rusak Berat</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Letak Kerusakan Komponen</span>
                      <input type="text" name="letak_kerusakan" placeholder="Contoh: Pintu Kanan Belakang" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-input focus:border-purple-400 rounded-lg border p-2" />
                    </label>
                  </div>

                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Keterangan Catatan Tambahan</span>
                      <input type="text" name="keterangan_kerusakan" placeholder="Contoh: Terdapat lecet sepanjang 10 cm" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-input focus:border-purple-400 rounded-lg border p-2" />
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Biaya Ganti Rugi Kerusakan (Rp)</span>
                      <input type="number" name="biaya_kerusakan" min="0" value="0" placeholder="Contoh: 500000" class="block w-full mt-1 text-sm dark:bg-gray-700 dark:text-gray-100 form-input focus:border-purple-400 rounded-lg border p-2" />
                    </label>
                  </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t dark:border-gray-700">
                  <a href="pengembalian.php" class="px-5 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Cancel</a>
                  <button type="submit" name="proses_kembali" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Save / Selesai</button>
                </div>
              </div>

            </form>
          <?php
          } else {
          ?>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-4">
              <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Daftar Mobil Sedang Disewa (Belum Pulang)</h3>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8 bg-white dark:bg-gray-800">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap text-left text-xs">
                  <thead>
                    <tr class="font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Transaksi</th>
                      <th class="px-4 py-3">Pelanggan</th>
                      <th class="px-4 py-3">Mobil</th>
                      <th class="px-4 py-3">Tgl Jatuh Tempo</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                    <?php
                    $q_load = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_kembali 
                                                   FROM transaksi_rental t
                                                   JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                   JOIN mobil m ON t.id_mobil = m.id_mobil
                                                   WHERE t.status_sewa = 'berjalan' ORDER BY t.tanggal_kembali ASC");

                    if (mysqli_num_rows($q_load) > 0) {
                      while ($row = mysqli_fetch_assoc($q_load)) {
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                          <td class="px-4 py-3 font-mono font-bold text-purple-600 dark:text-purple-400"><?php echo $row['id_transaksi']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($row['merk'] . " " . $row['tipe'] . " [" . $row['nomor_polisi'] . "]"); ?></td>
                          <td class="px-4 py-3 text-red-600 dark:text-red-400 font-bold"><?php echo date('d-m-Y', strtotime($row['tanggal_kembali'])); ?></td>
                          <td class="px-4 py-3">
                            <a href="pengembalian.php?action=proses&id=<?php echo $row['id_transaksi']; ?>" class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                              Selesai
                            </a>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">Semua mobil ada di dalam garasi. Belum ada unit yang sedang disewa keluar.</td></tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-4 mt-8 flex items-center justify-between w-full">
              <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider m-0">
                Log Riwayat Pengembalian & Denda
              </h3>

              <form method="GET" action="pengembalian.php" class="m-0 p-0 flex items-center gap-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari ID / nama pelanggan..."
                  class="w-48 pl-3 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:border-purple-400">
                <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-sm whitespace-nowrap">
                  Search
                </button>
              </form>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-4 bg-white dark:bg-gray-800">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap text-left text-xs">
                  <thead>
                    <tr class="font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Transaksi</th>
                      <th class="px-4 py-3">Pelanggan</th>
                      <th class="px-4 py-3">Mobil</th>
                      <th class="px-4 py-3">Total Denda Ditagih</th>
                      <th class="px-4 py-3 text-center">Aksi Petugas</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    <?php
                    $q_history = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.total_biaya 
                                                      FROM transaksi_rental t
                                                      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                      JOIN mobil m ON t.id_mobil = m.id_mobil
                                                      WHERE t.status_sewa = 'selesai' $where_clause
                                                      ORDER BY t.id_transaksi DESC 
                                                      LIMIT $start, $limit");

                    if (mysqli_num_rows($q_history) > 0) {
                      while ($h_row = mysqli_fetch_assoc($q_history)) {
                        $id_t_cek = $h_row['id_transaksi'];
                        $q_cek_biaya = mysqli_query($conn, "SELECT * FROM denda_kerusakan WHERE id_transaksi = '$id_t_cek'");
                        $denda_exist = mysqli_fetch_assoc($q_cek_biaya);

                        if ($denda_exist) {
                          $all_values = array_values($denda_exist);
                          $nominal_denda = (float)$all_values[4];
                        } else {
                          $nominal_denda = 0;
                        }
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                          <td class="px-4 py-3 font-mono font-bold text-purple-600 dark:text-purple-400"><?php echo $h_row['id_transaksi']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($h_row['nama_pelanggan']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($h_row['merk'] . " " . $h_row['tipe'] . " [" . $h_row['nomor_polisi'] . "]"); ?></td>
                          <td class="px-4 py-3 font-bold <?php echo ($nominal_denda > 0) ? 'text-red-600' : 'text-green-600'; ?>">
                            <?php echo ($nominal_denda > 0) ? 'Rp ' . number_format($nominal_denda, 0, ',', '.') : '✓ Bebas Denda'; ?>
                          </td>
                          <td class="px-4 py-3 text-center">
                            <a href="detail_pengembalian.php?id=<?php echo $h_row['id_transaksi']; ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-sm transition-colors">
                              Detail
                            </a>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">Data riwayat penyelesaian armada tidak ditemukan.</td></tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <?php if ($total_pages > 1): ?>
              <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t bg-gray-50 sm:grid-cols-4 dark:bg-gray-800 dark:text-gray-400 mb-8 rounded-lg shadow-sm">
                <span class="flex items-center col-span-2 text-gray-400 normal-case">Menampilkan <?php echo min($start + 1, $total_data); ?>-<?php echo min($start + $limit, $total_data); ?> dari <?php echo $total_data; ?> arsip log selesai</span>
                <span class="col-span-2 sm:mt-0 sm:justify-end flex">
                  <nav aria-label="Table navigation">
                    <ul class="inline-flex items-center space-x-1">
                      <li><a href="pengembalian.php?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page <= 1) echo 'pointer-events-none opacity-50'; ?>">Prev</a></li>
                      <li><span class="px-3 py-1 text-white bg-purple-600 rounded-md"><?php echo $page; ?> / <?php echo $total_pages; ?></span></li>
                      <li><a href="pengembalian.php?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page >= $total_pages) echo 'pointer-events-none opacity-50'; ?>">Next</a></li>
                    </ul>
                  </nav>
                </span>
              </div>
            <?php endif; ?>

          <?php
          }
          ?>
        </div>
      </main>
    </div>
  </div>
</body>

</html>