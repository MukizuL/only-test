<?php

require "../src/auth.php";
require "../src/db.php";
require "../src/helpers.php";

$message = "";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = normalizePhone($_POST["phone"]);
    $oldPassword = $_POST["oldPassword"];
    $newPassword = $_POST["newPassword"];
    $passwordConfirm = $_POST["passwordConfirm"];

    if ($newPassword !== $passwordConfirm) {
        $message = "Пароли не совпадают.";
    } else {
        if (!$username || !isValidEmail($email) || !isValidPhone($phone)) {
            $message = "Введите корректный логин, email и телефон.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");

                $stmt->execute([$_SESSION["user_id"]]);

                if (password_verify($oldPassword, $stmt->fetch()["password"])) {
                    if ($newPassword) {
                        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, phone=?, password=? WHERE id=?");

                        $stmt->execute([$username, $email, $phone, password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION["user_id"]]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, phone=? WHERE id=?");

                        $stmt->execute([$username, $email, $phone, $_SESSION["user_id"]]);
                    }

                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;
                    $_SESSION["phone"] = $phone;

                    header("Location: profile.php");
                    exit;
                } else {
                    $message = "Неверный пароль.";
                }
            } catch (PDOException $e) {
                $message = "Ошибка базы данных";
                error_log($e->getMessage());
            }
        }
    }
}
?>

<!doctype html>
<html lang="ru">
<body>

<h2>Профиль</h2>

<p><?= htmlspecialchars($message) ?></p>

<p>Добро пожаловать, <b><?= htmlspecialchars($_SESSION["username"]) ?></b></p>

<form method="post">
    <input name="username" type="text" placeholder="Логин" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required><br><br>
    <input name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required><br><br>
    <input name="phone" type="text" placeholder="Телефон" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" required><br><br>
    <input name="oldPassword" type="password" placeholder="Старый пароль"><br><br>
    <input name="newPassword" type="password" placeholder="Новый пароль"><br><br>
    <input name="passwordConfirm" type="password" placeholder="Повтор пароля"><br><br>

    <button>Сохранить</button>
</form>

<a href="logout.php">Выйти</a>

</body>
</html>