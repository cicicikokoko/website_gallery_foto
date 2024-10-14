<?php
include 'config.php';
session_start();

// Cek apakah pengguna sudah login sebagai admin
// if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'Admin') {
//     header("Location: login.php");
//     exit;
// }

if (isset($_GET['id'])) {
    $foto_id = $_GET['id'];

    // Query untuk menghapus foto
    $query = "DELETE FROM foto WHERE FotoID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $foto_id);

    if ($stmt->execute()) {
        echo "<script>alert('Foto berhasil dihapus.'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "Gagal menghapus foto: " . $conn->error;
    }
} else {
    echo "ID foto tidak diberikan.";
}

$conn->close();
?>
