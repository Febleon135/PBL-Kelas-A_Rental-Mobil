<?php
session_start();
include 'config.php';

/** @var mysqli $conn */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username_session = $_SESSION['username'];

$q_user = mysqli_query($conn, "SELECT * FROM pengguna WHERE username = '$username_session'");
$user   = mysqli_fetch_assoc($q_user);

$nama_user_encoded = urlencode($user['nama_pengguna']);
$avatar_inisial    = "https://ui-avatars.com/api/?name=" . $nama_user_encoded . "&background=9333ea&color=fff&size=128&font-size=0.4";

if (isset($_POST['update_profile'])) {
    $id_pengguna   = $user['id_pengguna']; 
    $nama_baru     = mysqli_real_escape_string($conn, $_POST['nama_pengguna']);
    $username_baru = mysqli_real_escape_string($conn, $_POST['username']);
    $password_baru = $_POST['password'];

    $cek_kembar = mysqli_query($conn, "SELECT id_pengguna FROM pengguna WHERE username = '$username_baru' AND id_pengguna != '$id_pengguna'");
    if (mysqli_num_rows($cek_kembar) > 0) {
        header("Location: profile.php?status=username-taken");
        exit;
    }

    if (!empty($password_baru)) {
        $password_fix = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update = "UPDATE pengguna SET nama_pengguna = '$nama_baru', username = '$username_baru', password = '$password_fix' WHERE id_pengguna = '$id_pengguna'";
    } else {
        $query_update = "UPDATE pengguna SET nama_pengguna = '$nama_baru', username = '$username_baru' WHERE id_pengguna = '$id_pengguna'";
    }

    if (mysqli_query($conn, $query_update)) {
        $_SESSION['username'] = $username_baru;

        $_SESSION['nama_pengguna'] = $nama_baru;

        header("Location: profile.php?status=success");
    } else {
        header("Location: profile.php?status=failed");
    }
    exit;
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
                        Pengaturan Profil Akun
                    </h2>

                    <?php if (isset($_GET['status']) && $_GET['status'] == 'username-taken'): ?>
                        <div class="mb-4 p-3 bg-amber-500 text-white rounded-lg text-sm font-semibold">
                            ⚠ Username sudah digunakan oleh akun karyawan lain, silakan cari nama unik lainnya!
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['status'])): ?>
                        <?php if ($_GET['status'] == 'success'): ?>
                            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg text-sm font-semibold">
                                ✓ Profil Anda berhasil diperbarui secara real-time!
                            </div>
                        <?php elseif ($_GET['status'] == 'failed'): ?>
                            <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-sm font-semibold">
                                ⚠ Gagal memperbarui data profil, periksa koneksi database.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="grid gap-6 mb-8 md:grid-cols-12">

                        <div class="md:col-span-4 p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700 text-center flex flex-col items-center justify-center">
                            <img src="<?php echo $avatar_inisial; ?>" alt="User Avatar" class="w-32 h-32 rounded-full border-4 border-purple-100 dark:border-purple-900 shadow-sm mb-4">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100"><?php echo htmlspecialchars($user['nama_pengguna']); ?></h3>
                            <p class="text-xs font-bold uppercase tracking-wider text-purple-600 bg-purple-50 dark:bg-purple-900 dark:text-purple-100 px-2.5 py-1 rounded-full mt-2">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </p>
                        </div>

                        <div class="md:col-span-8 p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-6 uppercase tracking-wider border-b pb-2 dark:border-gray-700">Form Ubah Data</h4>

                            <form action="profile.php" method="POST" class="space-y-4">

                                <label class="block text-sm">
                                    <span class="text-gray-700 dark:text-gray-400 font-medium">Username Login Akun</span>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400 rounded-lg text-gray-800 dark:text-gray-200" />
                                </label>

                                <label class="block text-sm">
                                    <span class="text-gray-700 dark:text-gray-400 font-medium">Nama Lengkap Pengguna</span>
                                    <input type="text" name="nama_pengguna" value="<?php echo htmlspecialchars($user['nama_pengguna']); ?>" required class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400 rounded-lg text-gray-800 dark:text-gray-200" />
                                </label>

                                <label class="block text-sm">
                                    <span class="text-gray-700 dark:text-gray-400 font-medium">Ganti Password (Opsional)</span>
                                    <input type="password" name="password" placeholder="••••••••" class="block w-full mt-1 text-sm dark:bg-gray-700 form-input focus:border-purple-400 rounded-lg text-gray-800 dark:text-gray-200" />
                                    <span class="text-[11px] text-gray-400 mt-1 block">Kosongkan kolom password ini jika Anda tidak berniat untuk mengganti password lama Anda.</span>
                                </label>

                                <div class="flex justify-end pt-4 border-t dark:border-gray-700 mt-6">
                                    <button type="submit" name="update_profile" class="px-5 py-2.5 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-sm duration-150">
                                        💾 Simpan Perubahan Profil
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>

                </div>
            </main>
        </div>
    </div>
</body>

</html>