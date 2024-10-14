<?php
include 'config.php';

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
