<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── LOGIKA BACKEND 1: HAPUS ARMADA MOBIL ───
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_SESSION['role'] !== 'staff') {
  $id_hapus = mysqli_real_escape_string($conn, $_GET['id']);

  $q_gbr = mysqli_query($conn, "SELECT gambar FROM mobil WHERE id_mobil = '$id_hapus'");
  $r_gbr = mysqli_fetch_assoc($q_gbr);
  if ($r_gbr && $r_gbr['gambar'] != 'default.png') {
    @unlink('assets/img/mobil/' . $r_gbr['gambar']);
  }

  $del = mysqli_query($conn, "DELETE FROM mobil WHERE id_mobil = '$id_hapus'");
  if ($del) {
    header("Location: mobil.php?status=del-success");
  } else {
    header("Location: mobil.php?status=failed");
  }
  exit;
}

// ─── LOGIKA BACKEND 2: TAMBAH MOBIL BARU ───
if (isset($_POST['save_mobil'])) {
  $id_mobil             = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_kategori          = mysqli_real_escape_string($conn, $_POST['id_kategori']);
  $merk                 = mysqli_real_escape_string($conn, $_POST['merk']);
  $tipe                 = mysqli_real_escape_string($conn, $_POST['tipe']);
  $nomor_polisi         = mysqli_real_escape_string($conn, $_POST['nomor_polisi']);
  $tahun                = mysqli_real_escape_string($conn, $_POST['tahun']);
  $pajak                = mysqli_real_escape_string($conn, $_POST['pajak']);
  $warna                = mysqli_real_escape_string($conn, $_POST['warna']);
  $harga_sewa_per_hari  = mysqli_real_escape_string($conn, $_POST['harga_sewa_per_hari']);
  $kapasitas            = mysqli_real_escape_string($conn, $_POST['kapasitas']);
  $status_mobil         = mysqli_real_escape_string($conn, $_POST['status_mobil']);

  $nama_gambar = "default.png";
  if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
    $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
    $x = explode('.', $_FILES['gambar']['name']);
    $ekstensi = strtolower(end($x));
    $file_tmp = $_FILES['gambar']['tmp_name'];
    $nama_gambar = "MOBIL-" . urlencode($merk) . "-" . time() . "." . $ekstensi;

    if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
      move_uploaded_file($file_tmp, 'assets/img/mobil/' . $nama_gambar);
    }
  }

  $query = "INSERT INTO mobil VALUES ('$id_mobil', '$id_kategori', '$merk', '$tipe', '$nomor_polisi', '$tahun', '$pajak', '$warna', '$harga_sewa_per_hari', '$kapasitas', '$status_mobil', '$nama_gambar')";
  if (mysqli_query($conn, $query)) {
    header("Location: mobil.php?status=success");
    exit;
  } else {
    header("Location: mobil.php?status=failed");
    exit;
  }
}

