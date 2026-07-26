<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

function load_content() {
    return json_decode(file_get_contents(CONTENT_FILE), true);
}
function save_content($data) {
    file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$content = load_content();
$message = '';
$error = '';
$tab = $_GET['tab'] ?? 'prices';

// -------- Обробка форм --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_prices'])) {
        foreach ($content['prices'] as $i => $row) {
            $content['prices'][$i]['chemistry'] = (int)($_POST['chemistry'][$i] ?? $row['chemistry']);
            $content['prices'][$i]['polish']    = (int)($_POST['polish'][$i] ?? $row['polish']);
            $content['prices'][$i]['ceramic']   = (int)($_POST['ceramic'][$i] ?? $row['ceramic']);
        }
        save_content($content);
        $message = 'Ціни збережено.';
        $tab = 'prices';

    } elseif (isset($_POST['save_texts'])) {
        $content['phone_href']   = trim($_POST['phone_href']);
        $content['phone_display']= trim($_POST['phone_display']);
        foreach ($content['texts'] as $key => $val) {
            if (isset($_POST['texts'][$key])) {
                $content['texts'][$key] = trim($_POST['texts'][$key]);
            }
        }
        save_content($content);
        $message = 'Тексти збережено.';
        $tab = 'texts';

    } elseif (isset($_POST['upload_photo'])) {
        $slot = $_POST['slot'] ?? '';
        $allowed = [
            'logo'      => 'src/img/logo.png',
            'logo_big'  => 'src/img/logo-2.png',
        ];
        if (!isset($allowed[$slot])) {
            $error = 'Невідоме поле фото.';
        } elseif (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Оберіть файл зображення для завантаження.';
        } else {
            $tmp = $_FILES['photo']['tmp_name'];
            $info = @getimagesize($tmp);
            $okTypes = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP];
            if (!$info || !in_array($info[2], $okTypes)) {
                $error = 'Дозволені формати: PNG, JPG, WEBP.';
            } else {
                $relPath = $allowed[$slot];
                if (is_uploaded_file($tmp)) {
                    foreach (SITE_COPIES as $base) {
                        $dest = $base . '/' . $relPath;
                        if (is_dir(dirname($dest))) {
                            copy($tmp, $dest);
                        }
                    }
                    $message = 'Фото оновлено на всіх сторінках сайту.';
                } else {
                    $error = 'Помилка завантаження файлу.';
                }
            }
        }
        $tab = 'photos';
    }
}

$prices = $content['prices'];
$texts  = $content['texts'];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Адмін-панель CarLux</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="admin-header">
    <div>CarLux — Адмін-панель</div>
    <a href="logout.php" class="logout-link">Вийти</a>
</header>

<nav class="tabs">
    <a href="?tab=prices" class="<?= $tab==='prices'?'active':'' ?>">Ціни</a>
    <a href="?tab=texts" class="<?= $tab==='texts'?'active':'' ?>">Тексти</a>
    <a href="?tab=photos" class="<?= $tab==='photos'?'active':'' ?>">Фото</a>
</nav>

