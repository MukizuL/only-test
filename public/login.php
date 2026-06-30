<?php

require "../src/db.php";
require "../src/auth.php";
require "../src/helpers.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST['smart-token'];

    if (check_captcha($token)) {
        $login = trim($_POST['login']);
        $password = $_POST["password"];

        if (!$message) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? OR email=? OR phone=?");

                $stmt->execute([$login, $login, normalizePhone($login)]);

                $user = $stmt->fetch();

                if ($user && password_verify($password, $user["password"])) {

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["email"] = $user["email"];

                    header("Location: profile.php");
                    exit;
                }

                $message = "Неверный логин или пароль.";
            } catch (PDOException $e) {
                $message = "Ошибка базы данных";
                error_log($e->getMessage());
            }
        }
    } else {
        $message = "Каптча не пройдена";

    }
}

function check_captcha($token): bool {
    $ch = curl_init("https://smartcaptcha.cloud.yandex.ru/validate");
    $args = [
            "secret" => SMARTCAPTCHA_SERVER_KEY,
            "token" => $token,
    ];
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpcode !== 200) {
        echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
        return true;
    }

    $resp = json_decode($server_output);
    return $resp->status === "ok";
}
?>

<!doctype html>
<html lang="ru">
<head>
    <script src="https://smartcaptcha.cloud.yandex.ru/captcha.js" defer></script>
</head>
<body>

<h2>Авторизация</h2>

<p><?= htmlspecialchars($message) ?></p>

<form method="post">

    <input type="text" name="login" placeholder="Логин" required><br><br>

    <input type="password" name="password" placeholder="Пароль" required><br><br>

    <div
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="<?= SMARTCAPTCHA_CLIENT_KEY ?>"
            style="height: 100px; width: 100px"
    ></div>

    <button>Войти</button>

</form>

<a href="register.php">Регистрация</a>

</body>
</html>