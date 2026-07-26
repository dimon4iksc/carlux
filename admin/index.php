<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

function load_content() {
    return json_decode(file_get_contents(CONTENT_FILE), true);
}
function save_content($data) {
    file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$mediaMap   = require __DIR__ . '/includes/media-map.php';
$categories = require __DIR__ . '/includes/media-categories.php';

$content = load_content();
$message = '';
$error   = '';
$tab     = $_GET['tab'] ?? 'texts';

// -------- Обробка форм --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_texts'])) {
        $content['phone_href']    = trim($_POST['phone_href']);
        $content['phone_display'] = trim($_POST['phone_display']);
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
        $tab  = 'photos';

        if (!isset($mediaMap[$slot])) {
            $error = 'Невідоме поле фото.';
        } elseif (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Оберіть файл зображення для завантаження.';
        } else {
            $tmp = $_FILES['photo']['tmp_name'];
            $info = @getimagesize($tmp);
            $okTypes = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!$info || !in_array($info[2], $okTypes)) {
                $error = 'Дозволені формати: PNG, JPG, GIF, WEBP.';
            } else {
                $relPath = $mediaMap[$slot]['path'];
                if (is_uploaded_file($tmp)) {
                    $copied = false;
                    foreach (SITE_COPIES as $base) {
                        $dest = $base . '/' . $relPath;
                        if (is_dir(dirname($dest))) {
                            copy($tmp, $dest);
                            $copied = true;
                        }
                    }
                    $message = $copied
                        ? 'Зображення «' . $mediaMap[$slot]['label'] . '» оновлено на всіх сторінках сайту.'
                        : 'Не вдалося знайти місце для збереження файлу.';
                } else {
                    $error = 'Помилка завантаження файлу.';
                }
            }
        }
    }
}

$texts = $content['texts'];

// Групуємо медіа-слоти за категоріями у порядку, заданому в media-categories.php
$grouped = [];
foreach ($categories as $catKey => $catLabel) {
    $grouped[$catKey] = [];
}
foreach ($mediaMap as $slug => $item) {
    $grouped[$item['cat']][$slug] = $item;
}
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
    <a href="?tab=texts" class="<?= $tab==='texts'?'active':'' ?>">Тексти</a>
    <a href="?tab=photos" class="<?= $tab==='photos'?'active':'' ?>">Фото сайту</a>
</nav>

<main class="admin-content">
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($tab === 'texts'): ?>
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
    <h2>Фото сайту</h2>
    <p class="hint">Тут зібрані всі зображення, які використовуються на сайті — від логотипів і фото відгуків до дрібних іконок. Завантажене зображення одразу замінить старе на всіх трьох сторінках сайту (де воно використовується). Натисніть на назву розділу, щоб розгорнути його.</p>

    <?php $first = true; foreach ($categories as $catKey => $catLabel): ?>
        <details class="media-cat" <?= $first ? 'open' : '' ?>>
            <summary><?= htmlspecialchars($catLabel) ?> <span class="media-cat-count">(<?= count($grouped[$catKey]) ?>)</span></summary>
            <div class="media-grid">
                <?php foreach ($grouped[$catKey] as $slug => $item): ?>
                    <div class="photo-slot media-slot">
                        <h3><?= htmlspecialchars($item['label']) ?></h3>
                        <img src="../<?= htmlspecialchars($item['path']) ?>" alt="<?= htmlspecialchars($item['label']) ?>" class="current-photo">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="slot" value="<?= htmlspecialchars($slug) ?>">
                            <input type="file" name="photo" accept="image/png,image/jpeg,image/gif,image/webp" required>
                            <button type="submit" name="upload_photo" value="1" class="btn-primary btn-small">Замінити</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </details>
    <?php $first = false; endforeach; ?>

    <p class="hint">Потрібно додати ще якесь зображення, якого тут немає (наприклад, нове фото в галерею)? Напишіть розробнику — це легко розширити.</p>
<?php endif; ?>
</main>
</body>
</html>
