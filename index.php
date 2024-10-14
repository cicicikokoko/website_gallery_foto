<?php
include 'config.php';
session_start();

// Ambil userID dan role dari session jika sudah login
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;

$query = "SELECT foto.*, user.Username, 
       COUNT(likefoto.LikeID) as Totallikefoto, 
       (SELECT GROUP_CONCAT(CONCAT(u.Username, ': ', k.IsiKomentar) SEPARATOR '||') 
        FROM komentarfoto k 
        JOIN user u ON k.UserID = u.UserID
        WHERE k.FotoID = foto.FotoID) as komentar,
       (SELECT GROUP_CONCAT(k.TanggalKomentar SEPARATOR '||') 
        FROM komentarfoto k 
        WHERE k.FotoID = foto.FotoID) as TanggalKomentar,
       (SELECT COUNT(k.KomentarID) 
        FROM komentarfoto k 
        WHERE k.FotoID = foto.FotoID) as TotalKomentar,
       (SELECT COUNT(likefoto.LikeID) 
        FROM likefoto 
        WHERE likefoto.FotoID = foto.FotoID AND likefoto.UserID = ?) as UserLiked 
FROM foto 
JOIN user ON foto.UserID = user.UserID
LEFT JOIN likefoto ON foto.FotoID = likefoto.FotoID
GROUP BY foto.FotoID";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID); // Masukkan userID yang login
$stmt->execute();
$result = $stmt->get_result();

// Proses Like/Unlike (Hanya jika login)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fotoID']) && isset($_POST['action'])) {
    if ($userID) {
        $fotoID = $_POST['fotoID'];
        if ($_POST['action'] === 'like') {
            // Proses like/unlike jika pengguna sudah login
            $query = "SELECT * FROM likefoto WHERE FotoID = ? AND UserID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $fotoID, $userID);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                // Jika sudah like, maka unlike
                $query = "DELETE FROM likefoto WHERE FotoID = ? AND UserID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $fotoID, $userID);
                $stmt->execute();
            } else {
                // Jika belum like, maka like
                $query = "INSERT INTO likefoto (FotoID, UserID, TanggalLike) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $fotoID, $userID);
                $stmt->execute();
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('Silakan login untuk memberi like.');</script>";
    }
    header("Location: index.php");
    exit;
}

// Proses komentar (Hanya jika login)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fotoID']) && isset($_POST['isiKomentar'])) {
    if ($userID) {
        $fotoID = $_POST['fotoID'];
        $isiKomentar = $_POST['isiKomentar'];
    
        // Insert komentar ke dalam database
        $query = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $fotoID, $userID, $isiKomentar);
        $stmt->execute();
        $stmt->close();
    
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Silakan login untuk mengirim komentar.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Galeri Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .photo-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .photo-box {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .icon-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #262626;
        }

        .icon-button i {
            font-size: 24px;
        }

        .icon-button:hover {
            opacity: 0.7;
        }

        .icon-button.like i.liked {
            color: #ed4956; /* Warna merah untuk like yang aktif */
        }

        .icon-button.comment i {
            color: #262626;
        }

        .comment-section {
            margin-top: 1rem;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.8); /* Black w/ opacity */
        }

        .modal-content {
            margin: 15% auto; /* 15% from the top and centered */
            display: block;
            max-width: 90%; /* Set a maximum width */
            max-height: 80%; /* Set a maximum height */
        }

        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header dengan ikon profile dan logout -->
    <header class="bg-white shadow-md py-4 mb-6">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-3xl font-bold">Galeri Foto</h1>
        <div class="relative">
            <?php if (isset($_SESSION['username'])): ?>
                <!-- Tampilkan dropdown jika user sudah login -->
                <button id="dropdownButton" class="flex items-center text-gray-600 hover:text-gray-800">
                    <i class="fas fa-user fa-lg mr-2"></i>
                  
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>

                <!-- Dropdown menu -->
                <div id="dropdownMenu" class="absolute right-0 z-10 hidden bg-white shadow-md rounded mt-2">
                    <a href="profile.php?id=<?= $_SESSION['userID'] ?>" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-user fa-lg mr-2"></i> Profile
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-tachometer-alt fa-lg mr-2"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>

            <?php else: ?>
                <!-- Jika user belum login, tampilkan tombol Login -->
                <a href="login.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-sign-in-alt fa-lg mr-2"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menampilkan Username Pengguna (jika login) atau sembunyikan -->
    <div class="container mx-auto text-right mb-2">
        <?php if (isset($_SESSION['username'])): ?>
            <span class="text-gray-600">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <?php endif; ?>
    </div>
