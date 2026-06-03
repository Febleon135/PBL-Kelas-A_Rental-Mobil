<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}
include 'config.php';
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
              Laporan Pendapatan
            </h2>

            <div class="mb-6 max-w-xs">
              <label class="block text-sm mb-2 text-gray-600 dark:text-gray-400 font-medium">
                Pilih Periode Laporan:
              </label>
              <select class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400">
                <option>Bulan Ini (Mei 2026)</option>
                <option>Bulan Lalu</option>
                <option>Tahun Ini (2026)</option>
              </select>
            </div>

            <div class="grid gap-6 mb-8 md:grid-cols-2">
              <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-3xl font-extrabold text-gray-700 dark:text-gray-200">142</p>
                  <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-1">Total Transaksi</p>
                </div>
              </div>

              <div class="flex items-center p-5 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-3xl font-extrabold text-gray-700 dark:text-gray-200">Rp84.500.000</p>
                  <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-1">Total Pendapatan</p>
                </div>
              </div>
            </div>

            <div class="min-w-0 p-6 bg-white rounded-lg border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 mb-8">
              <h4 class="mb-4 font-bold text-gray-800 dark:text-gray-300 uppercase tracking-wide">
                Grafik Tren Pendapatan bulanan
              </h4>
              <div class="relative h-64">
                <canvas id="lineLaporan"></canvas>
              </div>
              <div class="flex justify-center mt-4 space-x-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center">
                  <span class="inline-block w-3 h-3 mr-1 bg-purple-600 rounded-full"></span>
                  <span class="font-medium">Pendapatan Selesai Sewa (Rupiah)</span>
                </div>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('lineLaporan').getContext('2d');
        const myChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            datasets: [{
              label: 'Pendapatan',
              data: [18000000, 25000000, 19500000, 22000000],
              backgroundColor: 'rgba(147, 51, 234, 0.1)',
              borderColor: '#9333ea',
              borderWidth: 3,
              pointBackgroundColor: '#9333ea',
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true,
                  callback: function(value) {
                    return 'Rp ' + value.toLocaleString('id-ID');
                  }
                }
              }]
            },
            tooltips: {
              callbacks: {
                label: function(tooltipItem, data) {
                  return 'Pendapatan: Rp ' + tooltipItem.yLabel.toLocaleString('id-ID');
                }
              }
            }
          }
        });
      });
    </script>
  </body>
</html>