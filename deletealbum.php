<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah AlbumID disediakan
if (isset($_GET['albumID'])) {
    $albumID = intval($_GET['albumID']);
    $userID = $_SESSION['userID']; // Ambil UserID dari session

    // Hapus semua foto dalam album ini
    $queryDeleteFotos = "DELETE FROM foto WHERE AlbumID = ?";
    $stmt = $conn->prepare($queryDeleteFotos);
    $stmt->bind_param("i", $albumID);
    $stmt->execute();

    // Hapus album
    $queryDeleteAlbum = "DELETE FROM album WHERE AlbumID = ? AND UserID = ?";
    $stmt = $conn->prepare($queryDeleteAlbum);
    $stmt->bind_param("ii", $albumID, $userID);
    $stmt->execute();

    // Redirect setelah menghapus
    header("Location: profile.php");
    exit;
} else {
    // Jika tidak ada AlbumID, redirect ke profil
    header("Location: profile.php");
    exit;
}
?>
