<?php
require_once __DIR__ . '/../config.php';

function auth_data_exists() {
    return file_exists(AUTH_FILE);
}

function auth_load() {
    if (!auth_data_exists()) return null;
    $json = file_get_contents(AUTH_FILE);
    return json_decode($json, true);
}

function auth_save($login, $password_plain) {
    $data = [
        'login' => $login,
        'hash'  => password_hash($password_plain, PASSWORD_DEFAULT),
    ];
    file_put_contents(AUTH_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function require_login() {
    // Якщо пароль ще не створено — відправляємо на сторінку первинного налаштування
    if (!auth_data_exists()) {
        if (basename($_SERVER['PHP_SELF']) !== 'setup.php') {
            header('Location: setup.php');
            exit;
        }
        return;
    }
    if (empty($_SESSION['carlux_admin_logged_in'])) {
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header('Location: login.php');
            exit;
        }
    }
}
