<?php
$host = 'localhost';
$dbname = 'galleryfoto';  // Nama database sesuai yang kamu buat
$username = 'root';  // Sesuaikan dengan database kamu
$password = '';  // Sesuaikan dengan password MySQL kamu

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
