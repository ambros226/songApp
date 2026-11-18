<?php
session_start();
function dbConnect() {
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "songAppDB";

    $conn = new mysqli($servername, $username, $password, $dbname);

    return $conn;
}

function registerUser($name, $email, $password) {
    $conn = dbConnect();

    $check = $conn->prepare("SELECT listenerID FROM listener WHERE email = ?");
    if (!$check) return false;
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        $conn->close();
        return false;
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO listener (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        $conn->close();
        return false;
    }
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    $result = $stmt->execute();

    if ($result) {
        $userID = $conn->insert_id;
        $_SESSION["userID"] = $userID;
        $_SESSION["userName"] = $name;
        $_SESSION["userEmail"] = $email;
    }

    $stmt->close();
    $conn->close();

    return $result ? $userID : false;
}


function loginUser($email, $password) {
    $conn = dbConnect();

    $stmt = $conn->prepare("SELECT listenerID, name, password FROM listener WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $stmt->bind_result($id, $name, $hashedPassword);
    $stmt->fetch();

    if (!$id) {
        return false;
    }

    if (!password_verify($password, $hashedPassword)) {
        return false;
    }


    $_SESSION["userID"] = $id;
    $_SESSION["userName"] = $name;
    $_SESSION["userEmail"] = $email;

    return true;
}