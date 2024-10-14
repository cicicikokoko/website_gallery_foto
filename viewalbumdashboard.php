<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "galleryfoto");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Dapatkan AlbumID dari parameter GET
$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;

// Query untuk mendapatkan data album
$query_album = "SELECT AlbumID, NamaAlbum, Deskripsi FROM album WHERE AlbumID = ?";
$stmt = $conn->prepare($query_album);
$stmt->bind_param("i", $album_id);
$stmt->execute();
$result_album = $stmt->get_result();

// Query untuk mendapatkan foto dalam album
$query_foto = "
    SELECT FotoID, JudulFoto, DeskripsiFoto, LokasiFile
    FROM foto
    WHERE AlbumID = ?
";
$stmt_foto = $conn->prepare($query_foto);
$stmt_foto->bind_param("i", $album_id);
$stmt_foto->execute();
$result_foto = $stmt_foto->get_result();

$album = $result_album->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['NamaAlbum']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <a href="dashboard.php" class="text-blue-500 hover:text-blue-600 flex items-center mb-4">
            <i class="fas fa-arrow-left text-xl"></i>
            <span class="ml-2">Kembali</span>
        </a>

        <h1 class="text-3xl font-bold mb-6 text-center"><?php echo htmlspecialchars($album['NamaAlbum']); ?></h1>
        <p class="text-center mb-6"><?php echo htmlspecialchars($album['Deskripsi']); ?></p>

        <h2 class="text-2xl font-semibold mb-4">Foto dalam Album</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php while ($foto = $result_foto->fetch_assoc()) { ?>
                <div class="border rounded overflow-hidden shadow-lg">
                    <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold"><?php echo htmlspecialchars($foto['JudulFoto']); ?></h3>
                        <p><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>
</body>
</html>

<?php
$stmt->close();
$stmt_foto->close();
$conn->close();
?>
