<?php
include 'config.php';
session_start();

$userID = 1; // Ganti dengan user yang login

if (isset($_GET['fotoID'])) {
    $fotoID = $_GET['fotoID'];

    // Cek apakah user sudah memberi like pada foto ini
    $checkSql = "SELECT * FROM likefoto WHERE FotoID = '$fotoID' AND UserID = '$userID'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Jika belum, tambahkan like
        $sql = "INSERT INTO likefoto (FotoID, UserID, TanggalLike) VALUES ('$fotoID', '$userID', NOW())";
        if ($conn->query($sql) === TRUE) {
            echo "Like berhasil ditambahkan!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Kamu sudah menyukai foto ini.";
    }
}

header("Location: index.php");
?>
