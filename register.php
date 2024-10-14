<?php
include 'config.php';

// Kode rahasia yang dibutuhkan untuk mendaftar sebagai admin
$secret_code = "118006"; // Gantilah dengan kode rahasia Anda
$error_message = ""; // Variabel untuk menyimpan pesan kesalahan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Menggunakan password_hash untuk hashing password
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Jika role adalah admin, maka cek kode rahasianya
    if ($role == 'admin') {
        $input_secret_code = $_POST['secret_code'];
        
        // Validasi kode rahasia
        if ($input_secret_code !== $secret_code) {
            $error_message = "Kode rahasia salah! Tidak dapat mendaftar sebagai admin.";
        }
    }

    // Cek apakah username atau email sudah terdaftar
    $check_query = "SELECT * FROM user WHERE Username = ? OR Email = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Username atau Email sudah terdaftar!";
    }

    // Jika tidak ada kesalahan, lakukan insert
    if (empty($error_message)) {
        // Query untuk menambahkan pengguna baru dengan role
        $query = "INSERT INTO user (Username, Password, Email, Role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $password, $email, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Pendaftaran berhasil!'); window.location.href = 'login.php';</script>";
            exit;  // Pastikan untuk exit setelah header
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // Fungsi untuk menampilkan/menghilangkan field kode rahasia berdasarkan role
        function toggleSecretCodeField() {
            var role = document.getElementById('role').value;
            var secretCodeField = document.getElementById('secret_code_field');
            
            if (role == 'admin') {
                secretCodeField.style.display = 'block'; // Tampilkan field jika role admin
            } else {
                secretCodeField.style.display = 'none';  // Sembunyikan field jika bukan admin
            }
        }
        
        // Fungsi untuk menampilkan pesan kesalahan jika ada
        function showError(message) {
            var errorDiv = document.getElementById('error_message');
            errorDiv.innerText = message;
            errorDiv.style.display = 'block'; // Tampilkan pesan kesalahan
        }
    </script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="w-full max-w-xs">
        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div id="error_message" class="mb-4 text-red-500" style="display: <?= $error_message ? 'block' : 'none'; ?>;">
                <?= $error_message ?>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                <input type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input type="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Daftar Sebagai:</label>
                <select name="role" id="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="toggleSecretCodeField()" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <!-- Field untuk memasukkan kode rahasia jika memilih role Admin -->
            <div class="mb-4" id="secret_code_field" style="display: none;">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="secret_code">Kode Rahasia Admin</label>
                <input type="text" name="secret_code" id="secret_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Daftar</button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">Login</a>
            </div>
        </form>
    </div>
</body>
</html>
