<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_POST['userID'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $fotoProfil = $_FILES['fotoProfil']['name'];

    // Upload file gambar jika ada
    if (!empty($fotoProfil)) {
        $target_dir = "uploads/";  // Ganti dengan direktori tujuan Anda
        $target_file = $target_dir . basename($fotoProfil);
        move_uploaded_file($_FILES['fotoProfil']['tmp_name'], $target_file);

        // Update foto profil ke database
        $query = "UPDATE user SET Nama = ?, Email = ?, Bio = ?, FotoProfil = ? WHERE UserID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nama, $email, $bio, $target_file, $userID);
    } else {
        // Update tanpa mengganti foto
        $query = "UPDATE user SET Nama = ?, Email = ?, Bio = ? WHERE UserID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nama, $email, $bio, $userID);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil.']);
    }
    
    $stmt->close();
}
$conn->close();
?>
