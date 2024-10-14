<?php
include 'config.php';

if (isset($_GET['id'])) {
    $album_id = $_GET['id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete all photos associated with this album
        $delete_photos = "DELETE FROM foto WHERE AlbumID = ?";
        $stmt = $conn->prepare($delete_photos);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();

        // Then, delete the album
        $delete_album = "DELETE FROM album WHERE AlbumID = ?";
        $stmt = $conn->prepare($delete_album);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();

        // If we got this far, commit the transaction
        $conn->commit();

        echo "<script>alert('Album dan semua foto di dalamnya berhasil dihapus.'); window.location.href = 'dashboard.php';</script>";
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        echo "Gagal menghapus album: " . $e->getMessage();
    }
} else {
    echo "ID album tidak diberikan.";
}

$conn->close();
?>
