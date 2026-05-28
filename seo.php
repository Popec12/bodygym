<?php
// Определяем текущую страницу
$current_page = basename($_SERVER['PHP_SELF']);

// Массив с мета-тегами для каждой страницы (только "спортзал")
$seo_data = [
    'index.php' => [
        'title' => 'BodyGym - Спортзал в Рязани | Тренажерный зал, бассейн, спа',
        'description' => 'Современный спортзал BodyGym в ЖК Зеленый сад. Тренажерный зал, бассейн, спа-зона, групповые занятия. Абонементы от 1900 ₽/мес. Персональные тренеры.',
        'keywords' => 'спортзал рязань, тренажерный зал, бассейн, качалка, абонемент в спортзал',
        'robots' => 'index, follow'
    ],
    'AboutUs.php' => [
        'title' => 'О спортзале BodyGym в Рязани | Команда, отзывы, миссия',
        'description' => 'Узнайте о спортзале BodyGym: наша команда, миссия, ценности. Читайте реальные отзывы клиентов о тренажерном зале.',
        'keywords' => 'о нас, спортзал, команда, отзывы о спортзале, миссия',
        'robots' => 'index, follow'
    ],
    'Coach.php' => [
        'title' => 'Тренеры спортзала BodyGym | Опытные инструкторы в Рязани',
        'description' => 'Команда профессиональных тренеров спортзала BodyGym: единоборства, плавание, тяжелая атлетика, йога, кроссфит. Индивидуальный подход.',
        'keywords' => 'тренеры спортзала, инструктор тренажерного зала, персональный тренер, йога, бокс',
        'robots' => 'index, follow'
    ],
    'Services.php' => [
        'title' => 'Абонементы и цены спортзала BodyGym | Тест-драйв 800 ₽',
        'description' => 'Выберите абонемент в спортзал BodyGym: Platinum, Gold, Standart от 1900 ₽/мес. Тест-драйв тренажерного зала за 800 ₽. Акции и скидки.',
        'keywords' => 'абонемент в спортзал, цена спортзала, тест драйв тренажерного зала, акции спортзала',
        'robots' => 'index, follow'
    ],
    'Schedule.php' => [
        'title' => 'Расписание групповых занятий в спортзале BodyGym | Йога, бокс, плавание',
        'description' => 'Расписание групповых тренировок в спортзале BodyGym. Йога, пилатес, бокс, велогонка, аквафитнес, кроссфит.',
        'keywords' => 'расписание спортзала, групповые занятия, тренировки в спортзале, йога, бокс',
        'robots' => 'index, follow'
    ],
    'Contacts.php' => [
        'title' => 'Вопросы и ответы | Контакты спортзала BodyGym в Рязани',
        'description' => 'Часто задаваемые вопросы о спортзале BodyGym. Адрес, телефон, режим работы тренажерного зала. Как оплатить абонемент? Заморозка, возврат.',
        'keywords' => 'faq, вопросы о спортзале, контакты спортзала, адрес тренажерного зала, телефон',
        'robots' => 'index, follow'
    ]
];

// Для страниц, не требующих индексации
$noindex_pages = ['profile.php', 'login.php', 'logout.php', 'admin.php', '404.php'];

// Получаем данные для текущей страницы
$seo = isset($seo_data[$current_page]) ? $seo_data[$current_page] : $seo_data['index.php'];

// Для страниц с noindex
if (in_array($current_page, $noindex_pages)) {
    $seo['robots'] = 'noindex, nofollow';
    if ($current_page == 'login.php') {
        $seo['title'] = 'Вход и регистрация | Спортзал BodyGym';
        $seo['description'] = 'Войдите в личный кабинет спортзала BodyGym или зарегистрируйтесь.';
    }
    if ($current_page == '404.php') {
        $seo['title'] = 'Страница не найдена (404) | Спортзал BodyGym';
        $seo['description'] = 'Запрашиваемая страница не существует. Вернитесь на главную спортзала BodyGym.';
    }
    if ($current_page == 'profile.php') {
        $seo['title'] = 'Личный кабинет | Спортзал BodyGym';
        $seo['description'] = 'Управляйте профилем, смотрите историю заказов абонементов и отзывы о спортзале.';
    }
}

// Выводим мета-теги
function render_seo_meta($seo) {
    echo '<title>' . htmlspecialchars($seo['title']) . '</title>';
    echo '<meta name="description" content="' . htmlspecialchars($seo['description']) . '">';
    if (isset($seo['keywords'])) {
        echo '<meta name="keywords" content="' . htmlspecialchars($seo['keywords']) . '">';
    }
    echo '<meta name="robots" content="' . htmlspecialchars($seo['robots']) . '">';
    echo '<link rel="canonical" href="https://bodygym.ru/' . htmlspecialchars(basename($_SERVER['PHP_SELF'])) . '">';
}
?>