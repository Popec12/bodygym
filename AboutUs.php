<?php
session_start();
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

// Добавление/редактирование отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    verify_csrf();
    verify_honeypot();

    if (!isLoggedIn()) {
        header('Location: login.php?error=not_logged_in');
        exit;
    }

    $user_id = getCurrentUserId();

    // Проверяем наличие rating и устанавливаем значение по умолчанию
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $review_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Валидация
    $errors = [];
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Выберите оценку от 1 до 5';
    }
    if (empty($review_text)) {
        $errors[] = 'Напишите текст отзыва';
    }
    if (strlen($review_text) > 500) {
        $errors[] = 'Текст отзыва не должен превышать 500 символов';
    }

    if (empty($errors)) {
        try {
            // Проверяем, есть ли параметр edit в GET
            $edit_id = isset($_GET['edit']) ? filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) : null;

            if ($edit_id !== false && $edit_id !== null && $edit_id > 0) {
                // РЕДАКТИРОВАНИЕ отзыва
                $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW(), is_edited = 1 WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$rating, $review_text, $edit_id, $user_id])) {
                    $_SESSION['review_success'] = 'Отзыв успешно обновлен!';
                    header('Location: AboutUs.php#reviews');
                    exit;
                } else {
                    $_SESSION['review_error'] = 'Ошибка при обновлении отзыва';
                    header('Location: AboutUs.php#reviews');
                    exit;
                }
            } else {
                // НОВЫЙ отзыв
                $user = getUserInfo($pdo, $user_id);
                $user_name = $user['name'] ?? 'Пользователь';

                $stmt = $pdo->prepare("INSERT INTO reviews (user_id, name, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
                if ($stmt->execute([$user_id, $user_name, $rating, $review_text])) {
                    $_SESSION['review_success'] = 'Отзыв успешно опубликован! Спасибо за ваше мнение!';
                    header('Location: AboutUs.php#reviews');
                    exit;
                } else {
                    $_SESSION['review_error'] = 'Ошибка при сохранении отзыва';
                    header('Location: AboutUs.php#reviews');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $_SESSION['review_error'] = 'Ошибка базы данных: ' . $e->getMessage();
            header('Location: AboutUs.php#reviews');
            exit;
        }
    } else {
        $_SESSION['review_error'] = implode('<br>', $errors);
        header('Location: AboutUs.php#reviews');
        exit;
    }
}

// Получение отзыва для редактирования
$edit_review = null;
if (isset($_GET['edit']) && isLoggedIn()) {
    $review_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($review_id !== false && $review_id !== null && $review_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$review_id, getCurrentUserId()]);
        $edit_review = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Получаем сообщения из сессии
