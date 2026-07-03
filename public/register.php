<?php

require "../src/db.php";
require "../src/helpers.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = normalizePhone($_POST["phone"]);
    $password = $_POST["password"];
    $passwordConfirm = $_POST["passwordConfirm"];

    if ($password !== $passwordConfirm) {
        $message = "Пароли не совпадают.";
    } else {
        if (!$username || !isValidEmail($email) || !isValidPhone($phone)) {
            $message = "Введите корректный логин, email и телефон.";
        } else {
            try {
                $stmt = $pdo->prepare(
                        "SELECT id FROM users WHERE username=? OR email=? OR phone=?"
                );
                $stmt->execute([$username, $email, $phone]);

                if ($stmt->fetch()) {
                    $message = "Пользователь уже существует.";
                } else {

                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("INSERT INTO users(username,email,phone,password) VALUES(?,?,?,?)");

                    $stmt->execute([$username, $email, $phone, $hash]);

                    header("Location: login.php");
                    exit;
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
<html>
<body>

<h2>Регистрация</h2>

<p><?= htmlspecialchars($message) ?></p>

<form method="post">
    <input name="username" type="text" placeholder="Логин" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required><br><br>
    <input name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required><br><br>
    <input name="phone" type="text" placeholder="Телефон" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required><br><br>
    <input name="password" type="password" placeholder="Пароль" required><br><br>
    <input name="passwordConfirm" type="password" placeholder="Повтор пароля" required><br><br>

    <button>Создать аккаунт</button>
</form>

<a href="login.php">Войти</a>

</body>
</html>