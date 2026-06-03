<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// TARIF DENDA TETAP SESUAI FIGMA (Rp 50.000 per JAM keterlambatan)
define('TARIF_DENDA_PER_JAM', 50000);

// ─── LOGIKA PROSES BACKEND: PROSES PENGEMBALIAN & HITUNG DENDA ───
if (isset($_POST['proses_kembali'])) {
  $id_transaksi    = mysqli_real_escape_string($conn, $_POST['id_transaksi']);
  $tanggal_kembali_aktual = mysqli_real_escape_string($conn, $_POST['tanggal_kembali_aktual']); // Format datetime-local
  $kondisi_mobil   = mysqli_real_escape_string($conn, $_POST['kondisi_mobil']);
  $biaya_kerusakan = (float)$_POST['biaya_kerusakan'];

  // ─── PROSES UPLOAD BANYAK FOTO BUKTI KERUSAKAN ───
  $array_nama_foto = [];
  
  if (isset($_FILES['foto_bukti']['name']) && !empty(array_filter($_FILES['foto_bukti']['name']))) {
      $total_files = count($_FILES['foto_bukti']['name']);
      $ekstensi_ok = array('png', 'jpg', 'jpeg', 'webp');

      for ($i = 0; $i < $total_files; $i++) {
          $nama_file_asli = $_FILES['foto_bukti']['name'][$i];
          $file_tmp       = $_FILES['foto_bukti']['tmp_name'][$i];
          
          $x = explode('.', $nama_file_asli);
          $ekstensi = strtolower(end($x));
          
          if (in_array($ekstensi, $ekstensi_ok) === true) {
              // Menyusun nama unik file foto
              $nama_foto_unik = "BUKTI-" . $id_transaksi . "-" . ($i + 1) . "-" . time() . "." . $ekstensi;
              
              // Taruh di folder lokal project kalian
              if (move_uploaded_file($file_tmp, 'assets/img/mobil/' . $nama_foto_unik)) {
                  $array_nama_foto[] = $nama_foto_unik;
              }
          }
      }
  }

  // Gabungkan nama file gambar dengan tanda koma (Contoh: foto1.jpg,foto2.jpg)
  $string_foto_gabungan = implode(',', $array_nama_foto);

  // 1. Tarik data transaksi untuk tahu id_mobil dan tanggal_kembali seharusnya
  $q_trans = mysqli_query($conn, "SELECT id_mobil, tanggal_kembali FROM transaksi_rental WHERE id_transaksi = '$id_transaksi'");
  $r_trans = mysqli_fetch_assoc($q_trans);
  $id_mobil = $r_trans['id_mobil'];
  $tgl_seharusnya = $r_trans['tanggal_kembali'];

  // 2. HITUNG SELISIH JAM (Skala Jam Sesuai Figma)
  $selisih_detik = strtotime($tanggal_kembali_aktual) - strtotime($tgl_seharusnya);
  $jam_terlambat  = max(0, ceil($selisih_detik / 3600)); // Pembulatan ke atas jika lewat beberapa menit

  // 3. Rumus Denda Waktu & Akumulasi Total
  $total_denda_waktu = $jam_terlambat * TARIF_DENDA_PER_JAM;
  $total_denda_keseluruhan = $total_denda_waktu + $biaya_kerusakan;

  // Mulai Transaksi Database
  mysqli_begin_transaction($conn);

  // Update status transaksi rental menjadi 'selesai'
  $u_transaksi = mysqli_query($conn, "UPDATE transaksi_rental SET status_sewa = 'selesai' WHERE id_transaksi = '$id_transaksi'");

  // Kembalikan status mobil ke 'tersedia' atau 'maintenance' jika admin memilih rusak/lecet
  $status_mobil_baru = ($kondisi_mobil === 'rusak') ? 'maintenance' : 'tersedia';
  $u_mobil = mysqli_query($conn, "UPDATE mobil SET status_mobil = '$status_mobil_baru' WHERE id_mobil = '$id_mobil'");

  // Jika ada denda keterlambatan atau kerusakan fisik, simpan ke database
  $res_denda = true;
  if ($total_denda_keseluruhan > 0) {
    $q_id_d   = mysqli_query($conn, "SELECT id_denda FROM denda_kerusakan ORDER BY id_denda DESC LIMIT 1");
    $row_id_d = mysqli_fetch_assoc($q_id_d);
    $id_denda = $row_id_d ? "DND" . sprintf("%03d", substr($row_id_d['id_denda'], 3) + 1) : "DND001";

    // PERBAIKAN: Menyimpan string_foto_gabungan ke dalam kolom terakhir denda_kerusakan
    $ins_denda = "INSERT INTO denda_kerusakan VALUES ('$id_denda', '$id_transaksi', '$biaya_kerusakan', '$total_denda_waktu', '$total_denda_keseluruhan', '$string_foto_gabungan')";
    $res_denda = mysqli_query($conn, $ins_denda);
  }

  if ($u_transaksi && $u_mobil && $res_denda) {
    mysqli_commit($conn);
    header("Location: pengembalian.php?status=success");
    exit;
  } else {
    mysqli_rollback($conn);
    header("Location: pengembalian.php?status=failed");
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
            Pengembalian Mobil
          </h2>

          <!-- NOTIFIKASI STATUS -->
          <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
              ✓ Mobil berhasil dikembalikan! Data keuangan denda dan status armada otomatis diperbarui.
            </div>
          <?php endif; ?>

          <?php
          // ──────────────────────────────────────────────────────────────
          // INTERFACE 2: FORM TAMPILAN 3 CARD TERPISAH (PROSES KEMBALI)
          // ──────────────────────────────────────────────────────────────
          if (isset($_GET['action']) && $_GET['action'] == 'proses') {
            $id_t = mysqli_real_escape_string($conn, $_GET['id']);

            $q_data = mysqli_query($conn, "SELECT t.*, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, m.harga_sewa_per_hari 
                                           FROM transaksi_rental t
                                           JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                           JOIN mobil m ON t.id_mobil = m.id_mobil
                                           WHERE t.id_transaksi = '$id_t'");
            $dt = mysqli_fetch_assoc($q_data);

            // Perhitungan live simulasi denda jam untuk Card 1 sebelum data di-save
            $waktu_sekarang = date('Y-m-d H:i:s');
            $selisih_detik  = strtotime($waktu_sekarang) - strtotime($dt['tanggal_kembali']);
            $jam_terlambat  = max(0, ceil($selisih_detik / 3600));
            $denda_waktu_simulasi = $jam_terlambat * TARIF_DENDA_PER_JAM;
          ?>
            <div class="mb-6">
              <a href="pengembalian.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200">
                ← Kembali ke Daftar Antrean
              </a>
            </div>

            <!-- PERBAIKAN: Form Utama sekarang mendukung kiriman multipart file upload -->
            <form action="pengembalian.php" method="POST" enctype="multipart/form-data" class="space-y-6 mb-12">
              <input type="hidden" name="id_transaksi" value="<?php echo $dt['id_transaksi']; ?>">

              <!-- CARD 1: DETAIL TRANSAKSI -->
              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Detail Transaksi</h3>

                <!-- Container Flex dengan lebar kolom yang dipaksa rata -->
                <div style="display: flex; flex-wrap: wrap; gap: 16px; width: 100%;" class="text-xs">

                  <!-- Kolom 1 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">ID Transaksi</p>
                      <p class="font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo $dt['id_transaksi']; ?></p>
                    </div>
                    <div class="mt-auto">
                      <p class="text-gray-400 font-medium uppercase pt-2 border-t border-gray-50 dark:border-gray-700">Durasi Sewa</p>
                      <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1"><?php echo $dt['durasi_sewa']; ?> Hari</p>
                    </div>
                  </div>

                  <!-- Kolom 2 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Pelanggan</p>
                      <p class="font-bold text-gray-900 dark:text-white mt-1"><?php echo htmlspecialchars($dt['nama_pelanggan']); ?></p>
                    </div>
                    <div class="mt-auto">
                      <p class="text-red-600 font-bold uppercase pt-2 border-t border-gray-50 dark:border-gray-700">Keterlambatan</p>
                      <p class="font-bold text-red-600 mt-1"><?php echo $jam_terlambat; ?> Jam</p>
                    </div>
                  </div>

                  <!-- Kolom 3 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Mobil</p>
                      <p class="font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo htmlspecialchars($dt['merk'] . ' ' . $dt['tipe']); ?></p>
                      <p class="font-mono font-bold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($dt['nomor_polisi']); ?></p>
                    </div>
                    <div class="mt-auto">
                      <p class="text-red-600 font-bold uppercase pt-2 border-t border-gray-50 dark:border-gray-700">Tarif Keterlambatan</p>
                      <p class="font-bold text-red-600 mt-1">Rp <?php echo number_format(TARIF_DENDA_PER_JAM, 0, ',', '.'); ?>/Jam</p>
                    </div>
                  </div>

                  <!-- Kolom 4 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: flex-start; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Sewa</p>
                      <p class="font-semibold text-gray-700 dark:text-gray-300 mt-1"><?php echo date('d M Y', strtotime($dt['tanggal_sewa'])); ?></p>
                    </div>
                  </div>

                  <!-- Kolom 5 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: space-between; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Kembali (Seharusnya)</p>
                      <p class="font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo date('d M Y H:i', strtotime($dt['tanggal_kembali'])); ?></p>
                    </div>
                    <div class="mt-auto">
                      <p class="text-red-600 font-bold uppercase pt-2 border-t border-gray-50 dark:border-gray-700">Total Denda</p>
                      <p class="font-extrabold text-red-600 text-sm mt-1">Rp <?php echo number_format($denda_waktu_simulasi, 0, ',', '.'); ?></p>
                    </div>
                  </div>

                  <!-- Kolom 6 -->
                  <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; justify-content: flex-start; min-height: 85px;">
                    <div>
                      <p class="text-gray-400 font-medium uppercase">Tgl Pengembalian Unit</p>
                      <p class="font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo date('d M Y H:i', strtotime($waktu_sekarang)); ?></p>
                    </div>
                  </div>

                </div>
              </div>

              <!-- CARD 2: FORM PENGECEKAN AKHIR -->
              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Form Pengecekan Akhir</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal Cek</span>
                      <input type="datetime-local" name="tanggal_kembali_aktual" value="<?php echo date('Y-m-d\TH:i'); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Mobil</span>
                      <select name="kondisi_mobil" class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                        <option value="baik">Mulus / Baik</option>
                        <option value="rusak">Lecet / Rusak</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Mesin</span>
                      <select name="kondisi_mesin" class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                        <option value="baik">Baik</option>
                        <option value="bermasalah">Bermasalah / Perlu Service</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Kondisi Mesin (Bensin / Indikator BBM)</span>
                      <select name="kondisi_bensin" class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                        <option value="full">Full</option>
                        <option value="half">Half</option>
                      </select>
                    </label>
                  </div>

                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">KM Mobil</span>
                      <div class="relative mt-1 rounded-md shadow-sm">
                        <input type="number" name="km_mobil" placeholder="Contoh: 12950" class="block w-full text-sm dark:bg-gray-700 form-input pr-12 focus:border-purple-400" />
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
                        <p class="text-[11px] text-gray-400 mt-1.5 italic">💡 Kamu bisa menyorot atau memilih 2 sampai 4 foto lecet sekaligus sambil menekan tombol Ctrl/Shift di keyboard.</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- PERBAIKAN: Tombol Save di Card 2 diubah type="submit" agar sinkron memicu aksi simpan ke database -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t dark:border-gray-700">
                  <a href="pengembalian.php" class="px-5 py-2 text-sm font-bold text-white bg-red-700 rounded-lg hover:bg-red-800 transition-colors">Cancel</a>
                  <button type="submit" name="proses_kembali" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Save</button>
                </div>
              </div>

              <!-- CARD 3: FORM DENDA KERUSAKAN -->
              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Form Denda Kerusakan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Jenis Kerusakan</span>
                      <select name="jenis_kerusakan" class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                        <option value="none">Tidak Ada Kerusakan (Rp 0)</option>
                        <option value="lecet">Lecet</option>
                        <option value="penyok">Penyok / Rusak Berat</option>
                      </select>
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Letak Kerusakan</span>
                      <input type="text" name="letak_kerusakan" placeholder="Contoh: Pintu Kanan Belakang" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>
                  </div>

                  <div class="space-y-4">
                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Keterangan</span>
                      <input type="text" name="keterangan_kerusakan" placeholder="Contoh: Terdapat lecet sepanjang 10 cm" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>

                    <label class="block text-sm">
                      <span class="text-gray-700 dark:text-gray-400 font-medium">Biaya (Rp)</span>
                      <input type="number" name="biaya_kerusakan" min="0" value="0" placeholder="Contoh: 500000" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                    </label>
                  </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t dark:border-gray-700">
                  <a href="pengembalian.php" class="px-5 py-2 text-sm font-bold text-white bg-red-700 rounded-lg hover:bg-red-800 transition-colors">Cancel</a>
                  <button type="submit" name="proses_kembali" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Save</button>
                </div>
              </div>

            </form>
          <?php
          } else {
          ?>
            <!-- ────────────────────────────────────────────────────────────── -->
            <!-- INTERFACE 1: TABLE VIEW (DAFTAR ANTRIAN MOBIL DI JALAN)        -->
            <!-- ────────────────────────────────────────────────────────────── -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Daftar Mobil Sedang Disewa (Belum Pulang)</h3>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                  <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3">ID Transaksi</th>
                      <th class="px-4 py-3">Pelanggan</th>
                      <th class="px-4 py-3">Mobil</th>
                      <th class="px-4 py-3">Tgl Jatuh Tempo</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                    <?php
                    $q_load = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_kembali 
                                                   FROM transaksi_rental t
                                                   JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                   JOIN mobil m ON t.id_mobil = m.id_mobil
                                                   WHERE t.status_sewa = 'berjalan' ORDER BY t.tanggal_kembali ASC");

                    if (mysqli_num_rows($q_load) > 0) {
                      while ($row = mysqli_fetch_assoc($q_load)) {
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                          <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $row['id_transaksi']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($row['merk'] . " " . $row['tipe'] . " [" . $row['nomor_polisi'] . "]"); ?></td>
                          <td class="px-4 py-3 text-red-600 font-medium"><?php echo date('d-m-Y H:i', strtotime($row['tanggal_kembali'])); ?></td>
                          <td class="px-4 py-3">
                            <a href="pengembalian.php?action=proses&id=<?php echo $row['id_transaksi']; ?>" class="px-3 py-1 text-xs font-bold text-white bg-blue-600 rounded-md hover:bg-blue-700">
                              Proses Pulang
                            </a>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Semua mobil ada di dalam garasi. Belum ada unit yang sedang disewa keluar.</td></tr>';
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