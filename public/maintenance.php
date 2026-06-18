<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI HAK AKSES
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── 1. LOGIKA TAMBAH MANUAL MAINTENANCE BARU ───
if (isset($_POST['tambah_manual_maintenance'])) {
  $id_mobil_manual = mysqli_real_escape_string($conn, $_POST['id_mobil_manual']);
  $keterangan_awal = mysqli_real_escape_string($conn, $_POST['keterangan_awal']);
  $tgl_masuk       = date('Y-m-d');
  $tgl_estimasi    = mysqli_real_escape_string($conn, $_POST['tgl_estimasi']);
  
  mysqli_begin_transaction($conn);

  // Buat ID Maintenance Baru
  $q_id_m = mysqli_query($conn, "SELECT id_maintenance FROM maintenance ORDER BY id_maintenance DESC LIMIT 1");
  $row_id_m = mysqli_fetch_assoc($q_id_m);
  $id_maint_baru = $row_id_m ? "MNT" . sprintf("%03d", substr($row_id_m['id_maintenance'], 3) + 1) : "MNT001";

  // Masukkan ke tabel maintenance dengan status 'proses'
  $ins_maint = mysqli_query($conn, "INSERT INTO maintenance (id_maintenance, id_mobil, tgl_service, tgl_estimasi, biaya_service, status_maintenance, keterangan) 
                                    VALUES ('$id_maint_baru', '$id_mobil_manual', '$tgl_masuk', '$tgl_estimasi', 0, 'proses', 'Service Manual: $keterangan_awal')");

  // Ubah status mobil di pool menjadi 'Maintenance' (Sesuai ENUM baru)
  $upd_mobil = mysqli_query($conn, "UPDATE mobil SET status_mobil = 'Maintenance' WHERE id_mobil = '$id_mobil_manual'");

  if ($ins_maint && $upd_mobil) {
    mysqli_commit($conn);
    header("Location: maintenance.php?status=added");
    exit;
  } else {
    mysqli_rollback($conn);
    header("Location: maintenance.php?status=failed");
    exit;
  }
}

// ─── 2. LOGIKA UPDATE / EDIT SAAT MASIH PROSES ───
if (isset($_POST['update_proses_maintenance'])) {
  $id_maint_upd = mysqli_real_escape_string($conn, $_POST['id_maintenance_edit']);
  $tgl_estimasi_upd = mysqli_real_escape_string($conn, $_POST['tgl_estimasi_edit']);
  $keterangan_upd = mysqli_real_escape_string($conn, $_POST['keterangan_edit']);

  $q_update = mysqli_query($conn, "UPDATE maintenance SET tgl_estimasi = '$tgl_estimasi_upd', keterangan = '$keterangan_upd' WHERE id_maintenance = '$id_maint_upd'");
  if ($q_update) {
    header("Location: maintenance.php?status=updated");
  } else {
    header("Location: maintenance.php?status=failed");
  }
  exit;
}

// ─── 3. LOGIKA SELESAI MAINTENANCE BENGKEL (FINISH) ───
if (isset($_POST['proses_selesai_maintenance'])) {
  $id_mobil_fix   = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_maint_fix   = mysqli_real_escape_string($conn, $_POST['id_maintenance']);
  $biaya_service  = (float)$_POST['biaya_service'];
  $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);
  $tgl_selesai    = date('Y-m-d');

  mysqli_begin_transaction($conn);

  // 1. Kembalikan status armada mobil menjadi Tersedia (Sesuai ENUM baru)
  $u_mobil = mysqli_query($conn, "UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = '$id_mobil_fix'");

  // 2. Perbarui data di tabel maintenance: isi biaya, tanggal fix, keterangan akhir, dan ubah ke 'selesai'
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

// ─── FILTER STATUS (PROSES VS SELESAI) ───
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'proses';
$sql_filter = ($filter_status === 'selesai') ? "WHERE mn.status_maintenance = 'selesai'" : "WHERE mn.status_maintenance = 'proses'";
?>

<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="Object.assign(data(), { isAddOpen: false })" lang="en">

<head>
  <?php include 'components/head.php'; ?>
  <style>
    [x-cloak] { display: none !important; }
  </style>
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
              Bengkel & Maintenance Armada
            </h2>
            <button @click="isAddOpen = true" class="px-4 py-2 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-sm transition-colors flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              Input Servis Manual
            </button>
          </div>

          <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
              <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold shadow-sm">✓ Unit mobil selesai diperbaiki! Log finansial bengkel terekam dan status armada aktif kembali.</div>
            <?php elseif ($_GET['status'] == 'added'): ?>
              <div class="mb-4 p-3 bg-blue-500 text-white rounded-lg text-sm font-semibold shadow-sm">✓ Berhasil mendaftarkan mobil ke antrean bengkel secara manual!</div>
            <?php elseif ($_GET['status'] == 'updated'): ?>
              <div class="mb-4 p-3 bg-amber-500 text-white rounded-lg text-sm font-semibold shadow-sm">✓ Informasi estimasi & log montir berhasil diperbarui!</div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">
              Log Data Bengkel & Perbaikan
            </h3>
            
            <form action="maintenance.php" method="GET" class="flex items-center gap-2">
              <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Sortir Antrean:</label>
              <select name="filter_status" onchange="this.form.submit()" class="text-xs p-1.5 rounded border border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-purple-400 focus:ring-purple-300 font-bold">
                <option value="proses" <?php echo ($filter_status == 'proses') ? 'selected' : ''; ?>>🔧 Sedang Dalam Bengkel (Proses)</option>
                <option value="selesai" <?php echo ($filter_status == 'selesai') ? 'selected' : ''; ?>>✅ Selesai Dikerjakan (History)</option>
              </select>
            </form>
          </div>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-12">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-3">ID Maint / Plat</th>
                    <th class="px-4 py-3">Armada Mobil</th>
                    <th class="px-4 py-3">Jadwal Tanggal</th> <th class="px-4 py-3">Status / Biaya</th>
                    <th class="px-4 py-3 text-center">Aksi / Form Kendali</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                  <?php
                  $q_data = mysqli_query($conn, "SELECT mn.*, m.merk, m.tipe, m.nomor_polisi, m.gambar 
                                                 FROM maintenance mn 
                                                 JOIN mobil m ON mn.id_mobil = m.id_mobil 
                                                 $sql_filter 
                                                 ORDER BY mn.id_maintenance DESC");

                  if (mysqli_num_rows($q_data) > 0) {
                    while ($row = mysqli_fetch_assoc($q_data)) {
                  ?>
                      <?php if ($filter_status === 'proses'): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors" x-data="{ isEditing: false }">
                          <td class="px-4 py-3">
                            <p class="font-bold text-purple-600 dark:text-purple-400"><?php echo $row['id_maintenance']; ?></p>
                            <p class="font-mono text-[10px] text-gray-500 mt-1 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded inline-block border dark:border-gray-600"><?php echo $row['nomor_polisi']; ?></p>
                          </td>
                          <td class="px-4 py-3 flex items-center gap-3">
                            <div class="w-12 h-8 bg-gray-100 rounded overflow-hidden flex items-center justify-center border dark:border-gray-600 shrink-0">
                              <img src="assets/img/mobil/<?php echo $row['gambar']; ?>" class="max-w-full max-h-full object-contain">
                            </div>
                            <p class="font-bold text-gray-900 dark:text-white text-xs"><?php echo htmlspecialchars($row['merk'] . " " . $row['tipe']); ?></p>
                          </td>
                          <td class="px-4 py-3">
                            <div class="mb-1.5">
                              <p class="text-[9px] font-bold text-gray-800 uppercase">Tgl Masuk Bengkel:</p>
                              <p class="font-bold text-gray-800 dark:text-gray-200 text-[11px]"><?php echo date('d M Y', strtotime($row['tgl_service'])); ?></p>
                            </div>
                            <div>
                              <p class="text-[9px] font-bold text-amber-500 uppercase">Estimasi Beres:</p>
                              <p class="font-bold text-amber-600 text-[11px]"><?php echo empty($row['tgl_estimasi']) ? 'Belum Diatur' : date('d M Y', strtotime($row['tgl_estimasi'])); ?></p>
                            </div>
                          </td>
                          <td class="px-4 py-3">
                            <span class="px-2 py-1 text-[10px] font-bold text-amber-800 bg-amber-100 rounded-md uppercase">Sedang Proses</span>
                          </td>
                          <td class="px-4 py-3">
                            
                            <div x-show="!isEditing" class="flex flex-col gap-2 min-w-[200px]">
                              <p class="text-xs text-gray-500 italic border-l-2 border-purple-400 pl-2 leading-tight">"<?php echo htmlspecialchars($row['keterangan']); ?>"</p>
                              <div class="flex items-center gap-2 mt-1">
                                <button type="button" @click.prevent.stop="isEditing = true" class="px-3 py-1 text-[10px] font-bold text-blue-600 border border-blue-600 rounded hover:bg-blue-600 hover:text-white transition-colors">Ubah Est / Log</button>
                                
                                <form action="maintenance.php" method="POST" class="flex m-0 gap-1" onsubmit="return confirm('Apakah unit ini benar-benar selesai diperbaiki dan nota biaya sudah final?')">
                                  <input type="hidden" name="id_mobil" value="<?php echo $row['id_mobil']; ?>">
                                  <input type="hidden" name="id_maintenance" value="<?php echo $row['id_maintenance']; ?>">
                                  <input type="hidden" name="keterangan" value="<?php echo htmlspecialchars($row['keterangan']); ?> - (FINAL)">
                                  
                                  <input type="number" name="biaya_service" required min="0" placeholder="Rp Biaya..." class="text-[10px] p-1 rounded border border-green-300 dark:bg-gray-700 w-20 focus:border-green-500">
                                  <button type="submit" name="proses_selesai_maintenance" class="px-3 py-1 text-[10px] font-bold text-white bg-green-600 rounded hover:bg-green-700 transition-colors whitespace-nowrap">✔ Finish</button>
                                </form>
                              </div>
                            </div>

                            <div x-show="isEditing" x-cloak class="min-w-[250px] p-2 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg">
                              <form action="maintenance.php" method="POST" class="flex flex-col gap-2 text-xs">
                                <input type="hidden" name="id_maintenance_edit" value="<?php echo $row['id_maintenance']; ?>">
                                <div class="flex items-center justify-between gap-2">
                                  <label class="font-semibold text-gray-700 dark:text-gray-300">Ubah Estimasi:</label>
                                  <input type="date" name="tgl_estimasi_edit" value="<?php echo $row['tgl_estimasi']; ?>" required class="p-1 border rounded focus:border-blue-400 dark:bg-gray-600 dark:border-gray-500">
                                </div>
                                <input type="text" name="keterangan_edit" value="<?php echo htmlspecialchars($row['keterangan']); ?>" required placeholder="Log montir terbaru..." class="p-1.5 border rounded w-full focus:border-blue-400 dark:bg-gray-600 dark:border-gray-500">
                                <div class="flex gap-2 justify-end mt-1">
                                  <button type="button" @click.prevent.stop="isEditing = false" class="px-2 py-1 text-[10px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Batal</button>
                                  <button type="submit" name="update_proses_maintenance" class="px-3 py-1 text-[10px] font-bold text-white bg-blue-600 rounded hover:bg-blue-700">Simpan Perubahan</button>
                                </div>
                              </form>
                            </div>

                          </td>
                        </tr>

                      <?php else: ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors opacity-80">
                          <td class="px-4 py-3">
                            <p class="font-bold text-gray-600 dark:text-gray-400"><?php echo $row['id_maintenance']; ?></p>
                            <p class="font-mono text-[10px] text-gray-500 mt-1"><?php echo $row['nomor_polisi']; ?></p>
                          </td>
                          <td class="px-4 py-3 font-bold text-gray-700 dark:text-gray-300 text-xs">
                            <?php echo htmlspecialchars($row['merk'] . " " . $row['tipe']); ?>
                          </td>
                          <td class="px-4 py-3">
                            <p class="text-[10px] font-bold text-gray-400 uppercase mb-0.5">Tgl Selesai Perbaikan:</p>
                            <p class="font-bold text-green-600 text-xs"><?php echo date('d M Y', strtotime($row['tgl_service'])); ?></p>
                          </td>
                          <td class="px-4 py-3">
                            <p class="text-lg font-black text-red-600">Rp <?php echo number_format($row['biaya_service'], 0, ',', '.'); ?></p>
                          </td>
                          <td class="px-4 py-3 text-center">
                            <a href="detail_maintenance.php?id=<?php echo $row['id_maintenance']; ?>" class="inline-flex items-center px-4 py-1.5 text-xs font-bold text-gray-600 bg-gray-200 border border-gray-300 rounded hover:bg-gray-300 transition-colors shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                              👁 Lihat Berita Acara
                            </a>
                          </td>
                        </tr>
                      <?php endif; ?>
                  <?php
                    }
                  } else {
                    $pesan_kosong = ($filter_status === 'proses') ? "✨ Garasi bersih! Tidak ada unit yang sedang butuh perbaikan." : "Belum ada riwayat arsip perbaikan yang telah selesai dilakukan.";
                    echo "<tr><td colspan='5' class='px-4 py-8 text-center text-gray-400 font-medium italic'>$pesan_kosong</td></tr>";
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

  <div x-show="isAddOpen" x-cloak x-transition
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    
    <div @click.away="isAddOpen = false" class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl border border-gray-100 dark:border-gray-700 cursor-default" role="dialog">
      
      <header class="flex justify-between items-center border-b pb-3 mb-5 dark:border-gray-700">
        <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-sm">Masukan Unit ke Bengkel (Manual)</h3>
        <button type="button" @click.stop="isAddOpen = false" class="text-gray-400 hover:text-red-500 font-black text-lg transition-colors">✕</button>
      </header>

      <form action="maintenance.php" method="POST" class="space-y-4">
        
        <label class="block text-sm">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Pilih Unit Armada (Hanya Yang Tersedia)</span>
          <select name="id_mobil_manual" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400 rounded-lg border-gray-300 dark:border-gray-600 dark:text-gray-200 p-2.5">
            <option value="">-- Pilih Mobil --</option>
            <?php
            // FIX: Query sesuai ENUM (Huruf T besar)
            $mob_ready = mysqli_query($conn, "SELECT id_mobil, merk, tipe, nomor_polisi FROM mobil WHERE status_mobil = 'Tersedia'");
            while ($m = mysqli_fetch_assoc($mob_ready)) {
              echo "<option value='" . $m['id_mobil'] . "'>" . $m['merk'] . " " . $m['tipe'] . " [" . $m['nomor_polisi'] . "]</option>";
            }
            ?>
          </select>
        </label>

        <label class="block text-sm">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Tanggal Estimasi Selesai</span>
          <input type="date" name="tgl_estimasi" min="<?php echo date('Y-m-d'); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400 rounded-lg border-gray-300 dark:border-gray-600 dark:text-gray-200 p-2.5" />
        </label>

        <label class="block text-sm">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Keterangan / Keluhan Awal</span>
          <textarea name="keterangan_awal" rows="3" required placeholder="Contoh: Service rutin 10.000 KM ganti oli dan kampas rem..." class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400 rounded-lg border-gray-300 dark:border-gray-600 dark:text-gray-200 p-2.5"></textarea>
        </label>

        <div class="flex items-center justify-end space-x-3 pt-5 mt-2 border-t dark:border-gray-700">
          <button type="button" @click.stop="isAddOpen = false" class="px-5 py-2 text-sm font-bold text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">Batal</button>
          <button type="submit" name="tambah_manual_maintenance" class="px-5 py-2 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-md">Simpan ke Bengkel</button>
        </div>

      </form>
    </div>
  </div>

</body>
</html>