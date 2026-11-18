<?php
require('dbFun.php');
$conn = dbConnect();

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
        <div id="rankingsTable">
            <h1>Rankings</h1>
            <div>
                <p>Most streamed songs</p>
                <div class="scroll-box">
                    <?php
                    $sql = "
    SELECT 
        song.name AS song_name,
        song.listening,
        interpreter.name AS interpreter_name
    FROM song
    JOIN interpreter ON song.interpreterID = interpreter.interpreterID
    ORDER BY song.listening DESC
    LIMIT 50
";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $i = 1;
                    while ($song = $result->fetch_assoc()) {
                        echo "<div class='item'>"
                            . $i++ . ". "
                            . $song['song_name'] . " — "
                            . $song['interpreter_name'] . " — "
                            . $song['listening']
                            . "</div>";
                    }
                    ?>
                </div>
            </div>

            <div>
                <p>Most streamed interpreters</p>
                <div class="scroll-box">
                    <?php
                    $sql = "
    SELECT 
        interpreterID,
        name,
        listenersCount
    FROM interpreter
    ORDER BY listenersCount DESC
    LIMIT 50
";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='item'>"
                            . $i++ . ". "
                            . $row['name'] . " — "
                            . $row['listenersCount']
                            . "</div>";
                    }
                    ?>
                </div>
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
</body>
</html>
