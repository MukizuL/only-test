<?php

require "../src/auth.php";

requireLogin();

?>

<!doctype html>
<html lang="ru">
<body>

<h2>Профиль</h2>

<p>Добро пожаловать, <b><?= htmlspecialchars($_SESSION["username"]) ?></b></p>

<a href="logout.php">Выйти</a>

</body>
</html>