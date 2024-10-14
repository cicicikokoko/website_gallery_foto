<?php
include 'config.php';

if (isset($_GET['id'])) {
    $albumId = $_GET['id'];

    // Ambil data album
    $query = "SELECT * FROM album WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $albumId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $album = $result->fetch_assoc();
    } else {
        die("Album tidak ditemukan.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil ID album dari form
    $albumId = $_POST['albumId']; 
    $namaAlbum = $_POST['namaAlbum'];
    $Deskripsi = $_POST['Deskripsi'];

    // Update album
    $updateQuery = "UPDATE album SET NamaAlbum = ?, Deskripsi = ? WHERE AlbumID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $namaAlbum, $Deskripsi, $albumId);
    $updateStmt->execute();

    header("Location: dashboard.php");
    exit();
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
            <!-- Hidden input untuk menyimpan AlbumID -->
            <input type="hidden" name="albumId" value="<?php echo $albumId; ?>">

            <div class="mb-4">
                <label for="namaAlbum" class="block text-gray-700">Nama Album:</label>
                <input type="text" name="namaAlbum" id="namaAlbum" value="<?php echo htmlspecialchars($album['NamaAlbum']); ?>" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label for="Deskripsi" class="block text-gray-700">Deskripsi Album:</label>
                <textarea name="Deskripsi" id="Deskripsi" class="border rounded p-2 w-full" required><?php echo htmlspecialchars($album['Deskripsi']); ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
