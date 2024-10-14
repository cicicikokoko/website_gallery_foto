<?php
include 'config.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Periksa peran pengguna
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'; // Memastikan pemeriksaan role menggunakan huruf kecil

$albumID = isset($_GET['albumID']) ? intval($_GET['albumID']) : 0;

// Ambil informasi album berdasarkan AlbumID
$queryAlbum = "SELECT * FROM album WHERE AlbumID = ?";
$stmtAlbum = $conn->prepare($queryAlbum);
$stmtAlbum->bind_param("i", $albumID);
$stmtAlbum->execute();
$albumResult = $stmtAlbum->get_result();

if ($albumResult->num_rows == 0) {
    die("Album tidak ditemukan.");
}

$album = $albumResult->fetch_assoc();

// Ambil foto-foto dari album
$queryFotos = "SELECT * FROM foto WHERE AlbumID = ?";
$stmtFotos = $conn->prepare($queryFotos);
$stmtFotos->bind_param("i", $albumID);
$stmtFotos->execute();
$resultFotos = $stmtFotos->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($album['NamaAlbum']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .photo-container {
            position: relative;
            overflow: hidden;
        }

        .photo-container img {
            transition: transform 0.3s ease-in-out;
            display: block;
            width: 100%;
            height: 200px; /* Atur tinggi gambar */
            object-fit: cover; /* Mengatur gambar agar tetap proporsional */
        }

        .button-group {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px; /* Jarak antara tombol */
            z-index: 10; /* Pastikan tombol berada di atas elemen lain */
        }

        /* Modal Styles */
        .modal {
            display: none; /* Sembunyikan modal secara default */
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7); /* Latar belakang modal */
            justify-content: center;
            align-items: center;
            z-index: 1000; /* Z-index tinggi agar modal di atas konten lain */
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow-md py-4 mb-6">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($album['NamaAlbum']); ?></h1>
        <a href="profile.php?id=<?= $album['UserID']; ?>" class="text-gray-600 hover:text-gray-800" title="Kembali ke Profil">
            <i class="fas fa-arrow-left fa-2x"></i>
        </a>
    </div>
</header>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <h3 class="text-xl font-bold mt-6">Deskripsi Album</h3>
    <p><?php echo htmlspecialchars($album['Deskripsi']); ?></p>

    <h3 class="text-xl font-bold mt-6">Foto dalam Album</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <?php if ($resultFotos->num_rows > 0) {
            while ($foto = $resultFotos->fetch_assoc()) { ?>
                <div class="photo-container">
                    <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>">
                    <div class="button-group">
                        <button class="edit-button" title="Edit Foto" onclick="openEditModal(<?php echo $foto['FotoID']; ?>, '<?php echo addslashes(htmlspecialchars($foto['JudulFoto'])); ?>', '<?php echo addslashes(htmlspecialchars($foto['DeskripsiFoto'])); ?>')">
                            <i class="fas fa-edit fa-2x"></i>
                        </button>
                        <?php if ($isAdmin) { ?>
                            <button class="delete-button" title="Hapus Foto" onclick="openDeleteModal(<?php echo $foto['FotoID']; ?>, <?php echo $albumID; ?>)">
                                <i class="fas fa-trash fa-2x"></i>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            <?php }
        } else {
            echo "<p>Tidak ada foto di album ini.</p>";
        } ?>
    </div>

    <!-- Modal untuk Hapus Foto -->
    <div id="deleteModal" class="modal hidden">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-5">Konfirmasi Hapus</h2>
            <p>Apakah Anda yakin ingin menghapus foto ini?</p>
            <div class="mt-5">
                <button id="confirmDeleteButton" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700">Hapus</button>
                <button onclick="closeDeleteModal()" class="ml-2 bg-gray-300 text-black px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
            </div>
        </div>
    </div>

    <!-- Tombol untuk Upload Foto Baru -->
    <h3 class="text-xl font-bold mt-6">Upload Foto Baru</h3>
    <button onclick="openUploadModal()" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Upload Foto
    </button>

    <!-- Modal untuk Upload Foto -->
    <div id="uploadModal" class="modal hidden">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-5">Unggah Foto ke Album</h2>
            <form id="uploadForm" action="upload.php?albumID=<?php echo $albumID; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                    <input type="file" name="photo" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Unggah</button>
                    <button type="button" onclick="closeUploadModal()" class="ml-2 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Edit Foto -->
    <div id="editModal" class="modal hidden">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-5">Edit Foto</h2>
            <form id="editForm" action="editfoto.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="fotoID" id="editFotoID">
                <input type="hidden" name="albumID" value="<?php echo $albumID; ?>">
                <div>
                    <label class="block text-gray-700">Judul Foto:</label>
                    <input type="text" name="judulFoto" id="editJudulFoto" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700">Deskripsi Foto:</label>
                    <textarea name="deskripsiFoto" id="editDeskripsiFoto" required class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700">Unggah Foto Baru (opsional):</label>
                    <input type="file" name="photo" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                    <button type="button" onclick="closeEditModal()" class="ml-2 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(fotoID, albumID) {
        document.getElementById('confirmDeleteButton').onclick = function() {
            window.location.href = 'deletefoto.php?fotoID=' + fotoID + '&albumID=' + albumID; // Arahkan ke halaman hapus
        };
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function openEditModal(fotoID, judulFoto, deskripsiFoto) {
        document.getElementById('editFotoID').value = fotoID;
        document.getElementById('editJudulFoto').value = judulFoto;
        document.getElementById('editDeskripsiFoto').value = deskripsiFoto;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>

</body>
</html>
