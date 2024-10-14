<?php
include 'config.php';
session_start();

// Ambil parameter ID dari URL
if (!isset($_GET['id'])) {
    die("ID pengguna tidak ditemukan.");
}

$userID = intval($_GET['id']); // Pastikan bahwa ID adalah integer

// Ambil informasi pengguna berdasarkan UserID
$query = "SELECT * FROM user WHERE UserID = $userID";
$userResult = $conn->query($query);

if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
} else {
    die("Pengguna tidak ditemukan.");
}

// Ambil album pengguna berdasarkan UserID dari URL
$queryAlbums = "SELECT * FROM album WHERE UserID = $userID";
$resultAlbums = $conn->query($queryAlbums);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil - <?php echo htmlspecialchars($user['Username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.4); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
        }

        .profile-image {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        .photo-container {
            position: relative;
            overflow: hidden;
        }

        .photo-container img {
            transition: transform 0.3s ease-in-out;
        }

        .photo-container:hover img {
            transform: scale(1.1);
        }

        .icon-button {
            background: transparent;
            border: none;
            color: white;
            cursor: pointer;
            margin: 0 5px;
        }

        .album-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .album-card:hover {
            transform: scale(1.05);
            cursor: pointer;
        }

        .album-photo {
            width: 100%;
            height: 100px; /* Ubah ukuran sesuai kebutuhan */
            object-fit: cover;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-white shadow-md py-4 mb-6">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold">Profil</h1>
            <div class="flex space-x-4">
                <a href="index.php" class="text-gray-600 hover:text-gray-800" title="Kembali ke Beranda">
                    <i class="fas fa-arrow-left fa-2x"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center">
            <img src="<?php echo isset($user['FotoProfil']) && !empty($user['FotoProfil']) ? htmlspecialchars($user['FotoProfil']) : 'default_profile.png'; ?>" alt="Foto Profil" class="profile-image">
            <div class="ml-4">
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['Username']); ?></h2>
                <p class="text-gray-600">Email: <?php echo htmlspecialchars($user['Email']); ?></p>
                <?php if (isset($user['Nama'])) { ?>
                    <p class="text-gray-600">Nama: <?php echo htmlspecialchars($user['Nama']); ?></p>
                <?php } ?>
                <?php if (isset($user['Bio'])) { ?>
                    <p class="text-gray-600">Bio: <?php echo htmlspecialchars($user['Bio']); ?></p>
                <?php } ?>
            </div>
        </div>

        <h3 class="text-xl font-bold mt-6">Album</h3>
        <div class="mt-4">
            <?php if ($_SESSION['username'] == $user['Username']) { ?>
                <a href="tambahalbum.php" class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus"></i> Tambah Album
                </a>
            <?php } ?>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($resultAlbums->num_rows > 0) {
                while ($album = $resultAlbums->fetch_assoc()) { 
                    // Ambil foto berdasarkan AlbumID
                    $albumID = $album['AlbumID'];
                    $queryFotos = "SELECT * FROM foto WHERE AlbumID = $albumID";
                    $resultFotos = $conn->query($queryFotos);
            ?>
                <div class="album-card" onclick="window.location.href='album.php?albumID=<?php echo $album['AlbumID']; ?>'">
                    <h4 class="font-bold"><?php echo htmlspecialchars($album['NamaAlbum']); ?></h4>
                    <p><?php echo htmlspecialchars($album['Deskripsi']); ?></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-2">
                        <?php if ($resultFotos->num_rows > 0) {
                            while ($foto = $resultFotos->fetch_assoc()) { ?>
                                <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" class="album-photo">
                            <?php }
                        } else {
                            echo "<p>Tidak ada foto dalam album ini.</p>";
                        } ?>
                    </div>
                </div>
            <?php }
            } else {
                echo "<p>Tidak ada album yang tersedia.</p>";
            } ?>
        </div>
    </div>

</body>
</html>
