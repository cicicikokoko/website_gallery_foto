<?php
include 'config.php';

if (isset($_GET['id'])) {
    $foto_id = $_GET['id'];

    // Query untuk mendapatkan data foto
    $query = "SELECT f.FotoID, f.JudulFoto, f.DeskripsiFoto, f.LokasiFile, a.AlbumID, a.NamaAlbum
              FROM foto f
              JOIN album a ON f.AlbumID = a.AlbumID
              WHERE f.FotoID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $foto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $foto = $result->fetch_assoc();

    if (!$foto) {
        echo "Foto tidak ditemukan.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $foto_id = $_POST['foto_id'];
    $judul_foto = $_POST['judul_foto'];
    $deskripsi_foto = $_POST['deskripsi_foto'];

    $update_query = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ? WHERE FotoID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $judul_foto, $deskripsi_foto, $foto_id);

    if ($stmt->execute()) {
        echo "<script>alert('Foto berhasil diperbarui.'); window.location.href = 'dashboard.php';</script>";
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
    <title>Edit Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Edit Foto</h1>
        <form method="POST">
            <input type="hidden" name="foto_id" value="<?php echo $foto['FotoID']; ?>">
            <div class="mb-4">
                <label for="judul_foto" class="block text-gray-700">Judul Foto:</label>
                <input type="text" name="judul_foto" id="judul_foto" value="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi_foto" class="block text-gray-700">Deskripsi Foto:</label>
                <textarea name="deskripsi_foto" id="deskripsi_foto" class="border rounded p-2 w-full" required><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
