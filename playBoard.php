<?php
require_once "dbFun.php";
$conn = dbConnect();
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_GET['id'])) die("Neplatná písnička");

$songID = (int)$_GET['id'];
$stmt = $conn->prepare("
    SELECT s.name, s.songUrl, s.albumID, a.photo_path, i.interpreterID, i.name AS interpreter_name, a.name AS album_name
    FROM song s
    LEFT JOIN album a ON s.albumID = a.albumID
    LEFT JOIN interpreter i ON s.interpreterID = i.interpreterID
    WHERE s.songID=? LIMIT 1
");
$stmt->bind_param("i", $songID);
$stmt->execute();
$song = $stmt->get_result()->fetch_assoc();

// uložíme do session pro aside
$_SESSION['lastSongID'] = $songID;
$_SESSION['lastSongName'] = $song['name'];
$_SESSION['SongPlaying'] = true;
?>

<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($song['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<style>
    .song-detail {
        width: 1000px;
        min-height: 600px; /* místo pevné výšky */
        margin: 40px auto;
        padding: 20px;
        background: #2b2a2a;
        border: 4px solid #696a69;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        text-align: center;
        color: #edffe6;
        position: relative;
        z-index: 1;
    }

    .song-detail img {
        width: 300px;
        height: 300px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .song-detail h1 {
        margin-bottom: 10px;
        font-size: 1.8rem;
    }

    .audio-player {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
        position: relative;
        z-index: 5;
    }

    .controls {
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        z-index: 10;
    }

    .controls button {
        background: #8cac80;
        border: none;
        color: #fff;
        padding: 10px 15px;
        border-radius: 50px;
        cursor: pointer;
        font-size: 1rem;
        transition: background 0.2s;
    }

    .controls button:hover {
        background: #5c7e53;
    }

    #progress, #volume {
        width: 100%;
        max-width: 400px;
        appearance: none;
        height: 8px;
        border-radius: 4px;
        background: #eee;
        cursor: pointer;
    }

    #progress::-webkit-slider-thumb, #volume::-webkit-slider-thumb {
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #8cac80;
        cursor: pointer;
    }

    #currentTime, #duration {
        font-size: 0.9rem;
        color: #edffe6;
    }

    p a {
        text-decoration: none;
        color: #edffe6;
        font-weight: 500;
        margin: 0 5px;
    }

    p a:hover {
        border-bottom: 2px solid #98bc8d;
        color: #98bc8d;
    }
    #play{
        padding: 18px 20px ;
        border: none;
        background: #8cac80;
        border-radius: 50%;
        color: #edffe6;
        margin-top: 20px;
    }

</style>
<?php include_once "components/header.php"; ?>
<main>
    <section class="dashBoard">
        <div class="song-detail">
            <img src="<?= htmlspecialchars($song['photo_path']) ?>" alt="Cover" style="width:300px;border-radius:12px;">
            <h1><?= htmlspecialchars($song['name']) ?></h1>
            <p>
                <a href="interpreterBoard.php?id=<?= $song['interpreterID'] ?>"><?= htmlspecialchars($song['interpreter_name']) ?></a>
                •
                <a href="albumBoard.php?id=<?= $song['albumID'] ?>"><?= htmlspecialchars($song['album_name']) ?></a>
            </p>
            <button id="play" onclick="document.getElementById('globalAudio').play()"><i class="fa-solid fa-play"></i></button>
        </div>
    </section>
    <?php include_once "components/aside.php"; ?>
</main>
<?php include_once "components/footer.php"; ?>
<script>
    const audio = document.getElementById('audioPlayer');
    const playPauseBtn = document.getElementById('playPauseBtn');
    const stopBtn = document.getElementById('stopBtn');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl = document.getElementById('duration');
    const progress = document.getElementById('progress');
    const volume = document.getElementById('volume');

    let listenerAdded = false;

    // Inicializace volume
    audio.volume = volume.value;

    // Play / Pause
    playPauseBtn.addEventListener('click', () => {
        if (audio.paused) {
            console.log("click")
            audio.play().catch(err => console.error("Nepodařilo se spustit audio:", err));
            playPauseBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';

            if (!listenerAdded) {
                const body = new URLSearchParams({
                    songID: <?= (int)$songID ?>,
                    songName: <?= json_encode($song['song_name']) ?>,
                    type: 'play'
                });

                fetch('saveListeners.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: body.toString()
                })
                    .then(res => res.json())
                    .then(data => console.log('Listener přidán:', data))
                    .catch(err => console.error(err));

                listenerAdded = true;
            }
        } else {
            audio.pause();
            playPauseBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        }
    });

    // Stop
    stopBtn.addEventListener('click', () => {
        audio.pause();
        audio.currentTime = 0;
        playPauseBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
    });

    // Aktualizace progress bar
    audio.addEventListener('timeupdate', () => {
        const current = audio.currentTime;
        const durationTime = audio.duration || 0;
        currentTimeEl.textContent = `${Math.floor(current / 60)}:${Math.floor(current % 60).toString().padStart(2, '0')}`;
        durationEl.textContent = `${Math.floor(durationTime / 60)}:${Math.floor(durationTime % 60).toString().padStart(2, '0')}`;
        progress.value = durationTime ? (current / durationTime) * 100 : 0;
        progress.style.background = `linear-gradient(to right, #8cac80 ${progress.value}%, #eee ${progress.value}%)`;
    });

    // Seek
    progress.addEventListener('input', () => {
        audio.currentTime = (progress.value / 100) * audio.duration;
        progress.style.background = `linear-gradient(to right, #8cac80 ${progress.value}%, #eee ${progress.value}%)`;
    });

    // Volume
    volume.addEventListener('input', () => {
        audio.volume = volume.value;
        volume.style.background = `linear-gradient(to right, #5c7e53 ${volume.value * 100}%, #eee ${volume.value * 100}%)`;
    });
</script>
</body>
</html>

