<?php require_once 'config_bodygym.php';
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
    <style>
        .schedule-filters {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #444;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #313131;
            font-weight: bold;
        }

        .classes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .class-card {
            background: rgba(35, 35, 35, 0.85);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .class-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(255, 182, 193, 0.3);
            background: rgba(35, 35, 35, 0.95);
        }

        .class-img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            background: #444;
        }

        .class-info {
            padding: 25px;
        }

        .class-info h3 {
            font-size: 24px;
            color: pink;
            margin-bottom: 12px;
        }

        .class-info p {
            font-size: 15px;
            color: #ddd;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .class-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .intensity-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .intensity-high {
            background: linear-gradient(135deg, rgb(255, 175, 204), rgb(255, 143, 171));
            color: black;
        }

        .intensity-medium {
            background: linear-gradient(135deg, rgb(255, 175, 204), rgb(255, 143, 171));
            color: black;
        }

        .intensity-low {
            background: linear-gradient(135deg, rgb(255, 175, 204), rgb(255, 143, 171));
            color: black;
        }

        .duration-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
        }

        .coach-link {
            color: pink;
            cursor: pointer;
            transition: color 0.3s;
        }

        .coach-link:hover {
            color: #ff8fab;
            text-decoration: underline;
        }


        .load-more-btn {
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #313131;
            border: none;
            padding: 14px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .load-more-btn:hover {
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            .classes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .classes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body id="sched">
    <header class="Header" id="mainHeader">
        <div class="HeaderContainer">
            <div class="LeftHeaderContainer"><img src="assets/img/photo-logo.png" alt="" class="ImgLogoInHeader"><a href="index.php" class="NameLogoInHeader">BodyGym</a></div>
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
                    <?php if (isLoggedIn()): ?><li class="NavigationInHeader"><a href="profile.php" class="NavigationInHeaderText">Личный кабинет</a></li><?php else: ?><li class="NavigationInHeader"><a href="login.php" class="NavigationInHeaderText">Войти</a></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </header>
    <div class="page-content">
        <main style="background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95));">
            <div style="text-align: center; padding: 40px 20px 20px;">
                <h1 style="font-size: 48px; color: pink;">Групповые занятия</h1>
                <p style="font-size: 18px; color: #ccc; margin-top: 15px;">Выберите направление по душе и начните тренироваться с удовольствием</p>
            </div>
            <div class="schedule-filters"><button class="filter-btn active" data-filter="all">Все занятия</button><button class="filter-btn" data-filter="high">Высокая интенсивность</button><button class="filter-btn" data-filter="medium">Средняя интенсивность</button><button class="filter-btn" data-filter="low">Низкая интенсивность</button></div>
            <div id="classesContainer" class="classes-grid"></div>
            <div class="load-more-container" id="loadMoreContainer"><button class="load-more-btn" id="loadMoreBtn">Показать еще занятия</button></div>
        </main>
    </div>

    <!-- Модальное окно для занятия (без интенсивности и длительности) -->
    <div id="classModal" class="Modal">
        <div class="ModalContent" style="max-width: 1200px; background-color:rgba(19, 19, 19, 0.8);">
            <span class="ModalClose" onclick="closeClassModal()">&times;</span>
            <div class="ModalBody" id="classModalBody"></div>
        </div>
    </div>

    <footer class="NewFooter">
        <div class="FooterContainer">
            <div class="FooterCol FooterCol1">
                <div class="FooterLogoWrapper"><img src="assets/img/photo-logo.png" alt="" class="FooterImgLogo"><span class="FooterLogoName">BodyGym</span></div>
                <p class="FooterDesc">Пространство для тех, кто стремится к совершенству.</p>
            </div>
            <div class="FooterCol FooterCol2">
                <h4>Навигация</h4>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="AboutUs.php">О нас</a></li>
                    <li><a href="Coach.php">Тренеры</a></li>
                    <li><a href="Services.php">Услуги</a></li>
                    <li><a href="#sched">Групповые</a></li>
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
        // Данные о занятиях с расширенными описаниями
        const classesData = [{
                id: 1,
                name: "Велогонка",
                img: "assets/img/133dcdfd-a456-4840-9b6a-75c79aae172d.webp",
                shortDesc: "Интенсивная кардио-нагрузка на велотренажерах.",
                shudel: "Вт/Чт/Сб в 12:00.",
                fullDesc: "Велогонка — это высокоинтенсивное занятие на велотренажерах, которое поможет вам сжечь максимум калорий за короткое время. Под энергичную музыку вы будете выполнять спринты, имитации подъемов в гору и равномерный темп. Тренировка развивает выносливость сердечно-сосудистой системы и укрепляет мышцы ног. Идеально подходит для тех, кто хочет быстро привести себя в форму и избавиться от лишнего веса!",
                coach: "Одесский Евгений",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 2,
                name: "Йога",
                img: "assets/img/e1d88562-a7fe-42b7-b1f6-8012702bb012.webp",
                shortDesc: "Гармония тела и духа через асаны и дыхание.",
                shudel: "Ежедневно в 10:00.",
                fullDesc: "Йога — это древняя практика, объединяющая физические упражнения, дыхательные техники и медитацию. Занятия помогают улучшить гибкость, укрепить мышцы кора, снять стресс и обрести внутренний покой. Подходит для любого уровня подготовки, включая новичков. Регулярная практика йоги улучшает осанку, нормализует давление и дарит заряд бодрости на весь день!",
                coach: "Ласкова Кристина",
                duration: "90 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 3,
                name: "Плавание",
                img: "assets/img/x08.jpg.pagespeed.ic.eLlf3THrVG.webp",
                shortDesc: "Занятия в бассейне для всех уровней.",
                shudel: "Вс/Ср в 18:00.",
                fullDesc: "Групповые занятия плаванием под руководством опытного инструктора помогут вам освоить правильную технику, улучшить дыхание и увеличить выносливость. Плавание задействует все группы мышц, при этом щадяще воздействует на суставы и позвоночник. Регулярные тренировки в бассейне укрепляют сердечно-сосудистую систему, формируют красивый рельеф тела и дарят ощущение легкости. Подходит для начинающих и продвинутых пловцов!",
                coach: "Чуксеев Алексей",
                duration: "60 мин",
                intensity: "medium",
                intensityText: "Средняя"
            },
            {
                id: 4,
                name: "Бокс",
                img: "assets/img/i.webp",
                shortDesc: "Развитие силы, скорости и реакции.",
                shudel: "Вт/Чт в 14:30.",
                fullDesc: "Занятия боксом — это эффективный способ развить силу, скорость, координацию и реакцию. Тренировки включают работу на мешках, спарринг-упражнения, отработку защитных действий и атакующих комбинаций. Бокс помогает выплеснуть агрессию, укрепить дух и стать увереннее в себе. Отличная кардио-нагрузка и проработка мышц верхней части тела гарантированы!",
                coach: "Павлеско Виктор",
                duration: "90 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 5,
                name: "Памп",
                img: "assets/img/f3826a59-c046-48d6-8a39-b0e14dcefea0.webp",
                shortDesc: "Силовая тренировка со штангой.",
                shudel: "Пн/Пт в 15:00.",
                fullDesc: "BODYPUMP — это групповая силовая тренировка с использованием специальной штанги. Под зажигательную музыку вы проработаете все основные группы мышц: ноги, ягодицы, спину, грудь, плечи и руки. Упражнения подобраны так, чтобы давать оптимальную нагрузку и помогать быстро формировать рельеф. Идеальный выбор для тех, кто хочет укрепить мышцы, сжечь жир и стать сильнее!",
                coach: "Макаров Олег",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 6,
                name: "Ягодицы",
                img: "assets/img/2ffd73bd-b7d2-40d5-8faf-2dc58d48ff07.webp",
                shortDesc: "Специализированная тренировка для ягодиц.",
                shudel: "Ср в 16:00.",
                fullDesc: "Тренировка, направленная исключительно на проработку ягодичных мышц. Выполняйте приседания, выпады, ягодичные мостики и другие эффективные упражнения с собственным весом и утяжелителями. Регулярные занятия помогут подтянуть и округлить ягодицы, сделать их упругими и красивыми. Программа подходит для девушек и женщин любого уровня подготовки!",
                coach: "Сухарева Анастасия",
                duration: "60 мин",
                intensity: "medium",
                intensityText: "Средняя"
            },
            {
                id: 7,
                name: "Тренировки на открытом воздухе",
                img: "assets/img/dab304e6-1c9f-4366-9ffd-98b3f89bd29d.webp",
                shortDesc: "Функциональный тренинг на свежем воздухе.",
                shudel: "Вс в 10:00.",
                fullDesc: "Функциональные тренировки на свежем воздухе — это отличная возможность совместить пользу спорта и прогулку. Пробежки по пересеченной местности, отжимания, подтягивания, прыжки и другие упражнения развивают выносливость, силу и ловкость. Занятия проходят в парке, что добавляет позитивных эмоций и улучшает настроение. Подходит для любого уровня подготовки!",
                coach: "Чернышко Кирилл",
                duration: "90 мин",
                intensity: "medium",
                intensityText: "Средняя"
            },
            {
                id: 8,
                name: "Семинар про питание",
                img: "assets/img/59a934ff-f141-4ee1-b7f4-77a8bedd073b.webp",
                shortDesc: "Лекции о здоровом питании.",
                shudel: "Пн в 16:30.",
                fullDesc: "Хотите узнать, как правильно питаться для достижения спортивных целей? На семинаре вы получите знания о сбалансированном рационе, подсчете калорий и нутриентов. Эксперт расскажет, как составить меню для похудения, набора мышечной массы или поддержания веса. Вы также узнаете о полезных продуктах, режиме питания и мифах о диетах. Вопросы приветствуются!",
                coach: "Приглашенный эксперт",
                duration: "120 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 9,
                name: "Кардио",
                img: "assets/img/d253fe78-ec87-45ac-9d22-cfd4ebfa6e12.webp",
                shortDesc: "Энергичная тренировка для сердца.",
                shudel: "Ср/Вс в 14:30.",
                fullDesc: "Кардио-тренировка в высоком темпе поможет вам быстро сжечь калории, укрепить сердечно-сосудистую систему и повысить выносливость. В программе: бег, прыжки, берпи, скакалка и другие динамичные упражнения. Тренировка подходит для любого уровня, но требует полной отдачи. Отличный способ разогнать метаболизм и зарядиться энергией на весь день!",
                coach: "Одесский Евгений",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 10,
                name: "Пилатес",
                img: "assets/img/9004cebf-14aa-4c9e-b33b-b789f10d6cff.webp",
                shortDesc: "Укрепление мышц кора и спины.",
                shudel: "Ср в 17:00.",
                fullDesc: "Пилатес — это система упражнений, направленная на укрепление мышц кора, улучшение осанки и гибкости. Плавные, контролируемые движения тренируют глубокие мышцы, формируют мышечный корсет и делают тело сильным и подтянутым. Занятия подходят для любого возраста и уровня подготовки, включая восстановление после травм. Регулярный пилатес дарит легкость в теле и правильную осанку!",
                coach: "Ласкова Кристина",
                duration: "60 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 11,
                name: "Растяжка",
                img: "assets/img/245f108f-0931-45fd-b2c9-91d3a507afdd.webp",
                shortDesc: "Улучшение гибкости всего тела.",
                shudel: "Сб/Вс в 10:30.",
                fullDesc: "Комплекс упражнений на растяжку всех групп мышц поможет вам стать гибче, улучшить подвижность суставов и снять мышечное напряжение после тренировок. Занятия включают статическую и динамическую растяжку, упражнения на расслабление и дыхание. Регулярная растяжка улучшает кровообращение, снижает риск травм и дарит ощущение легкости во всем теле. Подходит для любого уровня!",
                coach: "Ласкова Кристина",
                duration: "60 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 12,
                name: "Королевская осанка",
                img: "assets/img/7a3614db-3ab9-4b11-ad13-387ef516bedd.webp",
                shortDesc: "Красивая спина и здоровая осанка.",
                shudel: "Пн/Чт в 18:00.",
                fullDesc: "Специализированная тренировка для укрепления мышц спины, раскрытия грудного отдела и формирования правильной осанки. Выполняйте упражнения с собственным весом, резиной и мячами. Программа поможет избавиться от сутулости, болей в спине и шее, улучшить дыхание и выглядеть выше и стройнее. Результат — королевская осанка и здоровая спина!",
                coach: "Сухарева Анастасия",
                duration: "60 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 13,
                name: "Воркаут",
                img: "assets/img/6b143ddb-421d-40fd-94c8-e6082df238f0.webp",
                shortDesc: "Тренировки с собственным весом.",
                shudel: "Ср/Пт в 15:30.",
                fullDesc: "Воркаут — это тренировки на турниках, брусьях и с использованием собственного веса. Программа развивает силу, выносливость, ловкость и координацию. Вы научитесь выполнять подтягивания, отжимания, выходы силой и другие сложные элементы. Подходит для мужчин и женщин, стремящихся к функциональной силе и красивому рельефу. Тренировки проходят на специальной площадке!",
                coach: "Чернышко Кирилл",
                duration: "90 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 14,
                name: "Перезагрузка",
                img: "assets/img/ad0ed107-5ad1-4f9f-8f52-04df6b068c49.webp",
                shortDesc: "Восстановление после стресса.",
                shudel: "Пт в 9:00.",
                fullDesc: "Медитативные и дыхательные практики для восстановления после стресса, улучшения сна и эмоционального равновесия. Программа включает техники релаксации, визуализации, глубокого дыхания и мягкой растяжки. Идеально подходит для людей с высоким уровнем стресса, бессонницей и эмоциональным выгоранием. Приходите перезагрузиться и наполниться энергией!",
                coach: "Ласкова Кристина",
                duration: "60 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 15,
                name: "Восстановление после родов",
                img: "assets/img/5da56ab8-e974-4af4-af68-5a2cbcff16b6.webp",
                shortDesc: "Безопасный фитнес для мам.",
                shudel: "Ср/Чт в 18:00.",
                fullDesc: "Специальная программа для молодых мам, направленная на укрепление тазового дна, мышц живота и восстановление после родов. Упражнения безопасны и эффективны, подходят для женщин с детьми от 6 недель. Программа помогает вернуть форму, улучшить самочувствие и повысить энергию. Занятия проходят в группах, где царит поддержка и понимание!",
                coach: "Сухарева Анастасия",
                duration: "120 мин",
                intensity: "low",
                intensityText: "Низкая"
            },
            {
                id: 16,
                name: "Сделать тело",
                img: "assets/img/68f49452-8e6f-4131-90c5-0a028b43fae4.webp",
                shortDesc: "Комплексный фитнес для рельефа.",
                shudel: "Вт/Сб в 17:30.",
                fullDesc: "Комплексная программа, сочетающая кардио- и силовые упражнения для эффективного жиросжигания и формирования рельефа. Тренировка включает работу с гантелями, штангой, собственным весом и функциональными снарядами. Подходит для любого уровня подготовки, помогает быстро достичь видимых результатов. Идеальный выбор для тех, кто хочет преобразить свое тело!",
                coach: "Макаров Олег",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 17,
                name: "Кроссфит",
                img: "assets/img/98140a8a-9156-4af7-86c4-672af4c68f64.webp",
                shortDesc: "Функциональный тренинг высокой интенсивности.",
                shudel: "Пн/Ср/Сб в 13:00.",
                fullDesc: "Кроссфит — это высокоинтенсивный функциональный тренинг, включающий элементы тяжелой атлетики, гимнастики и кардио. Каждое занятие — это WOD (Workout of the Day), который бросает вызов вашей силе, выносливости и скорости. Тренировки разнообразны и никогда не надоедают. Подходит для подготовленных людей, стремящихся к максимальной физической форме!",
                coach: "Чернышко Кирилл",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            },
            {
                id: 18,
                name: "Степ-аэробика",
                img: "assets/img/6f35cdf3-3b9b-4d2e-bc8a-6a1bfd050a78.webp",
                shortDesc: "Динамичные танцевальные шаги.",
                shudel: "Пт в 14:00.",
                fullDesc: "Степ-аэробика — это динамичная тренировка на специальных платформах под ритмичную музыку. Шаги, прыжки и связки развивают координацию, сжигают калории и укрепляют мышцы ног и ягодиц. Занятия проходят в веселой, энергичной атмосфере, подходят для любого уровня. Отличный способ поднять настроение и привести тело в тонус!",
                coach: "Сухарева Анастасия",
                duration: "60 мин",
                intensity: "medium",
                intensityText: "Средняя"
            },
            {
                id: 19,
                name: "Аквафитнес",
                img: "assets/img/female-group-aqua-aerobics-exercise-with-dumbbells-pool_266732-23405.avif",
                shortDesc: "Зарядка в воде для всех возрастов.",
                shudel: "Вт/Чт/Вс в 8:00.",
                fullDesc: "Аквафитнес — это тренировка в воде, которая дает отличную кардио-нагрузку при минимальном воздействии на суставы. Вода создает сопротивление, что усиливает эффективность упражнений. Программа включает плавание, бег в воде, силовые элементы и растяжку. Подходит для людей любого возраста, включая пожилых, беременных и восстанавливающихся после травм!",
                coach: "Нечесова Татьяна",
                duration: "60 мин",
                intensity: "medium",
                intensityText: "Средняя"
            },
            {
                id: 20,
                name: "Велотренировка",
                img: "assets/img/caf5ea31-2188-4cb9-b5c2-0b87ee217be7.webp",
                shortDesc: "Интервалы на велосипедах.",
                shudel: "Чт в 13:30.",
                fullDesc: "Интервальная велотренировка для максимального сжигания жира и развития выносливости. Чередование интенсивных спринтов и периодов восстановления заставляет организм работать на пределе. Занятия проходят в темном зале под энергичную музыку, что создает эффект полного погружения. Отличный выбор для тех, кто хочет быстро похудеть и укрепить сердечно-сосудистую систему!",
                coach: "Одесский Евгений",
                duration: "60 мин",
                intensity: "high",
                intensityText: "Высокая"
            }
        ];

        let displayedCount = 9;
        let currentFilter = "all";

        function filterClasses() {
            if (currentFilter === "all") return classesData;
            return classesData.filter(c => c.intensity === currentFilter);
        }

        function renderClasses() {
            const filtered = filterClasses();
            const toShow = filtered.slice(0, displayedCount);
            const container = document.getElementById("classesContainer");
            if (!container) return;

            container.innerHTML = toShow.map(c => `
                <div class="class-card fade-up" data-id="${c.id}">
                    <img src="${c.img}" alt="${c.name}" class="class-img" onerror="this.src='assets/img/default-class.jpg'">
                    <div class="class-info">
                        <h3>${c.name}</h3>
                        <p>${c.shortDesc}</p>
                        <div class="class-meta">
                            <span class="duration-badge">${c.duration}</span>
                            <span class="intensity-badge intensity-${c.intensity}">${c.intensityText}</span>
                        </div>
                    </div>
                </div>
            `).join("");

            document.querySelectorAll(".class-card").forEach(card => {
                card.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const id = parseInt(card.dataset.id);
                    const cls = classesData.find(c => c.id === id);
                    if (cls) openClassModal(cls);
                });
            });

            const loadMoreBtn = document.getElementById("loadMoreBtn");
            if (loadMoreBtn) loadMoreBtn.style.display = displayedCount >= filtered.length ? "none" : "inline-block";

            // Анимация для новых элементов
            document.querySelectorAll('.class-card.fade-up').forEach(el => {
                if (isElementInViewport(el)) el.classList.add('visible');
            });
        }

        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return rect.top <= window.innerHeight - 100 && rect.bottom >= 0;
        }

        function loadMore() {
            displayedCount += 3;
            renderClasses();
        }

        function applyFilter(filter) {
            currentFilter = filter;
            displayedCount = 9;
            renderClasses();
        }

        // Функция для перехода на страницу тренеров
        function goToCoachPage(coachName) {
            // Сохраняем имя тренера в sessionStorage для выделения на странице тренеров (опционально)
            sessionStorage.setItem('selectedCoach', coachName);
            window.location.href = 'Coach.php';
        }

        function openClassModal(cls) {
            const modal = document.getElementById("classModal");
            const body = document.getElementById("classModalBody");
            body.innerHTML = `
                <div style="display: flex; gap: 30px; flex-wrap: nowrap;">
                    <img src="${cls.img}" style="width: 40%; max-height: 1000px; border-radius: 12px; object-fit: cover;" onerror="this.src='assets/img/default-class.jpg'">
                    <div style="width: 55%;">
                        <h2 style="color: pink; margin-bottom: 15px;">${cls.name}</h2>
                        <p style="color: #ddd; line-height: 1.6; margin-bottom: 20px;"> ${cls.fullDesc}</p>
                        <p style="margin-bottom: 15px;">
                            <strong style="color: #ccc;">Тренер:</strong> 
                            <span class="coach-link" onclick="goToCoachPage('${cls.coach.replace(/'/g, "\\'")}'); event.stopPropagation();" style="color: pink; cursor: pointer; font-weight: bold;">${cls.coach}</span>
                        </p>
                        <p style="margin-bottom: 15px;">
                            <strong style="color: #ccc;">Расписание:</strong> 
                            <span style="color: #ddd; line-height: 1.6; margin-bottom: 20px;"">${cls.shudel}</span>
                        </p>
                    </div>
                </div>
            `;
            modal.style.display = "flex";
        }

        function closeClassModal() {
            document.getElementById("classModal").style.display = "none";
        }

        document.querySelectorAll(".filter-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                applyFilter(btn.dataset.filter);
            });
        });

        document.getElementById("loadMoreBtn")?.addEventListener("click", loadMore);

        window.onclick = function(event) {
            if (event.target === document.getElementById("classModal")) closeClassModal();
        };

        window.addEventListener("scroll", function() {
            const header = document.getElementById("mainHeader");
            if (window.scrollY > 50) header.classList.add("header-scrolled");
            else header.classList.remove("header-scrolled");

            document.querySelectorAll('.class-card.fade-up').forEach(el => {
                if (isElementInViewport(el) && !el.classList.contains('visible')) {
                    el.classList.add('visible');
                }
            });
        });

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            renderClasses();

            setTimeout(() => {
                document.querySelectorAll('.class-card').forEach(el => {
                    if (isElementInViewport(el)) el.classList.add('visible');
                });
            }, 100);
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