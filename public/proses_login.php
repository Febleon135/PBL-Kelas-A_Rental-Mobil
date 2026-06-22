<?php
session_start();
include 'config.php';

/** @var mysqli $conn */

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    $query  = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {

            $_SESSION['id_pengguna']   = $row['id_pengguna'];
            $_SESSION['username']      = $row['username'];
            $_SESSION['role']          = $row['role'];
            $_SESSION['nama_pengguna'] = $row['nama_pengguna']; 

            header("Location: index.php");
            exit;
        }
    }

    header("Location: login.php?pesan=gagal");
    exit;
} else {
    header("Location: login.php");
    exit;
}