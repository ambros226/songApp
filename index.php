<?php
require_once "dbFun.php";
$conn = dbConnect();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Songee</title>
</head>
<body>

<?php
include_once "components/header.php";
?>
<main>
    <section class="dashBoard">
        <h2 class="h2">Songs:</h2>
        <?php
        $sql = "SELECT
    song.name AS song_name,
    song.listening,
    song.songID,
    album.photo_path AS album_photo_path
FROM song
    JOIN album ON song.albumID = album.albumID 
    ORDER BY song.listening DESC
    LIMIT 10";

        $result = $conn->query($sql);

        ?>
        <div class="slider-wrapper">

            <div class="slider" id="songSlider">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="songSlide"> 
                        <div>
                            <a href="playBoard.php?id=<?= $row['songID'] ?>"><?= htmlspecialchars($row['song_name']) ?></a>
                            <img src="<?= $row['album_photo_path'] ?>" alt="songImg" >
                        </div>
                       
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <h2 class="h2">Albums:</h2>
        <?php


        $sql = "SELECT albumID, name, photo_path 
        FROM album 
        ORDER BY RAND() 
        LIMIT 6";

        $result = $conn->query($sql);
        ?>

        <div class="slider-wrapper">

            <div class="slider" id="albumSlider">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="albumSlide">
                        <a href="albumBoard.php?id=<?= $row['albumID'] ?>" style="font-size: 20px"><?= htmlspecialchars($row['name']) ?></a>
                        <img src="<?= $row['photo_path'] ?>" alt="albumImg">
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </section>

    <?php
    include_once "components/aside.php";
    ?>

</main>
<?php
include_once "components/footer.php";
?>
<script src="albumSlider.js"></script>
<script src="songSlider.js"></script>


</body>
</html>
