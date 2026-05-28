<?php
// login.php - исправленная версия
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

$error = '';
$success = '';
$active_tab = 'login';

$user = null;

// Проверяем и добавляем колонку is_admin если её нет
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {
    error_log("Ошибка при проверке/добавлении колонки is_admin: " . $e->getMessage());
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    verify_csrf();  // <--- ДОБАВИТЬ ЭТУ СТРОКУ
    verify_honeypot();  // <--- ДОБАВИТЬ ЭТУ СТРОКУ
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = isset($user['is_admin']) ? $user['is_admin'] : 0;
            if (isset($user['surname'])) {
                $_SESSION['user_surname'] = $user['surname'];
            }

            header('Location: profile.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
            $active_tab = 'login';
        }
    } else {
        $error = 'Заполните все поля';
        $active_tab = 'login';
    }
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    verify_csrf();  // <--- ДОБАВИТЬ
    verify_honeypot();  // <--- ДОБАВИТЬ
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $agree_terms = isset($_POST['agree_terms']);
    $agree_personal = isset($_POST['agree_personal']);

    // Очищаем телефон от лишних символов перед сохранением
    $cleanPhone = preg_replace('/[^\d]/', '', $phone);
    if (strlen($cleanPhone) == 11 && $cleanPhone[0] == '8') {
        $cleanPhone = '7' . substr($cleanPhone, 1);
    }

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($surname)) {
        $error = 'Заполните все поля';
        $active_tab = 'register';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
        $active_tab = 'register';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
        $active_tab = 'register';
    } elseif (strlen($cleanPhone) < 11) {
        $error = 'Введите корректный номер телефона';
        $active_tab = 'register';
    } elseif (!$agree_terms) {
        $error = 'Примите условия пользовательского соглашения';
        $active_tab = 'register';
    } elseif (!$agree_personal) {
        $error = 'Дайте согласие на обработку персональных данных';
        $active_tab = 'register';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = 'Пользователь с таким email уже существует';
            $active_tab = 'register';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, surname, email, phone, password, is_admin, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                $stmt->execute([$name, $surname, $email, $cleanPhone, $hashed_password]);

                $success = 'Регистрация успешна! Теперь войдите в систему.';
                $active_tab = 'login';
            } catch (PDOException $e) {
                $error = 'Ошибка при регистрации: ' . $e->getMessage();
                $active_tab = 'register';
            }
        }
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
        body {
            background-color: #313131;
        }

        .auth-container {
            padding-top: 200px;
            min-height: calc(100vh - 300px);
        }

        @media (max-width: 992px) {
            .auth-container {
                padding-top: 220px;
            }
        }

        @media (max-width: 768px) {
            .auth-container {
                padding-top: 200px;
            }
        }

        @media (max-width: 576px) {
            .auth-container {
                padding-top: 180px;
            }
        }

        .auth-checkboxes {
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: start;
            text-align: left;
            gap: 10px;
        }

        .auth-checkbox-label {
            display: flex;
            align-items: end;
            gap: 5px;
            font-size: 12px;
            color: #ccc;
            cursor: pointer;
        }

        .auth-checkbox-label input {
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .auth-checkbox-label a {
            color: pink;
            font-size: 12px;
            text-decoration: none;
        }

        .auth-checkbox-label a:hover {
            text-decoration: underline;
        }

        /* Уведомления */
        .notification-overlay {
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            min-width: 200px;
            max-width: 500px;
            background-color: #2e7d32;
            color: white;
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

        .notification-overlay.error {
            background-color: #c62828;
        }

        .notification-overlay .notification-message {
            flex: 1;
            text-align: left;
            font-size: 14px;
        }

        .notification-overlay .notification-close {
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: white;
            transition: color 0.3s;
            background: none;
            border: none;
            padding: 0 5px;
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
    </style>
</head>

<body>
    <header class="Header" id="mainHeader" style="background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95));">
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

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-tabs">
                <div class="auth-tab <?php echo $active_tab == 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">Вход</div>
                <div class="auth-tab <?php echo $active_tab == 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">Регистрация</div>
            </div>

            <!-- Форма входа -->
            <form id="login-form" class="auth-form active" method="POST" action="">
                <input type="hidden" name="login" value="1">
                <?php echo csrf_field(); ?> <!-- ЗАЩИТА ОТ CSRF -->
                <?php echo honeypot_field(); ?> <!-- ЗАЩИТА ОТ БОТОВ -->
                <input type="email" name="email" class="auth-input" placeholder="Email" required>
                <input type="password" name="password" class="auth-input" placeholder="Пароль" required minlength="6">
                <button type="submit" class="auth-btn">Войти</button>
            </form>

            <!-- Форма регистрации -->
            <form id="register-form" class="auth-form <?php echo $active_tab == 'register' ? 'active' : ''; ?>" method="POST" action="">
                <input type="hidden" name="register" value="1">
                <?php echo csrf_field(); ?>
                <?php echo honeypot_field(); ?>
                <div class="form-group">
                    <input type="text" name="name" class="auth-input" placeholder="Имя" required>
                    <div class="validation-message" id="name-error"></div>
                </div>

                <div class="form-group">
                    <input type="text" name="surname" class="auth-input" placeholder="Фамилия" required>
                    <div class="validation-message" id="surname-error"></div>
                </div>

                <div class="form-group">
                    <input type="email" name="email" class="auth-input" placeholder="Email" required>
                    <div class="validation-message" id="email-error"></div>
                </div>

                <div class="form-group">
                    <input type="tel" name="phone" class="auth-input phone-mask" placeholder="+7 (___) ___ __-__" required>
                    <div class="validation-message" id="phone-error"></div>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="auth-input" placeholder="Пароль (мин. 6 символов)" required minlength="6">
                    <div class="validation-message" id="password-error"></div>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" class="auth-input" placeholder="Подтвердите пароль" required>
                    <div class="validation-message" id="confirm-password-error"></div>
                </div>

                <!-- Чекбоксы -->
                <div class="auth-checkboxes">
                    <label class="auth-checkbox-label">
                        <input type="checkbox" name="agree_terms" id="agreeTerms" required>
                        Принимаю <a href="#" target="_blank">условия пользовательского соглашения</a>
                    </label>
                    <label class="auth-checkbox-label">
                        <input type="checkbox" name="agree_personal" id="agreePersonal" required>
                        Согласен на <a href="#" target="_blank">обработку персональных данных</a>
                    </label>
                </div>

                <button type="submit" class="auth-btn">Зарегистрироваться</button>
                <div class="form-footer">
                    Уже есть аккаунт? <a href="javascript:void(0)" onclick="switchTab('login')">Войдите</a>
                </div>
            </form>
        </div>
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
                <h4>Может быть полезно</h4>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="AboutUs.php">О нас</a></li>
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
                <div class="FooterSocials">
                    <a href="#"><img src="assets/img/vk.png" alt="VK"></a>
                    <a href="#"><img src="assets/img/max-app-icon-on-a-transparent-background-free-png.webp" alt="Max"></a>
                </div>
                <p class="FooterCopyright">© 2026 BodyGym. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            document.querySelector(`.auth-tab[onclick*="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-form`).classList.add('active');
        }

        function formatPhoneNumber(input) {
            let digits = input.value.replace(/\D/g, '');
            if (digits.startsWith('8')) digits = '7' + digits.substring(1);
            let formatted = '';
            if (digits.length > 0) formatted = '+7';
            if (digits.length > 1) formatted += ' ' + digits.substring(1, 4);
            if (digits.length > 4) formatted += ' ' + digits.substring(4, 7);
            if (digits.length > 7) formatted += ' ' + digits.substring(7, 9);
            if (digits.length > 9) formatted += '-' + digits.substring(9, 11);
            input.value = formatted;
        }

        function autoFormatName(input) {
            if (input.value.length > 0) {
                const start = input.selectionStart;
                const end = input.selectionEnd;
                input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
                input.setSelectionRange(start, end);
            }
        }

        function showNotification(message, type) {
            const existing = document.querySelector('.notification-overlay');
            if (existing) existing.remove();

            const notif = document.createElement('div');
            notif.className = `notification-overlay ${type}`;
            notif.innerHTML = `
                <div class="Notification-content">
            <div class="Notification-message">${message}</div>
            <button class="notification-close">✕</button>
        </div>
            `;
            document.body.appendChild(notif);

            const closeBtn = notif.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => notif.remove());
            setTimeout(() => {
                if (notif) notif.remove();
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($active_tab == 'register'): ?>
                switchTab('register');
            <?php endif; ?>

            <?php if ($error): ?>
                showNotification('<?php echo addslashes($error); ?>', 'error');
            <?php endif; ?>
            <?php if ($success): ?>
                showNotification('<?php echo addslashes($success); ?>', 'success');
            <?php endif; ?>

            // Форматирование телефона
            const phoneInputs = document.querySelectorAll('.phone-mask');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    formatPhoneNumber(this);
                });
                if (input.value) formatPhoneNumber(input);
            });

            // Форматирование имени/фамилии
            const nameInputs = document.querySelectorAll('input[name="name"], input[name="surname"]');
            nameInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    autoFormatName(this);
                });
            });

            // Валидация формы регистрации
            const registerForm = document.getElementById('register-form');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    let isValid = true;

                    const name = document.querySelector('input[name="name"]').value;
                    const surname = document.querySelector('input[name="surname"]').value;
                    const nameRegex = /^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/;
                    if (!nameRegex.test(name)) {
                        showNotification('Имя должно содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                        e.preventDefault();
                        return;
                    }
                    if (!nameRegex.test(surname)) {
                        showNotification('Фамилия должна содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                        e.preventDefault();
                        return;
                    }

                    const emailInput = this.querySelector('input[name="email"]');
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailInput.value)) {
                        document.getElementById('email-error').textContent = 'Введите корректный email';
                        document.getElementById('email-error').style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById('email-error').style.display = 'none';
                    }

                    const phoneInput = this.querySelector('input[name="phone"]');
                    const cleanPhone = phoneInput.value.replace(/\D/g, '');
                    if (cleanPhone.length < 11 || !cleanPhone.startsWith('7')) {
                        document.getElementById('phone-error').textContent = 'Введите корректный номер телефона в формате +7 (XXX) XXX XX-XX';
                        document.getElementById('phone-error').style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById('phone-error').style.display = 'none';
                    }

                    const password = this.querySelector('input[name="password"]');
                    const confirmPassword = this.querySelector('input[name="confirm_password"]');

                    if (password.value.length < 6) {
                        document.getElementById('password-error').textContent = 'Пароль должен быть не менее 6 символов';
                        document.getElementById('password-error').style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById('password-error').style.display = 'none';
                    }

                    if (password.value !== confirmPassword.value) {
                        document.getElementById('confirm-password-error').textContent = 'Пароли не совпадают';
                        document.getElementById('confirm-password-error').style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById('confirm-password-error').style.display = 'none';
                    }

                    const agreeTerms = document.getElementById('agreeTerms');
                    const agreePersonal = document.getElementById('agreePersonal');
                    if (!agreeTerms.checked) {
                        showNotification('Примите условия пользовательского соглашения', 'error');
                        isValid = false;
                    }
                    if (!agreePersonal.checked) {
                        showNotification('Дайте согласие на обработку персональных данных', 'error');
                        isValid = false;
                    }

                    if (!isValid) e.preventDefault();
                });
            }

            // Прозрачный хедер при скролле
            window.addEventListener('scroll', function() {
                const header = document.getElementById('mainHeader');
                if (window.scrollY > 50) header.classList.add('header-scrolled');
                else header.classList.remove('header-scrolled');
            });
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