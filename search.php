<?php
require_once "dbFun.php";
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$results = [];

if (!empty($_GET['query'])) {
    $query = trim($_GET['query']);

    $stmt = $conn->prepare("
        SELECT 
            s.songID,
            s.name AS song_name,
            i.name AS interpreter_name,
            a.name AS album_name
        FROM song s
        LEFT JOIN interpreter i ON s.interpreterID = i.interpreterID
        LEFT JOIN album a ON s.albumID = a.albumID
        WHERE s.name LIKE ?
        ORDER BY s.name
        LIMIT 50
    ");

    $like = "%$query%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Songee – Search</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "components/header.php"; ?>

<main>
    <section class="dashBoard">
        <div class="tableBig">
            <div>

                <h1>Search</h1>

                <form method="GET" id="searchTable">
                    <input type="text" name="query" placeholder="Hledat písničku..."
                           value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
                    <button id="searchBtn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>

                <div class="search-results">
                    <?php if (!empty($_GET['query'])): ?>
                        <?php if ($results && $results->num_rows > 0): ?>
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <div class="song-item">
                                    <a href="playBoard.php?id=<?= $row['songID'] ?>">
                                        <strong><?= htmlspecialchars($row['song_name']) ?></strong><br>
                                        <small>
                                            <?= htmlspecialchars($row['interpreter_name'] ?? 'Neznámý interpret') ?>
                                            •
                                            <?= htmlspecialchars($row['album_name'] ?? 'Neznámé album') ?>
                                        </small>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Nic nenalezeno.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>


            </div>
        </div>
    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>

</body>
</html>
