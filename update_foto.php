<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fotoID = $_POST['fotoID'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    // Jika pengguna mengupload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = 'uploads/';
        $filename = $_FILES['foto']['name'];
        $target_file = $upload_dir . basename($filename);

        // Pindahkan file yang di-upload ke direktori tujuan
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Update query untuk judul, deskripsi, dan lokasi file
            $updateQuery = "UPDATE foto SET JudulFoto='$judul', DeskripsiFoto='$deskripsi', LokasiFile='$target_file' WHERE FotoID='$fotoID'";
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal mengunggah foto"]);
            exit;
        }
    } else {
        // Update hanya judul dan deskripsi jika tidak ada foto baru yang diupload
        $updateQuery = "UPDATE foto SET JudulFoto='$judul', DeskripsiFoto='$deskripsi' WHERE FotoID='$fotoID'";
    }

    // Jalankan query dan kirim response
    if ($conn->query($updateQuery) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
?>
