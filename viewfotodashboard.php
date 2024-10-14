<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "galleryfoto");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $foto_id = $_GET['id'];

    // Query untuk mendapatkan data foto
    $query = "SELECT f.FotoID, f.JudulFoto, f.DeskripsiFoto, f.LokasiFile
              FROM foto f
              WHERE f.FotoID = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $foto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $foto = $result->fetch_assoc();

    if ($foto) {
        // Mengembalikan hanya judul foto dan gambar
        echo "<h3>" . htmlspecialchars($foto['JudulFoto']) . "</h3>";
        echo "<img src='" . htmlspecialchars($foto['LokasiFile']) . "' alt='" . htmlspecialchars($foto['JudulFoto']) . "' class='w-full'>";
    } else {
        echo "Foto tidak ditemukan.";
    }
} else {
    echo "ID foto tidak diberikan.";
}

$conn->close();
?>
