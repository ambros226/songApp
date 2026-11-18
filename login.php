<?php
require_once('dbFun.php');

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (loginUser($email, $password)) {
        header("Location: signUp.php?registered=1");
        exit;

    } else {
        header("Location: signUp.php?error=email_exists");
        exit;
    }
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

    <div>
        <a href="index.php">Back</a>
    </div>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Heslo" required>
        <button type="submit" name="submit">Přihlásit</button>
    </form>
</main>
<?php
include_once "components/footer.php";
?>
</body>
</html>