// ─── LOGIKA BACKEND 3: UPDATE DATA MOBIL ───
if (isset($_POST['update_mobil'])) {
  $id_mobil             = mysqli_real_escape_string($conn, $_POST['id_mobil']);
  $id_kategori          = mysqli_real_escape_string($conn, $_POST['id_kategori']);
  $merk                 = mysqli_real_escape_string($conn, $_POST['merk']);
  $tipe                 = mysqli_real_escape_string($conn, $_POST['tipe']);
  $nomor_polisi         = mysqli_real_escape_string($conn, $_POST['nomor_polisi']);
  $tahun                = mysqli_real_escape_string($conn, $_POST['tahun']);
  $pajak                = mysqli_real_escape_string($conn, $_POST['pajak']);
  $warna                = mysqli_real_escape_string($conn, $_POST['warna']);
  $harga_sewa_per_hari  = mysqli_real_escape_string($conn, $_POST['harga_sewa_per_hari']);
  $kapasitas            = mysqli_real_escape_string($conn, $_POST['kapasitas']);
  $status_mobil         = mysqli_real_escape_string($conn, $_POST['status_mobil']);

  if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
    $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
    $x = explode('.', $_FILES['gambar']['name']);
    $ekstensi = strtolower(end($x));
    $file_tmp = $_FILES['gambar']['tmp_name'];
    $nama_gambar = "MOBIL-" . urlencode($merk) . "-" . time() . "." . $ekstensi;

    if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
      $q_gbr = mysqli_query($conn, "SELECT gambar FROM mobil WHERE id_mobil = '$id_mobil'");
      $r_gbr = mysqli_fetch_assoc($q_gbr);
      if ($r_gbr && $r_gbr['gambar'] != 'default.png') {
        @unlink('assets/img/mobil/' . $r_gbr['gambar']);
      }
      move_uploaded_file($file_tmp, 'assets/img/mobil/' . $nama_gambar);
      mysqli_query($conn, "UPDATE mobil SET gambar = '$nama_gambar' WHERE id_mobil = '$id_mobil'");
    }
  }

  $q_up = "UPDATE mobil SET id_kategori='$id_kategori', merk='$merk', tipe='$tipe', nomor_polisi='$nomor_polisi', tahun='$tahun', pajak='$pajak', warna='$warna', harga_sewa_per_hari='$harga_sewa_per_hari', kapasitas='$kapasitas', status_mobil='$status_mobil' WHERE id_mobil='$id_mobil'";
  if (mysqli_query($conn, $q_up)) {
    header("Location: mobil.php?status=up-success");
  } else {
    header("Location: mobil.php?status=failed");
  }
  exit;
}

// ─── LOGIKA SINKRONISASI MULTI-FILTER TRIPLE ───
$where_clauses = [];
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_kategori = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($conn, $_GET['filter_kategori']) : "";
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($conn, $_GET['filter_status']) : "";
$filter_tahun = isset($_GET['filter_tahun']) ? mysqli_real_escape_string($conn, $_GET['filter_tahun']) : "";

if (!empty($search_keyword)) {
  $where_clauses[] = "(m.merk LIKE '%$search_keyword%' OR m.tipe LIKE '%$search_keyword%' OR m.nomor_polisi LIKE '%$search_keyword%')";
}
if (!empty($filter_kategori)) {
  $where_clauses[] = "m.id_kategori = '$filter_kategori'";
}
if (!empty($filter_status)) {
  $where_clauses[] = "m.status_mobil = '$filter_status'";
}
if (!empty($filter_tahun)) {
  $where_clauses[] = "m.tahun = '$filter_tahun'";
}

$string_where = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// ─── LOGIKA ENGINE PAGINATION (BATAS 12 UNIT CARD) ───
$batas_data = 12;
$halaman_aktif = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
$posisi_awal = ($halaman_aktif - 1) * $batas_data;

$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil m $string_where");
$r_total = mysqli_fetch_assoc($q_total);
$total_mobil = $r_total['total'] ?? 0;
$total_halaman = ceil($total_mobil / $batas_data);
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="Object.assign(data(), { isActionMenuOpen: false, isDetailOpen: false, isEditOpen: false, targetMobil: {} })" lang="en">

