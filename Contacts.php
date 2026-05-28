<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

// Обработка AJAX запроса для FAQ формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['callback_requests'])) {
    header('Content-Type: application/json');

    $phone = trim($_POST['phone'] ?? '');
    $cleanPhone = preg_replace('/\D/', '', $phone);

    if (strlen($cleanPhone) >= 11 && substr($cleanPhone, 0, 1) == '7') {
        try {
            $stmt = $pdo->prepare("INSERT INTO callback_requests (phone, created_at) VALUES (?, NOW())");
            $stmt->execute([$cleanPhone]);
            echo json_encode(['success' => true, 'message' => 'Спасибо! Скоро мы вам перезвоним.']);
            exit;
        } catch (PDOException $e) {
            error_log("Ошибка INSERT в callback_requests: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных. Попробуйте позже.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Введите корректный номер телефона']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php render_seo_meta($seo); ?>

    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura&family=Karantina:wght@300;400;700&family=League+Gothic&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/photo-logo.png">
    <style>
        .faq-page {
            background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 20px 0px 30px 0px;
        }

        .faq-title {
            text-align: center;
            font-size: 48px;
            font-weight: 700;
            color: pink;
            margin-bottom: 20px;
        }

        .faq-subtitle {
            text-align: center;
            font-size: 18px;
            color: #ccc;
            margin-bottom: 50px;
        }

        .faq-section {
            display: flex;
            flex-direction: column;
            width: 60%;
            margin-bottom: 40px;
            background: rgba(30, 29, 29, 0.8);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 192, 203, 0.3);
        }

        .faq-section-header {
            padding: 20px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .faq-section-header:hover {
            background: rgba(255, 192, 203, 0.1);
        }

        .faq-section-header h2 {
            font-size: 28px;
            color: pink;
            margin: 0;
        }

        .faq-section-toggle {
            font-size: 30px;
            color: pink;
            transition: transform 0.3s;
        }

        .faq-section-header.open .faq-section-toggle {
            transform: rotate(180deg);
        }

        .faq-items-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
        }

        .faq-items-container.open {
            max-height: 1000px;
            transition: max-height 0.5s ease-in;
        }

        .faq-item {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0;
        }

        .faq-question {
            padding: 18px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .faq-question:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .faq-question span:first-child {
            font-size: 18px;
            color: white;
            font-weight: 500;
        }

        .faq-question-icon {
            font-size: 22px;
            color: pink;
            transition: transform 0.3s;
        }

        .faq-question.open .faq-question-icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding: 0 30px;
            background: rgba(0, 0, 0, 0.3);
        }

        .faq-answer.open {
            max-height: 200px;
            padding: 20px 30px;
        }

        .faq-answer p {
            font-size: 16px;
            color: #ddd;
            line-height: 1.6;
            margin: 0;
        }

        .callback-section-faq {
            margin-top: 60px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .callback-form-faq {
            background: linear-gradient(145deg, rgba(35, 35, 35, 0.95), rgba(25, 25, 25, 0.95));
            border-radius: 25px;
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(255, 192, 203, 0.3);
        }

        .callback-form-faq h3 {
            font-size: 32px;
            color: pink;
            margin-bottom: 15px;
        }

        .callback-form-faq p {
            font-size: 16px;
            color: #ccc;
            margin-bottom: 25px;
        }

        .callback-input-faq {
            width: 100%;
            padding: 15px 20px;
            background-color: #444;
            border: 1px solid #666;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .callback-input-faq:focus {
            outline: none;
            border-color: pink;
        }

        .callback-checkboxes-faq {
            text-align: left;
            margin-bottom: 25px;
        }

        .callback-checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #ccc;
            cursor: pointer;
        }

        .callback-checkbox-label input {
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        .callback-checkbox-label a {
            color: pink;
            font-size: 14px;
        }

        .callback-btn-faq {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #313131;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .callback-btn-faq:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 143, 171, 0.3);
        }

        .top-notification {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            min-width: 320px;
            max-width: 500px;
            padding: 16px 25px;
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
        }

        .top-notification .notification-close {
            cursor: pointer;
            font-size: 22px;
            font-weight: bold;
            color: white;
            transition: transform 0.2s;
            background: none;
            border: none;
            padding: 0 5px;
            line-height: 1;
        }

        .top-notification .notification-close:hover {
            transform: scale(1.2);
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

        /* Анимация */
        .fade-up {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .fade-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-scale.visible {
            opacity: 1;
            transform: scale(1);
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

        @media (max-width: 768px) {
            .faq-title {
                font-size: 36px;
            }

            .faq-section-header h2 {
                font-size: 22px;
            }

            .faq-question span:first-child {
                font-size: 16px;
            }

            .faq-answer.open {
                padding: 15px 20px;
            }

            .faq-question {
                padding: 15px 20px;
            }

            .faq-section-header {
                padding: 15px 20px;
            }

            .top-notification {
                top: 70px;
                min-width: 280px;
                padding: 12px 20px;
                font-size: 14px;
            }

            .faq-section {
                width: 90%;
            }
        }
    </style>
</head>

<body id="Faqq">
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

    <div class="page-content">
        <main class="faq-page">
            <h1 class="faq-title fade-scale">Часто задаваемые вопросы</h1>
            <p class="faq-subtitle fade-up">Ответы на самые популярные вопросы о нашем спортзале</p>

            <div class="faq-section fade-up">
                <div class="faq-section-header" onclick="toggleSection(this)">
                    <p>Абонементы</p>
                    <span class="faq-section-toggle">▼</span>
                </div>
                <div class="faq-items-container">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Как можно оплатить абонемент?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Мы принимаем оплату наличными, банковскими картами (Visa, MasterCard, МИР), а также безналичным переводом по счету. Оплатить можно на ресепшене клуба.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Можно ли заморозить абонемент?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Да, вы можете заморозить абонемент на срок до 90 дней в зависимости от тарифа. Для этого необходимо написать заявление администратору клуба за 7 дней до планируемой заморозки.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Есть ли пробный период?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Да, вы можете приобрести тест-драйв на один день всего за 800₽. Это позволит вам оценить все зоны клуба, тренажеры и атмосферу перед покупкой полноценного абонемента.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Действуют ли скидки для студентов?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Да, для студентов очной формы обучения предоставляется скидка 15% на любой абонемент при предъявлении студенческого билета.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="faq-section fade-up delay-1">
                <div class="faq-section-header" onclick="toggleSection(this)">
                    <p>Оплата и возвраты</p>
                    <span class="faq-section-toggle">▼</span>
                </div>
                <div class="faq-items-container">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Как вернуть деньги за абонемент?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Возврат средств осуществляется согласно договору оферты. Для этого необходимо написать заявление на ресепшене клуба. Средства возвращаются за вычетом фактически использованных дней.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Можно ли оплатить абонемент в рассрочку?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Да, мы сотрудничаем с банками-партнерами, которые предоставляют рассрочку на 3, 6 или 12 месяцев без переплаты. Подробности уточняйте у администраторов клуба.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Есть ли скрытые комиссии?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Нет, все комиссии и сборы прозрачны и указаны в договоре. Вы платите только стоимость абонемента без дополнительных наценок.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="faq-section fade-up delay-2">
                <div class="faq-section-header" onclick="toggleSection(this)">
                    <p>Тренировки и занятия</p>
                    <span class="faq-section-toggle">▼</span>
                </div>
                <div class="faq-items-container">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Нужно ли записываться на групповые занятия?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Нет, записываться не надо, так как количество мест неограничено.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Что взять с собой на первую тренировку?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Возьмите удобную спортивную форму, сменную обувь, полотенце, бутылку воды и хорошее настроение. Для посещения бассейна необходима шапочка и резиновые тапочки.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleQuestion(this)">
                            <span>Есть ли занятия для начинающих?</span>
                            <span class="faq-question-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Конечно! У нас есть группы для начинающих по всем направлениям: плавание, йога, единоборства. Также проводится вводная тренировка с тренером при покупке абонемента.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="CallbackSection">
                <div class="CallbackForm">
                    <p class="CallbackTitle">Остались вопросы?</p>
                    <p class="CallbackSubtitle">Напиши свой номер телефона и наша служба заботы ответит на них</p>
                    <form id="callbackForm" onsubmit="submitCallback(event)">
                        <input type="tel" id="callbackPhone" class="CallbackInput" placeholder="+7 (___) ___ __-__" required>
                        <?php echo csrf_field(); ?>
                        <?php echo honeypot_field(); ?>
                        <div class="CallbackCheckboxes">
                            <label class="CheckboxLabel">
                                <input type="checkbox" id="agreePolicy" required>
                                Согласен на <a href="#">обработку персональных данных</a>
                            </label>
                            <label class="CheckboxLabel" style="margin-top: 10px;">
                                <input type="checkbox" id="agreeInfo" required>
                                Я согласен на получение <a href="#"> информационных материалов</a>
                            </label>
                        </div>
                        <button type="submit" class="CallbackBtn">Жду звонка</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

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
                <p>Может быть полезно</p>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="AboutUs.php">О нас</a></li>
                    <li><a href="Coach.php">Тренеры</a></li>
                    <li><a href="Services.php">Услуги</a></li>
                    <li><a href="Schedule.php">Групповые</a></li>
                    <li><a href="#Faqq">F.A.Q.</a></li>
                </ul>
            </div>
            <div class="FooterCol FooterCol3">
                <p>Документы</p>
                <ul>
                    <li><a href="#">Пользовательское соглашение</a></li>
                    <li><a href="#">Политика конфиденциальности</a></li>
                    <li><a href="#">Обработка персональных данных</a></li>
                    <li><a href="#">Публичная оферта</a></li>
                </ul>
            </div>
            <div class="FooterCol FooterCol4">
                <p>Социальные сети</p>
                <div class="FooterSocials">
                    <a href="#"><img src="assets/img/vk.png" alt="VK"></a>
                    <a href="#"><img src="assets/img/max-app-icon-on-a-transparent-background-free-png.webp" alt="Max"></a>
                </div>
                <p class="FooterCopyright">© 2026 BodyGym. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        });

        // Функции для FAQ аккордеона
        function toggleSection(header) {
            const section = header.parentElement;
            const container = section.querySelector('.faq-items-container');
            const toggleIcon = header.querySelector('.faq-section-toggle');

            container.classList.toggle('open');
            header.classList.toggle('open');

            if (toggleIcon) {
                toggleIcon.style.transform = container.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        function toggleQuestion(question) {
            const answer = question.parentElement.querySelector('.faq-answer');
            const icon = question.querySelector('.faq-question-icon');

            answer.classList.toggle('open');
            question.classList.toggle('open');

            if (icon) {
                icon.style.transform = answer.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
        // Форматирование телефона
        function formatPhoneNumber(input) {
            let digits = input.value.replace(/\D/g, '');
            if (digits.startsWith('8')) digits = '7' + digits.substring(1);
            let formatted = '';
            if (digits.length > 0) formatted = '+7 ';
            if (digits.length > 1) formatted += digits.substring(1, 4);
            if (digits.length > 4) formatted += '  ' + digits.substring(4, 7);
            if (digits.length > 7) formatted += '  ' + digits.substring(7, 9);
            if (digits.length > 9) formatted += '-' + digits.substring(9, 11);
            input.value = formatted;
        }

        // Форматирование телефона при вводе
        document.querySelectorAll('input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
        });

        // Обратный звонок с сохранением в БД
        function submitCallback(event) {
            event.preventDefault();
            const phone = document.getElementById('callbackPhone').value;
            const agreePolicy = document.getElementById('agreePolicy').checked;
            const agreeInfo = document.getElementById('agreeInfo').checked;

             const csrfToken = document.querySelector('#callbackForm input[name="csrf_token"]')?.value;

            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 11 || !cleanPhone.startsWith('7')) {
                showNotification('Введите корректный номер телефона', 'error');
                return;
            }
            if (!agreePolicy || !agreeInfo) {
                showNotification('Подтвердите согласие на обработку данных', 'error');
                return;
            }

            // Отправка данных на сервер
            const formData = new FormData();
            formData.append('action', 'callback');
            formData.append('phone', cleanPhone);

             if (csrfToken) formData.append('csrf_token', csrfToken);

            const submitBtn = document.querySelector('#callbackForm .CallbackBtn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Отправка...';
            submitBtn.disabled = true;

            fetch('submit_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Спасибо! Скоро мы вам перезвоним.', 'success');
                        document.getElementById('callbackForm').reset();
                        document.getElementById('callbackPhone').value = '';
                        document.getElementById('agreePolicy').checked = false;
                        document.getElementById('agreeInfo').checked = false;
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Ошибка при отправке. Попробуйте позже.', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        }

        function showNotification(message, type) {
            const oldNotification = document.getElementById('notification');
            if (oldNotification) {
                oldNotification.remove();
            }

            const notification = document.createElement('div');
            notification.id = 'notification';
            notification.className = `Notification ${type}`;
            notification.innerHTML = `
        <div class="Notification-content">
            <div class="Notification-message">${message}</div>
            <button class="Notification-close" id="closeNotifBtn">✕</button>
        </div>
    `;

            document.body.appendChild(notification);
            notification.style.display = 'block';

            // Добавляем обработчик событий
            const closeBtn = notification.querySelector('#closeNotifBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    notification.remove();
                });
            }
        }

        // Анимация при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.faq-section, .CallbackSection');
            animatedElements.forEach((el, index) => {
                el.classList.add('fade-up');
                if (index % 3 === 0) el.classList.add('delay-1');
                if (index % 3 === 1) el.classList.add('delay-2');
                if (index % 3 === 2) el.classList.add('delay-3');
            });
            const titles = document.querySelectorAll('.faq-title');
            titles.forEach(title => title.classList.add('fade-scale'));

            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return rect.top <= window.innerHeight - 100 && rect.bottom >= 0;
            }

            function checkVisibility() {
                document.querySelectorAll('.fade-up, .fade-scale').forEach(el => {
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