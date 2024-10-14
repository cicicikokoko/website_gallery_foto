<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "galleryfoto");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mendapatkan data album
$query_album = "SELECT AlbumID, NamaAlbum, Deskripsi FROM album";
$result_album = $conn->query($query_album);

// Query untuk mendapatkan data foto
$query_foto = "
    SELECT f.FotoID, f.JudulFoto, f.DeskripsiFoto, f.LokasiFile, a.NamaAlbum
    FROM foto f
    JOIN album a ON f.AlbumID = a.AlbumID
";
$result_foto = $conn->query($query_foto);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
    </style>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function loadData(id, type) {
    let url;

    if (type === 'view') {
        url = 'viewfotodashboard.php?id=' + id;
    } else if (type === 'viewalbum') {
        url = 'viewalbumdashboard.php?id=' + id; // New endpoint for viewing album
    } else if (type === 'edit') {
        url = 'editfotodashboard.php?id=' + id;
    } else if (type === 'hapus') {
        url = 'hapusfotodashboard.php?id=' + id;
    }

    if (url) {
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modal-body').innerHTML = data;
            });
    }
}


        function searchAlbum() {
            const input = document.getElementById('searchAlbumInput').value.toLowerCase();
            const rows = document.querySelectorAll('#albumTable tbody tr');
            rows.forEach(row => {
                const cells = row.cells;
                const albumId = cells[0].textContent.toLowerCase(); // ID
                const albumName = cells[1].textContent.toLowerCase(); // Nama Album
                const albumDescription = cells[2].textContent.toLowerCase(); // Deskripsi

                // Cek apakah input ada dalam ID, Nama Album, atau Deskripsi
                row.style.display = albumId.includes(input) || albumName.includes(input) || albumDescription.includes(input) ? '' : 'none';
            });
        }

        function searchFoto() {
            const input = document.getElementById('searchFotoInput').value.toLowerCase();
            const rows = document.querySelectorAll('#fotoTable tbody tr');
            rows.forEach(row => {
                const cells = row.cells;
                const fotoId = cells[0].textContent.toLowerCase(); // ID Foto
                const fotoTitle = cells[1].textContent.toLowerCase(); // Judul Foto
                const fotoDescription = cells[2].textContent.toLowerCase(); // Deskripsi Foto

                // Cek apakah input ada dalam ID Foto, Judul Foto, atau Deskripsi Foto
                row.style.display = fotoId.includes(input) || fotoTitle.includes(input) || fotoDescription.includes(input) ? '' : 'none';
            });
        }

        function printFoto(lokasiFile) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Cetak Foto</title>');
            printWindow.document.write('<style>body { margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #fff;} img { width: 100%; height: auto; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<img src="' + lokasiFile + '" alt="Foto" />');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }

        function uploadAlbum() {
            const nama_album = prompt("Masukkan nama album:");
            const deskripsi_album = prompt("Masukkan deskripsi album:");

            if (nama_album && deskripsi_album) {
                const formData = new FormData();
                formData.append('nama_album', nama_album);
                formData.append('deskripsi', deskripsi_album);

                // Kirim data menggunakan Fetch API
                fetch('tambahalbum.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Tampilkan respons
                    document.getElementById('response-album').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
            } else {
                alert("Nama album dan deskripsi tidak boleh kosong!");
            }
        }

        function uploadFoto() {
            const judul_foto = prompt("Masukkan judul foto:");
            const deskripsi_foto = prompt("Masukkan deskripsi foto:");
            const file_foto = document.getElementById('file_foto').files[0];

            if (judul_foto && deskripsi_foto && file_foto) {
                const formData = new FormData();
                formData.append('judul_foto', judul_foto);
                formData.append('deskripsi', deskripsi_foto);
                formData.append('file_foto', file_foto);

                // Kirim data menggunakan Fetch API
                fetch('upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Tampilkan respons
                    document.getElementById('response-foto').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
            } else {
                alert("Judul, deskripsi, dan gambar tidak boleh kosong!");
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <a href="index.php" class="text-blue-500 hover:text-blue-600 flex items-center">
            <i class="fas fa-arrow-left text-xl"></i>
            <span class="ml-2">Kembali</span>
        </a>

        <h1 class="text-3xl font-bold mb-6 text-center">Dashboard Admin</h1>

        <!-- Upload Album Button -->
        <div class="mb-4">
        <a href="tambahalbum.php?redirect=dashboard.php" class="bg-blue-500 text-white px-4 py-2 rounded">Upload Album</a>
            <input type="text" id="searchAlbumInput" placeholder="Cari Album" onkeyup="searchAlbum()" class="border px-2 py-1 rounded ml-4">
        </div>

        <h2 class="text-2xl font-semibold mb-4">Daftar Album</h2>
        <table id="albumTable" class="min-w-full bg-white border border-gray-300 mb-6">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">ID</th>
                    <th class="border border-gray-300 px-4 py-2">Nama Album</th>
                    <th class="border border-gray-300 px-4 py-2">Deskripsi</th>
                    <th class="border border-gray-300 px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($album = $result_album->fetch_assoc()) { ?>
    <tr>
        <td class="border border-gray-300 px-4 py-2"><?php echo $album['AlbumID']; ?></td>
        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($album['NamaAlbum']); ?></td>
        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($album['Deskripsi']); ?></td>
        <td class="border border-gray-300 px-4 py-2">
    <a href="viewalbumdashboard.php?album_id=<?php echo $album['AlbumID']; ?>" class="text-blue-500 hover:underline">View</a> |
    <a href="editalbum.php?id=<?php echo $album['AlbumID']; ?>" class="text-yellow-500 hover:underline">Edit</a> |
    <a href="hapusalbum.php?id=<?php echo $album['AlbumID']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus album ini beserta semua fotonya?');">Hapus</a>
</td>
    </tr>
<?php } ?>

            </tbody>
        </table>

        <!-- Upload Foto Button -->
        <div class="mb-4">
    <a href="admin_upload.php" class="bg-blue-500 text-white px-4 py-2 rounded inline-block">Upload Foto</a>
    <input type="text" id="searchFotoInput" placeholder="Cari Foto" onkeyup="searchFoto()" class="border px-2 py-1 rounded ml-4">
</div>

        <h2 class="text-2xl font-semibold mb-4">Daftar Foto</h2>
        <table id="fotoTable" class="min-w-full bg-white border border-gray-300 mb-6">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">ID Foto</th>
                    <th class="border border-gray-300 px-4 py-2">Judul Foto</th>
                    <th class="border border-gray-300 px-4 py-2">Deskripsi Foto</th>
                    <th class="border border-gray-300 px-4 py-2">Foto</th>
                    <th class="border border-gray-300 px-4 py-2">Album</th>
                    <th class="border border-gray-300 px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php while ($foto = $result_foto->fetch_assoc()) { ?>
        <tr>
            <td class="border border-gray-300 px-4 py-2"><?php echo $foto['FotoID']; ?></td>
            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($foto['JudulFoto']); ?></td>
            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></td>
            <td class="border border-gray-300 px-4 py-2">
                            <img src="<?php echo $foto['LokasiFile']; ?>" alt="Thumbnail" class="w-16 h-16 object-cover">
                        </td>
            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($foto['NamaAlbum']); ?></td>
            <td class="border border-gray-300 px-4 py-2">
                <button onclick="openModal('modal-view'); loadData(<?php echo $foto['FotoID']; ?>, 'view');" class="text-blue-500 hover:underline">View</button> |
                <a href="admin_edit.php?id=<?php echo $foto['FotoID']; ?>" class="text-yellow-500 hover:underline">Edit</a> |
                <a href="admin_delete.php?id=<?php echo $foto['FotoID']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?');">Hapus</a> |
                <button onclick="printFoto('<?php echo $foto['LokasiFile']; ?>')" class="text-green-500 hover:underline">Cetak</button>
            </td>
        </tr>
    <?php } ?>
</tbody>

        </table>

        <!-- Modal untuk Upload Album -->
        <div id="modal-upload-album" class="modal">
            <div class="modal-content">
                <span onclick="closeModal('modal-upload-album')" class="cursor-pointer float-right text-red-500">×</span>
                <h2 class="text-xl font-bold mb-4">Upload Album</h2>
                <form id="uploadAlbumForm">
                    <div class="mb-4">
                        <label for="nama_album" class="block mb-2">Nama Album:</label>
                        <input type="text" id="nama_album" name="nama_album" class="border px-2 py-1 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="deskripsi" class="block mb-2">Deskripsi:</label>
                        <textarea id="deskripsi" name="deskripsi" class="border px-2 py-1 w-full" required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
                    <div id="response-album" class="mt-4"></div>
                </form>
            </div>
        </div>

        <!-- Modal untuk Upload Foto -->
        <div id="modal-upload-foto" class="modal">
            <div class="modal-content">
                <span onclick="closeModal('modal-upload-foto')" class="cursor-pointer float-right text-red-500">×</span>
                <h2 class="text-xl font-bold mb-4">Upload Foto</h2>
                <form id="uploadFotoForm">
                    <div class="mb-4">
                        <label for="judul_foto" class="block mb-2">Judul Foto:</label>
                        <input type="text" id="judul_foto" name="judul_foto" class="border px-2 py-1 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="deskripsi_foto" class="block mb-2">Deskripsi Foto:</label>
                        <textarea id="deskripsi_foto" name="deskripsi_foto" class="border px-2 py-1 w-full" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="file_foto" class="block mb-2">Pilih Foto:</label>
                        <input type="file" id="file_foto" name="file_foto" accept="image/*" class="border px-2 py-1 w-full" required>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
                    <div id="response-foto" class="mt-4"></div>
                </form>
            </div>
        </div>

        <!-- Modal untuk Melihat Foto -->
        <div id="modal-view" class="modal">
            <div class="modal-content">
                <span onclick="closeModal('modal-view')" class="cursor-pointer float-right text-red-500">×</span>
                <h2 class="text-xl font-bold mb-4">Detail Foto</h2>
                <div id="modal-body"></div>
            </div>
        </div>

        <!-- Modal untuk Edit Foto -->
        <div id="modal-edit" class="modal">
            <div class="modal-content">
                <span onclick="closeModal('modal-edit')" class="cursor-pointer float-right text-red-500">×</span>
                <h2 class="text-xl font-bold mb-4">Edit Foto</h2>
                <div id="modal-body-edit"></div>
            </div>
        </div>

    </div>
</body>
</html>