<head>
  <?php include 'components/head.php'; ?>
  <style>
    [x-cloak] {
      display: none !important;
    }

    .figma-grid {
      display: grid !important;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
      gap: 24px !important;
      width: 100% !important;
      margin-bottom: 32px !important;
    }

    .figma-card {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      border-radius: 16px !important;
      padding: 24px !important;
      box-sizing: border-box !important;
      position: relative !important;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .figma-img-box {
      width: 100% !important;
      height: 140px !important;
      border-radius: 12px !important;
      padding: 8px !important;
      margin-bottom: 16px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      overflow: hidden !important;
    }

    .figma-img {
      max-width: 100% !important;
      max-height: 100% !important;
      object-fit: contain !important;
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
            Data Mobil
          </h2>

          <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
              <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-xs font-semibold">✓ Aset mobil baru berhasil didaftarkan ke sistem!</div>
            <?php elseif ($_GET['status'] == 'up-success'): ?>
              <div class="mb-4 p-3 bg-blue-500 text-white rounded-lg text-xs font-semibold">✓ Spesifikasi unit armada berhasil diperbarui!</div>
            <?php elseif ($_GET['status'] == 'del-success'): ?>
              <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-xs font-semibold">✓ Unit mobil berhasil dihapus permanen dari sistem.</div>
            <?php elseif ($_GET['status'] == 'failed'): ?>
              <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-xs font-semibold">❌ Terjadi kesalahan query internal database.</div>
            <?php endif; ?>
          <?php endif; ?>

          <?php
          // =========================================================================
          // VIEW A: FORM TAMBAH DATA MOBIL BARU
          // =========================================================================
          if (isset($_GET['action']) && $_GET['action'] == 'add') {
            $q_id   = mysqli_query($conn, "SELECT id_mobil FROM mobil ORDER BY id_mobil DESC LIMIT 1");
            $row_id = mysqli_fetch_assoc($q_id);
            $form_id = $row_id ? "MOB" . sprintf("%03d", substr($row_id['id_mobil'], 3) + 1) : "MOB001";
          ?>
            <div class="mb-4">
              <a href="mobil.php" class="inline-flex items-center px-3 py-2 text-xs font-bold text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-100">
                ← Kembali ke Galeri Armada
              </a>
            </div>

            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 dark:bg-gray-800 mb-8">
              <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wide border-b pb-2 dark:border-gray-700">
                TAMBAH DATA ARMADA MOBIL
              </h4>

              <form action="mobil.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-medium text-gray-700 dark:text-gray-300">
                <input type="hidden" name="id_mobil" value="<?php echo $form_id; ?>">

                <label class="block">
                  <span>Kategori Kelas</span>
                  <select name="id_kategori" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 dark:text-gray-100 focus:border-purple-400 focus:outline-none">
                    <?php
                    $kat = mysqli_query($conn, "SELECT * FROM kategori");
                    while ($k = mysqli_fetch_assoc($kat)) {
                      echo "<option value='" . $k['id_kategori'] . "'>" . $k['jenis_kategori'] . "</option>";
                    }
                    ?>
                  </select>
                </label>

                <label class="block">
                  <span>Merk Mobil</span>
                  <input type="text" name="merk" required placeholder="Contoh: Toyota" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Tipe Unit</span>
                  <input type="text" name="tipe" required placeholder="Contoh: Fortuner" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Nomor Polisi (Plat)</span>
                  <input type="text" name="nomor_polisi" required placeholder="Contoh: D 6402 UD" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Tahun Perakitan</span>
                  <input type="number" name="tahun" required placeholder="Contoh: 2026" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Masa Berlaku Pajak</span>
                  <input type="date" name="pajak" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Warna Kendaraan</span>
                  <input type="text" name="warna" required placeholder="Contoh: Putih" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Harga Sewa / Hari (Rp)</span>
                  <input type="number" name="harga_sewa_per_hari" required placeholder="Contoh: 500000" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Kapasitas Kursi</span>
                  <input type="number" name="kapasitas" required placeholder="Contoh: 7" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
                </label>

                <label class="block">
                  <span>Status Awal</span>
                  <select name="status_mobil" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none">
                    <option value="tersedia">Tersedia</option>
                    <option value="maintenance">Maintenance</option>
                  </select>
                </label>

                <div class="block md:col-span-2">
                  <span>Foto Unit Kendaraan</span>
                  <input type="file" name="gambar" accept="image/*" required class="block w-full mt-1" />
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t dark:border-gray-700 md:col-span-2">
                  <a href="mobil.php" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</a>
                  <button type="submit" name="save_mobil" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow">Save</button>
                </div>
              </form>
            </div>

          <?php
            // =========================================================================
            // VIEW B: ETALASE UTAMA + BAR MULTI-FILTER TRIPLE & TOMBOL ADD SEBARIS
            // =========================================================================
          } else {
          ?>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">

              <form action="mobil.php" method="GET" class="flex flex-wrap items-center gap-2 m-0 p-0 w-full md:w-auto">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Cari unit / plat..." class="text-xs p-2.5 w-36 rounded-lg border bg-gray-50 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:border-purple-400 border-gray-200 dark:border-gray-600" />

                <select name="filter_kategori" class="text-xs p-2.5 rounded-lg border bg-gray-50 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:border-purple-400 border-gray-200 dark:border-gray-600">
                  <option value="">-- Kategori --</option>
                  <?php
                  $f_kat = mysqli_query($conn, "SELECT * FROM kategori");
                  while ($fk = mysqli_fetch_assoc($f_kat)) {
                    $sel = ($filter_kategori == $fk['id_kategori']) ? "selected" : "";
                    echo "<option value='" . $fk['id_kategori'] . "' $sel>" . $fk['jenis_kategori'] . "</option>";
                  }
                  ?>
                </select>

                <select name="filter_status" class="text-xs p-2.5 rounded-lg border bg-gray-50 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:border-purple-400 border-gray-200 dark:border-gray-600">
                  <option value="">-- Status --</option>
                  <option value="tersedia" <?php if ($filter_status == 'tersedia') echo 'selected'; ?>>Tersedia</option>
                  <option value="disewa" <?php if ($filter_status == 'disewa') echo 'selected'; ?>>Disewa</option>
                  <option value="maintenance" <?php if ($filter_status == 'maintenance') echo 'selected'; ?>>Maintenance</option>
                </select>

                <select name="filter_tahun" class="text-xs p-2.5 rounded-lg border bg-gray-50 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:border-purple-400 border-gray-200 dark:border-gray-600">
                  <option value="">-- Tahun --</option>
                  <?php
                  $f_thn = mysqli_query($conn, "SELECT DISTINCT tahun FROM mobil ORDER BY tahun DESC");
                  while ($ft = mysqli_fetch_assoc($f_thn)) {
                    $sel_t = ($filter_tahun == $ft['tahun']) ? "selected" : "";
                    echo "<option value='" . $ft['tahun'] . "' $sel_t>" . $ft['tahun'] . "</option>";
                  }
                  ?>
                </select>

                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition shadow-sm">Filter</button>
                <?php if (!empty($search_keyword) || !empty($filter_kategori) || !empty($filter_status) || !empty($filter_tahun)): ?>
                  <a href="mobil.php" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 rounded-lg">Clear</a>
                <?php endif; ?>
              </form>

              <?php if ($_SESSION['role'] !== 'staff'): ?>
                <a href="mobil.php?action=add" class="w-32 text-center px-4 py-2.5 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-sm transition-colors whitespace-nowrap flex items-center justify-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                  </svg>
                  <span>Add Mobil</span>
                </a>
              <?php endif; ?>
            </div>

            <div class="figma-grid">
              <?php
              $cars = mysqli_query($conn, "SELECT m.*, k.jenis_kategori FROM mobil m 
                                            JOIN kategori k ON m.id_kategori = k.id_kategori 
                                            $string_where 
                                            ORDER BY m.id_mobil ASC 
                                            LIMIT $posisi_awal, $batas_data");

              if (mysqli_num_rows($cars) > 0) {
                while ($c = mysqli_fetch_assoc($cars)) {
                  $badge_text  = "Tersedia";
                  $badge_style = "text-green-700 bg-green-100 dark:bg-green-900/40 dark:text-green-400";
                  if ($c['status_mobil'] == 'disewa') {
                    $badge_text = "Disewa";
                    $badge_style = "text-blue-700 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400";
                  }
                  if ($c['status_mobil'] == 'maintenance') {
                    $badge_text = "Maintenance";
                    $badge_style = "text-amber-700 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-400";
                  }

                  $payload_json = json_encode([
                    'id' => $c['id_mobil'],
                    'kat_id' => $c['id_kategori'],
                    'kat_nama' => $c['jenis_kategori'],
                    'merk' => $c['merk'],
                    'tipe' => $c['tipe'],
                    'plat' => $c['nomor_polisi'],
                    'thn' => $c['tahun'],
                    'pajak' => date('d M Y', strtotime($c['pajak'])),
                    'pajak_raw' => $c['pajak'],
                    'warna' => $c['warna'],
                    'harga' => number_format($c['harga_sewa_per_hari'], 0, ',', '.'),
                    'harga_raw' => $c['harga_sewa_per_hari'],
                    'caps' => $c['kapasitas'],
                    'status' => $c['status_mobil'],
                    'status_txt' => $badge_text,
                    'gbr' => $c['gambar']
                  ]);
              ?>
                  <div @click="targetMobil = <?php echo htmlspecialchars($payload_json); ?>; isActionMenuOpen = true"
                    class="figma-card bg-white border border-gray-100 dark:bg-gray-800 dark:border-gray-700 shadow-sm hover:scale-[1.02] hover:shadow-md transition-all duration-150">

                    <div class="figma-img-box bg-gray-50 border border-gray-100 dark:bg-gray-700 dark:border-gray-600">
                      <img src="assets/img/mobil/<?php echo $c['gambar']; ?>" alt="Mobil" class="figma-img" />
                    </div>

                    <div class="text-center w-full mt-1">
                      <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm mb-1 tracking-tight">
                        <?php echo htmlspecialchars($c['merk'] . ' ' . $c['tipe']); ?>
                      </h4>
                      <p class="font-mono text-xs text-purple-600 dark:text-purple-400 font-bold mb-2">
                        <?php echo htmlspecialchars($c['nomor_polisi']); ?>
                      </p>
                      <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mb-3">
                        Harga per hari : <span class="text-gray-700 dark:text-gray-300 font-bold">Rp <?php echo number_format($c['harga_sewa_per_hari'], 0, ',', '.'); ?></span>
                      </p>
                    </div>

                    <div class="w-full flex justify-center pt-2 border-t border-gray-100 dark:border-gray-700 mt-auto">
                      <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded uppercase <?php echo $badge_style; ?>">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full" style="background-color: currentColor;"></span><?php echo $badge_text; ?>
                      </span>
                    </div>
                  </div>
              <?php
                }
              } else {
                echo '<div class="w-full col-span-full p-12 bg-white dark:bg-gray-800 rounded-xl text-center text-gray-500 border border-gray-100 dark:border-gray-700 italic">Armada mobil yang Anda saring tidak tersedia.</div>';
              }
              ?>
            </div>

            <?php if ($total_halaman > 1): ?>
              <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg shadow-sm sm:grid-cols-9 dark:text-gray-400 mb-8">
                <span class="flex items-center sm:col-span-3 text-[11px] normal-case text-gray-400">
                  Menampilkan <?php echo ($total_mobil > 0) ? $posisi_awal + 1 : 0; ?>-<?php echo min($posisi_awal + $batas_data, $total_mobil); ?> dari <?php echo $total_mobil; ?> unit mobil
                </span>
                <span class="sm:col-span-2"></span>
                <span class="flex col-span-4 mt-2 sm:mt-0 sm:justify-end">
                  <nav aria-label="Table navigation">
                    <ul class="inline-flex items-center space-x-1">
                      <li>
                        <?php if ($halaman_aktif > 1): ?>
                          <a href="mobil.php?page=<?php echo $halaman_aktif - 1; ?>&search=<?php echo urlencode($search_keyword); ?>&filter_kategori=<?php echo urlencode($filter_kategori); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_tahun=<?php echo urlencode($filter_tahun); ?>" class="px-3 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
                            </svg></a>
                        <?php else: ?>
                          <button disabled class="px-3 py-1 opacity-30 cursor-not-allowed flex items-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
                            </svg></button>
                        <?php endif; ?>
                      </li>
                      <li class="px-3 py-1 text-gray-400 normal-case text-[11px]">Halaman <b><?php echo $halaman_aktif; ?></b> dari <?php echo $total_halaman; ?></li>
                      <li>
                        <?php if ($halaman_aktif < $total_halaman): ?>
                          <a href="mobil.php?page=<?php echo $halaman_aktif + 1; ?>&search=<?php echo urlencode($search_keyword); ?>&filter_kategori=<?php echo urlencode($filter_kategori); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_tahun=<?php echo urlencode($filter_tahun); ?>" class="px-3 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
                            </svg></a>
                        <?php else: ?>
                          <button disabled class="px-3 py-1 opacity-30 cursor-not-allowed flex items-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
                            </svg></button>
                        <?php endif; ?>
                      </li>
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

  <div x-show="isActionMenuOpen" x-cloak
    style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.5) !important; backdrop-filter: blur(4px) !important; z-index: 9999 !important; display: flex !important; align-items: center !important; justify-content: center !important;">

    <div @click.away="isActionMenuOpen = false"
      class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-2xl border border-gray-100 dark:border-gray-700"
      style="width: 280px !important; position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; cursor: default;">

      <div class="mb-5">
        <span class="px-2.5 py-0.5 rounded-md font-mono text-[10px] font-extrabold bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 uppercase tracking-wider" x-text="targetMobil.plat"></span>
        <h4 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight mt-2" x-text="targetMobil.merk + ' ' + targetMobil.tipe"></h4>
      </div>

      <div class="flex flex-col space-y-2 text-xs font-bold">
        <button @click="isActionMenuOpen = false; isDetailOpen = true"
          class="w-full py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition duration-150 shadow-sm">
          Detail Spesifikasi
        </button>

        <?php if ($_SESSION['role'] !== 'staff'): ?>
          <button @click="isActionMenuOpen = false; isEditOpen = true"
            class="w-full py-2.5 text-amber-700 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:hover:bg-amber-900/60 rounded-xl transition duration-150">
            Edit Data Unit
          </button>

          <a :href="'mobil.php?action=delete&id=' + targetMobil.id"
            onclick="return confirm('Apakah Anda yakin ingin menghapus total unit armada ini dari database?')"
            class="w-full py-2.5 text-center block text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-400 dark:hover:bg-red-900/60 rounded-xl transition duration-150">
            Delete Permanen
          </a>
        <?php else: ?>
          <div class="py-2 bg-gray-50 dark:bg-gray-700/50 text-gray-400 dark:text-gray-500 italic rounded-xl text-[11px] font-medium border border-dashed dark:border-gray-600">
            Akses Modifikasi Dikunci (Staff)
          </div>
        <?php endif; ?>

        <button @click="isActionMenuOpen = false"
          class="w-full py-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-[11px] font-semibold pt-3 transition duration-150">
          ✕ Batalkan & Tutup
        </button>
      </div>

    </div>
  </div>

  <div x-show="isDetailOpen" x-cloak
    style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.4) !important; backdrop-filter: blur(4px) !important; z-index: 9999 !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 16px !important;">

    <div @click.away="isDetailOpen = false" class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl border border-gray-100 dark:border-gray-700 text-xs text-left" role="dialog"
      style="cursor: default; position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important;">

      <header class="flex justify-between items-center border-b pb-3 mb-4 dark:border-gray-700">
        <p class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-xs">Spesifikasi Detail Armada</p>
        <button type="button" @click="isDetailOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold text-sm">✕</button>
      </header>

      <div class="flex flex-col sm:flex-row gap-5 items-stretch">

        <div class="w-full sm:w-2/5 flex flex-col justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600 min-h-[160px]">
          <div class="w-full h-full flex items-center justify-center overflow-hidden">
            <img :src="'assets/img/mobil/' + targetMobil.gbr" class="max-h-32 max-w-full object-contain transform hover:scale-105 transition duration-150">
          </div>
          <div class="pt-2 text-center">
            <span class="inline-block px-3 py-1 text-[9px] font-black uppercase rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 w-full" x-text="targetMobil.status_txt"></span>
          </div>
        </div>

        <div class="w-full sm:w-3/5 flex flex-col justify-between">
          <div class="grid grid-cols-2 gap-x-3 gap-y-2.5">
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">ID / Kategori</b>
              <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetMobil.id + ' / ' + targetMobil.kat_nama"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Unit Armada</b>
              <p class="font-extrabold text-gray-900 dark:text-white mt-0.5" x-text="targetMobil.merk + ' ' + targetMobil.tipe"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Plat Nomor</b>
              <p class="font-mono text-purple-600 dark:text-purple-400 font-bold text-sm mt-0.5" x-text="targetMobil.plat"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Tahun Rakit</b>
              <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetMobil.thn"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Masa Pajak STNK</b>
              <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetMobil.pajak"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Warna Body</b>
              <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetMobil.warna"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Kapasitas</b>
              <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5" x-text="targetMobil.caps + ' Kursi'"></p>
            </div>
            <div>
              <b class="text-gray-400 dark:text-gray-500 text-[10px] uppercase">Harga Sewa</b>
              <p class="text-green-600 dark:text-green-400 font-black text-sm mt-0.5" x-text="'Rp ' + targetMobil.harga + '/Hari'"></p>
            </div>
          </div>

          <div class="flex justify-end pt-4 mt-3 border-t dark:border-gray-700">
            <button type="button" @click="isDetailOpen = false" class="px-4 py-1.5 font-bold text-xs text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition shadow-sm">
              Oke, Tutup
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>

  <div x-show="isEditOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" x-transition>
    <div @click.away="isEditOpen = false" class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl border border-gray-100 dark:border-gray-700 text-xs" role="dialog">
      <header class="flex justify-between items-center border-b pb-2 mb-4 dark:border-gray-700">
        <p class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Form Update Data Kendaraan</p>
        <button @click="isEditOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
      </header>

      <form action="mobil.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-gray-700 dark:text-gray-300">
        <input type="hidden" name="id_mobil" :value="targetMobil.id">

        <label class="block">
          <span>Kategori Kelas</span>
          <select name="id_kategori" :value="targetMobil.kat_id" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none">
            <?php
            $kat2 = mysqli_query($conn, "SELECT * FROM kategori");
            while ($k2 = mysqli_fetch_assoc($kat2)) {
              echo "<option value='" . $k2['id_kategori'] . "'>" . $k2['jenis_kategori'] . "</option>";
            }
            ?>
          </select>
        </label>

        <label class="block">
          <span>Merk Mobil</span>
          <input type="text" name="merk" :value="targetMobil.merk" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Tipe Unit</span>
          <input type="text" name="tipe" :value="targetMobil.tipe" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Nomor Polisi (Plat)</span>
          <input type="text" name="nomor_polisi" :value="targetMobil.plat" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Tahun Perakitan</span>
          <input type="number" name="tahun" :value="targetMobil.thn" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Masa Berlaku Pajak</span>
          <input type="date" name="pajak" :value="targetMobil.pajak_raw" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Warna Kendaraan</span>
          <input type="text" name="warna" :value="targetMobil.warna" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Harga Sewa / Hari (Rp)</span>
          <input type="number" name="harga_sewa_per_hari" :value="targetMobil.harga_raw" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Kapasitas Kursi</span>
          <input type="number" name="kapasitas" :value="targetMobil.caps" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none" />
        </label>

        <label class="block">
          <span>Status Armada</span>
          <select name="status_mobil" :value="targetMobil.status" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 focus:outline-none">
            <option value="tersedia">Tersedia</option>
            <option value="disewa">Disewa</option>
            <option value="maintenance">Maintenance</option>
          </select>
        </label>

        <div class="block md:col-span-2">
          <span>Ganti Gambar Baru (Opsional)</span>
          <input type="file" name="gambar" accept="image/*" class="block w-full mt-1 text-gray-400" />
        </div>

        <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-700 md:col-span-2">
          <button type="button" @click="isEditOpen = false" class="px-4 py-2 font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
          <button type="submit" name="update_mobil" class="px-4 py-2 font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow">Update Data</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>