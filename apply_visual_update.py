# -*- coding: utf-8 -*-
"""
Застосовує однакові правки візуалу до 3 копій сайту CarLux
(index.html, detailing/index.html, poliruvannya/index.html):
1) бейдж "-10% знижка на замовлення онлайн" у формі hero
2) новий блок довіри з великими цифрами (як на carlux-detailing.com.ua/plr)
3) бейдж рейтингу Google Maps над відгуками
4) заміна карти на 2 реальні вбудовані Google Maps з рефересної сторінки
Кольори НЕ змінюються — використана та сама синьо-чорна неонова гама
(#0b63c9 / #2196f3 / градієнт #0d1a30 -> #060c17), що вже є на сайті.
"""

FILES = ["index.html", "detailing/index.html", "poliruvannya/index.html"]

# ---------- 1. Бейдж знижки у hero-формі ----------
OLD_FORM_TITLE = '''<div class="form-title">
\t\t\t\t\t\t\t\t\t<span>Залиште Ваш телефон і наш консультант зв’яжеться з Вами</span>
\t\t\t\t\t\t\t\t</div>'''

NEW_FORM_TITLE = '''<div class="form-title">
\t\t\t\t\t\t\t\t\t<span>Залиште Ваш телефон і наш консультант зв’яжеться з Вами</span>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t<div class="discount-badge">-10% знижка на замовлення онлайн</div>'''

# ---------- 2. Блок довіри (великі цифри) — вставляємо одразу після секції why-we ----------
TRUST_STATS_BLOCK = '''
\t<section id="trust-stats" class="trust-stats">
\t\t<div class="container">
\t\t\t<div class="trust-stats-row">
\t\t\t\t<div class="trust-stat">
\t\t\t\t\t<span class="trust-stat-num">7+</span>
\t\t\t\t\t<span class="trust-stat-label">Років досвіду</span>
\t\t\t\t</div>
\t\t\t\t<div class="trust-stat">
\t\t\t\t\t<span class="trust-stat-num">100%</span>
\t\t\t\t\t<span class="trust-stat-label">Задоволених клієнтів</span>
\t\t\t\t</div>
\t\t\t\t<div class="trust-stat">
\t\t\t\t\t<span class="trust-stat-num">11256+</span>
\t\t\t\t\t<span class="trust-stat-label">Авто виконаних детейлінг послуг</span>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t</section>
'''

# ---------- 3. Бейдж рейтингу Google Maps над відгуками ----------
OLD_REVIEWS_H2 = '''<section id="reviews" class="reviews">
\t\t<div class="container">
\t\t\t<h2 class="">Відгуки</h2>'''

NEW_REVIEWS_H2 = '''<section id="reviews" class="reviews">
\t\t<div class="container">
\t\t\t<h2 class="">Відгуки</h2>
\t\t\t<div class="google-rating-badge">
\t\t\t\t<div class="grb-seal">
\t\t\t\t\t<span class="grb-score">4.9</span>
\t\t\t\t\t<span class="grb-stars">★★★★★</span>
\t\t\t\t\t<span class="grb-caption">Google Rating</span>
\t\t\t\t</div>
\t\t\t\t<div class="grb-text">
\t\t\t\t\t<strong>Рейтинг Google Maps</strong>
\t\t\t\t\t<span>Висока оцінка якості від наших клієнтів</span>
\t\t\t\t</div>
\t\t\t</div>'''

# ---------- 4. Дві реальні карти замість старого directions-embed ----------
OLD_MAP_IFRAME_START = '<iframe src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d10301.997613109359'

NEW_MAP_BLOCK = '''<div class="map-embeds">
\t\t\t\t<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d10301.05243712404!2d24.0051981!3d49.799921!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473ae651b5ab5325%3A0x2eff8f569d5d0ae5!2z0KXRltC80YfQuNGB0YLQutCwINCw0LLRgtC-IC0gQ2FyTHV4IHwg0JTQtdGC0LXQudC70ZbQvdCzINCw0LLRgtC-INCb0YzQstGW0LI!5e0!3m2!1suk!2sua!4v1688044607671!5m2!1suk!2sua" loading="lazy" allowfullscreen></iframe>
\t\t\t\t<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2575.262926636648!2d24.002623176867566!3d49.79992443398886!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473ae651b5ab5325%3A0x2eff8f569d5d0ae5!2z0KXRltC80YfQuNGB0YLQutCwINCw0LLRgtC-IC0gQ2FyTHV4IHwg0JTQtdGC0LXQudC70ZbQvdCzINCw0LLRgtC-INCb0YzQstGW0LI!5e0!3m2!1suk!2sua!4v1688124777782!5m2!1suk!2sua" loading="lazy" allowfullscreen></iframe>
\t\t\t</div>'''


def patch_file(path):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()

    changes = []

    if OLD_FORM_TITLE in content:
        content = content.replace(OLD_FORM_TITLE, NEW_FORM_TITLE)
        changes.append("discount-badge у hero")
    else:
        changes.append("‼ НЕ ЗНАЙДЕНО form-title (пропущено)")

    if OLD_REVIEWS_H2 in content:
        content = content.replace(OLD_REVIEWS_H2, NEW_REVIEWS_H2)
        changes.append("google-rating-badge над відгуками")
    else:
        changes.append("‼ НЕ ЗНАЙДЕНО reviews h2 (пропущено)")

    # Блок довіри — вставляємо перед </section> секції why-we, після чого йде наступна секція
    marker = '\t</section>\n\n\t<section id="about-us"'
    if marker in content:
        content = content.replace(marker, '\t</section>\n' + TRUST_STATS_BLOCK + '\n\t<section id="about-us"', 1)
        changes.append("блок довіри (7+/100%/11256+) вставлено перед about-us")
    else:
        changes.append("‼ НЕ ЗНАЙДЕНО маркер вставки блоку довіри (пропущено)")

    # Карта: знаходимо повний тег <iframe ...></iframe> що починається з OLD_MAP_IFRAME_START
    start = content.find(OLD_MAP_IFRAME_START)
    if start != -1:
        end = content.find("</iframe>", start) + len("</iframe>")
        content = content[:start] + NEW_MAP_BLOCK + content[end:]
        changes.append("карта замінена на 2 реальні Google Maps embed")
    else:
        changes.append("‼ НЕ ЗНАЙДЕНО стару карту (пропущено)")

    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

    print(f"--- {path} ---")
    for c in changes:
        print("  ", c)


if __name__ == "__main__":
    for f in FILES:
        patch_file(f)
