<?php
require_once "dbFun.php";
$conn = dbConnect();

if (empty($_POST['songID'])) {
    http_response_code(400);
    exit("Neplatné ID");
}

$songID = (int)$_POST['songID'];
$songName = (string)$_POST['songName'];

$stmt = $conn->prepare("SELECT interpreterID FROM song WHERE songID = ?");
$stmt->bind_param("i", $songID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit("Písnička nenalezena");
}

$song = $result->fetch_assoc();
$interpreterID = (int)$song['interpreterID'];

$stmt = $conn->prepare("UPDATE song SET listening = listening + 1 WHERE songID = ?");
$stmt->bind_param("i", $songID);
$stmt->execute();

$stmt = $conn->prepare("UPDATE interpreter SET listenersCount = listenersCount + 1 WHERE interpreterID = ?");
$stmt->bind_param("i", $interpreterID);
$stmt->execute();

$_SESSION['lastSongID']=$songID;
$_SESSION['lastSongName']=$songName;
$_SESSION['SongPlaying']=true;


echo json_encode(['success' => true]);
