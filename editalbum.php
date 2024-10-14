<?php
include 'config.php';

if (isset($_GET['id'])) {
    $album_id = $_GET['id'];

    $query = "SELECT * FROM album WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $album_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $album = $result->fetch_assoc();

    if (!$album) {
        echo "Album tidak ditemukan.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $album_id = $_POST['album_id'];
    $nama_album = $_POST['nama_album'];
    $deskripsi = $_POST['deskripsi'];

    $update_query = "UPDATE album SET NamaAlbum = ?, Deskripsi = ? WHERE AlbumID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $nama_album, $deskripsi, $album_id);

    if ($stmt->execute()) {
        echo "<script>alert('Album berhasil diperbarui.'); window.location.href = 'dashboard.php';</script>";
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Edit Album</h1>
        <form method="POST">
            <input type="hidden" name="album_id" value="<?php echo $album['AlbumID']; ?>">
            <div class="mb-4">
                <label for="nama_album" class="block text-gray-700">Nama Album:</label>
                <input type="text" name="nama_album" id="nama_album" value="<?php echo htmlspecialchars($album['NamaAlbum']); ?>" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="block text-gray-700">Deskripsi Album:</label>
                <textarea name="deskripsi" id="deskripsi" class="border rounded p-2 w-full" required><?php echo htmlspecialchars($album['Deskripsi']); ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
