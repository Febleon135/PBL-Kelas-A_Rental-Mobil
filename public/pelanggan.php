<?php
session_start();
include 'config.php';

/** @var mysqli $conn */

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

if (isset($_POST['save_pelanggan'])) {
  $id_pelanggan   = mysqli_real_escape_string($conn, $_POST['id_pelanggan']);
  $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
  $no_telepon     = mysqli_real_escape_string($conn, $_POST['no_telepon']);
  $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
  $NIK            = mysqli_real_escape_string($conn, $_POST['NIK']);

  if ($_POST['action_form'] == 'add') {
    $query = "INSERT INTO pelanggan VALUES ('$id_pelanggan', '$nama_pelanggan', '$no_telepon', '$alamat', '$NIK')";
  } else {
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

if (isset($_GET['delete_id'])) {
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

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";

if (!empty($search_keyword)) {
  $where_clause = "WHERE id_pelanggan LIKE '%$search_keyword%' 
                   OR nama_pelanggan LIKE '%$search_keyword%' 
                   OR no_telepon LIKE '%$search_keyword%' 
                   OR NIK LIKE '%$search_keyword%'";
}

$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan $where_clause");
$r_total = mysqli_fetch_assoc($q_total);
$total_data = $r_total['total'] ?? 0;
$total_pages = ceil($total_data / $limit);
if ($total_pages < 1) $total_pages = 1;

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
            <div class="mb-4 p-3 rounded-lg text-sm font-semibold text-white <?php echo ($_GET['status'] == 'success' || $_GET['status'] == 'deleted') ? 'bg-green-500' : 'bg-red-500'; ?>">
              <?php
              if ($_GET['status'] == 'success') echo "✓ Data pelanggan berhasil disimpan.";
              if ($_GET['status'] == 'deleted') echo "✓ Data pelanggan berhasil dihapus.";
              if ($_GET['status'] == 'restricted') echo "❌ Akses ditolak. Staff tidak memiliki izin menghapus data.";
              if ($_GET['status'] == 'failed') echo "❌ Terjadi kesalahan pada sistem database.";
              ?>
            </div>
          <?php endif; ?>

          <?php
          if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')) {
            $isEdit = ($_GET['action'] == 'edit');

            $form_id    = "";
            $form_nama  = "";
            $form_telp  = "";
            $form_alamat = "";
            $form_nik   = "";

            if ($isEdit) {
              $edit_id = mysqli_real_escape_string($conn, $_GET['id']);
              $q_edit  = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = '$edit_id'");
              $d_edit  = mysqli_fetch_assoc($q_edit);

              if($d_edit) {
                  $form_id    = $d_edit['id_pelanggan'];
                  $form_nama  = $d_edit['nama_pelanggan'];
                  $form_telp  = $d_edit['no_telepon'];
                  $form_alamat = $d_edit['alamat'];
                  $form_nik   = $d_edit['NIK'];
              }
            } else {
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

            <div class="p-8 bg-white rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 dark:bg-gray-800 mb-8 max-w-2xl mx-auto w-full">
              <h4 class="mb-6 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide border-b pb-3 dark:border-gray-700">
                <?php echo $isEdit ? 'Edit Data Pelanggan' : 'Tambah Data Pelanggan'; ?>
              </h4>

              <form action="pelanggan.php" method="POST" class="space-y-5">
                <input type="hidden" name="action_form" value="<?php echo $_GET['action']; ?>">

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">ID Pelanggan</span>
                  <input type="text" name="id_pelanggan" value="<?php echo $form_id; ?>" readonly class="block w-full mt-1.5 text-sm bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg p-2.5 border-gray-200 dark:border-gray-600 cursor-not-allowed font-mono font-bold text-purple-600" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Nama Lengkap Pelanggan</span>
                  <input type="text" name="nama_pelanggan" value="<?php echo $form_nama; ?>" required placeholder="Masukkan Nama Pelanggan" class="block w-full mt-1.5 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400 focus:ring-purple-300 rounded-lg border-gray-300 dark:border-gray-600 p-2.5" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Nomor Telepon / WhatsApp Aktif</span>
                  <input type="text" name="no_telepon" value="<?php echo $form_telp; ?>" required placeholder="Masukkan Nomor Telepon" class="block w-full mt-1.5 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400 focus:ring-purple-300 rounded-lg border-gray-300 dark:border-gray-600 p-2.5" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Alamat Domisili</span>
                  <input type="text" name="alamat" value="<?php echo $form_alamat; ?>" required placeholder="Masukkan Alamat" class="block w-full mt-1.5 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400 focus:ring-purple-300 rounded-lg border-gray-300 dark:border-gray-600 p-2.5" />
                </label>

                <label class="block text-sm">
                  <span class="text-gray-700 dark:text-gray-400 font-medium">Nomor Induk Kependudukan (NIK)</span>
                  <input type="text" name="NIK" value="<?php echo $form_nik; ?>" required placeholder="Masukkan NIK KTP Pelanggan" class="block w-full mt-1.5 text-sm dark:text-gray-300 dark:bg-gray-700 form-input focus:border-purple-400 focus:ring-purple-300 rounded-lg border-gray-300 dark:border-gray-600 p-2.5" />
                </label>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t dark:border-gray-700 mt-2">
                  <a href="pelanggan.php" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors shadow-sm">Batal</a>
                  <button type="submit" name="save_pelanggan" class="px-5 py-2.5 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-md">Simpan Data</button>
                </div>
              </form>
            </div>

          <?php
          } else {
          ?>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 mt-4 flex flex-col sm:flex-row justify-between items-center gap-4 w-full">
              <div class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider w-full sm:w-auto">Data Pelanggan</div>
              
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end w-full sm:w-auto gap-3">
                <form action="pelanggan.php" method="GET" class="flex items-center gap-2 m-0 p-0 w-full sm:w-auto">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Cari Nama / NIK..." class="text-xs p-2 rounded-lg border bg-gray-50 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:border-purple-400 border-gray-200 dark:border-gray-600 w-full sm:w-48" />
                    <button type="submit" class="px-3 py-2 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition shadow-sm">Search</button>
                </form>
                
                <a href="pelanggan.php?action=add" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-black shadow whitespace-nowrap">
                  + Add Pelanggan
                </a>
              </div>
            </div>

            <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-4">
              <div class="w-full overflow-x-auto pb-4">
                <table class="w-full text-left">
                  <thead>
                    <tr class="text-xs font-semibold tracking-wide text-gray-500 uppercase border-b bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <th class="px-4 py-3 whitespace-nowrap">ID Pelanggan</th>
                      <th class="px-4 py-3 min-w-[150px]">Nama Pelanggan</th>
                      <th class="px-4 py-3 whitespace-nowrap">No. Telepon</th>
                      <th class="px-4 py-3 min-w-[200px]">Alamat</th>
                      <th class="px-4 py-3 whitespace-nowrap">NIK</th>
                      <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-medium">
                    <?php
                    $q_list = mysqli_query($conn, "SELECT * FROM pelanggan $where_clause ORDER BY id_pelanggan DESC LIMIT $start, $limit");
                    if (mysqli_num_rows($q_list) > 0) {
                      while ($row = mysqli_fetch_assoc($q_list)) {
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                          <td class="px-4 py-3 font-mono font-bold text-purple-600 dark:text-purple-400 whitespace-nowrap"><?php echo $row['id_pelanggan']; ?></td>
                          <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                          <td class="px-4 py-3 whitespace-nowrap"><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                          <td class="px-4 py-3"><?php echo htmlspecialchars($row['alamat']); ?></td>
                          <td class="px-4 py-3 font-mono whitespace-nowrap"><?php echo htmlspecialchars($row['NIK']); ?></td>

                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2 font-bold">
                              <a href="pelanggan.php?action=edit&id=<?php echo $row['id_pelanggan']; ?>"
                                class="flex items-center justify-center px-3 py-1.5 text-amber-700 bg-amber-100 hover:bg-amber-200 dark:text-white dark:bg-amber-600 dark:hover:bg-amber-500 rounded-lg transition-colors duration-150 shadow-sm gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path>
                                </svg>
                                <span>Edit</span>
                              </a>

                              <?php if ($_SESSION['role'] !== 'staff'): ?>
                                <a href="pelanggan.php?delete_id=<?php echo $row['id_pelanggan']; ?>"
                                  onclick="return confirm('Menghapus pelanggan ini akan menghapus riwayat transaksi mereka. Yakin melanjutkan?')"
                                  class="flex items-center justify-center px-3 py-1.5 text-red-700 bg-red-100 hover:bg-red-200 dark:text-white dark:bg-red-600 dark:hover:bg-red-500 rounded-lg transition-colors duration-150 shadow-sm gap-1">
                                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                  </svg>
                                  <span>Delete</span>
                                </a>
                              <?php endif; ?>

                            </div>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 italic">Data pelanggan tidak ditemukan.</td></tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <?php if ($total_pages > 1): ?>
              <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t bg-gray-50 sm:grid-cols-4 dark:bg-gray-800 dark:text-gray-400 mb-8 rounded-lg">
                <span class="flex items-center col-span-2">Menampilkan <?php echo min($start + 1, $total_data); ?>-<?php echo min($start + $limit, $total_data); ?> dari <?php echo $total_data; ?> Pelanggan</span>
                <span class="col-span-2 sm:mt-0 sm:justify-end flex">
                  <nav aria-label="Table navigation">
                    <ul class="inline-flex items-center space-x-1">
                      <li><a href="pelanggan.php?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search_keyword); ?>" class="px-3 py-1 rounded-md rounded-l-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page <= 1) echo 'pointer-events-none opacity-50'; ?>">Prev</a></li>
                      <li><span class="px-3 py-1 text-white bg-purple-600 border border-purple-600 rounded-md"><?php echo $page; ?> / <?php echo $total_pages; ?></span></li>
                      <li><a href="pelanggan.php?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search_keyword); ?>" class="px-3 py-1 rounded-md rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?php if ($page >= $total_pages) echo 'pointer-events-none opacity-50'; ?>">Next</a></li>
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