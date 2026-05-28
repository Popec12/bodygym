<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

function ensureApplicationsTable($pdo)
{
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'applications'");
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE applications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NULL DEFAULT NULL,
                name VARCHAR(100) NOT NULL,
                surname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                birthdate DATE NOT NULL,
                gender VARCHAR(10) NOT NULL,
                experience VARCHAR(50) NOT NULL,
                tariff_name VARCHAR(50) NOT NULL,
                tariff_price INT NOT NULL,
                message TEXT,
                status VARCHAR(50) DEFAULT 'В обработке',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            error_log("Таблица applications обновлена");
        } else {
            // Добавляем недостающие колонки
            $columns = ['birthdate', 'gender', 'experience', 'tariff_name', 'tariff_price'];
            foreach ($columns as $col) {
                $stmt = $pdo->query("SHOW COLUMNS FROM applications WHERE Field = '$col'");
                if ($stmt->rowCount() == 0) {
                    if ($col == 'birthdate') {
                        $pdo->exec("ALTER TABLE applications ADD COLUMN birthdate DATE");
                    } elseif ($col == 'gender') {
                        $pdo->exec("ALTER TABLE applications ADD COLUMN gender VARCHAR(10)");
                    } elseif ($col == 'experience') {
                        $pdo->exec("ALTER TABLE applications ADD COLUMN experience VARCHAR(50)");
                    } elseif ($col == 'tariff_name') {
                        $pdo->exec("ALTER TABLE applications ADD COLUMN tariff_name VARCHAR(50)");
                    } elseif ($col == 'tariff_price') {
                        $pdo->exec("ALTER TABLE applications ADD COLUMN tariff_price INT");
                    }
                }
            }
        }
        return true;
    } catch (PDOException $e) {
        error_log("Ошибка при проверке таблицы applications: " . $e->getMessage());
        return false;
    }
}

function validateBirthdate($birthdate)
{
    if (empty($birthdate)) return false;
    $date = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$date || $date->format('Y-m-d') !== $birthdate) return false;
    $minDate = new DateTime('1900-01-01');
    $maxDate = new DateTime();
    $age = $maxDate->diff($date)->y;
    if ($date < $minDate || $date > $maxDate) return false;
    if ($age < 16) return false;
    return true;
}

// Создаём/проверяем таблицу перед обработкой запроса
ensureApplicationsTable($pdo);

