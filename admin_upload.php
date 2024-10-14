<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "galleryfoto");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek apakah user sudah login
if (!isset($_SESSION['userID'])) {
    die("Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['userID'];

// Verifikasi apakah user_id ada di database
$user_check_query = "SELECT UserID FROM user WHERE UserID = ?";
$user_check_stmt = $conn->prepare($user_check_query);
$user_check_stmt->bind_param("i", $user_id);
$user_check_stmt->execute();
$user_check_result = $user_check_stmt->get_result();

if ($user_check_result->num_rows === 0) {
    die("User tidak valid.");
}

$user_check_stmt->close();

// Query untuk mendapatkan daftar album
$query_album = "SELECT AlbumID, NamaAlbum FROM album WHERE UserID = ?";
$stmt_album = $conn->prepare($query_album);
$stmt_album->bind_param("i", $user_id);
$stmt_album->execute();
$result_album = $stmt_album->get_result();

$upload_error = '';

// Proses upload foto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul_foto = $_POST['judul_foto'];
    $deskripsi_foto = $_POST['deskripsi_foto'];
    $album_id = $_POST['album_id'];

    // Verifikasi apakah album milik user ini
    $album_check_query = "SELECT AlbumID FROM album WHERE AlbumID = ? AND UserID = ?";
    $album_check_stmt = $conn->prepare($album_check_query);
    $album_check_stmt->bind_param("ii", $album_id, $user_id);
    $album_check_stmt->execute();
    $album_check_result = $album_check_stmt->get_result();

    if ($album_check_result->num_rows === 0) {
        $upload_error = "Album tidak valid atau bukan milik Anda.";
    } else {
        // Proses upload file
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["file_foto"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["file_foto"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $upload_error = "File bukan gambar. ";
            $uploadOk = 0;
        }

        // Cek ukuran file
        // if ($_FILES["file_foto"]["size"] > 500000) {
        //     $upload_error .= "Maaf, file terlalu besar. ";
        //     $uploadOk = 0;
        // }

        // Izinkan format file tertentu
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $upload_error .= "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan. ";
            $uploadOk = 0;
        }

        // Jika tidak ada error, lakukan upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["file_foto"]["tmp_name"], $target_file)) {
                // Insert data ke database
                $sql = "INSERT INTO foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID)
                        VALUES (?, ?, NOW(), ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssii", $judul_foto, $deskripsi_foto, $target_file, $album_id, $user_id);

                if ($stmt->execute()) {
                    $upload_error = "Foto berhasil diunggah.";
                } else {
                    $upload_error = "Maaf, terjadi kesalahan saat mengunggah foto ke database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $upload_error = "Maaf, terjadi kesalahan saat memindahkan file yang diunggah. Error: " . error_get_last()['message'];
            }
        }
    }
    $album_check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Upload Foto</h1>
        <?php
        if (!empty($upload_error)) {
            $alertClass = strpos($upload_error, "berhasil") !== false ? "bg-green-100 border-green-400 text-green-700" : "bg-red-100 border-red-400 text-red-700";
            echo "<div class='$alertClass px-4 py-3 rounded relative mb-4' role='alert'>";
            echo "<strong class='font-bold'>Pesan:</strong>";
            echo "<span class='block sm:inline'> $upload_error</span>";
            echo "</div>";
        }
        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="judul_foto">
                    Judul Foto
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="judul_foto" type="text" name="judul_foto" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi_foto">
                    Deskripsi Foto
                </label>
                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="deskripsi_foto" name="deskripsi_foto" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="album_id">
                    Pilih Album
                </label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="album_id" name="album_id" required>
                    <?php
                    while ($row = $result_album->fetch_assoc()) {
                        echo "<option value='" . $row['AlbumID'] . "'>" . htmlspecialchars($row['NamaAlbum']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="file_foto">
                    Pilih Foto
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="file_foto" type="file" name="file_foto" accept="image/*" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Upload
                </button>
                <a href="dashboard.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Kembali ke Dashboard
                </a>
            </div>
        </form>
    </div>
</body>
</html>
