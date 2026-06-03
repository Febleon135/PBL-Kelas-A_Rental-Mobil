<?php
session_start();
include 'config.php';

/** @var mysqli $conn */ 
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// ─── QUERY UMUM (Bisa Dilihat Semua Role) ───
$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan");
$r_pelanggan = mysqli_fetch_assoc($q_pelanggan);

$q_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental WHERE status_sewa = 'berjalan'");
$r_transaksi = mysqli_fetch_assoc($q_transaksi);

$q_mobil_ready = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'tersedia'");
$r_mobil_ready = mysqli_fetch_assoc($q_mobil_ready);

$q_mobil_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil");
$r_mobil_total = mysqli_fetch_assoc($q_mobil_total);

$q_mobil_mt = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'maintenance'");
$r_mobil_mt = mysqli_fetch_assoc($q_mobil_mt);

// ─── QUERY TAMBAHAN KHUSUS ADMIN/STAFF (NON-SENSITIF) ───
// Menghitung jumlah mobil yang sedang disewa
$q_mobil_rented = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'disewa'");
$r_mobil_rented = mysqli_fetch_assoc($q_mobil_rented);

// Menghitung transaksi yang sudah selesai
$q_sewa_done = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
$r_sewa_done = mysqli_fetch_assoc($q_sewa_done);


// ─── QUERY KHUSUS OWNER (DATA SENSITIF UANG) ───
$total_denda = 0;
$total_omzet = 0;

if ($_SESSION['role'] === 'owner') {
  $q_denda = mysqli_query($conn, "SELECT SUM(biaya_denda) as total FROM denda_kerusakan");
  $r_denda = mysqli_fetch_assoc($q_denda);
  $total_denda = $r_denda['total'] ?? 0;

  $q_omzet = mysqli_query($conn, "SELECT SUM(total_biaya) as total FROM transaksi_rental WHERE status_sewa = 'selesai'");
  $r_omzet = mysqli_fetch_assoc($q_omzet);
  $total_omzet = $r_omzet['total'] ?? 0;
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
            Dashboard - Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_pengguna']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)
          </h2>

          <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">

            <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
              <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A6.005 6.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg></div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_pelanggan['total']; ?></p>
                <p class="text-xs font-semibold text-gray-500 uppercase">Total Pelanggan</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
              <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg></div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_mobil_ready['total'] . '/' . $r_mobil_total['total']; ?></p>
                <p class="text-xs font-semibold text-gray-500 uppercase">Mobil Tersedia</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
              <div class="p-3 mr-4 text-amber-500 bg-amber-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg></div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_mobil_mt['total']; ?></p>
                <p class="text-xs font-semibold text-gray-500 uppercase">Mobil Maintenance</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
              <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg></div>
              <div>
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_transaksi['total']; ?></p>
                <p class="text-xs font-semibold text-gray-500 uppercase">Transaksi Aktif</p>
              </div>
            </div>


            <?php if ($_SESSION['role'] === 'owner'): ?>

              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg></div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_denda, 0, ',', '.'); ?></p>
                  <p class="text-xs font-semibold text-gray-500 uppercase">Total Denda (Bulan Ini)</p>
                </div>
              </div>

              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path>
                  </svg></div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></p>
                  <p class="text-xs font-semibold text-gray-500 uppercase">Total Pendapatan (Bulan Ini)</p>
                </div>
              </div>

            <?php else: ?>

              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                  </svg></div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_sewa_done['total']; ?></p>
                  <p class="text-xs font-semibold text-gray-500 uppercase">Transaksi Selesai</p>
                </div>
              </div>

              <div class="flex items-center p-4 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 text-indigo-500 bg-indigo-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg></div>
                <div>
                  <p class="text-2xl font-bold text-gray-700 dark:text-gray-200"><?php echo $r_mobil_rented['total']; ?></p>
                  <p class="text-xs font-semibold text-gray-500 uppercase">Mobil Sedang Disewa</p>
                </div>
              </div>

            <?php endif; ?>

          </div>

          <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">
            Transaksi Terbaru
          </h3>

          <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-8">
            <div class="w-full overflow-x-auto">
              <table class="w-full whitespace-no-wrap">
                <thead>
                  <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <th class="px-4 py-3">ID Transaksi</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Mobil</th>
                    <th class="px-4 py-3">Tanggal Sewa</th>
                    <th class="px-4 py-3">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm">

                  <?php
                  // Ambil 3 transaksi paling baru yang ada di database WheelsRent
                  $q_table = mysqli_query($conn, "SELECT t.id_transaksi, p.nama_pelanggan, m.merk, m.tipe, m.nomor_polisi, t.tanggal_sewa, t.status_sewa 
                                                    FROM transaksi_rental t
                                                    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                    JOIN mobil m ON t.id_mobil = m.id_mobil
                                                    ORDER BY t.tanggal_sewa DESC LIMIT 3");

                  if (mysqli_num_rows($q_table) > 0) {
                    while ($row = mysqli_fetch_assoc($q_table)) {
                      // Mewarnai badge status secara dinamis
                      $badge_color = "bg-blue-600 text-white";
                      if ($row['status_sewa'] == 'selesai') $badge_color = "bg-green-600 text-white";
                      if ($row['status_sewa'] == 'terlambat') $badge_color = "bg-red-600 text-white";
                  ?>
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 font-semibold text-purple-600"><?php echo $row['id_transaksi']; ?></td>
                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                        <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($row['merk'] . ' ' . $row['tipe'] . ' (' . $row['nomor_polisi'] . ')'); ?></td>
                        <td class="px-4 py-3"><?php echo date('d M Y', strtotime($row['tanggal_sewa'])); ?></td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full <?php echo $badge_color; ?>">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-white rounded-full"></span><?php echo ucfirst($row['status_sewa']); ?>
                          </span>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat transaksi sewa masuk.</td></tr>';
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
</body>

</html>