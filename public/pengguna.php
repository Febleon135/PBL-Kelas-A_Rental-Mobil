<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
// PROTEKSI DOUBLE: Jika belum login ATAU login tapi BUKAN owner, tendang ke dashboard
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
  header("Location: index.php");
  exit;
}

// ─── LOGIKA BACKEND 1: TAMBAH PENGGUNA BARU ───
if (isset($_POST['tambah_user'])) {
  $nama_pengguna = mysqli_real_escape_string($conn, $_POST['nama_pengguna']);
  $username      = mysqli_real_escape_string($conn, $_POST['username']);
  $password      = $_POST['password']; 
  
  // PERBAIKAN KEAMANAN: Password sekarang wajib di-hash agar sinkron dengan proses_login.php
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $role          = mysqli_real_escape_string($conn, $_POST['role']);

  // Auto-generate ID Pengguna (Contoh: USR001, USR002, dst)
  $q_id   = mysqli_query($conn, "SELECT id_pengguna FROM pengguna WHERE id_pengguna LIKE 'USR%' ORDER BY id_pengguna DESC LIMIT 1");
  $row_id = mysqli_fetch_assoc($q_id);
  $id_baru = $row_id ? "USR" . sprintf("%03d", substr($row_id['id_pengguna'], 3) + 1) : "USR001";

  // Cek validasi agar tidak ada username kembar (duplicate)
  $cek_user = mysqli_query($conn, "SELECT username FROM pengguna WHERE username = '$username'");
  if (mysqli_num_rows($cek_user) > 0) {
    header("Location: pengguna.php?status=duplicate");
    exit;
  }

  // Masukkan password_hash ke kolom database
  $insert = mysqli_query($conn, "INSERT INTO pengguna VALUES ('$id_baru', '$nama_pengguna', '$username', '$password_hash', '$role')");
  if ($insert) {
    header("Location: pengguna.php?status=add-success");
  } else {
    header("Location: pengguna.php?status=failed");
  }
  exit;
}

// ─── LOGIKA BACKEND 2: HAPUS HAK AKSES PENGGUNA ───
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
  $id_hapus = mysqli_real_escape_string($conn, $_GET['id']);

  // Proteksi: Owner tidak boleh menghapus dirinya sendiri secara tidak sengaja
  if ($id_hapus === $_SESSION['id_pengguna'] || $id_hapus == 'USROOT') {
    header("Location: pengguna.php?status=denied");
    exit;
  }

  $delete = mysqli_query($conn, "DELETE FROM pengguna WHERE id_pengguna = '$id_hapus'");
  if ($delete) {
    header("Location: pengguna.php?status=del-success");
  } else {
    header("Location: pengguna.php?status=failed");
  }
  exit;
}

