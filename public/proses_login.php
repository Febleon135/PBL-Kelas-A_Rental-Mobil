<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (isset($_POST['login'])) {
    if (!isset($conn) || !$conn) {
        die('Database connection not established.');
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    $query  = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if ($password === $row['password']) {
            
            $_SESSION['id_pengguna']   = $row['id_pengguna'];
            $_SESSION['nama_pengguna'] = $row['nama_pengguna'];
            $_SESSION['username']      = $row['username'];
            $_SESSION['role']          = $row['role'];

            header("Location: index.php");
            exit;
        }
    }
    
    header("Location: login.php?error=gagal");
    exit;
}
?>