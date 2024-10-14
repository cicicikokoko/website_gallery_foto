<?php
include 'config.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Periksa apakah ada fotoID dan albumID yang dikirimkan
if (isset($_GET['fotoID']) && isset($_GET['albumID'])) {
    $fotoID = intval($_GET['fotoID']);
    $albumID = intval($_GET['albumID']);
    
    // Cek peran pengguna
    if ($_SESSION['role'] !== 'admin') {
        die("Anda tidak memiliki hak akses untuk menghapus foto ini.");
    }

    // Ambil lokasi file foto dari database
    $queryFetch = "SELECT LokasiFile FROM foto WHERE FotoID = ?";
    $stmtFetch = $conn->prepare($queryFetch);
    $stmtFetch->bind_param("i", $fotoID);
    $stmtFetch->execute();
    $resultFetch = $stmtFetch->get_result();

    if ($resultFetch->num_rows > 0) {
        $fotoData = $resultFetch->fetch_assoc();
        $filePath = $fotoData['LokasiFile'];
        
        // Hapus foto dari database
        $queryDelete = "DELETE FROM foto WHERE FotoID = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        $stmtDelete->bind_param("i", $fotoID);

        if ($stmtDelete->execute()) {
            // Hapus file dari server
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            // Arahkan kembali ke halaman album setelah penghapusan
            header("Location: album.php?albumID=" . $albumID);
            exit;
        } else {
            echo "Terjadi kesalahan saat menghapus foto.";
        }
    } else {
        echo "Foto tidak ditemukan.";
    }

    $stmtFetch->close();
    $stmtDelete->close();
} else {
    echo "ID foto atau album tidak ditemukan.";
}

$conn->close();
?>
