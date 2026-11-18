<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "dbFun.php";

$lastSongID = $_SESSION['lastSongID'] ?? null;
$song = null;

if ($lastSongID) {
    $conn = dbConnect();
    $stmt = $conn->prepare("
        SELECT s.name, s.songUrl, a.photo_path
        FROM song s
        LEFT JOIN album a ON s.albumID = a.albumID
        WHERE s.songID = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $lastSongID);
    $stmt->execute();
    $song = $stmt->get_result()->fetch_assoc();
    $conn->close();
}
?>

<aside>
    <?php if ($song): ?>
        <div class="aside-player">
            <img src="<?= htmlspecialchars($song['photo_path']) ?>" alt="Cover">
            <a href="playBoard.php?id=<?= $lastSongID ?>"><?= htmlspecialchars($song['name']) ?></a>

            <audio id="globalAudio" preload="metadata">
                <source src="<?= htmlspecialchars($song['songUrl']) ?>" type="audio/mpeg">
            </audio>

            <div class="player-controls">
                <button id="playPauseBtn">⏯</button>
                <button id="stopBtn">⏹</button>
            </div>
        </div>
    <?php endif; ?>

    <ul>
        <li><i class="fa-solid fa-house"></i><a href="index.php">Home</a></li>
        <li><i class="fa-solid fa-magnifying-glass"></i><a href="search.php">Search</a></li>
        <li><i class="fa-solid fa-chart-simple"></i><a href="rankings.php">Rankings</a></li>
        <li><i class="fa-solid fa-circle-user"></i><a href="profileEdit.php">Profile</a></li>
    </ul>
</aside>

<script>
    const audio = document.getElementById('globalAudio');
    const playPauseBtn = document.getElementById('playPauseBtn');
    const stopBtn = document.getElementById('stopBtn');

    if(audio && playPauseBtn && stopBtn){
        // obnov stav z localStorage
        const savedTime = localStorage.getItem('globalAudioTime');
        const isPlaying = localStorage.getItem('globalAudioPlaying');

        if(savedTime) audio.currentTime = parseFloat(savedTime);
        if(isPlaying === '1') audio.play().catch(()=>{});

        // Play/Pause
        playPauseBtn.addEventListener('click', ()=>{
            if(audio.paused){
                audio.play();
                localStorage.setItem('globalAudioPlaying','1');
            } else {
                audio.pause();
                localStorage.setItem('globalAudioPlaying','0');
            }
        });

        // Stop
        stopBtn.addEventListener('click', ()=>{
            audio.pause();
            audio.currentTime = 0;
            localStorage.setItem('globalAudioPlaying','0');
        });

        // automaticky ukládat čas
        audio.addEventListener('timeupdate', ()=>{
            localStorage.setItem('globalAudioTime', audio.currentTime);
        });

        // změna ikonky
        audio.addEventListener('play', ()=>{ playPauseBtn.textContent='⏸'; });
        audio.addEventListener('pause', ()=>{ playPauseBtn.textContent='⏯'; });
    }

</script>

<style>
    .aside-player {
        background:#2b2a2a;
        padding:12px;
        margin:12px;
        border-radius:12px;
        color:#edffe6;
        text-align:center;
    }
    .aside-player img { width:120px; border-radius:8px; }
    .aside-player a { display:block; color:#edffe6; margin:8px 0; text-decoration:none; }
    .aside-player a:hover { color:#98bc8d; }
    .player-controls button {
        background:#8cac80;
        border:none;
        padding:6px 12px;
        border-radius:50px;
        cursor:pointer;
        font-size:16px;
        margin:0 4px;
    }
    .player-controls button:hover { background:#5c7e53; }
</style>
