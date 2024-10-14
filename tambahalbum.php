<?php
include 'config.php';
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username']) || !isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

// Ambil userID dari session login
$userID = $_SESSION['userID'];

// Get the redirect URL from the parameter, default to profile.php if not set
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : "profile.php?id=$userID";

// Menambahkan album baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['namaAlbum'])) {
    $namaAlbum = $_POST['namaAlbum'];
    $Deskripsi = $_POST['Deskripsi'];

    // Masukkan album ke dalam tabel album
    $albumSql = "INSERT INTO album (NamaAlbum, Deskripsi, UserID, TanggalDibuat) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($albumSql);
    $stmt->bind_param("ssi", $namaAlbum, $Deskripsi, $userID);

    if ($stmt->execute()) {
        $albumID = $conn->insert_id;  // Ambil ID album yang baru dibuat

        // Mengupload foto jika ada
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            // ... [rest of the photo upload code remains the same]
        }

        // Redirect to the specified page
        header("Location: $redirect");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Album dan Unggah Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white p-5 shadow-lg rounded">
        <h2 class="text-xl font-bold mb-5">Buat Album dan Unggah Foto</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-gray-700">Nama Album:</label>
                <input type="text" name="namaAlbum" placeholder="Nama Album" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700">Deskripsi Album:</label>
                <textarea name="Deskripsi" placeholder="Deskripsi Album" required class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-gray-700">Unggah Foto:</label>
                <input type="file" name="photo" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Buat Album</button>
            </div>
        </form>
    </div>
</body>
</html>
