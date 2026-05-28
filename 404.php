<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';
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
    <div class="page-content" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
        <div style="text-align: center; padding: 60px 20px;">
            <h1 style="font-size: 180px; color: pink; margin: 0; line-height: 1;">404</h1>
            <h2 style="font-size: 36px; color: white; margin: 20px 0;">Страница не найдена</h2>
            <p style="font-size: 18px; color: #ccc; margin-bottom: 40px;">К сожалению, запрашиваемая страница не существует или была перемещена.</p>
            <a href="index.php" style="display: inline-block; background: linear-gradient(135deg, #ffafcc, #ff8fab); color: #313131; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 18px;">Вернуться на главную</a>
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
                    <li><a href="#Main">Главная</a></li>
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
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
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