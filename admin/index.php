<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

// Поля з текстів, де дозволений обмежений набір HTML-тегів (перенос рядка, жирний тощо).
// Решта текстових полів завжди виводяться через htmlspecialchars — звичайний текст, без HTML.
const HTML_ALLOWED_TEXT_FIELDS = ['about_us_html', 'service_1_desc', 'service_2_desc'];
const ALLOWED_INLINE_TAGS = '<p><br><ul><li><strong><em><b><i><span>';

// Максимальні розміри фото після обробки — щоб замовник не поклав фото 6000x4000
// на місце маленької іконки і не зламав верстку/швидкість сайту.
const MAX_IMAGE_DIMENSION = 1920;
const JPEG_QUALITY = 82;
const WEBP_QUALITY = 82;
const PNG_COMPRESSION = 6;
const MAX_UPLOAD_BYTES = 15 * 1024 * 1024; // 15MB — запобіжник до обробки

function load_content() {
    return json_decode(file_get_contents(CONTENT_FILE), true);
}

function backup_content_before_save() {
    if (!file_exists(CONTENT_FILE)) return;
    $backupDir = __DIR__ . '/../data/backups';
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0755, true);
    }
    $stamp = date('Y-m-d_His');
    @copy(CONTENT_FILE, $backupDir . "/content-{$stamp}.json");

    // Тримаємо тільки останні 20 бекапів, щоб папка не росла нескінченно.
    $files = glob($backupDir . '/content-*.json');
    if ($files && count($files) > 20) {
        usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));
        $toDelete = array_slice($files, 0, count($files) - 20);
        foreach ($toDelete as $f) { @unlink($f); }
    }
}

function save_content($data) {
    backup_content_before_save();
    file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Прибирає весь HTML крім невеликого дозволеного набору тегів (без атрибутів,
// без class/onclick/скриптів) — щоб текстове поле не могло зламати верстку чи вставити щось шкідливе.
function sanitize_html_field($value) {
    $clean = strip_tags($value, ALLOWED_INLINE_TAGS);
    // strip_tags лишає атрибути всередині дозволених тегів — виріжемо їх теж.
    $clean = preg_replace('/<(\w+)[^>]*>/', '<$1>', $clean);
    return trim($clean);
}

// Зменшує та перестискає зображення, щоб великі фото з телефону не роздували сторінку
// і не ламали верстку слотів, розрахованих на маленькі іконки/лого.
// Повертає шлях до тимчасового обробленого файлу, або null якщо обробка неможлива (тоді копіюємо оригінал як є).
function process_uploaded_image($tmpPath, $imageType) {
    switch ($imageType) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmpPath); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($tmpPath);  break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($tmpPath); break;
        case IMAGETYPE_GIF:
            // GIF може бути анімованим — пережимання зламає анімацію, тому лише пропускаємо.
            return null;
        default:
            return null;
    }
    if (!$src) return null;

    $width  = imagesx($src);
    $height = imagesy($src);
    $scale  = min(1, MAX_IMAGE_DIMENSION / max($width, $height));

    if ($scale < 1) {
        $newW = (int) round($width * $scale);
        $newH = (int) round($height * $scale);
        $resized = imagecreatetruecolor($newW, $newH);
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);
        $src = $resized;
    }

    $outTmp = tempnam(sys_get_temp_dir(), 'carlux_img_');
    switch ($imageType) {
        case IMAGETYPE_JPEG: imagejpeg($src, $outTmp, JPEG_QUALITY); break;
        case IMAGETYPE_PNG:  imagepng($src, $outTmp, PNG_COMPRESSION); break;
        case IMAGETYPE_WEBP: imagewebp($src, $outTmp, WEBP_QUALITY); break;
    }
    imagedestroy($src);
    return $outTmp;
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
                $raw = $_POST['texts'][$key];
                $content['texts'][$key] = in_array($key, HTML_ALLOWED_TEXT_FIELDS, true)
                    ? sanitize_html_field($raw)
                    : trim($raw);
            }
        }
        save_content($content);
        $message = 'Тексти збережено. Попередню версію збережено в бекап.';
        $tab = 'texts';

    } elseif (isset($_POST['upload_photo'])) {
        $slot = $_POST['slot'] ?? '';
        $tab  = 'photos';

        if (!isset($mediaMap[$slot])) {
            $error = 'Невідоме поле фото.';
        } elseif (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Оберіть файл зображення для завантаження.';
        } elseif ($_FILES['photo']['size'] > MAX_UPLOAD_BYTES) {
            $error = 'Файл завеликий (максимум 15 МБ).';
        } else {
            $tmp = $_FILES['photo']['tmp_name'];
            $info = @getimagesize($tmp);
            $okTypes = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!$info || !in_array($info[2], $okTypes)) {
                $error = 'Дозволені формати: PNG, JPG, GIF, WEBP.';
            } else {
                $relPath = $mediaMap[$slot]['path'];
                if (is_uploaded_file($tmp)) {
                    // Стискаємо/зменшуємо фото перед збереженням, щоб не роздувати сторінку
                    // і не ламати верстку слоту, розрахованого на маленьку іконку.
                    $processedTmp = process_uploaded_image($tmp, $info[2]);
                    $sourceFile = $processedTmp ?? $tmp;

                    $copied = false;
                    foreach (SITE_COPIES as $base) {
                        $dest = $base . '/' . $relPath;
                        if (is_dir(dirname($dest))) {
                            copy($sourceFile, $dest);
                            $copied = true;
                        }
                    }
                    if ($processedTmp) { @unlink($processedTmp); }

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

        <fieldset>
            <legend>Наші послуги (блок на всіх трьох сторінках)</legend>
            <label>Назва послуги 1
                <input type="text" name="texts[service_1_title]" value="<?= htmlspecialchars($texts['service_1_title']) ?>">
            </label>
            <label>Опис послуги 1 (можна перенос рядка, розділюйте як окремі речення)
                <textarea name="texts[service_1_desc]" rows="3"><?= htmlspecialchars($texts['service_1_desc']) ?></textarea>
            </label>
            <label>Назва послуги 2
                <input type="text" name="texts[service_2_title]" value="<?= htmlspecialchars($texts['service_2_title']) ?>">
            </label>
            <label>Опис послуги 2
                <textarea name="texts[service_2_desc]" rows="3"><?= htmlspecialchars($texts['service_2_desc']) ?></textarea>
            </label>
        </fieldset>

        <fieldset>
            <legend>Блок «Про нас» (головна і сторінка «Полірування»)</legend>
            <label>Текст блоку (можна абзаци <code>&lt;p&gt;</code>, списки <code>&lt;ul&gt;&lt;li&gt;</code>, жирний <code>&lt;strong&gt;</code> — інші теги буде видалено при збереженні)
                <textarea name="texts[about_us_html]" rows="12"><?= htmlspecialchars($texts['about_us_html']) ?></textarea>
            </label>
        </fieldset>

        <fieldset>
            <legend>Футер (адреса та Instagram, на всіх трьох сторінках)</legend>
            <label>Адреса, рядок 1
                <input type="text" name="texts[footer_address_line1]" value="<?= htmlspecialchars($texts['footer_address_line1']) ?>">
            </label>
            <label>Адреса, рядок 2
                <input type="text" name="texts[footer_address_line2]" value="<?= htmlspecialchars($texts['footer_address_line2']) ?>">
            </label>
            <label>Instagram (текст біля значка, напр. @carlux.detailing)
                <input type="text" name="texts[footer_instagram_handle]" value="<?= htmlspecialchars($texts['footer_instagram_handle']) ?>">
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
