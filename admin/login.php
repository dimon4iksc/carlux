<?php
require_once __DIR__ . '/includes/auth.php';

if (!auth_data_exists()) {
    header('Location: setup.php');
    exit;
}

if (!empty($_SESSION['carlux_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $auth  = auth_load();

    if ($auth && $login === $auth['login'] && password_verify($pass, $auth['hash'])) {
        $_SESSION['carlux_admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Невірний логін або пароль.';
    }
}
$created = isset($_GET['created']);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Вхід — Адмін-панель CarLux</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-box">
    <h1>CarLux — Адмін-панель</h1>
    <?php if ($created): ?><div class="alert alert-success">Акаунт створено. Тепер увійдіть.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Логін
            <input type="text" name="login" required autofocus>
        </label>
        <label>Пароль
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Увійти</button>
    </form>
</div>
</body>
</html>
