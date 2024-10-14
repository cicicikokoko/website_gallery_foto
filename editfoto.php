<?php
include 'config.php';
session_start();

echo "User ID dari session: " . $_SESSION['userID']; // Tambahkan ini untuk debugging


// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Periksa peran pengguna (admin/user)
$isAdmin = $_SESSION['role'] === 'admin';

// Ambil FotoID dan AlbumID dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fotoID = isset($_POST['fotoID']) ? intval($_POST['fotoID']) : 0;
    $albumID = isset($_POST['albumID']) ? intval($_POST['albumID']) : 0;
    $judulFoto = $_POST['judulFoto'];
    $deskripsiFoto = $_POST['deskripsiFoto'];

    // Jika bukan admin, pastikan pengguna hanya mengedit fotonya sendiri
 // Jika bukan admin, pastikan pengguna hanya mengedit fotonya sendiri
if (!$isAdmin) {
    // Query untuk memeriksa apakah foto tersebut milik user yang sedang login
    $queryCheckOwner = "SELECT * FROM foto WHERE FotoID = ? AND UserID = ?";
    $stmtCheckOwner = $conn->prepare($queryCheckOwner);
    $stmtCheckOwner->bind_param("ii", $fotoID, $_SESSION['userID']); // Menggunakan userID yang benar
    $stmtCheckOwner->execute();
    $resultOwner = $stmtCheckOwner->get_result();

    // Jika tidak ditemukan, berarti user tidak memiliki hak untuk mengedit
    if ($resultOwner->num_rows === 0) {
        die("Anda tidak memiliki izin untuk mengedit foto ini.");
    }
}


    // Update judul dan deskripsi foto
    $updateQuery = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ? WHERE FotoID = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("ssi", $judulFoto, $deskripsiFoto, $fotoID);

    // Jalankan query update
    if ($stmtUpdate->execute()) {
        // Periksa apakah ada file baru yang diupload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['photo']['tmp_name'];
            $fileName = $_FILES['photo']['name'];
            $fileSize = $_FILES['photo']['size'];
            $fileType = $_FILES['photo']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Cek ekstensi file yang valid
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Ambil lokasi file lama dari database
                $queryFetchOldFile = "SELECT LokasiFile FROM foto WHERE FotoID = ?";
                $stmtFetchOldFile = $conn->prepare($queryFetchOldFile);
                $stmtFetchOldFile->bind_param("i", $fotoID);
                $stmtFetchOldFile->execute();
                $resultOldFile = $stmtFetchOldFile->get_result();
                $oldFilePath = '';
                
                if ($resultOldFile->num_rows > 0) {
                    $oldFileData = $resultOldFile->fetch_assoc();
                    $oldFilePath = $oldFileData['LokasiFile'];
                }

                // Tentukan lokasi penyimpanan file baru
                $uploadFileDir = './uploads/';
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Menghindari duplikasi nama file
                $dest_path = $uploadFileDir . $newFileName;

                // Pindahkan file ke direktori tujuan
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Update lokasi file di database
                    $updateFotoQuery = "UPDATE foto SET LokasiFile = ? WHERE FotoID = ?";
                    $stmtUpdateFile = $conn->prepare($updateFotoQuery);
                    $stmtUpdateFile->bind_param("si", $dest_path, $fotoID);
                    $stmtUpdateFile->execute();

                    // Hapus file lama jika ada
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                } else {
                    echo "Terjadi kesalahan saat mengupload file.";
                }
            } else {
                echo "Ekstensi file tidak diperbolehkan.";
            }
        }

        // Umpan balik sukses
        $_SESSION['message'] = "Foto berhasil diperbarui.";
        header("Location: album.php?albumID=" . $albumID);
        exit;
    } else {
        echo "Gagal memperbarui foto: " . $stmtUpdate->error;
    }
}
?>
