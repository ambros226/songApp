<?php
require('dbFun.php');
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   Načtení interpretů a jejich alb
========================= */
$interpretersResult = $conn->query("SELECT interpreterID, name FROM interpreter ORDER BY name");
$interpreters = $interpretersResult->fetch_all(MYSQLI_ASSOC);

$albumsResult = $conn->query("SELECT albumID, name, interpreterID FROM album ORDER BY name");
$albums = $albumsResult->fetch_all(MYSQLI_ASSOC);

/* =========================
   Zpracování formuláře
========================= */
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $songName = trim($_POST['song_name'] ?? '');
    $albumID = (int)($_POST['albumID'] ?? 0);

    if ($songName && $albumID && !empty($_FILES['audio']['name'])) {

        // zjistíme interpreta z alba
        $stmt = $conn->prepare("SELECT interpreterID FROM album WHERE albumID = ?");
        $stmt->bind_param("i", $albumID);
        $stmt->execute();
        $res = $stmt->get_result();
        $album = $res->fetch_assoc();
        if (!$album) {
            $message = "Vybrané album neexistuje!";
        } else {
            $interpreterID = $album['interpreterID'];

            // nahrání audio souboru
            $uploadDir = __DIR__ . "/songs/";
            $webPath = "songs/";

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid("song_") . "." . $ext;
            $diskPath = $uploadDir . $fileName;
            $dbPath = $webPath . $fileName;

            if (move_uploaded_file($_FILES['audio']['tmp_name'], $diskPath)) {

                // INSERT do song
                $stmt = $conn->prepare("
                    INSERT INTO song (name, songUrl, albumID, interpreterID, listening)
                    VALUES (?, ?, ?, ?, 0)
                ");
                $stmt->bind_param("ssii", $songName, $dbPath, $albumID, $interpreterID);

                if ($stmt->execute()) {
                    $message = "Písnička byla úspěšně přidána.";
                } else {
                    $message = "Chyba při ukládání písničky: " . $stmt->error;
                }

            } else {
                $message = "Chyba při uploadu souboru.";
            }
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
    <title>Přidat písničku</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
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
        <h1>Přidat písničku</h1>

        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-form">

            <label>Vyber album</label>
            <select name="albumID" required>
                <option value="">-- vyber album --</option>
                <?php foreach ($albums as $a): ?>
                    <option value="<?= $a['albumID'] ?>">
                        <?= htmlspecialchars($a['name']) ?> —
                        <?= htmlspecialchars(array_values(array_filter($interpreters, fn($i)=>$i['interpreterID']==$a['interpreterID']))[0]['name'] ?? 'Neznámý interpret') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Název písničky</label>
            <input type="text" name="song_name" required>

            <label>Audio soubor (MP3)</label>
            <input type="file" name="audio" accept="audio/*" required>

            <button type="submit">Přidat písničku</button>
        </form>
    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
