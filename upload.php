<?php
include 'config.php';
session_start();

// Cek apakah pengguna sudah login, jika belum, arahkan ke halaman login
if (!isset($_SESSION['username']) || !isset($_SESSION['userID'])) {
    header("Location: login.php");  // Arahkan ke halaman login jika belum login
    exit;
}

// Ambil userID dari session login
$userID = $_SESSION['userID'];

// Ambil AlbumID dari parameter URL
if (!isset($_GET['albumID'])) {
    die("AlbumID tidak ditemukan.");
}

$albumID = $_GET['albumID'];

// Menambahkan foto ke album
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judulFoto = $_POST['judulFoto'];
    $deskripsiFoto = $_POST['deskripsiFoto'];
    
    // Pastikan folder uploads sudah ada
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);  // Membuat folder uploads jika belum ada
    }

    $filename = $_FILES['photo']['name'];
    $target_file = $upload_dir . basename($filename);

    // Mengecek apakah file berhasil diupload dari tmp folder ke folder tujuan
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        // Masukkan data foto ke dalam tabel foto
        $sql = "INSERT INTO foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID) 
                VALUES (?, ?, NOW(), ?, ?, ?)";
        
        $stmtInsert = $conn->prepare($sql);
        $stmtInsert->bind_param("ssssi", $judulFoto, $deskripsiFoto, $target_file, $albumID, $userID);
        
        // Jalankan query dan cek hasilnya
        if ($stmtInsert->execute()) {
            // Foto berhasil diunggah, arahkan ke halaman album
            header("Location: album.php?albumID=" . $albumID);
            exit();  // Pastikan kode berhenti di sini setelah redirect
        } else {
            echo "Error: " . $stmtInsert->error;
        }
    } else {
        echo "Gagal mengunggah file!";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Unggah Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white p-5 shadow-lg rounded">
        <h2 class="text-xl font-bold mb-5">Unggah Foto ke Album</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-gray-700">Judul Foto:</label>
                <input type="text" name="judulFoto" placeholder="Judul Foto" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700">Deskripsi Foto:</label>
                <textarea name="deskripsiFoto" placeholder="Deskripsi Foto" required class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-gray-700">Unggah Foto:</label>
                <input type="file" name="photo" accept="image/*" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Unggah</button>
            </div>
        </form>
    </div>
</body>
</html>