// ─── LOGIKA FILTER PENCARIAN USERNAME ───
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
  $keyword = mysqli_real_escape_string($conn, $_GET['search']);
  $search_query = "WHERE username LIKE '%$keyword%' OR nama_pengguna LIKE '%$keyword%'";
}
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="Object.assign(data(), { isModalOpen: false })" lang="en">

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
            Manajemen Pengguna (Hak Akses Owner)
          </h2>

          <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'add-success'): ?>
              <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-xs font-semibold">✓ Berhasil mendaftarkan akun internal staff baru dengan enkripsi hash aman!</div>
            <?php elseif ($_GET['status'] == 'del-success'): ?>
              <div class="mb-4 p-3 bg-blue-500 text-white rounded-lg text-xs font-semibold">✓ Berhasil menghapus hak akses pengguna dari sistem.</div>
            <?php elseif ($_GET['status'] == 'duplicate'): ?>
              <div class="mb-4 p-3 bg-amber-500 text-white rounded-lg text-xs font-semibold">⚠ Gagal! Username sudah digunakan oleh karyawan lain.</div>
            <?php elseif ($_GET['status'] == 'denied'): ?>
              <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-xs font-semibold">❌ Keamanan Ditolak! Anda tidak bisa menghapus akun Owner utama Anda sendiri.</div>
            <?php elseif ($_GET['status'] == 'failed'): ?>
              <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-xs font-semibold">❌ Terjadi kesalahan query internal database.</div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 w-full">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; width: 100%;">
              
              <div style="display: flex; align-items: center; gap: 12px;">
                <form action="pengguna.php" method="GET" style="display: flex; align-items: center; margin: 0; padding: 0;">
                  <div style="position: relative; display: flex; align-items: center;">
                    <div style="position: absolute; left: 12px; display: flex; align-items: center; pointer-events: none;">
                      <svg class="w-4 h-4 text-gray-400" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                    </div>
                    <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Cari nama / username..." style="width: 260px; height: 38px; padding: 0 12px 0 36px; font-size: 14px; border: 1px solid #D1D5DB; border-radius: 8px 0 0 8px; outline: none; background-color: #F9FAFB;" />
                  </div>
                  <button type="submit" style="height: 38px; padding: 0 16px; font-size: 12px; font-weight: bold; color: white; background-color: #7E3AF2; border: none; border-radius: 0 8px 8px 0; cursor: pointer; display: flex; align-items: center; justify-content: center; white-space: nowrap;">
                    Cari
                  </button>
                </form>

                <a href="pengguna.php" style="height: 38px; padding: 0 16px; font-size: 12px; font-weight: 600; color: #374151; background-color: white; border: 1px solid #D1D5DB; border-radius: 8px; text-decoration: none; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                  Clear
                </a>
              </div>

              <div>
                <button type="button" @click="isModalOpen = true" style="height: 38px; padding: 0 16px; font-size: 12px; font-weight: bold; color: white; background-color: #7E3AF2; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                  <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                  </svg>
                  Add
                </button>
              </div>

            </div>
          </div>

          <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">
            Daftar Otoritas Hak Akses Sistem Internal
          </h3>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-12">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-3">ID Pengguna</th>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Username Kendali</th>
                    <th class="px-4 py-3">Tingkatan Role</th>
                    <th class="px-4 py-3">Tindakan Otoritas</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">

                  <?php
                  $q_user = mysqli_query($conn, "SELECT id_pengguna, nama_pengguna, username, role FROM pengguna $search_query ORDER BY id_pengguna ASC");

                  if (mysqli_num_rows($q_user) > 0) {
                    while ($user = mysqli_fetch_assoc($q_user)) {
                      $bg_badge = "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200";
                      if ($user['role'] === 'owner') {
                        $bg_badge = "bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-100";
                      } elseif ($user['role'] === 'admin') {
                        $bg_badge = "bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-100";
                      }
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600 font-mono"><?php echo $user['id_pengguna']; ?></td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['nama_pengguna']); ?></td>
                        <td class="px-4 py-3 font-mono text-xs"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-4 py-3">
                          <span class="px-2 py-0.5 text-xs font-extrabold rounded uppercase <?php echo $bg_badge; ?>">
                            <?php echo $user['role']; ?>
                          </span>
                        </td>
                        <td class="px-4 py-3">
                          <?php if ($user['role'] !== 'owner'): ?>
                            <a href="pengguna.php?action=delete&id=<?php echo $user['id_pengguna']; ?>" onclick="return confirm('Apakah Anda yakin ingin memblokir dan menghapus total hak akses dari akun karyawan ini?')" class="px-2 py-1 text-xs font-bold text-white bg-red-600 rounded hover:bg-red-700 transition-colors">
                              Revoke Akses (Hapus)
                            </a>
                          <?php else: ?>
                            <span class="text-xs text-gray-400 italic">Master Akun (Lock)</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">Data karyawan/username tidak ditemukan di database.</td></tr>';
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

  <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center" x-transition>
    <div @click.away="isModalOpen = false" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog">

      <header class="flex justify-between items-center border-b pb-2 mb-4 dark:border-gray-700">
        <p class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Registrasi Akun Karyawan Internal Baru</p>
        <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
      </header>

      <form action="pengguna.php" method="POST" class="space-y-4 text-xs">
        <label class="block">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Nama Lengkap Karyawan</span>
          <input type="text" name="nama_pengguna" required placeholder="Contoh: Febrianus Leon" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 dark:text-gray-100 focus:border-purple-400 focus:outline-none" />
        </label>

        <label class="block">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Username Aplikasi (Tanpa Spasi)</span>
          <input type="text" name="username" required placeholder="Contoh: leonputra" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 dark:text-gray-100 focus:border-purple-400 focus:outline-none" />
        </label>

        <label class="block">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Password Default Akun</span>
          <input type="password" name="password" required placeholder="••••••••" class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 dark:text-gray-100 focus:border-purple-400 focus:outline-none" />
        </label>

        <label class="block">
          <span class="text-gray-700 dark:text-gray-400 font-medium">Tingkatan Otoritas (Role Hak Akses)</span>
          <select name="role" required class="block w-full mt-1 p-2 rounded border dark:bg-gray-700 dark:text-gray-100 focus:border-purple-400 focus:outline-none">
            <option value="staff">Staff Operasional Lapangan</option>
            <option value="admin">Admin Kasir & Finansial</option>
          </select>
        </label>

        <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-700">
          <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
          <button type="submit" name="tambah_user" class="px-4 py-2 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow">Simpan & Daftarkan</button>
        </div>
      </form>

    </div>
  </div>

</body>