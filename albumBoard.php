<?php
require_once "dbFun.php";
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_GET['id'])) {
    die("Neplatné album");
}

$albumID = (int)$_GET['id'];

// nejprve načteme informace o albu (název + obrázek)
$stmt = $conn->prepare("SELECT name, photo_path FROM album WHERE albumID = ?");
$stmt->bind_param("i", $albumID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Album nenalezeno");
}

$album = $result->fetch_assoc();

// potom načteme písničky
$stmt = $conn->prepare("
    SELECT 
        s.songID,
        s.name AS song_name,
        i.name AS interpreter_name,
        i.interpreterID AS interpreter_id
    FROM song s
    LEFT JOIN interpreter i ON s.interpreterID = i.interpreterID
    WHERE s.albumID = ?
    ORDER BY s.name
");
$stmt->bind_param("i", $albumID);
$stmt->execute();
$result = $stmt->get_result();

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

// vezmeme prvního interpreta pro odkaz nad albem
$interpreter_id = !empty($songs) ? $songs[0]['interpreter_id'] : null;
$interpreter_name = !empty($songs) ? $songs[0]['interpreter_name'] : '';
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($album['name']) ?> – písničky</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .album-header {
            text-align: center;
            margin-bottom: 20px;
            a{
                color: #edffe6;
                text-decoration: none;
                padding-bottom: 3px;
            }
            a:hover{
                border-bottom: 2px solid #98bc8d;
                color: #98bc8d;
            }
        }
        .album-header img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            display: block;
            margin: 0 auto 10px;
            border-radius: 10px;
        }
        .scroll-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-inline: auto;
        }
        .scroll-box .item {
            padding: 5px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
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
        <div class="tableBigHigh">
            <div class="album-header">
                <img src="<?= htmlspecialchars($album['photo_path']) ?>" alt="<?= htmlspecialchars($album['name']) ?>">
                <h1><?= htmlspecialchars($album['name']) ?></h1>
                <?php if ($interpreter_id): ?>
                    <a href="interpreterBoard.php?id=<?= $interpreter_id ?>"><?= htmlspecialchars($interpreter_name) ?></a>
                <?php endif; ?>
            </div>

            <div class="scroll-box">
                <?php if (!empty($songs)): ?>
                    <?php foreach ($songs as $song): ?>
                        <div class="item" onclick="location.href='playBoard.php?id=<?= $song['songID'] ?>'">
                            <?= htmlspecialchars($song['song_name']) ?> — <?= htmlspecialchars($song['interpreter_name']) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>V tomto albu nejsou žádné písničky.</p>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
