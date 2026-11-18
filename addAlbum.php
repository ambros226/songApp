<?php
require('dbFun.php');
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   Načtení interpretů
========================= */
$result = $conn->query("SELECT interpreterID, name FROM interpreter ORDER BY name");
$interpreters = $result->fetch_all(MYSQLI_ASSOC);

/* =========================
   Zpracování formuláře
========================= */
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $albumName = trim($_POST['album_name'] ?? '');
    $interpreterID = (int)($_POST['interpreterID'] ?? 0);

    if ($albumName && $interpreterID && !empty($_FILES['photo']['name'])) {

        $uploadDir = __DIR__ . "/photoAlbum/";   // cesta na serveru
        $webPath = "photoAlbum/";                // cesta pro DB/web
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("album_") . "." . $ext;

        $diskPath = $uploadDir . $fileName;
        $dbPath = $webPath . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $diskPath)) {

            $stmt = $conn->prepare("
                INSERT INTO album (name, photo_path, interpreterID)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ssi", $albumName, $dbPath, $interpreterID);

            if ($stmt->execute()) {
                $albumID = $stmt->insert_id;
                $message = "Album bylo úspěšně přidáno.";
            } else {
                $message = "Chyba při ukládání alba: " . $stmt->error;
            }

        } else {
            $message = "Chyba při uploadu obrázku.";
        }

    } else {
        $message = "Vyplň všechna pole.";
    }
}
?>

<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přidat album</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* =========================
           ADMIN FORM
        ========================= */
        .admin-form {
            max-width: 500px;
            margin: 30px auto;
            padding: 25px;
            background: #2b2a2a;
            border: 3px solid #696a69;
            border-radius: 14px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .admin-form label {
            color: #edffe6;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .admin-form input,
        .admin-form select {
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            outline: none;
            background: #f1f1f1;
            font-size: 0.95rem;
        }

        .admin-form input[type="file"] {
            padding: 6px;
            background: transparent;
            color: #edffe6;
        }

        .admin-form button {
            margin-top: 10px;
            padding: 12px;
            background: #8cac80;
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        .admin-form button:hover {
            background: #5c7e53;
            transform: translateY(-2px);
        }

        .dashBoard h1 {
            text-align: center;
            color: #edffe6;
            margin-top: 30px;
        }

        .dashBoard p {
            text-align: center;
            color: #98bc8d;
            font-weight: 500;
        }

        .admin-form select {
            appearance: none;
            background-image:
                    linear-gradient(45deg, transparent 50%, #333 50%),
                    linear-gradient(135deg, #333 50%, transparent 50%);
            background-position:
                    calc(100% - 20px) calc(50% - 4px),
                    calc(100% - 15px) calc(50% - 4px);
            background-size: 6px 6px;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body>
<?php include_once "components/header.php"; ?>

<main>
    <section class="dashBoard">
        <h1>Přidat album</h1>

        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-form">

            <label>Interpret</label>
            <select name="interpreterID" required>
                <option value="">-- vyber interpreta --</option>
                <?php foreach ($interpreters as $i): ?>
                    <option value="<?= $i['interpreterID'] ?>">
                        <?= htmlspecialchars($i['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Název alba</label>
            <input type="text" name="album_name" required>

            <label>Cover alba</label>
            <input type="file" name="photo" accept="image/*" required>

            <button type="submit">Přidat album</button>
        </form>
    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
