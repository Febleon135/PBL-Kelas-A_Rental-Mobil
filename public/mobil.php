<?php
session_start();
include 'config.php';

/** @var mysqli $conn */ 
// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ─── LOGIKA PROSES BACKEND: TAMBAH MOBIL BARU ───
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

    // Logika Upload Gambar (Saran Dosen: Simpan nama filenya saja di DB)
    $nama_gambar = "default.png"; // Default jika tidak ada file di-upload
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $_FILES['gambar']['name']);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['gambar']['tmp_name'];
        
        // Buat nama unik baru agar tidak bentrok (Contoh: MOBIL-Innova-12345.png)
        $nama_gambar = "MOBIL-" . urlencode($merk) . "-" . time() . "." . $ekstensi;

        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            // Pindahkan file fisik ke folder aset lokal yang kita buat kemarin
            move_uploaded_file($file_tmp, 'assets/img/mobil/' . $nama_gambar);
        }
    }

    // Query Insert ke Tabel Mobil
    $query = "INSERT INTO mobil VALUES ('$id_mobil', '$id_kategori', '$merk', '$tipe', '$nomor_polisi', '$tahun', '$pajak', '$warna', '$harga_sewa_per_hari', '$kapasitas', '$status_mobil', '$nama_gambar')";

    if (mysqli_query($conn, $query)) {
        header("Location: mobil.php?status=success");
        exit;
    } else {
        header("Location: mobil.php?status=failed");
        exit;
    }
}
?>