$success_message = $_SESSION['review_success'] ?? null;
$error_message = $_SESSION['review_error'] ?? null;
unset($_SESSION['review_success']);
unset($_SESSION['review_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php render_seo_meta($seo); ?>

    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Jura&family=Karantina:wght@300;400;700&family=League+Gothic&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" href="assets/img/photo-logo.png">
    <style>
        .top-notification {
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            min-width: 320px;
            max-width: 500px;
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 16px;
            text-align: center;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .top-notification.success {
            background-color: #2e7d32;
            color: white;
        }

        .top-notification.error {
            background-color: #c62828;
            color: white;
        }

        .top-notification .notification-message {
            flex: 1;
            text-align: left;
            font-size: 16px;
        }

        .top-notification .notification-close {
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: white;
            transition: color 0.3s;
            background: none;
            border: none;
            padding: 0 5px;
        }

        .notification-close:hover {
            color: #ffb6c1;
        }


        @keyframes slideDown {
            from {
                transform: translateX(-50%) translateY(-100px);
                opacity: 0;
            }

            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        .feedback-stars {
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 12px;
            font-size: 45px;
        }

        .feedback-stars input[type="radio"] {
            display: none;
        }

        .feedback-stars label {
            color: #555;
            cursor: pointer;
            font-size: 45px;
            transition: color 0.2s;
        }

        .feedback-stars label:hover,
        .feedback-stars label:hover~label {
            color: #ffd700;
        }

        .feedback-stars input[type="radio"]:checked~label {
            color: #ffd700;
        }

        /* Стили для анимации */
        .fade-up,
        .fade-left,
        .fade-right,
        .fade-scale {
            opacity: 0;
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-up {
            transform: translateY(40px);
        }

        .fade-left {
            transform: translateX(-40px);
        }

        .fade-right {
            transform: translateX(40px);
        }

        .fade-scale {
            transform: scale(0.9);
        }

        .fade-up.visible,
        .fade-left.visible,
        .fade-right.visible,
        .fade-scale.visible {
            opacity: 1;
            transform: translateY(0) translateX(0) scale(1);
        }

        .delay-1 {
            transition-delay: 0.1s;
        }

        .delay-2 {
            transition-delay: 0.2s;
        }

        .delay-3 {
            transition-delay: 0.3s;
        }

        .delay-4 {
            transition-delay: 0.4s;
        }

        .delay-5 {
            transition-delay: 0.5s;
        }
    </style>
</head>

<body>

    <header class="Header" id="mainHeader">
        <div class="HeaderContainer">
            <div class="LeftHeaderContainer">
                <img src="assets/img/photo-logo.png" alt="" class="ImgLogoInHeader">
                <a href="index.php" class="NameLogoInHeader">BodyGym</a>
            </div>
            <div class="RightHeaderContainer">
                <div class="burger-menu" id="burgerMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="UpperPartRHP">
                    <li class="LinkLocation"><a href="#map" class="LinkLocationText">г.Рязань, Касимовское шоссе, д. 30 | ЖК "Зеленый сад"</a></li>
                    <li class="ContactNumber"><a href="#" class="ContactNumberText">+7 920 639-22-35</a></li>
                </ul>
                <ul class="LowerPartRHP">
                    <li class="NavigationInHeader"><a href="index.php" class="NavigationInHeaderText">Главная</a></li>
                    <li class="NavigationInHeader"><a href="AboutUs.php" class="NavigationInHeaderText">О нас</a></li>
                    <li class="NavigationInHeader"><a href="Coach.php" class="NavigationInHeaderText">Тренера</a></li>
                    <li class="NavigationInHeader"><a href="Services.php" class="NavigationInHeaderText">Услуги</a></li>
                    <li class="NavigationInHeader"><a href="Schedule.php" class="NavigationInHeaderText">Групповые</a></li>
                    <li class="NavigationInHeader"><a href="Contacts.php" class="NavigationInHeaderText">F.A.Q.</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="NavigationInHeader"><a href="profile.php" class="NavigationInHeaderText">Личный кабинет</a></li>
                    <?php else: ?>
                        <li class="NavigationInHeader"><a href="login.php" class="NavigationInHeaderText">Войти</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <main id="Main" class="Main">

        <div class="about fade-up">
            <div class="AboutBlock">
                <div class="AboutImage">
                    <img src="assets/img/about1.png" alt="Наш зал" class="about__image-img">
                    <img src="assets/img/about2.png" alt="Наши тренировки" class="about__image-img">
                </div>
                <div class="AboutContent">
                    <p class="about__text">Body<span class="AboutContentspan">Gym</span> — это не просто спортивный зал. Это место, где мечты о здоровом теле становятся реальностью. Мы создали пространство, в котором каждый чувствует себя желанным гостем, независимо от уровня подготовки.<br><br>
                        Здесь ценят усилия, поддерживают в трудных начинаниях и искренне радуются твоим победам. Наши тренажеры — лучшие помощники на пути к силе и выносливости, а квалифицированные тренеры всегда подскажут верный путь.<br><br>
                        Добро пожаловать в Body<span class="AboutContentspan">Gym</span>. Твоя трансформация начинается сегодня!</p>
                </div>
            </div>
        </div>

        <div class="SecondAbout fade-up">
            <div class="NameSecondAbout">
                <p class="NameSecondAboutP">Body<span class="NameSecondspan">Gym</span> сегодня</p>
            </div>
            <div class="CarouselWrapper">
                <button class="CarouselBtn CarouselBtnPrev" id="prevBtn">‹</button>
                <div class="CarouselContainer">
                    <div class="CarouselTrack" id="carouselTrack">
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h1 class="SecondAboutitemH1">МИССИЯ Body<span>Gym</span></h1>
                                <p class="SecondAboutItemP">Главная цель — помочь людям изменить их жизнь и здоровье к лучшему. Мы хотим чтобы спорт был доступен для всех, вне зависимости от социального положения, пола и возраста. Каждый заслуживает быть лучшей версией себя!</p>
                            </div>
                        </div>
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h2 class="SecondAboutitemH1">ЦЕННОСТИ Body<span>Gym</span></h2>
                                <p class="SecondAboutItemP">Мы реализуем специальные спортивные программы для пенсионеров, воспитанников детских домов, людей с ограниченными возможностями. Наши сотрудники принимают участие в благотворительных акциях и социальных мероприятиях.</p>
                            </div>
                        </div>
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h3 class="SecondAboutitemH1">СТРАТЕГИЯ Body<span>Gym</span></h3>
                                <p class="SecondAboutItemP">Наш приоритет — оптимальное соотношения цены и качества предоставляемых услуг. Мы стремимся, чтобы спорт-услуги становились более доступными для людей по всей стране.</p>
                            </div>
                        </div>
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h4 class="SecondAboutitemH1">ИННОВАЦИИ Body<span>Gym</span></h4>
                                <p class="SecondAboutItemP">Мы постоянно следим за новейшими тенденциями в мире спорта и внедряем самые эффективные методики тренировок. Современное оборудование и программы позволяют достигать максимальных результатов в кратчайшие сроки.</p>
                            </div>
                        </div>
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h5 class="SecondAboutitemH1">КОМАНДА Body<span>Gym</span></h5>
                                <p class="SecondAboutItemP">Наши тренеры - это не просто сотрудники, это единомышленники, которые горят своим делом. Постоянное повышение квалификации и участие в международных семинарах - наша традиция.</p>
                            </div>
                        </div>
                        <div class="SecondAboutItem">
                            <div class="SecondAboutItem2">
                                <h6 class="SecondAboutitemH1">СООБЩЕСТВО Body<span>Gym</span></h6>
                                <p class="SecondAboutItemP">Мы создаем не просто спортзал, а настоящее сообщество единомышленников. Регулярные мероприятия, челленджи и соревнования объединяют наших членов в большую спортивную семью.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="CarouselBtn CarouselBtnNext" id="nextBtn">›</button>
            </div>
            <div class="CarouselDots" id="carouselDots"></div>
        </div>

        <div class="feedback-page fade-up">
            <div class="container">
                <h1 class="FeedbackTitle"><?php echo $edit_review ? 'Редактировать отзыв' : 'Оставить свой отзыв'; ?></h1>

                <?php if (!isLoggedIn()): ?>
                    <div class="auth-required-message">
                        <p>Для оставления отзыва необходимо войти в систему</p>
                        <a href="login.php">Войти в аккаунт</a>
                    </div>
                <?php else: ?>
                    <div class="feedback-form-container">
                        <form id="feedback-form" method="post" class="feedback-form">
                            <input type="hidden" name="submit" value="1">
                            <?php echo csrf_field(); ?>
                            <?php echo honeypot_field(); ?>
                            <div class="feedback-stars-container">
                                <div class="stars-label">Оцените наше заведение:</div>
                                <div class="feedback-stars">
                                    <input type="radio" id="star1" name="rating" value="1" <?php echo ($edit_review && $edit_review['rating'] == 1) ? 'checked' : ''; ?>>
                                    <label for="star1">★</label>
                                    <input type="radio" id="star2" name="rating" value="2" <?php echo ($edit_review && $edit_review['rating'] == 2) ? 'checked' : ''; ?>>
                                    <label for="star2">★</label>
                                    <input type="radio" id="star3" name="rating" value="3" <?php echo ($edit_review && $edit_review['rating'] == 3) ? 'checked' : ''; ?>>
                                    <label for="star3">★</label>
                                    <input type="radio" id="star4" name="rating" value="4" <?php echo ($edit_review && $edit_review['rating'] == 4) ? 'checked' : ''; ?>>
                                    <label for="star4">★</label>
                                    <input type="radio" id="star5" name="rating" value="5" <?php echo ($edit_review && $edit_review['rating'] == 5) ? 'checked' : ''; ?>>
                                    <label for="star5">★</label>
                                </div>
                            </div>
                            <div class="feedback-form-right">
                                <textarea id="comment" name="comment" rows="4" placeholder="Расскажите о вашем опыте посещения нашего спортзала..." required><?php echo $edit_review ? htmlspecialchars($edit_review['comment']) : ''; ?></textarea>
                                <div class="feedback-form-actions">
                                    <button type="submit"><?php echo $edit_review ? 'Обновить отзыв' : 'Опубликовать отзыв'; ?></button>
                                    <?php if ($edit_review): ?><a href="AboutUs.php" class="cancel-btn">Отмена</a><?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="reviews" class="reviews-container fade-up">
            <h1 class="title review-title" style="color: white;">Отзывы наших клиентов</h1>
            <div class="reviews">
                <?php
                try {
                    $stmt = $pdo->query("SELECT r.*, u.avatar, u.email as user_email FROM reviews r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<div class='review-block'>";
                            echo "<div class='reviewer'>";
                            $avatar_path = 'assets/img/i (1).webp';
                            if (!empty($row['avatar'])) {
                                $avatar_file = 'assets/uploads/avatars/' . $row['avatar'];
                                if (file_exists($avatar_file)) $avatar_path = $avatar_file;
                            }
                            echo "<img src='" . safe($avatar_path) . "' alt='Аватар' class='reviewer-avatar'>";
                            echo "<span>" . safe($row['name'] ?? 'Пользователь') . "</span>";
                            echo "</div>";
                            echo "<div class='rating'>" . str_repeat("★", (int)($row['rating'] ?? 0)) . str_repeat("☆", 5 - (int)($row['rating'] ?? 0)) . "</div>";
                            echo "<div class='comment'>" . nl2br(safe($row['comment'] ?? '')) . "</div>";
                            if (!empty($row['admin_comment'])) {
                                echo "<div class='admin-comment'><strong>Ответ администратора:</strong><br><div class='admin-comment-text'>" . nl2br(safe($row['admin_comment'])) . "</div></div>";
                            }
                            echo "<div class='user-info'><br>";
                            if (!empty($row['created_at'])) echo date('d.m.Y H:i', strtotime($row['created_at']));
                            if (!empty($row['updated_at']) && $row['updated_at'] != '0000-00-00 00:00:00') echo " (отредактирован)";
                            if (!empty($row['user_id'])) echo " · Проверенный пользователь";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<div class='empty-reviews' style='grid-column: 1 / -1; text-align: center; color: #888; padding: 60px;'><p>Пока никто не оставил отзыв. Будьте первым!</p></div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='alert alert-error' style='grid-column: 1 / -1;'>Ошибка при загрузке отзывов: " . safe($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>
    </main>

    <footer class="NewFooter">
        <div class="FooterContainer">
            <div class="FooterCol FooterCol1">
                <div class="FooterLogoWrapper">
                    <img src="assets/img/photo-logo.png" alt="" class="FooterImgLogo">
                    <span class="FooterLogoName">BodyGym</span>
                </div>
                <p class="FooterDesc">Пространство для тех, кто стремится к совершенству. Современный спортзал с профессиональным подходом.</p>
            </div>
            <div class="FooterCol FooterCol2">
                <h4>Может быть полезно</h4>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="#Main">О нас</a></li>
                    <li><a href="Coach.php">Тренеры</a></li>
                    <li><a href="Services.php">Услуги</a></li>
                    <li><a href="Schedule.php">Групповые</a></li>
                    <li><a href="Contacts.php">F.A.Q.</a></li>
                </ul>
            </div>
            <div class="FooterCol FooterCol3">
                <h4>Документы</h4>
                <ul>
                    <li><a href="#">Пользовательское соглашение</a></li>
                    <li><a href="#">Политика конфиденциальности</a></li>
                    <li><a href="#">Обработка персональных данных</a></li>
                    <li><a href="#">Публичная оферта</a></li>
                </ul>
            </div>
            <div class="FooterCol FooterCol4">
                <h4>Социальные сети</h4>
                <div class="FooterSocials"><a href="#"><img src="assets/img/vk.png" alt="VK"></a><a href="#"><img src="assets/img/max-app-icon-on-a-transparent-background-free-png.webp" alt="Max"></a></div>
                <p class="FooterCopyright">© 2026 BodyGym. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <div id="notification" class="Notification"></div>

    <script>
        function showTopNotification(message, type) {
            const existing = document.querySelector('.top-notification');
            if (existing) existing.remove();
            const notif = document.createElement('div');
            notif.className = `top-notification ${type}`;
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => {
                notif.style.opacity = '0';
                notif.style.transform = 'translateX(-50%) translateY(-100px)';
                setTimeout(() => notif.remove(), 300);
            }, 4000);
        }

        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        });

        let currentPage = 1;
        const reviewsPerPage = 3;
        let allReviews = [];

        function initReviewsPagination() {
            const reviewBlocks = document.querySelectorAll('.review-block');
            allReviews = Array.from(reviewBlocks);
            if (allReviews.length === 0) return;
            if (allReviews.length > reviewsPerPage) {
                allReviews.forEach(review => review.style.display = 'none');
                showPage(1);
                if (!document.querySelector('.load-more-container')) addLoadMoreButton();
            } else {
                allReviews.forEach(review => review.style.display = 'flex');
            }
        }

        function showPage(page) {
            const endIndex = page * reviewsPerPage;
            for (let i = 0; i < allReviews.length; i++) {
                allReviews[i].style.display = i < endIndex ? 'flex' : 'none';
            }
            currentPage = page;
            const btn = document.querySelector('.load-more-btn');
            if (btn) btn.style.display = endIndex >= allReviews.length ? 'none' : 'inline-block';
        }

        function addLoadMoreButton() {
            if (document.querySelector('.load-more-container')) return;
            const container = document.querySelector('.reviews-container');
            if (!container) return;
            const loadMoreDiv = document.createElement('div');
            loadMoreDiv.className = 'load-more-container';
            loadMoreDiv.innerHTML = `<button class="load-more-btn">Показать еще отзывы</button>`;
            container.appendChild(loadMoreDiv);
            const btn = loadMoreDiv.querySelector('.load-more-btn');
            btn.addEventListener('click', () => {
                if (currentPage * reviewsPerPage < allReviews.length) showPage(currentPage + 1);
            });
        }

        // Анимация выбора звезд (слева направо)
        document.addEventListener('DOMContentLoaded', function() {
            const starLabels = document.querySelectorAll('.feedback-stars label');
            const starInputs = document.querySelectorAll('.feedback-stars input[type="radio"]');
            const feedbackForm = document.getElementById('feedback-form');

            // Функция обновления цвета звезд от выбранного значения
            function updateStarsColor(selectedValue) {
                for (let i = 0; i < starLabels.length; i++) {
                    const starValue = i + 1;
                    if (starValue <= selectedValue) {
                        starLabels[i].style.color = '#ffd700';
                    } else {
                        starLabels[i].style.color = '#555';
                    }
                }
            }

            // При наведении закрашиваем звезды слева направо
            starLabels.forEach((label, index) => {
                label.addEventListener('mouseover', function() {
                    const starValue = index + 1;
                    for (let i = 0; i < starLabels.length; i++) {
                        if (i <= index) {
                            starLabels[i].style.color = '#ffd700';
                        } else {
                            starLabels[i].style.color = '#555';
                        }
                    }
                });
            });

            // Обработка выбора звезд
            starInputs.forEach((input) => {
                input.addEventListener('change', function() {
                    if (this.checked) {
                        const value = parseInt(this.value);
                        updateStarsColor(value);
                    }
                });
            });

            // Возврат к выбранному значению при уходе мыши
            const container = document.querySelector('.feedback-stars');
            if (container) {
                container.addEventListener('mouseleave', function() {
                    const checked = document.querySelector('.feedback-stars input[type="radio"]:checked');
                    if (checked) {
                        updateStarsColor(parseInt(checked.value));
                    } else {
                        for (let i = 0; i < starLabels.length; i++) {
                            starLabels[i].style.color = '#555';
                        }
                    }
                });
            }

            // Валидация формы перед отправкой
            if (feedbackForm) {
                feedbackForm.addEventListener('submit', function(e) {
                    const selectedRating = document.querySelector('.feedback-stars input[type="radio"]:checked');
                    if (!selectedRating) {
                        e.preventDefault();
                        showTopNotification('Пожалуйста, выберите количество звезд (оценку)', 'error');
                        return false;
                    }
                });
            }

            // Инициализация при загрузке
            const checkedInput = document.querySelector('.feedback-stars input[type="radio"]:checked');
            if (checkedInput) {
                updateStarsColor(parseInt(checkedInput.value));
            }
        });

        // Функция для показа уведомления
        function showTopNotification(message, type) {
            const existing = document.querySelector('.top-notification');
            if (existing) existing.remove();

            const notif = document.createElement('div');
            notif.className = `top-notification ${type}`;
            notif.innerHTML = `
        <div class="Notification-content">
            <div class="Notification-message">${message}</div>
            <button class="notification-close" id="closeNotifBtn">✕</button>
        </div>
    `;
            document.body.appendChild(notif);

            const closeBtn = notif.querySelector('.notification-close');
            closeBtn.addEventListener('click', function() {
                notif.remove();
            });

            setTimeout(() => {
                if (notif) notif.remove();
            }, 4000);
        }

        // Карусель
        const track = document.getElementById('carouselTrack');
        const dotsContainer = document.getElementById('carouselDots');
        let currentCarouselIndex = 0;
        let carouselItems = [];
        let totalCarouselItems = 0;
        let itemsPerView = 3;
        let autoPlayInterval;

        function getItemsPerView() {
            if (window.innerWidth <= 768) return 1;
            if (window.innerWidth <= 992) return 2;
            return 3;
        }

        function cloneItemsForInfinite() {
            const originalItems = document.querySelectorAll('.SecondAboutItem');
            const container = track;
            const itemsArray = Array.from(originalItems);

            container.innerHTML = '';

            const clonesStart = itemsArray.slice(-itemsPerView).map(item => item.cloneNode(true));
            const clonesEnd = itemsArray.slice(0, itemsPerView).map(item => item.cloneNode(true));

            clonesStart.forEach(clone => container.appendChild(clone));
            itemsArray.forEach(item => container.appendChild(item.cloneNode(true)));
            clonesEnd.forEach(clone => container.appendChild(clone));

            carouselItems = document.querySelectorAll('.SecondAboutItem');
            totalCarouselItems = carouselItems.length;

            currentCarouselIndex = itemsPerView;
            updateCarousel(false);
        }

        function updateCarousel(animate = true) {
            if (!carouselItems.length) return;
            const itemWidth = carouselItems[0]?.offsetWidth || 0;
            const gap = 30;
            const translateX = -(currentCarouselIndex * (itemWidth + gap));

            if (animate) {
                track.style.transition = 'transform 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            } else {
                track.style.transition = 'none';
            }

            track.style.transform = `translateX(${translateX}px)`;
            updateDots();

            setTimeout(() => {
                const originalStartIndex = itemsPerView;
                const originalEndIndex = totalCarouselItems - itemsPerView - 1;

                if (currentCarouselIndex >= originalEndIndex) {
                    track.style.transition = 'none';
                    currentCarouselIndex = originalStartIndex;
                    const newTranslateX = -(currentCarouselIndex * (itemWidth + gap));
                    track.style.transform = `translateX(${newTranslateX}px)`;
                } else if (currentCarouselIndex < originalStartIndex) {
                    track.style.transition = 'none';
                    currentCarouselIndex = originalEndIndex - 1;
                    const newTranslateX = -(currentCarouselIndex * (itemWidth + gap));
                    track.style.transform = `translateX(${newTranslateX}px)`;
                }
            }, 500);
        }

        function updateDots() {
            if (!dotsContainer) return;
            const dotsCount = Math.ceil((totalCarouselItems - itemsPerView * 2) / itemsPerView);
            const actualIndex = currentCarouselIndex - itemsPerView;
            let currentDotIndex = Math.floor(actualIndex / itemsPerView);

            if (currentDotIndex >= dotsCount) currentDotIndex = dotsCount - 1;
            if (currentDotIndex < 0) currentDotIndex = 0;

            dotsContainer.innerHTML = '';
            for (let i = 0; i < dotsCount; i++) {
                const dot = document.createElement('div');
                dot.classList.add('CarouselDot');
                if (i === currentDotIndex) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    currentCarouselIndex = itemsPerView + (i * itemsPerView);
                    updateCarousel(true);
                    resetAutoPlay();
                });
                dotsContainer.appendChild(dot);
            }
        }

        function nextSlide() {
            currentCarouselIndex++;
            updateCarousel(true);
            resetAutoPlay();
        }

        function prevSlide() {
            currentCarouselIndex--;
            updateCarousel(true);
            resetAutoPlay();
        }

        function startAutoPlay() {
            if (autoPlayInterval) clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(() => nextSlide(), 1000000);
        }

        function resetAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                startAutoPlay();
            }
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        function initCarousel() {
            itemsPerView = getItemsPerView();
            cloneItemsForInfinite();

            // Получаем кнопки и добавляем обработчики
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (prevBtn) {
                // Удаляем старые обработчики через клонирование
                const newPrevBtn = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
                document.getElementById('prevBtn').addEventListener('click', prevSlide);
            }

            if (nextBtn) {
                const newNextBtn = nextBtn.cloneNode(true);
                nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
                document.getElementById('nextBtn').addEventListener('click', nextSlide);
            }

            startAutoPlay();
        }

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                stopAutoPlay();
                itemsPerView = getItemsPerView();
                initCarousel();
            }, 250);
        });

        setTimeout(() => initCarousel(), 100);

        const carouselContainer = document.querySelector('.CarouselWrapper');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', stopAutoPlay);
            carouselContainer.addEventListener('mouseleave', startAutoPlay);
        }

        // Анимация при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.GalleryItem, .TariffItem, .QuickLink, .SecondAboutItem, .CoachCard, .review-block, .MapContainer, .CallbackForm, .AboutBlock, .ContentSecondPage');
            animatedElements.forEach((el, index) => {
                el.classList.add('fade-up');
                if (index % 3 === 0) el.classList.add('delay-1');
                if (index % 3 === 1) el.classList.add('delay-2');
                if (index % 3 === 2) el.classList.add('delay-3');
            });
            const titles = document.querySelectorAll('.GalleryTitleP, .TariffsTitleP, .MapTitleP, .NameSecondPageP, .CoachListTitle, .ContactsPageTitle');
            titles.forEach(title => title.classList.add('fade-scale'));
            const leftContents = document.querySelectorAll('.LeftContentSecondPage, .LeftContentFifthPage');
            leftContents.forEach(el => el.classList.add('fade-left'));
            const rightContents = document.querySelectorAll('.RightContentSecondPage, .RightContentFifthPage, .MapInfo');
            rightContents.forEach(el => el.classList.add('fade-right'));

            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return rect.top <= window.innerHeight - 100 && rect.bottom >= 0;
            }

            function checkVisibility() {
                document.querySelectorAll('.fade-up, .fade-left, .fade-right, .fade-scale').forEach(el => {
                    if (isElementInViewport(el) && !el.classList.contains('visible')) el.classList.add('visible');
                });
            }
            checkVisibility();
            window.addEventListener('scroll', checkVisibility);
            window.addEventListener('resize', checkVisibility);
        });

        // Burger menu
        const burger = document.getElementById('burgerMenu');
        const navMenu = document.querySelector('.LowerPartRHP');
        if (burger && navMenu) {
            burger.addEventListener('click', function() {
                burger.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    burger.classList.remove('active');
                    navMenu.classList.remove('active');
                });
            });
        }
    </script>
</body>

</html>