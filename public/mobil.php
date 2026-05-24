<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <?php include 'components/head.php'; ?>
    
    <style>
      .figma-container {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        clear: both !important;
      }
      .figma-grid {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 24px !important;
        width: 100% !important;
        margin-bottom: 32px !important;
      }
      .figma-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 16px !important;
        padding: 24px !important;
        width: calc(33.333% - 16px) !important; /* Mengunci mati 3 kolom ke samping */
        min-width: 280px !important;
        box-sizing: border-box !important;
        position: relative !important;
        display: flex !important;
        flex-col: column !important;
        flex-direction: column !important;
        align-items: center !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
      }
      .figma-img-box {
        width: 160px !important;
        height: 160px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 16px !important;
        padding: 12px !important;
        margin-bottom: 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
      }
      .figma-img {
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
      }
      /* Mengatasi bug tag bocor dari sidebar template */
      .flex.flex-col.flex-1.w-full {
        width: 100% !important;
        min-w: 0 !important;
        display: flex !important;
      }
    </style>
  </head>
  <body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
      <?php include 'components/sidebar.php'; ?>

      <div class="flex flex-col flex-1 w-full">
        <?php include 'components/header.php'; ?>

        <main class="h-full overflow-y-auto">
          <div class="container px-6 mx-auto figma-container">
            
            <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
              Data Mobil
            </h2>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 w-full">
              <div class="relative w-full max-w-xs text-gray-500 focus-within:text-purple-600">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input class="w-full pl-9 pr-4 py-1.5 text-sm text-gray-700 placeholder-gray-500 bg-white border border-gray-300 rounded-lg dark:placeholder-gray-500 dark:bg-gray-700 dark:text-gray-200 focus:border-purple-300 focus:outline-none focus:shadow-outline-purple form-input" type="text" placeholder="Cari Mobil" />
              </div>

              <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.212 8H17"></path>
                </svg>
                Refresh
              </button>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                DATA MOBIL
              </h3>
              
              <div class="flex flex-wrap items-center gap-1 w-full sm:w-auto sm:justify-end">
                <button class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  Delete
                </button>
                <button class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                  Sort
                </button>
                <button class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                  Filters
                </button>
                <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 shadow-sm ml-2">
                  <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  Edit
                </button>
                <button class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-black rounded-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-black shadow transition-colors duration-150 ml-1">
                  <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                  Add
                </button>
              </div>
            </div>

            <div class="figma-grid">
              
              <?php
              // Loop membuat 6 kartu mobil statis sesuai mockup halaman 4 kelompokmu
              for ($i = 1; $i <= 6; $i++) {
              ?>
                <div class="figma-card">
                  
                  <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                    </svg>
                  </button>

                  <div class="figma-img-box">
                    <img src="https://toyotamobile.id/wp-content/uploads/2023/01/4-Attitude-Black.webp" alt="Toyota Innova Zenix" class="figma-img" />
                  </div>

                  <div class="text-center w-full mt-1">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm mb-1 tracking-tight">
                      Toyota Innova Zenix - D 5289 PO
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mb-3">
                      Harga per hari : <span class="text-gray-700 dark:text-gray-300 font-semibold">Rp 600.000</span>
                    </p>
                  </div>

                  <div class="w-full flex justify-center pt-2.5 border-t border-gray-100 dark:border-gray-700 mt-auto">
                    <span class="inline-flex items-center text-[11px] font-bold text-green-600 dark:text-green-400">
                      <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>Tersedia
                    </span>
                  </div>

                </div>
              <?php
              }
              ?>

            </div>

          </div>
        </main>
      </div>
    </div>
  </body>
</html>