<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <?php include 'components/head.php'; ?>
    <style>
      .figma-grid { display: flex !important; flex-wrap: wrap !important; gap: 24px !important; width: 100% !important; margin-bottom: 32px !important; }
      .figma-card { background: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 16px !important; padding: 24px !important; width: calc(33.333% - 16px) !important; min-width: 280px !important; box-sizing: border-box !important; position: relative !important; display: flex !important; flex-direction: column !important; align-items: center !important; box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important; }
      .figma-img-box { width: 140px !important; height: 140px !important; border: 1px solid #e5e7eb !important; border-radius: 16px !important; padding: 8px !important; margin-bottom: 16px !important; display: flex !important; align-items: center !important; justify-content: center !important; overflow: hidden !important; }
      .figma-img { max-width: 100% !important; max-height: 100% !important; object-fit: contain !important; }
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

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
              <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
                ✓ Aset mobil baru berhasil didaftarkan ke sistem!
              </div>
            <?php endif; ?>

            <?php
            // KONTROL ALUR TAMPILAN (FORM TAMBAH VS GRID VIEW)
            if (isset($_GET['action']) && $_GET['action'] == 'add') {
                
                // OTO-GENERATE ID MOBIL (Contoh: MOB001, MOB002)
                $q_id   = mysqli_query($conn, "SELECT id_mobil FROM mobil ORDER BY id_mobil DESC LIMIT 1");
                $row_id = mysqli_fetch_assoc($q_id);
                $form_id = $row_id ? "MOB" . sprintf("%03d", substr($row_id['id_mobil'], 3) + 1) : "MOB001";
            ?>
              <div class="mb-4">
                <a href="mobil.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200">
                  ← Kembali ke Galeri Armada
                </a>
              </div>

              <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-8">
                <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide border-b pb-2 dark:border-gray-700">
                  TAMBAH DATA ARMADA MOBIL
                </h4>

                <form action="mobil.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input type="hidden" name="id_mobil" value="<?php echo $form_id; ?>">

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Kategori Kelas</span>
                    <select name="id_kategori" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                      <?php 
                        $kat = mysqli_query($conn, "SELECT * FROM kategori");
                        while($k = mysqli_fetch_assoc($kat)) {
                            echo "<option value='".$k['id_kategori']."'>".$k['jenis_kategori']."</option>";
                        }
                      ?>
                    </select>
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Merk Mobil</span>
                    <input type="text" name="merk" required placeholder="Contoh: Toyota, Mitsubishi" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Tipe Unit</span>
                    <input type="text" name="tipe" required placeholder="Contoh: Innova Zenix, Pajero Sport" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Nomor Polisi (Plat)</span>
                    <input type="text" name="nomor_polisi" required placeholder="Contoh: D 5289 PO" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Tahun Perakitan</span>
                    <input type="number" name="tahun" required placeholder="Contoh: 2024" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Masa Berlaku Pajak</span>
                    <input type="date" name="pajak" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Warna Kendaraan</span>
                    <input type="text" name="warna" required placeholder="Contoh: Putih, Hitam Metalik" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Harga Sewa / Hari (Rp)</span>
                    <input type="number" name="harga_sewa_per_hari" required placeholder="Contoh: 600000" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Kapasitas Penumpang</span>
                    <input type="number" name="kapasitas" required placeholder="Contoh: 7" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Status Awal</span>
                    <select name="status_mobil" class="block w-full mt-1 text-sm dark:bg-gray-700 form-select focus:border-purple-400">
                      <option value="tersedia">Tersedia</option>
                      <option value="maintenance">Maintenance</option>
                    </select>
                  </label>

                  <div class="block text-sm md:col-span-2">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Foto Unit Kendaraan</span>
                    <input type="file" name="gambar" accept="image/*" required class="block w-full mt-1 text-sm text-gray-500 dark:text-gray-400" />
                  </div>

                  <div class="flex items-center justify-end space-x-3 pt-4 border-t dark:border-gray-700 md:col-span-2">
                    <a href="mobil.php" class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">Cancel</a>
                    <button type="submit" name="save_mobil" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                  </div>
                </form>
              </div>

            <?php
            } else {
            ?>
              <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4 flex justify-between items-center w-full">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">GALERI ARMADA</h3>
                
                <?php if ($_SESSION['role'] !== 'staff'): ?>
                  <a href="mobil.php?action=add" class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 shadow">
                    + Add Mobil
                  </a>
                <?php endif; ?>
              </div>

              <div class="figma-grid">
                <?php
                $cars = mysqli_query($conn, "SELECT * FROM mobil ORDER BY id_mobil ASC");
                if (mysqli_num_rows($cars) > 0) {
                    while ($c = mysqli_fetch_assoc($cars)) {
                        // Logika deteksi badge status
                        $badge_text  = "Tersedia";
                        $badge_style = "text-green-600 bg-green-50 dark:text-green-400";
                        if ($c['status_mobil'] == 'disewa') { $badge_text = "Disewa"; $badge_style = "text-blue-600 bg-blue-50"; }
                        if ($c['status_mobil'] == 'maintenance') { $badge_text = "Maintenance"; $badge_style = "text-amber-600 bg-amber-50"; }
                ?>
                      <div class="figma-card">
                        <div class="figma-img-box">
                          <img src="assets/img/mobil/<?php echo $c['gambar']; ?>" alt="Mobil" class="figma-img" />
                        </div>

                        <div class="text-center w-full mt-1">
                          <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm mb-1 tracking-tight">
                            <?php echo htmlspecialchars($c['merk'] . ' ' . $c['tipe'] . ' - ' . $c['nomor_polisi']); ?>
                          </h4>
                          <p class="text-[11px] text-gray-500 font-medium mb-3">
                            Harga per hari : <span class="text-gray-700 dark:text-gray-300 font-semibold">Rp <?php echo number_format($c['harga_sewa_per_hari'], 0, ',', '.'); ?></span>
                          </p>
                        </div>

                        <div class="w-full flex justify-center pt-2.5 border-t border-gray-100 dark:border-gray-700 mt-auto">
                          <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 rounded <?php echo $badge_style; ?>">
                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full" style="background-color: currentColor;"></span><?php echo $badge_text; ?>
                          </span>
                        </div>
                      </div>
                <?php
                    }
                } else {
                    echo '<div class="w-full p-12 bg-white rounded-xl text-center text-gray-500 border border-gray-200">Belum ada unit mobil terdaftar di database. Silakan klik "+ Add Mobil".</div>';
                }
                ?>
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