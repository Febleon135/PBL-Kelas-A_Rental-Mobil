<?php
session_start();
include 'config.php';

/** @var mysqli $conn */ 

// PROTEKSI: Jika belum login, tendang ke login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ─── ALUR LOGIKA PROSES BACKEND (CREATE & UPDATE) ───
if (isset($_POST['save_pelanggan'])) {
    $id_pelanggan   = mysqli_real_escape_string($conn, $_POST['id_pelanggan']);
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $no_telepon     = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
    $NIK            = mysqli_real_escape_string($conn, $_POST['NIK']);

    if ($_POST['action_form'] == 'add') {
        // Query Insert Data Baru
        $query = "INSERT INTO pelanggan VALUES ('$id_pelanggan', '$nama_pelanggan', '$no_telepon', '$alamat', '$NIK')";
    } else {
        // Query Update Data Lama
        $query = "UPDATE pelanggan SET nama_pelanggan='$nama_pelanggan', no_telepon='$no_telepon', alamat='$alamat', NIK='$NIK' WHERE id_pelanggan='$id_pelanggan'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: pelanggan.php?status=success");
        exit;
    } else {
        header("Location: pelanggan.php?status=failed");
        exit;
    }
}

// ─── ALUR LOGIKA PROSES BACKEND (DELETE) ───
if (isset($_GET['delete_id'])) {
    // Pengamanan hak akses: Staff dilarang menghapus data pelanggan
    if ($_SESSION['role'] === 'staff') {
        header("Location: pelanggan.php?status=restricted");
        exit;
    }

    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    if (mysqli_query($conn, "DELETE FROM pelanggan WHERE id_pelanggan = '$delete_id'")) {
        header("Location: pelanggan.php?status=deleted");
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
              Data Pelanggan
            </h2>

            <?php if (isset($_GET['status'])): ?>
              <div class="mb-4 p-3 rounded-lg text-sm font-semibold text-white <?php echo ($_GET['status']=='success' || $_GET['status']=='deleted') ? 'bg-green-500' : 'bg-red-500'; ?>">
                <?php 
                  if($_GET['status'] == 'success') echo "✓ Data pelanggan berhasil disimpan!";
                  if($_GET['status'] == 'deleted') echo "✓ Data pelanggan berhasil dihapus!";
                  if($_GET['status'] == 'restricted') echo "❌ Akses ditolak! Staff tidak boleh menghapus data.";
                  if($_GET['status'] == 'failed') echo "❌ Gagal memproses data database.";
                ?>
              </div>
            <?php endif; ?>

            <?php
            // IF-ELSE TAMPILAN FORM (TAMBAH / EDIT)
            if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
              $isEdit = ($_GET['action'] == 'edit');
              
              // Nilai default form kosong
              $form_id    = "";
              $form_nama  = "";
              $form_telp  = "";
              $form_alamat= "";
              $form_nik   = "";

              if ($isEdit) {
                  $edit_id = mysqli_real_escape_string($conn, $_GET['id']);
                  $q_edit  = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = '$edit_id'");
                  $d_edit  = mysqli_fetch_assoc($q_edit);
                  
                  $form_id    = $d_edit['id_pelanggan'];
                  $form_nama  = $d_edit['nama_pelanggan'];
                  $form_telp  = $d_edit['no_telepon'];
                  $form_alamat= $d_edit['alamat'];
                  $form_nik   = $d_edit['NIK'];
              } else {
                  // OTO-GENERATE ID PELANGGAN (Contoh: PLG001, PLG002)
                  $q_id   = mysqli_query($conn, "SELECT id_pelanggan FROM pelanggan ORDER BY id_pelanggan DESC LIMIT 1");
                  $row_id = mysqli_fetch_assoc($q_id);
                  if ($row_id) {
                      $num = substr($row_id['id_pelanggan'], 3);
                      $form_id = "PLG" . sprintf("%03d", $num + 1);
                  } else {
                      $form_id = "PLG001";
                  }
              }
            ?>
              <div class="mb-4">
                <a href="pelanggan.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200">
                  ← Kembali ke Daftar Pelanggan
                </a>
              </div>

              <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-8">
                <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide border-b pb-2 dark:border-gray-700">
                  <?php echo $isEdit ? 'EDIT DATA PELANGGAN' : 'TAMBAH DATA PELANGGAN'; ?>
                </h4>

                <form action="pelanggan.php" method="POST" class="space-y-4 max-w-2xl">
                  <input type="hidden" name="action_form" value="<?php echo $_GET['action']; ?>">
                  
                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">ID Pelanggan</span>
                    <input type="text" name="id_pelanggan" value="<?php echo $form_id; ?>" readonly class="block w-full mt-1 text-sm bg-gray-100 dark:bg-gray-700 dark:text-gray-300 form-input" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Nama Pelanggan</span>
                    <input type="text" name="nama_pelanggan" value="<?php echo $form_nama; ?>" required placeholder="Masukkan Nama Pelanggan" class="block w-full mt-1 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Nomor Telepon</span>
                    <input type="text" name="no_telepon" value="<?php echo $form_telp; ?>" required placeholder="Masukkan Nomor Telepon" class="block w-full mt-1 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">Alamat</span>
                    <input type="text" name="alamat" value="<?php echo $form_alamat; ?>" required placeholder="Masukkan Alamat" class="block w-full mt-1 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400 font-medium">NIK</span>
                    <input type="text" name="NIK" value="<?php echo $form_nik; ?>" required placeholder="Masukkan NIK" class="block w-full mt-1 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400" />
                  </label>

                  <div class="flex items-center justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                    <a href="pelanggan.php" class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">Cancel</a>
                    <button type="submit" name="save_pelanggan" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
                  </div>
                </form>
              </div>

            <?php
            } else {
            ?>
              <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4">
                <div class="flex justify-between items-center w-full">
                  <div class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">DATA PELANGGAN</div>
                  <a href="pelanggan.php?action=add" class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-black shadow">
                    + Add Pelanggan
                  </a>
                </div>
              </div>

              <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
                <div class="w-full overflow-x-auto">
                  <table class="w-full whitespace-no-wrap">
                    <thead>
                      <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        <th class="px-4 py-3">ID Pelanggan</th>
                        <th class="px-4 py-3">Nama Pelanggan</th>
                        <th class="px-4 py-3">No. Telepon</th>
                        <th class="px-4 py-3">Alamat</th>
                        <th class="px-4 py-3">NIK</th>
                        <th class="px-4 py-3">Aksi</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">
                      
                      <?php
                      $q_list = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY id_pelanggan ASC");
                      if (mysqli_num_rows($q_list) > 0) {
                          while ($row = mysqli_fetch_assoc($q_list)) {
                      ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                              <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $row['id_pelanggan']; ?></td>
                              <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                              <td class="px-4 py-3"><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                              <td class="px-4 py-3"><?php echo htmlspecialchars($row['alamat']); ?></td>
                              <td class="px-4 py-3 font-mono"><?php echo htmlspecialchars($row['NIK']); ?></td>
                              <td class="px-4 py-3 space-x-2">
                                <a href="pelanggan.php?action=edit&id=<?php echo $row['id_pelanggan']; ?>" class="text-blue-600 hover:underline font-semibold">Edit</a>
                                <?php if ($_SESSION['role'] !== 'staff'): ?>
                                  <a href="pelanggan.php?delete_id=<?php echo $row['id_pelanggan']; ?>" onclick="return confirm('Apakah anda yakin ingin menghapus pelanggan ini?')" class="text-red-600 hover:underline font-semibold">Delete</a>
                                <?php endif; ?>
                              </td>
                            </tr>
                      <?php
                          }
                      } else {
                          echo '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data pelanggan di database. Klik "+ Add Pelanggan" untuk mengisi.</td></tr>';
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