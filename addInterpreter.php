<?php
require('dbFun.php');
$conn = dbConnect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kontrola, jestli je uživatel admin
if (!($_SESSION['isAdmin'] ?? false)) {
    die("Nemáte oprávnění přidávat interprety.");
}

$message = "";

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');


    if (!$name) {
        $message = "Zadejte jméno interpreta!";
    }

        // Vložení do DB
        $stmt = $conn->prepare("INSERT INTO interpreter (name) VALUES (?)");
        $stmt->bind_param("s", $name, );
        if ($stmt->execute()) {
            $message = "Interpret byl úspěšně přidán!";
        } else {
            $message = "Chyba při ukládání interpreta.";
        }
}
?>

<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přidat interpreta</title>
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
            font-family: sans-serif;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        .dashBoard h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #3a3a3a;
            padding: 20px;
            border-radius: 10px;
        }

        input[type="text"], input[type="file"] {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #696a69;
            background: #2b2a2a;
            color: #edffe6;
        }

        button {
            background: #8cac80;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.2s;
        }

        button:hover {
            background: #5c7e53;
            transform: scale(1.05);
        }

        .message {
            text-align: center;
            color: #98bc8d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include_once "components/header.php"; ?>

<main>
    <section class="dashBoard">
        <h1>Přidat interpreta</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Jméno interpreta:</label>
            <input type="text" name="name" required>

            <button type="submit"><i class="fa-solid fa-plus"></i> Přidat interpreta</button>
        </form>
    </section>

    <?php include_once "components/aside.php"; ?>
</main>

<?php include_once "components/footer.php"; ?>
</body>
</html>