<main class="admin-content">
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($tab === 'prices'): ?>
    <h2>Таблиця цін</h2>
    <p class="hint">Ці ціни одразу з'являться на сайті (розділ «Наші ціни») на всіх трьох сторінках.</p>
    <form method="post">
        <table class="price-table-admin">
            <thead>
                <tr><th>Категорія авто</th><th>Хімчистка, грн</th><th>Полірування, $</th><th>Кераміка, $</th></tr>
            </thead>
            <tbody>
            <?php foreach ($prices as $i => $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><input type="number" name="chemistry[<?= $i ?>]" value="<?= (int)$row['chemistry'] ?>" min="0"></td>
                    <td><input type="number" name="polish[<?= $i ?>]" value="<?= (int)$row['polish'] ?>" min="0"></td>
                    <td><input type="number" name="ceramic[<?= $i ?>]" value="<?= (int)$row['ceramic'] ?>" min="0"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="save_prices" value="1" class="btn-primary">Зберегти ціни</button>
    </form>

<?php elseif ($tab === 'texts'): ?>
    <h2>Тексти сайту</h2>
    <form method="post">
        <fieldset>
            <legend>Телефон (відображається у шапці та в контактах на всіх сторінках)</legend>
            <label>Номер для дзвінка (формат tel:, напр. +380972189000)
                <input type="text" name="phone_href" value="<?= htmlspecialchars($content['phone_href']) ?>">
            </label>
            <label>Номер, як він показується на сайті (напр. 097 218-9000)
                <input type="text" name="phone_display" value="<?= htmlspecialchars($content['phone_display']) ?>">
            </label>
        </fieldset>

        <fieldset>
            <legend>Головна сторінка — заголовок банера</legend>
            <label>Виділений жовтим фрагмент
                <input type="text" name="texts[hero_highlight_main]" value="<?= htmlspecialchars($texts['hero_highlight_main']) ?>">
            </label>
            <label>Решта заголовка
                <input type="text" name="texts[hero_rest_main]" value="<?= htmlspecialchars($texts['hero_rest_main']) ?>">
            </label>
            <label>Підзаголовок
                <input type="text" name="texts[hero_sub_main]" value="<?= htmlspecialchars($texts['hero_sub_main']) ?>">
            </label>
        </fieldset>

        <fieldset>
            <legend>Сторінка «Хімчистка» — заголовок банера</legend>
            <label>Виділений жовтим фрагмент
                <input type="text" name="texts[hero_highlight_detailing]" value="<?= htmlspecialchars($texts['hero_highlight_detailing']) ?>">
            </label>
            <label>Решта заголовка
                <input type="text" name="texts[hero_rest_detailing]" value="<?= htmlspecialchars($texts['hero_rest_detailing']) ?>">
            </label>
            <label>Підзаголовок
                <input type="text" name="texts[hero_sub_detailing]" value="<?= htmlspecialchars($texts['hero_sub_detailing']) ?>">
            </label>
        </fieldset>

        <fieldset>
            <legend>Сторінка «Полірування» — заголовок банера</legend>
            <label>Виділений жовтим фрагмент
                <input type="text" name="texts[hero_highlight_poliruvannya]" value="<?= htmlspecialchars($texts['hero_highlight_poliruvannya']) ?>">
            </label>
            <label>Решта заголовка
                <input type="text" name="texts[hero_rest_poliruvannya]" value="<?= htmlspecialchars($texts['hero_rest_poliruvannya']) ?>">
            </label>
            <label>Підзаголовок
                <input type="text" name="texts[hero_sub_poliruvannya]" value="<?= htmlspecialchars($texts['hero_sub_poliruvannya']) ?>">
            </label>
        </fieldset>

        <button type="submit" name="save_texts" value="1" class="btn-primary">Зберегти тексти</button>
    </form>

<?php elseif ($tab === 'photos'): ?>
    <h2>Фото</h2>
    <p class="hint">Завантажене фото автоматично замінить старе на всіх трьох сторінках сайту (головна, «Хімчистка», «Полірування»).</p>

    <div class="photo-slot">
        <h3>Логотип у шапці сайту</h3>
        <img src="../src/img/logo.png" alt="поточний логотип" class="current-photo">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="slot" value="logo">
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required>
            <button type="submit" name="upload_photo" value="1" class="btn-primary">Замінити</button>
        </form>
    </div>

    <div class="photo-slot">
        <h3>Великий логотип на банері (головний екран)</h3>
        <img src="../src/img/logo-2.png" alt="поточний банер-логотип" class="current-photo">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="slot" value="logo_big">
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required>
            <button type="submit" name="upload_photo" value="1" class="btn-primary">Замінити</button>
        </form>
    </div>

    <p class="hint">Потрібно додати більше фото (галерея, іконки послуг тощо)? Напишіть розробнику — це легко розширити.</p>
<?php endif; ?>
</main>
</body>
</html>
