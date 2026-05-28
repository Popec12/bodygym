<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = getCurrentUserId();
$user = getUserInfo($pdo, $user_id);

// Обновление информации пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $errors = [];

    if (empty($name) || strlen($name) < 2 || !preg_match('/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/u', $name)) {
        $errors[] = 'Имя должно содержать минимум 2 буквы (только буквы, дефис или пробел)';
    }
    if (empty($surname) || strlen($surname) < 2 || !preg_match('/^[A-Za-zА-Яа-яёЁ\s\-]{2,}$/u', $surname)) {
        $errors[] = 'Фамилия должна содержать минимум 2 буквы (только буквы, дефис или пробел)';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }

    $cleanPhone = preg_replace('/[^\d]/', '', $phone);
    if (empty($phone) || strlen($cleanPhone) < 11) {
        $errors[] = 'Введите корректный номер телефона';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);

        if ($stmt->rowCount() > 0) {
            $errors[] = 'Этот email уже используется другим пользователем';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, email = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$name, $surname, $email, $phone, $user_id])) {
            $user['name'] = $name;
            $user['surname'] = $surname;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['profile_success'] = 'Профиль успешно обновлен';
            header('Location: profile.php?success=profile_updated');
            exit;
        } else {
            $_SESSION['profile_error'] = 'Ошибка при обновлении профиля';
            header('Location: profile.php?error=profile_error');
            exit;
        }
    } else {
        $_SESSION['profile_error'] = implode('<br>', $errors);
        header('Location: profile.php?error=profile_error');
        exit;
    }
}

// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    if (!empty($_FILES['avatar']['name'])) {
        $avatar = uploadAvatar($_FILES['avatar'], $user_id);
        if ($avatar) {
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$avatar, $user_id]);
            $user['avatar'] = $avatar;
            $_SESSION['profile_success'] = 'Аватар успешно обновлен';
            header('Location: profile.php?success=avatar_updated');
            exit;
        } else {
            $_SESSION['profile_error'] = 'Ошибка при загрузке аватара. Проверьте формат и размер файла (до 2MB)';
            header('Location: profile.php?error=avatar_error');
            exit;
        }
    }
}

// Удаление отзыва
if (isset($_GET['delete_review'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['delete_review'], $user_id])) {
        $_SESSION['profile_success'] = 'Отзыв успешно удален';
        header('Location: profile.php?success=review_deleted');
        exit;
    }
}

// Удаление заявки на тест-драйв
if (isset($_GET['delete_application'])) {
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['delete_application'], $user_id])) {
        $_SESSION['profile_success'] = 'Заявка успешно удалена';
        header('Location: profile.php?success=application_deleted');
        exit;
    }
}

// Удаление заказа абонемента
if (isset($_GET['delete_membership_order'])) {
    $stmt = $pdo->prepare("DELETE FROM membership_orders WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['delete_membership_order'], $user_id])) {
        $_SESSION['profile_success'] = 'Заказ абонемента успешно удален';
        header('Location: profile.php?success=order_deleted');
        exit;
    }
}

