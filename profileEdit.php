<?php
require_once "dbFun.php";
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit;
}

// Načtení dat uživatele
$stmt = $conn->prepare("SELECT name, email FROM listener WHERE listenerID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = "";

// Inicializace isAdmin
if (!isset($_SESSION['isAdmin'])) {
    $_SESSION['isAdmin'] = false;
}
$isAdmin = $_SESSION['isAdmin'];

// Zpracování formuláře pro úpravu profilu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveProfile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE listener SET name=?, email=?, password=? WHERE listenerID=?");
        $stmt->bind_param("sssi", $name, $email, $passwordHash, $userID);
    } else {
        $stmt = $conn->prepare("UPDATE listener SET name=?, email=? WHERE listenerID=?");
        $stmt->bind_param("ssi", $name, $email, $userID);
    }

    if ($stmt->execute()) {
        $message = "Profil byl úspěšně aktualizován!";
        $user['name'] = $name;
        $user['email'] = $email;
        $_SESSION['userName'] = $name;
        $_SESSION['userEmail'] = $email;
    } else {
        $message = "Chyba při ukládání profilu.";
    }
}

// Zpracování formuláře pro aktivaci admina
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['becomeAdmin'])) {
    $_SESSION['isAdmin'] = true;
    $isAdmin = true;
    $message = "Nyní jste admin!";
}

// Zpracování admin tlačítek
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addInterpreter'])) {
        header("Location: addInterpreter.php");
        exit;
    }
    if (isset($_POST['addAlbum'])) {
        header("Location: addAlbum.php");
        exit;
    }
    if (isset($_POST['addSong'])) {
        header("Location: addSong.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava profilu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.typekit.net/ely1daf.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashBoard {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #2b2a2a;
            border-radius: 12px;
            color: #edffe6;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            font-family: sans-serif;
        }

        .dashBoard h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 30px;
            padding: 15px;
            background: #3a3a3a;
            border-radius: 10px;
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #696a69;
            background: #2b2a2a;
            color: #edffe6;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #8cac80;
        }

        button {
            background: #8cac80;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
        }

        button:hover {
            background: #5c7e53;
            transform: scale(1.05);
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #98bc8d;
        }

        .admin-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }

        .admin-buttons button {
            width: 120px;
        }
    </style>
</head>
<body>

<?php include_once "components/header.php"; ?>

<main>
    <section class="dashBoard">
        <h1>Úprava profilu</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
        <?php endif; ?>

        <!-- Formulář pro úpravu profilu -->
        <form method="POST">
            <div>
                <label>Jméno:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>" required>
            </div>
            <div>
                <label>Heslo (ponechat prázdné, pokud nechceš měnit):</label>
                <input type="password" name="password">
            </div>
            <button type="submit" name="saveProfile"><i class="fa-solid fa-floppy-disk"></i> Uložit změny</button>
        </form>

        <!-- Formulář pro admin / stát se admin -->
        <?php if ($isAdmin): ?>
            <div class="admin-buttons">
                <form method="POST"><button type="submit" name="addInterpreter"><i class="fa-solid fa-user-plus"></i> Add Interpreter</button></form>
                <form method="POST"><button type="submit" name="addAlbum"><i class="fa-solid fa-compact-disc"></i> Add Album</button></form>
                <form method="POST"><button type="submit" name="addSong"><i class="fa-solid fa-music"></i> Add Song</button></form>
            </div>
        <?php else: ?>
            <form method="POST" class="admin-form">
                <button type="submit" name="becomeAdmin"><i class="fa-solid fa-user-shield"></i> Staň se adminem</button>
            </form>
        <?php endif; ?>

    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
