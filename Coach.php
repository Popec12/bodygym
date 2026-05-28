<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php render_seo_meta($seo); ?>

    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura&family=Karantina:wght@300;400;700&family=League+Gothic&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/photo-logo.png">
    <style>
        .CoachAdvantages {
            order: 2;
        }

        .CoachAdvantageCard {
            text-align: left;
            transition: none;
            cursor: default;
        }

        .CoachAdvantageCard:hover {
            transform: none;
            cursor: default;
        }

        .CoachAdvantageCard p {
            text-align: left;
        }

        .CoachGrid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .CoachCard {
            background: transparent;
            border: none;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .CoachCard:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .CoachCard:hover .CoachCardImg {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .CoachCardImg {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: box-shadow 0.3s ease;
        }

        .CoachCardInfo {
            padding: 15px 0 15px 15px;
            text-align: left;
        }

        .CoachCardInfo h3 {
            font-size: 22px;
            color: pink;
        }

        .CoachCardInfo p {
            font-size: 16px;
            color: #ccc;
        }

        .ModalCoach {
            max-width: 900px;
            width: 90%;
        }

        .CoachModalContent {
            display: flex;
            gap: 30px;
            flex-wrap: nowrap;
        }

        .CoachModalImg {
            width: 40%;
            height: auto;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
        }

        .CoachModalInfo {
            width: 60%;
            text-align: left;
        }

        .CoachModalInfo h2 {
            font-size: 48px;
            color: pink;
            margin-bottom: 10px;
        }

        .CoachModalInfo h3 {
            font-size: 28px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .CoachModalInfo p {
            font-size: 20px;
            color: #ddd;
            line-height: 1.6;
        }

        /* Анимация */
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

        @media (max-width: 768px) {
            .CoachModalContent {
                flex-direction: column;
            }

            .CoachModalImg,
            .CoachModalInfo {
                width: 100%;
            }

            .CoachGrid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
        }

        @media (max-width: 576px) {
            .CoachGrid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body id="coach">
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
        <main>
            <div class="CoachListSection">
                <h2 class="CoachListTitle fade-scale">Наша команда</h2>
                <div class="CoachGrid">
                    <div class="CoachCard fade-up" onclick="openCoachModal('victor')">
                        <img src="assets/img/Третий.png" alt="Павлеско Виктор" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Павлеско Виктор</h3>
                            <p>Тренер по единоборствам</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-1" onclick="openCoachModal('alexey')">
                        <img src="assets/img/Первый.png" alt="Чуксеев Алексей" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Чуксеев Алексей</h3>
                            <p>Инструктор по плаванию</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-2" onclick="openCoachModal('evgeny')">
                        <img src="assets/img/Второй.png" alt="Одесский Евгений" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Одесский Евгений</h3>
                            <p>Персональный тренер</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-3" onclick="openCoachModal('oleg')">
                        <img src="assets/img/Четвертый.png" alt="Макаров Олег" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Макаров Олег</h3>
                            <p>Тренер</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-1" onclick="openCoachModal('kirill')">
                        <img src="assets/img/пятый тренер.png" alt="Чернышко Кирилл" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Чернышко Кирилл</h3>
                            <p>Тренер по кроссфиту</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-2" onclick="openCoachModal('kristina')">
                        <img src="assets/img/седьмая.png" alt="Ласкова Кристина" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Ласкова Кристина</h3>
                            <p>Инструктор йоги</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-3" onclick="openCoachModal('anastasia')">
                        <img src="assets/img/седьмая2.png" alt="Сухарева Анастасия" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Сухарева Анастасия</h3>
                            <p>Тренер по степ-аэробике</p>
                        </div>
                    </div>
                    <div class="CoachCard fade-up delay-1" onclick="openCoachModal('tatiana')">
                        <img src="assets/img/восьмая.png" alt="Нечесова Татьяна" class="CoachCardImg">
                        <div class="CoachCardInfo">
                            <h3>Нечесова Татьяна</h3>
                            <p>Инструктор аквааэробики</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="CoachAdvantages">
                <h1 class="CoachAdvantagesTitle fade-scale">Наши тренеры</h1>
                <p class="CoachAdvantagesSubtitle fade-up">Профессионалы, которым можно доверять своё здоровье</p>
                <div class="CoachAdvantagesGrid">
                    <div class="CoachAdvantageCard fade-up">
                        <h3>Международный опыт</h3>
                        <p>Наши тренеры проходили стажировки в ведущих спорт-центрах Европы и США, перенимая лучшие мировые практики.</p>
                    </div>
                    <div class="CoachAdvantageCard fade-up delay-1">
                        <h3>Профессиональное образование</h3>
                        <p>Все тренеры имеют профильное высшее образование и регулярно повышают квалификацию на международных семинарах.</p>
                    </div>
                    <div class="CoachAdvantageCard fade-up delay-2">
                        <h3>Призеры соревнований</h3>
                        <p>Многие из наших тренеров являются действующими спортсменами и победителями всероссийских и международных турниров.</p>
                    </div>
                    <div class="CoachAdvantageCard fade-up delay-3">
                        <h3>Индивидуальный подход</h3>
                        <p>Каждый тренер разрабатывает персональную программу тренировок с учетом целей, особенностей здоровья и физической подготовки клиента.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="coachModal" class="Modal">
        <div class="ModalContent ModalCoach">
            <span class="ModalClose" onclick="closeCoachModal()">&times;</span>
            <div class="ModalBody" id="coachModalBody"></div>
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
                <h4>Навигация</h4>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="AboutUs.php">О нас</a></li>
                    <li><a href="#coach">Тренеры</a></li>
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
                    <a href="#"><img src="assets/img/tg.png" alt="Telegram"></a>
                </div>
                <p class="FooterCopyright">© 2026 BodyGym. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });

        const coachData = {
            victor: {
                name: 'Павлеско Виктор',
                position: 'Тренер по единоборствам',
                img: 'assets/img/Третий.png',
                bio: 'Мастер спорта по боксу. Чемпион Рязанской области 2019-2023 годов. Стаж тренерской работы - 8 лет. Специализируется на обучении детей и взрослых навыкам самообороны, технике ударов и тактике ведения боя. Воспитанник заслуженных тренеров России.'
            },
            alexey: {
                name: 'Чуксеев Алексей',
                position: 'Инструктор по плаванию',
                img: 'assets/img/Первый.png',
                bio: 'Кандидат в мастера спорта по плаванию. Опыт работы - 10 лет. Специализируется на обучении плаванию взрослых с нуля и детей с 5 лет. Индивидуальный подход к каждому ученику, методика быстрого обучения. Проводит групповые и персональные занятия.'
            },
            evgeny: {
                name: 'Одесский Евгений',
                position: 'Персональный тренер',
                img: 'assets/img/Второй.png',
                bio: 'Сертифицированный тренер международного уровня. Специализация: функциональный тренинг, подготовка к соревнованиям, коррекция фигуры. Автор уникальной методики жиросжигающих тренировок. Личный тренер участников чемпионатов по бодибилдингу.'
            },
            oleg: {
                name: 'Макаров Олег',
                position: 'Тренер',
                img: 'assets/img/Четвертый.png',
                bio: 'Тренер с 7-летним стажем. Специализируется на программах снижения веса, коррекции осанки, восстановлении после травм. Разрабатывает персональные программы питания и тренировок с учетом физических особенностей клиента.'
            },
            kirill: {
                name: 'Чернышко Кирилл',
                position: 'Тренер по кроссфиту',
                img: 'assets/img/пятый тренер.png',
                bio: 'Мастер спорта по пауэрлифтингу. Судья международной категории по кроссфиту. Тренирует спортсменов уровня PRO. Помогает развить выносливость, силу и функциональность. Организатор ежегодных соревнований по кроссфиту в Рязани.'
            },
            kristina: {
                name: 'Ласкова Кристина',
                position: 'Инструктор йоги',
                img: 'assets/img/седьмая.png',
                bio: 'Сертифицированный инструктор по хатха-йоге и йога-нидре. Прошла обучение в Индии. Стаж преподавания - 6 лет. Проводит занятия для начинающих и практикующих, адаптирует практику под физические возможности ученика. Специализируется на релаксации и работе со стрессом.'
            },
            anastasia: {
                name: 'Сухарева Анастасия',
                position: 'Тренер по степ-аэробике',
                img: 'assets/img/седьмая2.png',
                bio: 'Хореограф и тренер с 9-летним опытом. Чемпионка России по аэробике 2021 года. Разрабатывает динамичные программы тренировок, сочетающие кардио и силовые элементы. Занятия проходят под энергичную музыку в формате интервальных тренировок.'
            },
            tatiana: {
                name: 'Нечесова Татьяна',
                position: 'Инструктор аквааэробики',
                img: 'assets/img/восьмая.png',
                bio: 'Мастер спорта по синхронному плаванию. Инструктор по аквафитнесу с 10-летним стажем. Проводит занятия для людей с ограниченными возможностями, беременных и пожилых. Автор методики реабилитации в воде после травм опорно-двигательного аппарата.'
            }
        };

        function openCoachModal(coachKey) {
            const coach = coachData[coachKey];
            const modal = document.getElementById('coachModal');
            const body = document.getElementById('coachModalBody');
            body.innerHTML = `
                <div class="CoachModalContent">
                    <img src="${coach.img}" alt="${coach.name}" class="CoachModalImg">
                    <div class="CoachModalInfo">
                        <h2>${coach.name}</h2>
                        <h3>${coach.position}</h3>
                        <p>${coach.bio}</p>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function closeCoachModal() {
            document.getElementById('coachModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('coachModal');
            if (event.target === modal) closeCoachModal();
        }

        // Анимация при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.CoachCard, .CoachAdvantageCard');
            animatedElements.forEach((el, index) => {
                el.classList.add('fade-up');
                if (index % 3 === 0) el.classList.add('delay-1');
                if (index % 3 === 1) el.classList.add('delay-2');
                if (index % 3 === 2) el.classList.add('delay-3');
            });

            const titles = document.querySelectorAll('.CoachListTitle, .CoachAdvantagesTitle');
            titles.forEach(title => title.classList.add('fade-scale'));

            const subtitle = document.querySelector('.CoachAdvantagesSubtitle');
            if (subtitle) subtitle.classList.add('fade-up');

            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return rect.top <= window.innerHeight - 100 && rect.bottom >= 0;
            }

            function checkVisibility() {
                document.querySelectorAll('.fade-up, .fade-scale').forEach(el => {
                    if (isElementInViewport(el) && !el.classList.contains('visible')) {
                        el.classList.add('visible');
                    }
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