<?php
require_once "dbFun.php";
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_GET['id'])) {
    die("Neplatný interpret");
}

$interpreterID = (int)$_GET['id'];

// načteme jméno interpreta
$stmt = $conn->prepare("SELECT name FROM interpreter WHERE interpreterID = ?");
$stmt->bind_param("i", $interpreterID);
$stmt->execute();
$result = $stmt->get_result();
$interpreter = $result->fetch_assoc();

if (!$interpreter) {
    die("Interpret nenalezen");
}

// načteme všechna alba interpreta
$stmt = $conn->prepare("
    SELECT DISTINCT a.albumID, a.name AS album_name, a.photo_path
    FROM album a
    JOIN song s ON s.albumID = a.albumID
    WHERE s.interpreterID = ?
    ORDER BY a.name
");
$stmt->bind_param("i", $interpreterID);
$stmt->execute();
$result = $stmt->get_result();

$albums = [];
while ($row = $result->fetch_assoc()) {
    $albums[] = $row;
}
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Alba interpreta – <?= htmlspecialchars($interpreter['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .scroll-box {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-inline: auto;
            margin-top: 20px;
        }

        h1 {
            font-size: 40px;
            color: #eee;
        }

        .scroll-box .item {
            display: flex;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            height: 120px;
        }

        .scroll-box .item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-top: -5px;

        }
        .scroll-box .item p {
            color: #2b2a2a;
            font-size: 20px;
            margin: auto 20px;
        }

        .scroll-box .item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<?php include_once "components/header.php"; ?>

<main>
    <section class="dashBoard">
        <div class="tableBig">
            <h1><?= htmlspecialchars($interpreter['name']) ?></h1>
            <h3>Alba interpreta:</h3>
            <div class="scroll-box">
                <?php if (!empty($albums)): ?>
                    <?php foreach ($albums as $album): ?>
                        <div class="item" onclick="location.href='albumBoard.php?id=<?= $album['albumID'] ?>'">
                            <img src="<?= htmlspecialchars($album['photo_path']) ?>"
                                 alt="<?= htmlspecialchars($album['album_name']) ?>">
                            <p><?= htmlspecialchars($album['album_name']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tento interpret nemá žádná alba.</p>
                <?php endif; ?>
            </div>
        </div>

    </section>
    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
