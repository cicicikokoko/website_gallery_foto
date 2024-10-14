<?php
include 'config.php';

if (isset($_GET['id'])) {
    $albumId = $_GET['id'];

    // Periksa apakah album ada sebelum menghapus
    $query = "SELECT * FROM album WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $albumId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Hapus album
        $deleteQuery = "DELETE FROM album WHERE AlbumID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $albumId);
        $stmt->execute();
        
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Album tidak ditemukan.";
    }
}
?>