// Получение отзывов пользователя
$user_reviews = [];
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
if ($stmt->rowCount() > 0) {
    $user_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение заявок на тест-драйв
$user_applications = [];
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
if ($stmt->rowCount() > 0) {
    $user_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение заказов абонементов
$user_orders = [];
$stmt = $pdo->prepare("SELECT * FROM membership_orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
if ($stmt->rowCount() > 0) {
    $user_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function uploadAvatar($file, $user_id)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024;

    if (!in_array($file['type'], $allowed_types)) return false;
    if ($file['size'] > $max_size) return false;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $upload_path = 'assets/uploads/avatars/';

    if (!file_exists($upload_path)) mkdir($upload_path, 0777, true);

    if (move_uploaded_file($file['tmp_name'], $upload_path . $filename)) return $filename;
    return false;
}

// Получаем сообщения из сессии
$success_message = $_SESSION['profile_success'] ?? null;
$error_message = $_SESSION['profile_error'] ?? null;
unset($_SESSION['profile_success']);
unset($_SESSION['profile_error']);
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
            font-size: 1px;
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

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .form-buttons .form-btn {
            flex: 1;
            margin-top: 0;
        }

        .cancel-btn {
            background-color: #666;
        }

        .cancel-btn:hover {
            background-color: #888;
        }

        .admin-panel-btn {
            text-align: center;
            width: 45%;
            font-weight: bold;
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #000;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-size: 14px;
        }

        .admin-panel-btn:hover {
            box-shadow: 0 0px 15px rgba(255, 143, 171, 0.4);
        }

        .page-content {

            margin-top: 166px;
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
    <div class="page-content">
        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar-container">
                    <form method="POST" action="" enctype="multipart/form-data" id="avatar-form">
                        <input type="hidden" name="update_avatar" value="1">
                        <label for="avatar-input" style="cursor: pointer;">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="assets/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Аватар" class="avatar">
                            <?php else: ?>
                                <img src="assets/img/i (1).webp" alt="" class="avatar">
                            <?php endif; ?>

                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;" onchange="document.getElementById('avatar-form').submit();">
                    </form>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['name'] . ' ' . ($user['surname'] ?? '')); ?></h1>
                    <p>Email: <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    <p>Телефон: <?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
                    <p>Дата регистрации: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="admin-panel-btn">Админ-панель</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-tabs">
                <div class="profile-tab active" onclick="switchTab('edit')">Редактировать профиль</div>
                <div class="profile-tab" onclick="switchTab('reviews')">Мои отзывы (<?php echo count($user_reviews); ?>)</div>
                <div class="profile-tab" onclick="switchTab('applications')">Заявки на тест-драйв (<?php echo count($user_applications); ?>)</div>
                <div class="profile-tab" onclick="switchTab('orders')">Заказы абонементов (<?php echo count($user_orders); ?>)</div>
            </div>

            <!-- Вкладка редактирования профиля -->
            <div id="edit-tab" class="tab-content active">
                <form method="POST" action="" id="profile-form">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" id="editName" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="form-input" required minlength="2" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Фамилия</label>
                        <input type="text" name="surname" id="editSurname" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>" class="form-input" required minlength="2" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" name="phone" id="editPhone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="form-input phone-mask" required>
                    </div>

                    <div class="form-buttons" id="formButtons" style="display: none;">
                        <button type="submit" class="form-btn">Сохранить изменения</button>
                        <button type="button" class="form-btn cancel-btn" onclick="cancelEdit()">Отменить</button>
                    </div>
                </form>
            </div>

            <!-- Вкладка отзывов -->
            <div id="reviews-tab" class="tab-content">
                <div class="reviews-list">
                    <?php if (!empty($user_reviews)): ?>
                        <?php foreach ($user_reviews as $review): ?>
                            <div class="review-item" style="background: linear-gradient(145deg, rgba(49,49,49,0.95), rgba(35,35,35,0.95)); border: 1px solid rgba(255,192,203,0.3); border-radius: 16px; padding: 25px; margin-bottom: 25px;">
                                <div class="review-rating" style="color: #ffd700; font-size: 28px; margin-bottom: 15px;">
                                    <?php echo str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']); ?>
                                </div>
                                <div class="comment" style="color: #f0f0f0; font-size: 20px; line-height: 1.6; margin-bottom: 15px;">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                                <div class="review-date" style="font-size: 13px; color: #888; border-top: 1px solid #444; padding-top: 12px;">
                                    Опубликован: <?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?>
                                    <?php if (!empty($review['updated_at']) && $review['updated_at'] != '0000-00-00 00:00:00'): ?>
                                        <br>Отредактирован: <?php echo date('d.m.Y H:i', strtotime($review['updated_at'])); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($review['admin_comment'])): ?>
                                    <div class="admin-comment-profile" style="margin-top: 15px; padding: 15px; background-color: rgba(244,177,177,0.15); border-left: 4px solid pink; border-radius: 8px;">
                                        <strong style="color: pink; display: block; margin-bottom: 8px;">Ответ администратора:</strong>
                                        <?php echo nl2br(htmlspecialchars($review['admin_comment'])); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="action-buttons" style="margin-top: 15px;">
                                    <a href="profile.php?delete_review=<?php echo $review['id']; ?>" class="action-btn btn-delete" style="background: #ff9dad; color: #313131; padding: 8px 20px; border-radius: 30px; text-decoration: none;" onclick="return confirm('Удалить отзыв?')">Удалить отзыв</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Вы еще не оставляли отзывы</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="AboutUs.php" class="form-btn btn-new" style="display: inline-block; margin-top: 20px;">Добавить отзыв</a>
            </div>

            <!-- Вкладка заявок на тест-драйв -->
            <div id="applications-tab" class="tab-content">
                <div class="applications-list">
                    <?php if (!empty($user_applications)): ?>
                        <?php foreach ($user_applications as $app): ?>
                            <div class="admin-item" style="background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95)); padding: 25px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 192, 203, 0.3);">
                                <div class="item-header" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                    <div>
                                        <div class="item-user" style="font-size: 20px;  color: #ff8ab5; font-weight: bold;"><?php echo htmlspecialchars($app['name'] . ' ' . $app['surname']); ?></div>
                                        <div class="item-email" style="color: #888;">Email: <?php echo htmlspecialchars($app['email']); ?></div>
                                        <div class="item-email" style="color: #888;">Телефон: <?php echo htmlspecialchars($app['phone']); ?></div>
                                    </div>
                                </div>
                                <div class="app-status <?php
                                                        if ($app['status'] == 'Одобрена') echo 'status-approved';
                                                        elseif ($app['status'] == 'Отклонена') echo 'status-declined';
                                                        else echo 'status-pending';
                                                        ?>" style="display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: bold; margin-bottom: 15px;">
                                    Статус: <?php echo htmlspecialchars($app['status']); ?>
                                </div>
                                <div class="app-details" style="background: #333; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <p>Тариф: <?php echo htmlspecialchars($app['tariff_name'] ?? 'Platinum (Тест-драйв)'); ?></p>
                                    <p>Цена: <?php echo number_format($app['tariff_price'] ?? 800, 0, '', ' '); ?> ₽/день</p>
                                    <p>Дата рождения: <?php echo date('d.m.Y', strtotime($app['birthdate'])); ?></p>
                                    <p>Пол: <?php echo $app['gender'] == 'male' ? 'Мужчина' : 'Женщина'; ?></p>
                                    <p>Опыт: <?php
                                                $expMap = ['beginner' => 'Новичок', 'intermediate' => 'Любитель', 'advanced' => 'Продвинутый', 'pro' => 'Профессионал'];
                                                echo $expMap[$app['experience']] ?? $app['experience'];
                                                ?></p>
                                    <?php if (!empty($app['message'])): ?>
                                        <p>Комментарий: <?php echo nl2br(htmlspecialchars($app['message'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="application-date" style="color: #aaa; font-size: 13px; margin-bottom: 10px;">Отправлена: <?php echo date('d.m.Y H:i', strtotime($app['created_at'])); ?></div>


                                <?php if (!empty($app['admin_response'])): ?>
                                    <div class="admin-response" style="margin-top: 15px; padding: 15px; background-color: rgba(244,177,177,0.15); border-left: 4px solid pink; border-radius: 8px;">
                                        <strong style="color: pink; display: block; margin-bottom: 8px;">Ответ администратора:</strong>
                                        <div style="word-wrap: break-word; font-size: 14px;"><?php echo nl2br(htmlspecialchars($app['admin_response'])); ?></div>
                                    </div>
                                <?php endif; ?>



                                <div class="action-buttons" style="margin-top: 15px;">
                                    <a href="profile.php?delete_application=<?php echo $app['id']; ?>" class="action-btn btn-delete" style="background: #ff9dad; color: #313131; padding: 8px 20px; border-radius: 30px; text-decoration: none;" onclick="return confirm('Удалить заявку?')">Удалить заявку</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Вы еще не отправляли заявки на тест-драйв</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="Services.php" class="form-btn btn-new">Новая заявка</a>
            </div>

            <!-- Вкладка заказов абонементов -->
            <div id="orders-tab" class="tab-content">
                <div class="applications-list">
                    <?php if (!empty($user_orders)): ?>
                        <?php foreach ($user_orders as $order): ?>
                            <div class="admin-item" style="background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95)); padding: 25px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 192, 203, 0.3);">
                                <div class="item-header" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                    <div>
                                        <div class="item-user" style="font-size: 20px;  color: #ff8ab5; font-weight: bold;"><?php echo htmlspecialchars($order['name'] . ' ' . $order['surname']); ?></div>
                                        <div class="item-email" style="color: #888;">Email: <?php echo htmlspecialchars($order['email']); ?></div>
                                        <div class="item-email" style="color: #888;">Телефон: <?php echo htmlspecialchars($order['phone']); ?></div>
                                    </div>
                                </div>
                                <div class="app-status <?php
                                                        if ($order['status'] == 'Одобрен') echo 'status-approved';
                                                        elseif ($order['status'] == 'Отклонен') echo 'status-declined';
                                                        else echo 'status-pending';
                                                        ?>" style="display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: bold; margin-bottom: 15px;">
                                    Статус: <?php echo htmlspecialchars($order['status']); ?>
                                </div>
                                <div class="app-details" style="background: #333; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <p>Тариф: <?php echo htmlspecialchars($order['tariff_name']); ?></p>
                                    <p>Цена: <?php echo number_format($order['tariff_price'], 0, '', ' '); ?> ₽/мес</p>
                                    <p>Дата рождения: <?php echo date('d.m.Y', strtotime($order['birthdate'])); ?></p>
                                    <p>Пол: <?php echo $order['gender'] == 'male' ? 'Мужчина' : 'Женщина'; ?></p>
                                    <p>Опыт: <?php
                                                $expMap = ['beginner' => 'Новичок', 'intermediate' => 'Любитель', 'advanced' => 'Продвинутый', 'pro' => 'Профессионал'];
                                                echo safe($expMap[$order['experience']]) ?? safe($order['experience']);
                                                ?></p>
                                </div>
                                <div class="application-date" style="color: #aaa; font-size: 13px; margin-bottom: 10px;">Отправлен: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                                <div class="action-buttons" style="margin-top: 15px;">
                                    <a href="profile.php?delete_membership_order=<?php echo safe($order['id']); ?>" class="action-btn btn-delete" style="background: #ff9dad; color: #313131; padding: 8px 20px; border-radius: 30px; text-decoration: none;" onclick="return confirm('Удалить заказ?')">Удалить заказ</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Вы еще не оформляли заказы абонементов</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="Services.php" class="form-btn btn-new">Оформить абонемент</a>
            </div>

            <div class="logout-btn-container">
                <a href="logout.php" class="logout-btn">Выйти из аккаунта</a>
            </div>
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
        // Прозрачный хедер при скролле
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });

        function switchTab(tabName) {
            document.querySelectorAll('.profile-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.querySelector(`.profile-tab[onclick*="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

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
            const existing = document.querySelector('.notification-overlay');
            if (existing) existing.remove();

            const notif = document.createElement('div');
            notif.className = `notification-overlay ${type}`;
            notif.innerHTML = `
            <div class="Notification-content">
                    <div class="Notification-message">${message}</div>
                    <button class="notification-close" id="closeNotifBtn">✕</button>
                </div>
        `;
            document.body.appendChild(notif);

            const closeBtn = notif.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => notif.remove());
            setTimeout(() => {
                if (notif) notif.remove();
            }, 5000);
        }

        // Сохраняем исходные значения полей
        let originalValues = {};

        function checkFormChanges() {
            const name = document.getElementById('editName');
            const surname = document.getElementById('editSurname');
            const email = document.getElementById('editEmail');
            const phone = document.getElementById('editPhone');

            const hasChanges = (name.value !== originalValues.name) ||
                (surname.value !== originalValues.surname) ||
                (email.value !== originalValues.email) ||
                (phone.value !== originalValues.phone);

            const formButtons = document.getElementById('formButtons');
            formButtons.style.display = hasChanges ? 'flex' : 'none';
        }

        function cancelEdit() {
            document.getElementById('editName').value = originalValues.name;
            document.getElementById('editSurname').value = originalValues.surname;
            document.getElementById('editEmail').value = originalValues.email;
            document.getElementById('editPhone').value = originalValues.phone;
            formatPhoneNumber(document.getElementById('editPhone'));
            document.getElementById('formButtons').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Сохраняем исходные значения
            originalValues = {
                name: document.getElementById('editName').value,
                surname: document.getElementById('editSurname').value,
                email: document.getElementById('editEmail').value,
                phone: document.getElementById('editPhone').value
            };

            // Добавляем обработчики на поля
            const fields = ['editName', 'editSurname', 'editEmail', 'editPhone'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) field.addEventListener('input', checkFormChanges);
            });

            // Форматирование телефона
            const phoneInputs = document.querySelectorAll('.phone-mask');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function() {
                    formatPhoneNumber(this);
                });
                if (input.value) formatPhoneNumber(input);
            });

            // Прозрачный хедер при скролле
            window.addEventListener('scroll', function() {
                const header = document.getElementById('mainHeader');
                if (window.scrollY > 50) header.classList.add('header-scrolled');
                else header.classList.remove('header-scrolled');
            });

            <?php if ($success_message): ?>
                showNotification('<?php echo addslashes($success_message); ?>', 'success');
            <?php endif; ?>

            <?php if ($error_message): ?>
                showNotification('<?php echo addslashes($error_message); ?>', 'error');
            <?php endif; ?>
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