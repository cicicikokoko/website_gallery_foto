<?php
include 'config.php';
session_start();

$userID = 1; // Ganti dengan user yang login
$fotoID = $_GET['fotoID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isiKomentar = $_POST['isiKomentar'];
    $sql = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) 
            VALUES ('$fotoID', '$userID', '$isiKomentar', NOW())";
    if ($conn->query($sql) === TRUE) {
        echo "Komentar berhasil ditambahkan!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT komentarfoto.IsiKomentar, komentarfoto.TanggalKomentar, user.Username 
        FROM komentarfoto 
        JOIN user ON komentarfoto.UserID = user.UserID 
        WHERE FotoID = '$fotoID'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Komentar Foto</title>
</head>
<body>
    <h2>Komentar Foto</h2>

    <form method="POST">
        <textarea name="isiKomentar" placeholder="Tambahkan komentar..." required></textarea><br>
        <button type="submit">Kirim Komentar</button>
    </form>

    <h3>Komentar:</h3>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <p><strong><?php echo $row['Username']; ?>:</strong> <?php echo $row['IsiKomentar']; ?> (<?php echo $row['TanggalKomentar']; ?>)</p>
    <?php } ?>
</body>
</html>
