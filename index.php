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
    <link
        href="https://fonts.googleapis.com/css2?family=Jura&family=Karantina:wght@300;400;700&family=League+Gothic&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" href="assets/img/photo-logo.png">
</head>
<style>
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
        <!-- First page - Hero -->
        <div class="MainPage">
            <img src="assets/img/back.png" alt="" class="ImgBackground" id="heroImage">
            <div class="MainPageContent">
                <div class="MainPageContentCenter">
                    <div class="IntroText">
                        <h1 class="IntroTextH1">Современный <br> спортзал <br>
                            Body<span>Gym</span></h1>
                        <a href="#about" class="IntroButtonText">Начать путь</a>
                    </div>
                </div>
                <div class="MainDescription">
                    <div class="MainDescriptionParts">
                        <p class="BackgroundMainDecriptionParts">1</p>
                        <p class="MainDescriptionPartsText">Собственный бассейн <br> и спа-зона</p>
                    </div>
                    <div class="MainDescriptionParts">
                        <p class="BackgroundMainDecriptionParts">2</p>
                        <p class="MainDescriptionPartsText">Спортзал рядом с <br> вами в ЖК <br> "Зеленый сад"</p>
                    </div>
                    <div class="MainDescriptionParts">
                        <p class="BackgroundMainDecriptionParts">3</p>
                        <p class="MainDescriptionPartsText">Вы можете сделать <br> BodyGym таким, <br> как вам нравится
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- About section -->
        <div class="SecondPage">
            <div class="ContentSecondPage">
                <div class="LeftContentSecondPage">
                    <p class="NameSecondPageP">О нас</p>
                    <p class="ContentSecondPageP">
                        Добро пожаловать в наш спортивный зал!
                        Мы - команда профессионалов, которая стремится помочь каждому нашему
                        участнику достичь своих спортивных целей и улучшить свое физическое
                        состояние. <br><br>Мы предлагаем самые современные тренажеры, квалифицированных
                        тренеров, индивидуальные и групповые занятия, атмосферу поддержки и мотивации.
                        <br><br>Присоединяйтесь к нашей семье и давайте вместе создадим лучшую версию себя!
                    </p>
                </div>
                <div class="RightContentSecondPage">
                    <img src="assets/img/gym.png" alt="" class="ContentSecondPageImg">
                </div>
            </div>
        </div>

        <!-- Gallery section (заменил тренеров) -->
        <div class="GallerySection">
            <div class="GalleryTitle">
                <p class="GalleryTitleP">Наши пространства</p>
            </div>
            <div class="GalleryItems">
                <div class="GalleryItem" data-gallery="gym">
                    <img src="assets/img/gym3.jpeg" alt="Тренажерный зал" class="GalleryItemImg">
                    <h3 class="GalleryItemTitle">Тренажерный зал</h3>
                    <p class="GalleryItemDesc">Современное оборудование премиум-класса для эффективных тренировок</p>
                    <button class="GalleryItemBtn" onclick="openGalleryModal('gym')">Подробнее</button>
                </div>
                <div class="GalleryItem" data-gallery="group">
                    <img src="assets/img/image-18.jpg" alt="Групповые тренировки" class="GalleryItemImg">
                    <h3 class="GalleryItemTitle">Групповые тренировки</h3>
                    <p class="GalleryItemDesc">Занятия с единомышленниками под руководством опытных тренеров</p>
                    <button class="GalleryItemBtn" onclick="openGalleryModal('group')">Подробнее</button>
                </div>
                <div class="GalleryItem" data-gallery="spa">
                    <img src="assets/img/turkish-hammam.webp" alt="Спа-зона" class="GalleryItemImg">
                    <h3 class="GalleryItemTitle">Спа-зона</h3>
                    <p class="GalleryItemDesc">Полное расслабление и восстановление после интенсивных тренировок</p>
                    <button id="about" class="GalleryItemBtn" onclick="openGalleryModal('spa')">Подробнее</button>
                </div>
            </div>
        </div>

        <!-- Tariffs section -->
        <div class="TariffsSection">
            <div class="TariffsTitle">
                <p class="TariffsTitleP">Старт твоей трансформации</p>
            </div>
            <div class="TariffsItems">
                <div class="TariffItem">
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
                <div class="TariffItem">
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
                <div class="TariffItem">
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

            <div class="QuickLinks">
                <div class="QuickLink">
                    <h4>Акции и скидки</h4>
                    <p>Специальные предложения для новых клиентов</p>
                    <a href="Services.php#promo" class="QuickLinkBtn">Узнать</a>
                </div>
                <div class="QuickLink">
                    <h4>Наши тренеры</h4>
                    <p>Профессионалы с международным опытом</p>
                    <a href="Coach.php" class="QuickLinkBtn">Познакомиться</a>
                </div>
                <div class="QuickLink">
                    <h4>Групповые программы</h4>
                    <p>Разнообразные направления для всех уровней</p>
                    <a href="Schedule.php" class="QuickLinkBtn">Выбрать</a>
                </div>
            </div>
        </div>

        <!-- Map section -->
        <div class="MapSection">
            <div class="MapTitle">
                <p class="MapTitleP">Как нас найти</p>
            </div>
            <div class="MapContainer">
                <div id="map" class="MapFrame"></div>
                <div class="MapInfo">
                    <h3>BodyGym</h3>
                    <p><strong>Адрес:</strong><br> г.Рязань, Касимовское шоссе, д. 30 | ЖК "Зеленый сад"</p>
                    <p><strong>Телефон:</strong><br> +7 920 639-22-35</p>
                    <p><strong>Email:</strong><br> info@bodygym.ru</p>
                    <p><strong>Режим работы:</strong><br> Ежедневно с 07:00 до 23:00</p>
                </div>
            </div>
        </div>

        <!-- Callback form -->
        <div class="CallbackSection">
            <div class="CallbackForm">
                <h2 class="CallbackTitle">Остались вопросы?</h2>
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

    <!-- Модальное окно для галереи -->
    <div id="galleryModal" class="Modal">
        <div class="ModalContent">
            <span class="ModalClose" onclick="closeGalleryModal()">&times;</span>
            <div class="ModalBody" id="galleryModalBody"></div>
        </div>
    </div>

    <!-- Модальное окно для тарифа -->

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
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">Имя</label>
                            <input type="text" id="orderName" class="TariffFormInput" placeholder="Ваше имя" required>
                        </div>
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">Фамилия</label>
                            <input type="text" id="orderSurname" class="TariffFormInput" placeholder="Ваша фамилия" required>
                        </div>
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">
                                Дата рождения
                                <span class="HelpIcon" data-tooltip="Заниматься можно только с 16 лет">!</span>
                            </label>
                            <input type="date" id="orderBirthdate" class="TariffFormInput" required min="1900-01-01" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">Email</label>
                            <input type="email" id="orderEmail" class="TariffFormInput" placeholder="example@mail.ru" required>
                        </div>
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">Опыт в спорте</label>
                            <select id="orderExperience" class="TariffFormInput" required>
                                <option value="" style="font-size: 14px;">Выберите ваш уровень</option>
                                <option value="beginner" style="font-size: 13px;">Новичок (менее 3 месяцев)</option>
                                <option value="intermediate" style="font-size: 13px;">Любитель (3-12 месяцев)</option>
                                <option value="advanced" style="font-size: 13px;">Продвинутый (1-3 года)</option>
                                <option value="pro" style="font-size: 13px;">Профессионал (более 3 лет)</option>
                            </select>
                        </div>
                        <div class="TariffFormField">
                            <label class="TariffFormLabel">Телефон</label>
                            <input type="tel" id="orderPhone" class="TariffFormInput phone-mask" placeholder="+7 (___) ___ __-__" required>
                        </div>
                        <div class="TariffFormField full-width">
                            <label class="TariffFormLabel">Пол</label>
                            <div class="GenderGroup">
                                <label class="GenderLabel">
                                    <input type="radio" name="gender" value="male" required> Мужчина
                                </label>
                                <label class="GenderLabel">
                                    <input type="radio" name="gender" value="female" required> Женщина
                                </label>
                            </div>
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

    <!-- Уведомление -->
    <div id="notification" class="Notification"></div>

    <script src="https://api-maps.yandex.ru/2.1/?apikey=f56b3ced-2573-42c2-8122-c57440e50a17&lang=ru_RU"></script>
    <script>
        // Прозрачный хедер при скролле
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });

        // Осветление картинки на 25%
        const heroImage = document.getElementById('heroImage');
        if (heroImage) {
            heroImage.style.filter = 'brightness(0.7)';
            heroImage.style.height = '1000px';
            heroImage.style.objectFit = 'cover';
        }

        // Форматирование телефона
        function formatPhoneNumber(input) {
            let digits = input.value.replace(/\D/g, '');
            if (digits.startsWith('8')) digits = '7' + digits.substring(1);
            let formatted = '';
            if (digits.length > 0) formatted = '+7 ';
            if (digits.length > 1) formatted += digits.substring(1, 4);
            if (digits.length > 4) formatted += ' ' + digits.substring(4, 7);
            if (digits.length > 7) formatted += ' ' + digits.substring(7, 9);
            if (digits.length > 9) formatted += '-' + digits.substring(9, 11);
            input.value = formatted;
        }

        // Модальное окно галереи
        function openGalleryModal(type) {
            const modal = document.getElementById('galleryModal');
            const body = document.getElementById('galleryModalBody');
            const contents = {
                gym: {
                    title: 'Тренажерный зал',
                    img: 'assets/img/gym2.webp',
                    text: 'Наш тренажерный зал оснащен самым современным оборудованием от ведущих мировых производителей. Здесь вы найдете все необходимое для эффективных тренировок: от кардиозон до зон свободных весов. Просторные помещения и качественная вентиляция создают комфортные условия для занятий. Инструкторы всегда готовы помочь с техникой выполнения упражнений.<br><br> Мы очень стараемся предоставить нашим гостям все для получения максимального удовольствия от тренировок!'
                },
                group: {
                    title: 'Групповые тренировки',
                    img: 'assets/img/ddb8838d2b34063becbc4b934381fbdd.jpg',
                    text: 'Групповые занятия — это отличная возможность тренироваться в компании единомышленников. Йога, пилатес, зумба, кроссфит, танцы и многие другие направления. Занятия проходят в просторных залах под руководством опытных инструкторов. Атмосфера поддержки и взаимопомощи помогает достигать лучших результатов. <br><br> Мы создаем пространство, где каждый чувствует себя частью большой спортивной семьи!'
                },
                spa: {
                    title: 'Спа-зона',
                    img: 'assets/img/fin-sauna.webp',
                    text: 'После интенсивной тренировки так важно дать телу восстановиться. Наша спа-зона включает финскую сауну, турецкий хаммам и джакузи. Профессиональные массажисты помогут снять мышечное напряжение. Зона отдыха с травяным чаем и свежими фруктами дополнит опыт полного расслабления. Это идеальное место для восстановления сил и снятия стресса. <br><br> Мы позаботились о том, чтобы каждый визит в зал приносил не только пользу, но и настоящее удовольствие!'
                }
            };
            const content = contents[type];
            body.innerHTML = `
                <div class="ModalGallery">
                    <img src="${content.img}" alt="${content.title}" class="ModalGalleryImg">
                    <div class="ModalGalleryInfo">
                        <h2>${content.title}</h2>
                        <p>${content.text}</p>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function closeGalleryModal() {
            document.getElementById('galleryModal').style.display = 'none';
        }

        // Модальное окно тарифа
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

        // Оформление абонемента с сохранением в БД
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

            const csrfToken = document.querySelector('#tariffOrderForm input[name="csrf_token"]')?.value;

            // Валидация имени
            if (name.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(name)) {
                showNotification('Имя должно содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }
            if (surname.length < 2 || !/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/.test(surname)) {
                showNotification('Фамилия должна содержать минимум 2 буквы (только буквы, дефис или пробел)', 'error');
                return;
            }

            // Валидация даты рождения (проверка возраста 16+)
            if (birthdate) {
                const birth = new Date(birthdate);
                const today = new Date();
                const minDate = new Date('1900-01-01');

                if (birth < minDate || birth > today) {
                    showNotification('Укажите корректную дату рождения (не ранее 1900 года)', 'error');
                    return;
                }

                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                    age--;
                }
                if (age < 16) {
                    showNotification('Извините, занятия в спортзале доступны с 16 лет', 'error');
                    return;
                }
            } else {
                showNotification('Укажите дату рождения', 'error');
                return;
            }

            // Валидация email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showNotification('Введите корректный email', 'error');
                return;
            }

            // Валидация опыта
            if (!experience) {
                showNotification('Выберите ваш уровень подготовки', 'error');
                return;
            }

            // Валидация телефона
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 11 || !cleanPhone.startsWith('7')) {
                showNotification('Введите корректный номер телефона в формате +7 (XXX) XXX XX-XX', 'error');
                return;
            }

            // Валидация пола
            if (!gender) {
                showNotification('Укажите ваш пол', 'error');
                return;
            }

            // Отправка данных на сервер
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
                    if (data.success) {
                        showNotification(`Спасибо, ${name}! В ближайшее время с вами свяжется наш менеджер для оформления абонемента "${tariffName}"`, 'success');
                        closeTariffModal();
                        document.getElementById('tariffOrderForm').reset();
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

        function submitCallback(event) {
            event.preventDefault();
            const phone = document.getElementById('callbackPhone').value;
            const agreePolicy = document.getElementById('agreePolicy').checked;
            const agreeInfo = document.getElementById('agreeInfo').checked;

            // ДОБАВИТЬ ЭТУ СТРОКУ — получаем CSRF-токен
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

            const formData = new FormData();
            formData.append('action', 'callback');
            formData.append('phone', cleanPhone);

            // ДОБАВИТЬ ЭТУ СТРОКУ — передаём токен на сервер
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
                    console.error('Error:', error);
                    showNotification('Ошибка при отправке. Попробуйте позже.', 'error');
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

        function initMap() {
            var coords = [54.619912, 39.788558];

            var myMap = new ymaps.Map('map', {
                center: coords,
                zoom: 17,
                controls: ['zoomControl']
            });

            myMap.behaviors.enable('scrollZoom');
            myMap.options.set('suppressMapOpenBlock', true);
            myMap.controls.remove('copyrights');
            myMap.controls.remove('logo');

            var myPlacemark = new ymaps.Placemark(coords, {
                hintContent: 'BodyGym',
                balloonContentHeader: '<div style="display:flex; align-items:center; gap:10px;"><img src="assets/img/photo-logo.png" style="width:30px; height:30px; border-radius:50%;"><span style="color:#333333; font-weight:bold; font-size:18px;">BodyGym</span></div>',

            }, {
                preset: 'islands#pinkCircleIcon',
                iconColor: '#ff69b4'
            });

            myMap.geoObjects.add(myPlacemark);
            myPlacemark.balloon.open();
        }

        ymaps.ready(initMap);

        // Форматирование телефона при вводе
        document.querySelectorAll('input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
        });

        // Закрытие модальных окон по клику вне
        window.onclick = function(event) {
            const galleryModal = document.getElementById('galleryModal');
            const tariffModal = document.getElementById('tariffModal');
            if (event.target === galleryModal) closeGalleryModal();
            if (event.target === tariffModal) closeTariffModal();
        }


        // Плавное появление элементов при скролле
        document.addEventListener('DOMContentLoaded', function() {
            // Выбираем все элементы, которые нужно анимировать
            const animatedElements = document.querySelectorAll('.GalleryItem, .TariffItem, .QuickLink, .SecondAboutItem, .CoachCard, .review-block, .MapContainer, .CallbackForm, .AboutBlock, .ContentSecondPage');

            // Добавляем классы анимации
            animatedElements.forEach((el, index) => {
                el.classList.add('fade-up');
                // Добавляем задержку для последовательного появления
                if (index % 3 === 0) el.classList.add('delay-1');
                if (index % 3 === 1) el.classList.add('delay-2');
                if (index % 3 === 2) el.classList.add('delay-3');
            });

            // Анимация для заголовков
            const titles = document.querySelectorAll('.GalleryTitleP, .TariffsTitleP, .MapTitleP, .NameSecondPageP, .CoachListTitle, .ContactsPageTitle');
            titles.forEach(title => {
                title.classList.add('fade-scale');
            });

            // Анимация для левой и правой частей
            const leftContents = document.querySelectorAll('.LeftContentSecondPage, .LeftContentFifthPage');
            leftContents.forEach(el => {
                el.classList.add('fade-left');
            });

            const rightContents = document.querySelectorAll('.RightContentSecondPage, .RightContentFifthPage, .MapInfo');
            rightContents.forEach(el => {
                el.classList.add('fade-right');
            });

            // Функция проверки видимости элемента
            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                const windowHeight = window.innerHeight || document.documentElement.clientHeight;
                return rect.top <= windowHeight - 100 && rect.bottom >= 0;
            }

            // Функция для активации анимаций
            function checkVisibility() {
                const elements = document.querySelectorAll('.fade-up, .fade-left, .fade-right, .fade-scale');
                elements.forEach(el => {
                    if (isElementInViewport(el) && !el.classList.contains('visible')) {
                        el.classList.add('visible');
                    }
                });
            }

            // Запускаем проверку при загрузке
            checkVisibility();

            // Запускаем проверку при скролле
            window.addEventListener('scroll', checkVisibility);

            // Запускаем проверку при ресайзе окна
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

            // Закрытие при клике на ссылку
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    burger.classList.remove('active');
                    navMenu.classList.remove('active');
                });
            });
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SportsClub",
            "name": "BodyGym",
            "alternateName": "BodyGym Спортзал",
            "image": "https://bodygym.ru/assets/img/photo-logo.png",
            "description": "Современный спортзал BodyGym в Рязани. Тренажерный зал, бассейн, спа-зона, групповые занятия.",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Касимовское шоссе, д. 30",
                "addressLocality": "Рязань",
                "addressRegion": "Рязанская область",
                "postalCode": "390000",
                "addressCountry": "RU"
            },
            "telephone": "+79206392277",
            "priceRange": "₽1900 - ₽3900",
            "openingHours": "Mo-Su 07:00-23:00",
            "openingHoursSpecification": [{
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
                "opens": "07:00",
                "closes": "23:00"
            }],
            "sameAs": [
                "https://vk.com/bodygym",
                "https://t.me/bodygym"
            ]
        }
    </script>
</body>

</html>