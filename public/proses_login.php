<?php
session_start();
include 'config.php';

/** @var mysqli $conn */

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    // 1. Cari user berdasarkan username di database
    $query  = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // 2. Verifikasi kecocokan password bcrypt hash
        if (password_verify($password, $row['password'])) {

            // Set session utama untuk otorisasi dashboard Windmill
            $_SESSION['id_pengguna']   = $row['id_pengguna'];
            $_SESSION['username']      = $row['username'];
            $_SESSION['role']          = $row['role'];
            $_SESSION['nama_pengguna'] = $row['nama_pengguna']; 

            // Sukses! Melesat langsung ke dashboard
            header("Location: index.php");
            exit;
        }
    }

    // Jika password salah atau username tidak terdaftar, tendang kembali ke login
    header("Location: login.php?pesan=gagal");
    exit;
} else {
    header("Location: login.php");
    exit;
}