// Обработка AJAX заявки на тест-драйв
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test_drive'])) {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $tariff_name = 'Platinum (Тест-драйв)';
    $tariff_price = 800;
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    $user_id = isLoggedIn() ? getCurrentUserId() : null;

    $errors = [];
    if (strlen($name) < 2) $errors[] = 'Имя слишком короткое (минимум 2 символа)';
    if (strlen($surname) < 2) $errors[] = 'Фамилия слишком короткая (минимум 2 символа)';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Неверный формат email';

    $cleanPhone = preg_replace('/\D/', '', $phone);
    if (strlen($cleanPhone) < 11) $errors[] = 'Неверный номер телефона (нужно 11 цифр)';
    if (!validateBirthdate($birthdate)) $errors[] = 'Укажите корректную дату рождения (от 16 лет, не ранее 1900 года)';
    if (empty($gender)) $errors[] = 'Укажите пол';
    if (empty($experience)) $errors[] = 'Укажите опыт в спорте';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO applications 
                (user_id, name, surname, email, phone, birthdate, gender, experience, tariff_name, tariff_price, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'В обработке', NOW())");
            $stmt->execute([$user_id, $name, $surname, $email, $cleanPhone, $birthdate, $gender, $experience, $tariff_name, $tariff_price, $message]);

            echo json_encode(['success' => true, 'message' => 'Заявка на тест-драйв успешно отправлена! Менеджер свяжется с вами.']);
            exit;
        } catch (PDOException $e) {
            error_log("Ошибка INSERT: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
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
        .PromoSection {
            padding: 40px 20px 80px;
        }

        .PromoTitle {
            text-align: center;
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 50px;
            color: pink;
        }

        .PromoGrid {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            max-width: 1400px;
            margin: 0 auto;
        }

        .PromoCard {
            width: 350px;
            background: linear-gradient(145deg, rgba(35, 35, 35, 0.95), rgba(25, 25, 25, 0.95));
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(255, 192, 203, 0.3);
            transition: transform 0.3s;
        }

        .PromoCard:hover {
            transform: translateY(-5px);
            border-color: pink;
        }

        .PromoCard h3 {
            font-size: 26px;
            color: pink;
            margin-bottom: 15px;
        }

        .PromoCard p {
            font-size: 16px;
            color: #ccc;
            line-height: 1.5;
        }

        .TariffItem {
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            height: 540px;
        }

        .TariffItem:hover {
            transform: scale(1.03);
            box-shadow: 0 0 25px rgba(255, 192, 203, 0.5);
            border-color: pink;
        }

        .TariffFeatures {
            flex: 1;
        }

        .TariffBtn {
            margin-top: auto;
        }
    </style>
</head>

<body id="serv">
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
        <main class="services-page">
            <div class="FourthPage2">
                <div class="NameFourthPage2">
                    <p class="NameFourthPageP">Выберите свою карту</p>
                </div>
                <div class="TariffsItems">
                    <div class="TariffItem fade-up">
                        <img src="assets/img/фиолетовый.png" alt="" class="TariffLogo">
                        <h2 class="TariffName">Platinum</h2>
                        <div class="TariffPrice">3 900 ₽<span>/мес</span></div>
                        <ul class="TariffFeatures">
                            <li>Безлимитный доступ в зал</li>
                            <li>Вводная тренировка с тренером</li>
                            <li>20+ групповых тренировок</li>
                            <li>Гостевой доступ для друзей</li>
                            <li>Семейный доступ</li>
                            <li>Спа-зона</li>
                        </ul>
                        <button class="TariffBtn" onclick="openTariffModal('Platinum', 3900)">Оформить</button>
                    </div>
                    <div class="TariffItem fade-up delay-1">
                        <img src="assets/img/желтый.png" alt="" class="TariffLogo">
                        <h2 class="TariffName">Gold</h2>
                        <div class="TariffPrice">2 900 ₽<span>/мес</span></div>
                        <ul class="TariffFeatures">
                            <li>Безлимитный доступ в зал</li>
                            <li>Вводная тренировка с тренером</li>
                            <li>10+ групповых тренировок</li>
                            <li>Спа-зона</li>
                        </ul>
                        <button class="TariffBtn" onclick="openTariffModal('Gold', 2900)">Оформить</button>
                    </div>
                    <div class="TariffItem fade-up delay-2">
                        <img src="assets/img/оранжевый.png" alt="" class="TariffLogo">
                        <h2 class="TariffName">Standart</h2>
                        <div class="TariffPrice">1 900 ₽<span>/мес</span></div>
                        <ul class="TariffFeatures">
                            <li>Безлимитный доступ в зал</li>
                            <li>Вводная тренировка с тренером</li>
                            <li>5+ групповых тренировок</li>
                        </ul>
                        <button class="TariffBtn" onclick="openTariffModal('Standart', 1900)">Оформить</button>
                    </div>
                </div>
            </div>

            <div class="PromoSection fade-up">
                <h2 class="PromoTitle">Акции и предложения</h2>
                <div class="PromoGrid">
                    <div class="PromoCard">
                        <h3>Студентам скидка 15%</h3>
                        <p>Предъявите студенческий билет и получите скидку 15% на любой абонемент.</p>
                    </div>
                    <div class="PromoCard">
                        <h3>Скидка в день рождения</h3>
                        <p>В ваш праздник мы дарим скидку 20% на оформление любого абонемента.</p>
                    </div>
                    <div class="PromoCard">
                        <h3>Приведи друга</h3>
                        <p>Приведите друга в зал и получите месяц бесплатно!</p>
                    </div>
                </div>
            </div>

            <div class="FormBlock fade-up">
                <div class="form">
                    <div class="application-form">
                        <h2 class="form-title">Пробный урок по тарифу "Platinum"</h2>
                        <p style="text-align: center; color: #ccc; margin-bottom: 25px;">Всего за 800₽ вы получите полный доступ ко всем зонам зала на один день!</p>
                        <button type="button" class="testform-btn" onclick="openTestDriveModal()">Попробовать</button>
                    </div>
                </div>
                <div class="ImgForm">
                    <img src="assets/img/form.jpg" alt="Тренажерный зал BodyGym" class="ImgForm-img">
                </div>
            </div>
        </main>
    </div>

    <!-- Модальное окно для абонемента -->
    <div id="tariffModal" class="Modal">
        <div class="ModalContent ModalSmall">
            <span class="ModalClose" onclick="closeTariffModal()">&times;</span>
            <div class="ModalBody">
                <h3 class="TariffFormTitle" id="tariffModalTitle">Оформление абонемента</h3>
                <p class="TariffFormSubtitle">Пожалуйста, заполните все поля. Это нужно для оформления договора</p>
                <form id="tariffOrderForm" onsubmit="submitTariffOrder(event)">
                    <input type="hidden" id="tariffName" name="tariff_name">
                    <input type="hidden" id="tariffPrice" name="tariff_price">
                    <?php echo csrf_field(); ?>
                    <?php echo honeypot_field(); ?>
                    <div class="TariffFormGrid">
                        <div class="TariffFormField"><label class="TariffFormLabel">Имя</label><input type="text" id="orderName" class="TariffFormInput" placeholder="Ваше имя" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Фамилия</label><input type="text" id="orderSurname" class="TariffFormInput" placeholder="Ваша фамилия" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Дата рождения <span class="HelpIcon" data-tooltip="Заниматься можно только с 16 лет">!</span></label><input type="date" id="orderBirthdate" class="TariffFormInput" required min="1900-01-01" max="<?php echo date('Y-m-d'); ?>"></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Email</label><input type="email" id="orderEmail" class="TariffFormInput" placeholder="example@mail.ru" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Опыт в спорте</label><select id="orderExperience" class="TariffFormInput" required>
                                <option value="" style="font-size: 14px;">Выберите ваш уровень</option>
                                <option value="beginner" style="font-size: 13px;">Новичок (менее 3 месяцев)</option>
                                <option value="intermediate" style="font-size: 13px;">Любитель (3-12 месяцев)</option>
                                <option value="advanced" style="font-size: 13px;">Продвинутый (1-3 года)</option>
                                <option value="pro" style="font-size: 13px;">Профессионал (более 3 лет)</option>
                            </select></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Телефон</label><input type="tel" id="orderPhone" class="TariffFormInput phone-mask" placeholder="+7 (___) ___ __-__" required></div>
                        <div class="TariffFormField full-width"><label class="TariffFormLabel">Пол</label>
                            <div class="GenderGroup"><label class="GenderLabel"><input type="radio" name="gender" value="male" required> Мужчина</label><label class="GenderLabel"><input type="radio" name="gender" value="female" required> Женщина</label></div>
                        </div>
                        <div class="TariffFormField full-width">
                            <label class="CheckboxLabel" style="justify-content: center; gap: 10px;">
                                <input type="checkbox" id="agreeTerms" required>
                                Нажимая кнопку, вы соглашаетесь с <a href="#" target="_blank">Пользовательским соглашением</a> и
                                <a href="#" target="_blank">Политикой конфиденциальности</a>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="TariffModalBtn">Купить</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для тест-драйва -->
    <div id="testDriveModal" class="Modal">
        <div class="ModalContent ModalSmall">
            <span class="ModalClose" onclick="closeTestDriveModal()">&times;</span>
            <div class="ModalBody">
                <h3 class="TariffFormTitle">Оформление пробного урока по тарифу "Platinum"</h3>
                <p class="TariffFormSubtitle">Пожалуйста, заполните все поля. Менеджер свяжется с вами.</p>
                <form id="testDriveOrderForm">
                    <?php echo csrf_field(); ?>
                    <?php echo honeypot_field(); ?>
                    <div class="TariffFormGrid">
                        <div class="TariffFormField"><label class="TariffFormLabel">Имя</label><input type="text" id="testOrderName" class="TariffFormInput" placeholder="Ваше имя" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Фамилия</label><input type="text" id="testOrderSurname" class="TariffFormInput" placeholder="Ваша фамилия" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Дата рождения <span class="HelpIcon" data-tooltip="Заниматься можно только с 16 лет">!</span></label><input type="date" id="testOrderBirthdate" class="TariffFormInput" required min="1900-01-01" max="<?php echo date('Y-m-d'); ?>"></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Email</label><input type="email" id="testOrderEmail" class="TariffFormInput" placeholder="example@mail.ru" required></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Опыт в спорте</label><select id="testOrderExperience" class="TariffFormInput" required>
                                <option value="" style="font-size: 14px;">Выберите ваш уровень</option>
                                <option value="beginner" style="font-size: 13px;">Новичок (менее 3 месяцев)</option>
                                <option value="intermediate" style="font-size: 13px;">Любитель (3-12 месяцев)</option>
                                <option value="advanced" style="font-size: 13px;">Продвинутый (1-3 года)</option>
                                <option value="pro" style="font-size: 13px;">Профессионал (более 3 лет)</option>
                            </select></div>
                        <div class="TariffFormField"><label class="TariffFormLabel">Телефон</label><input type="tel" id="testOrderPhone" class="TariffFormInput phone-mask" placeholder="+7 (___) ___ __-__" required></div>
                        <div class="TariffFormField full-width"><label class="TariffFormLabel">Пол</label>
                            <div class="GenderGroup"><label class="GenderLabel"><input type="radio" name="testGender" value="male" required> Мужчина</label><label class="GenderLabel"><input type="radio" name="testGender" value="female" required> Женщина</label></div>
                        </div>
                        <div class="TariffFormField full-width"><label class="TariffFormLabel">Комментарий (необязательно)</label><textarea name="message" id="testOrderMessage" rows="2" class="TariffFormInput" placeholder="Например: Хочу попробовать тренировку с тренером..."></textarea></div>
                        <div class="TariffFormField full-width">
                            <label class="CheckboxLabel" style="justify-content: center; gap: 10px;">
                                <input type="checkbox" id="agreeTermsTest" required>
                                Нажимая кнопку, вы соглашаетесь с <a href="#" target="_blank">Пользовательским соглашением</a> и
                                <a href="#" target="_blank">Политикой конфиденциальности</a>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="TariffModalBtn">Записаться</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="NewFooter">
        <div class="FooterContainer">
            <div class="FooterCol FooterCol1">
                <div class="FooterLogoWrapper"><img src="assets/img/photo-logo.png" alt="" class="FooterImgLogo"><span class="FooterLogoName">BodyGym</span></div>
                <p class="FooterDesc">Пространство для тех, кто стремится к совершенству. Современный спортзал с профессиональным подходом.</p>
            </div>
            <div class="FooterCol FooterCol2">
                <h4>Может быть полезно</h4>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="AboutUs.php">О нас</a></li>
                    <li><a href="Coach.php">Тренеры</a></li>
                    <li><a href="#serv">Услуги</a></li>
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

    <script>
        function formatPhoneNumber(input) {
            let digits = input.value.replace(/\D/g, '');
            if (digits.startsWith('8')) digits = '7' + digits.substring(1);
            let formatted = '';
            if (digits.length > 0) formatted = '+7';
            if (digits.length > 1) formatted += ' (' + digits.substring(1, 4);
            if (digits.length > 4) formatted += ') ' + digits.substring(4, 7);
            if (digits.length > 7) formatted += ' ' + digits.substring(7, 9);
            if (digits.length > 9) formatted += '-' + digits.substring(9, 11);
            input.value = formatted;
        }

        function showNotification(message, type) {
            const oldNotification = document.getElementById('notification');
            if (oldNotification) oldNotification.remove();

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

            const closeBtn = notification.querySelector('#closeNotifBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    notification.remove();
                });
            }
            setTimeout(() => {
                if (notification) notification.remove();
            }, 5000);
        }

        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        });

        document.querySelectorAll('.phone-mask').forEach(input => {
            input.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
            if (input.value) formatPhoneNumber(input);
        });

        function validateBirthdate(dateString) {
            if (!dateString) return false;
            const date = new Date(dateString);
            const today = new Date();
            const minDate = new Date('1900-01-01');
            if (date < minDate || date > today) return false;
            let age = today.getFullYear() - date.getFullYear();
            const monthDiff = today.getMonth() - date.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) age--;
            return age >= 16;
        }

        // Абонемент
        function openTariffModal(name, price) {
            document.getElementById('tariffName').value = name;
            document.getElementById('tariffPrice').value = price;
            document.getElementById('tariffModalTitle').innerHTML = `Оформление абонемента "${name}"`;
            document.getElementById('tariffModal').style.display = 'flex';
        }

        function closeTariffModal() {
            document.getElementById('tariffModal').style.display = 'none';
            document.getElementById('tariffOrderForm').reset();
        }

        function submitTariffOrder(event) {
            event.preventDefault();
            const name = document.getElementById('orderName').value;
            const surname = document.getElementById('orderSurname').value;
            const birthdate = document.getElementById('orderBirthdate').value;
            const email = document.getElementById('orderEmail').value;
            const experience = document.getElementById('orderExperience').value;
            const phone = document.getElementById('orderPhone').value;
            const gender = document.querySelector('input[name="gender"]:checked');
            const tariffName = document.getElementById('tariffName').value;
            const tariffPrice = document.getElementById('tariffPrice').value;

            // ДОБАВИТЬ ЭТУ СТРОКУ — получаем CSRF-токен
            const csrfToken = document.querySelector('#tariffOrderForm input[name="csrf_token"]')?.value;

            if (name.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(name)) {
                showNotification('Имя должно содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }
            if (surname.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(surname)) {
                showNotification('Фамилия должна содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }
            if (!validateBirthdate(birthdate)) return showNotification('Укажите корректную дату рождения (от 16 лет, не ранее 1900 года)', 'error');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showNotification('Введите корректный email', 'error');
            if (!experience) return showNotification('Выберите ваш уровень подготовки', 'error');
            if (phone.replace(/\D/g, '').length < 11) return showNotification('Введите корректный номер телефона', 'error');
            if (!gender) return showNotification('Укажите ваш пол', 'error');

            const cleanPhone = phone.replace(/\D/g, '');
            const formData = new FormData();
            formData.append('action', 'submit_order');
            formData.append('name', name);
            formData.append('surname', surname);
            formData.append('birthdate', birthdate);
            formData.append('email', email);
            formData.append('experience', experience);
            formData.append('phone', cleanPhone);
            formData.append('gender', gender.value);
            formData.append('tariff_name', tariffName);
            formData.append('tariff_price', tariffPrice);

            // ДОБАВИТЬ ЭТУ СТРОКУ — передаём токен на сервер
            if (csrfToken) formData.append('csrf_token', csrfToken);


            const submitBtn = document.querySelector('#tariffModal .TariffModalBtn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Отправка...';
            submitBtn.disabled = true;

            fetch('submit_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showNotification(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        closeTariffModal();
                        document.getElementById('tariffOrderForm').reset();
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

        // Тест-драйв
        function openTestDriveModal() {
            document.getElementById('testDriveModal').style.display = 'flex';
        }

        function closeTestDriveModal() {
            document.getElementById('testDriveModal').style.display = 'none';
            document.getElementById('testDriveOrderForm').reset();
        }

        document.getElementById('testDriveOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('testOrderName').value;
            const surname = document.getElementById('testOrderSurname').value;
            const birthdate = document.getElementById('testOrderBirthdate').value;
            const email = document.getElementById('testOrderEmail').value;
            const experience = document.getElementById('testOrderExperience').value;
            const phone = document.getElementById('testOrderPhone').value;
            const gender = document.querySelector('input[name="testGender"]:checked');
            const message = document.getElementById('testOrderMessage').value;
            const agreeTerms = document.getElementById('agreeTermsTest').checked;

            const csrfToken = document.querySelector('#testDriveOrderForm input[name="csrf_token"]')?.value;

            if (name.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(name)) {
                showNotification('Имя должно содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }
            if (surname.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(surname)) {
                showNotification('Фамилия должна содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }
            if (!validateBirthdate(birthdate)) return showNotification('Укажите корректную дату рождения (от 16 лет, не ранее 1900 года)', 'error');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showNotification('Введите корректный email', 'error');
            if (!experience) return showNotification('Выберите ваш уровень подготовки', 'error');
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 11) return showNotification('Введите корректный номер телефона', 'error');
            if (!gender) return showNotification('Укажите ваш пол', 'error');
            if (!agreeTerms) return showNotification('Подтвердите согласие с условиями', 'error');

            const formData = new FormData();
            formData.append('submit_test_drive', '1');
            formData.append('name', name);
            formData.append('surname', surname);
            formData.append('email', email);
            formData.append('phone', cleanPhone);
            formData.append('birthdate', birthdate);
            formData.append('gender', gender.value);
            formData.append('experience', experience);
            formData.append('message', message);

            if (csrfToken) formData.append('csrf_token', csrfToken);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Отправка...';
            submitBtn.disabled = true;

            fetch('Services.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showNotification(data.message, data.success ? 'success' : 'error');
                    if (data.success) closeTestDriveModal();
                })
                .catch(error => {
                    showNotification('Ошибка при отправке. Попробуйте позже.', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });

        window.onclick = function(event) {
            if (event.target === document.getElementById('tariffModal')) closeTariffModal();
            if (event.target === document.getElementById('testDriveModal')) closeTestDriveModal();
        }

        // Анимация при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.TariffItem, .PromoCard, .FormBlock');
            animatedElements.forEach((el, index) => {
                el.classList.add('fade-up');
                if (index % 3 === 1) el.classList.add('delay-1');
                if (index % 3 === 2) el.classList.add('delay-2');
            });

            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return rect.top <= window.innerHeight - 100 && rect.bottom >= 0;
            }

            function checkVisibility() {
                document.querySelectorAll('.fade-up').forEach(el => {
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