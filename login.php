<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah user ada di database
    $query = "SELECT * FROM user WHERE Username = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Query error: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password yang di-hash
        if (password_verify($password, $user['Password'])) {
            // Simpan session user dan role
            $_SESSION['username'] = $user['Username'];
            $_SESSION['userID'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];

            // Redirect ke index.php
            header("Location: index.php");
            exit;
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Username tidak ditemukan!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="w-full max-w-xs">
        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                <input type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Login</button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">Daftar</a>
            </div>
        </form>
    </div>
</body>
</html>
