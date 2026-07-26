<?php
require_once __DIR__ . '/includes/auth.php';

// Якщо адмінка вже налаштована - на сторінку входу
if (auth_data_exists()) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($login === '' || $pass1 === '') {
        $error = 'Заповніть логін і пароль.';
    } elseif (strlen($pass1) < 6) {
        $error = 'Пароль має містити щонайменше 6 символів.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Паролі не співпадають.';
    } else {
        auth_save($login, $pass1);
        header('Location: login.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Перше налаштування адмін-панелі — CarLux</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-box">
    <h1>Створення адмін-акаунту</h1>
    <p class="hint">Це відбувається лише один раз. Придумайте логін і пароль, якими будете заходити в адмін-панель сайту CarLux.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Логін
            <input type="text" name="login" required autofocus>
        </label>
        <label>Пароль
            <input type="password" name="password" required minlength="6">
        </label>
        <label>Повторіть пароль
            <input type="password" name="password2" required minlength="6">
        </label>
        <button type="submit" class="btn-primary">Створити акаунт і увійти</button>
    </form>
</div>
</body>
</html>