</header>


    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php while ($foto = $result->fetch_assoc()) { ?>
                <div class="bg-white shadow-md rounded-lg p-4 photo-box relative">
                    <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" class="rounded-lg w-full h-48 object-cover">
                    <h2 class="text-xl font-bold mt-4"><?php echo $foto['JudulFoto']; ?></h2>
                    <p class="text-gray-600"><?php echo $foto['DeskripsiFoto']; ?></p>
                    <a href="profile.php?id=<?php echo $foto['UserID']; ?>" class="text-sm text-gray-500">Diunggah oleh <?php echo $foto['Username']; ?></a>

                    <div class="mt-4 flex items-center">
<!-- Tombol Like -->
<form action="index.php" method="post">
                            <input type="hidden" name="fotoID" value="<?php echo $foto['FotoID']; ?>">
                            <input type="hidden" name="action" value="like">
                            <?php if ($userID) { ?>
                                <button type="submit" class="icon-button like">
                                    <i class="fas fa-heart <?php echo ($foto['UserLiked'] > 0) ? 'liked' : ''; ?>"></i>
                                    <?php echo $foto['Totallikefoto']; ?>
                                </button>
                            <?php } else { ?>
                                <button type="button" class="icon-button like" onclick="alert('Silakan login untuk memberi like.')">
                                    <i class="fas fa-heart"></i>
                                    <?php echo $foto['Totallikefoto']; ?>
                                </button>
                            <?php } ?>
                        </form>       
                        <!-- Tombol Komentar -->
                        <button type="button" class="icon-button comment ml-4" onclick="toggleCommentForm(<?php echo $foto['FotoID']; ?>)">
                            <i class="fas fa-comment"></i>
                            <?php echo $foto['TotalKomentar']; ?>
                        </button>
                    </div>

                    <!-- Form Komentar dan Tampilkan Komentar -->
                    <div id="commentForm<?php echo $foto['FotoID']; ?>" class="hidden mt-4">
                        <?php if ($userID) { ?>
                            <form action="index.php" method="post">
                                <input type="hidden" name="fotoID" value="<?php echo $foto['FotoID']; ?>">
                                <textarea name="isiKomentar" rows="2" class="w-full p-2 border border-gray-300 rounded" placeholder="Tulis komentar..."></textarea>
                                <button type="submit" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Kirim</button>
                            </form>
                        <?php } else { ?>
                            <p class="text-gray-500">Silakan login untuk menulis komentar.</p>
                        <?php } ?>
                    </div>

                   <!-- Tampilkan Komentar -->
<div class="comment-section mt-4">
    <?php
    $komentarArr = explode('||', $foto['komentar']);
    $tanggalKomentarArr = explode('||', $foto['TanggalKomentar']);
    foreach ($komentarArr as $index => $komentar) {
        echo '<p class="text-gray-600"><strong>' . htmlspecialchars($komentar) . '</strong> - <small>' . htmlspecialchars($tanggalKomentarArr[$index]) . '</small></p>';
    }
    ?>
</div>

                </div>
            <?php } ?>
        </div>
    </div>


    <!-- Modal untuk menampilkan gambar besar -->
    <div id="modal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage" src="">
        <div id="modalCaption"></div>
    </div>

    <script>

        function toggleCommentForm(fotoID) {
            var form = document.getElementById('commentForm' + fotoID);
            form.classList.toggle('hidden');
        }

        // Fungsi untuk membuka modal
        function openModal(src, caption) {
            const modal = document.getElementById("modal");
            const modalImg = document.getElementById("modalImage");
            const modalCaption = document.getElementById("modalCaption");
            modal.style.display = "block";
            modalImg.src = src;
            modalCaption.innerHTML = caption;
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            const modal = document.getElementById("modal");
            modal.style.display = "none";
        }

        // Menangani klik pada tombol komentar
        document.querySelectorAll('.comment-button').forEach(button => {
            button.addEventListener('click', function() {
                const fotoID = this.getAttribute('data-foto-id');
                const commentForm = this.closest('.photo-box').querySelector('.comment-form');
                commentForm.classList.toggle('hidden'); // Toggle visibility
            });
        });

        // Menangani dropdown menu
        const dropdownButton = document.getElementById("dropdownButton");
        const dropdownMenu = document.getElementById("dropdownMenu");
        dropdownButton.addEventListener("click", function() {
            dropdownMenu.classList.toggle("hidden");
        });

        // Menutup dropdown jika klik di luar
        window.onclick = function(event) {
            if (!event.target.matches('#dropdownButton')) {
                if (!dropdownMenu.classList.contains('hidden')) {
                    dropdownMenu.classList.add('hidden');
                }
            }
        }
    </script>
</body>
</html>
