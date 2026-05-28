<?php
require_once 'config_bodygym.php';
require_once 'includes/seo.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

// Управление отзывами
if (isset($_POST['update_review'])) {
    $review_id = $_POST['review_id'];
    $admin_comment = htmlspecialchars(trim($_POST['admin_comment']));

    $stmt = $pdo->prepare("UPDATE reviews SET admin_comment = ? WHERE id = ?");
    if ($stmt->execute([$admin_comment, $review_id])) {
        $_SESSION['admin_success'] = 'Ответ на отзыв сохранен';
        header('Location: admin.php?section=reviews');
        exit;
    }
}

if (isset($_GET['delete_review'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$_GET['delete_review']])) {
        $_SESSION['admin_success'] = 'Отзыв удален';
        header('Location: admin.php?section=reviews');
        exit;
    }
}

// Управление заявками на тест-драйв
if (isset($_POST['update_application'])) {
    $app_id = $_POST['app_id'];
    $status = $_POST['status'];
    $admin_response = htmlspecialchars(trim($_POST['admin_response']));

    $is_approved = $status == 'approved' ? 1 : 0;
    $is_declined = $status == 'declined' ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE applications SET is_approved = ?, is_declined = ?, admin_response = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$is_approved, $is_declined, $admin_response, $status, $app_id])) {
        $_SESSION['admin_success'] = 'Статус заявки обновлен';
        header('Location: admin.php?section=applications');
        exit;
    }
}

// Удаление заявки на тест-драйв
if (isset($_GET['delete_application'])) {
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    if ($stmt->execute([$_GET['delete_application']])) {
        $_SESSION['admin_success'] = 'Заявка удалена';
        header('Location: admin.php?section=applications');
        exit;
    }
}

// Управление заказами абонементов
if (isset($_POST['update_membership_order'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE membership_orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $_SESSION['admin_success'] = 'Статус заказа обновлен';
        header('Location: admin.php?section=orders');
        exit;
    }
}

if (isset($_GET['delete_membership_order'])) {
    $stmt = $pdo->prepare("DELETE FROM membership_orders WHERE id = ?");
    if ($stmt->execute([$_GET['delete_membership_order']])) {
        $_SESSION['admin_success'] = 'Заказ удален';
        header('Location: admin.php?section=orders');
        exit;
    }
}

// Управление заявками на обратный звонок
if (isset($_POST['update_callback'])) {
    $callback_id = $_POST['callback_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE callback_requests SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $callback_id])) {
        $_SESSION['admin_success'] = 'Статус заявки обновлен';
        header('Location: admin.php?section=callbacks');
        exit;
    }
}

if (isset($_GET['delete_callback'])) {
    $stmt = $pdo->prepare("DELETE FROM callback_requests WHERE id = ?");
    if ($stmt->execute([$_GET['delete_callback']])) {
        $_SESSION['admin_success'] = 'Заявка удалена';
        header('Location: admin.php?section=callbacks');
        exit;
    }
}

// Получение данных
$reviews_stmt = $pdo->query("SELECT r.*, u.email as user_email FROM reviews r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

$apps_stmt = $pdo->query("SELECT a.*, u.email as user_email FROM applications a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
$applications = $apps_stmt->fetchAll(PDO::FETCH_ASSOC);

$orders_stmt = $pdo->query("SELECT * FROM membership_orders ORDER BY created_at DESC");
$membership_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

$callbacks_stmt = $pdo->query("SELECT * FROM callback_requests ORDER BY created_at DESC");
$callbacks = $callbacks_stmt->fetchAll(PDO::FETCH_ASSOC);

$active_section = $_GET['section'] ?? 'orders';

$success_message = $_SESSION['admin_success'] ?? null;
$error_message = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success']);
unset($_SESSION['admin_error']);
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
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid pink;
        }

        .admin-header h1 {
            color: pink;
            font-size: 36px;
        }

        .admin-tabs {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .admin-tab {
            background: #444;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .admin-tab:hover,
        .admin-tab.active {
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #313131;
            font-weight: bold;
        }

        .admin-section {
            background: rgba(30, 29, 29, 0.95);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid #444;
        }

        .admin-section p {
            color: pink;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }

        .admin-items-container {
            max-height: 600px;
            overflow-y: auto;
        }

        .admin-items-container::-webkit-scrollbar {
            width: 8px;
        }

        .admin-items-container::-webkit-scrollbar-track {
            background: #444;
            border-radius: 4px;
        }

        .admin-items-container::-webkit-scrollbar-thumb {
            background: pink;
            border-radius: 4px;
        }

        .admin-item {
            background: linear-gradient(145deg, rgba(49, 49, 49, 0.95), rgba(35, 35, 35, 0.95));
            border: 1px solid rgba(255, 192, 203, 0.3);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .item-user {
            color: #ff8ab5;
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .item-email {
            color: #888;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .item-date {
            font-size: 20px;
            margin: 5px 0;
            color: #ddd;
        }

        .item-gend {
            font-size: 20px;
            margin: 5px 0;
            color: #ddd;
        }

        .item-rating {
            font-size: 20px;
            margin: 5px 0;
            color: #ddd;
        }

        .item-price- {
            font-size: 20px;
            margin: 5px 0;
            color: #ddd;
        }

        .app-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            color: #ff8ab5;
        }

        .app-details {
            font-size: 20px;
            margin: 5px 0;
            color: #ddd;
        }

        .app-details p {
            margin: 5px 0;
            color: #ddd;
        }

        .item-content {
            color: white;
            margin-bottom: 10px;
            line-height: 1.5;
            font-size: 20px;
            background-color: #333;
            padding: 12px;
            border-radius: 5px;

        }

        .admin-response {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(244, 177, 177, 0.15);
            border-left: 3px solid pink;
            border-radius: 5px;
            font-size: 16px;
        }

        .admin-response strong {
            color: pink;
            display: block;
            margin-bottom: 5px;
        }

        .admin-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #555;
        }

        .form-select {
            padding: 8px 12px;
            background: #555;
            color: white;
            border: 1px solid #666;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-textarea {
            width: 100%;
            padding: 10px;
            background: #555;
            color: white;
            border: 1px solid #666;
            border-radius: 5px;
            margin-bottom: 10px;
            resize: vertical;
        }

        .form-btn-small {
            padding: 10px 25px;
            background: linear-gradient(135deg, #ffafcc, #ff8fab);
            color: #141414;
            transition: all 0.3s ease;
            border: 1px solid #666;
            border-radius: 6px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 0px 15px rgba(255, 18, 77, 0.4);
        }

        .form-btn-small:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #ff81af, #ff6b90);
            box-shadow: 0 0px 15px rgba(255, 143, 171, 0.4);
        }


        .btn-delete {
            width: 100px;
            text-align: center;
            background-color: #bd0068;
            color: #000000;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 0px 15px rgba(255, 143, 171, 0.4);
        }

        .btn-delete:hover {
            color: #ffffff;
            background-color: #69003a;
            box-shadow: 0 0px 15px rgba(255, 18, 77, 0.4);
        }

        .empty-state {
            text-align: center;
            color: #888;
            padding: 40px 20px;
        }

        /* Уведомления */
        .notification-overlay {
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            max-width: 300px;
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

        .page-content {
            margin-top: 180px;
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
        <div class="admin-container">
            <div class="admin-tabs">
                <button class="admin-tab <?php echo $active_section == 'orders' ? 'active' : ''; ?>" onclick="switchSection('orders')">Заказы абонементов</button>
                <button class="admin-tab <?php echo $active_section == 'callbacks' ? 'active' : ''; ?>" onclick="switchSection('callbacks')">Заявки на обратный звонок</button>
                <button class="admin-tab <?php echo $active_section == 'reviews' ? 'active' : ''; ?>" onclick="switchSection('reviews')">Управление отзывами</button>
                <button class="admin-tab <?php echo $active_section == 'applications' ? 'active' : ''; ?>" onclick="switchSection('applications')">Заявки на тест-драйв</button>
            </div>

            <!-- Заказы абонементов -->
            <div id="orders-section" class="admin-section" style="display: <?php echo $active_section == 'orders' ? 'block' : 'none'; ?>">
                <p>Заказы абонементов (<?php echo count($membership_orders); ?>)</p>
                <div class="admin-items-container">
                    <?php if (empty($membership_orders)): ?>
                        <div class="empty-state">
                            <p>Заказов пока нет</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($membership_orders as $order): ?>
                            <div class="admin-item">
                                <div class="item-header" style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 10px;">
                                    <div>
                                        <div class="item-user" style="color: #ff8ab5; font-weight: bold;"><?php echo htmlspecialchars(safe($order['name']) . ' ' . safe($order['surname'])); ?></div>
                                        <div class="item-email" style="color: #888;">Email: <?php echo htmlspecialchars(safe($order['email'])); ?></div>
                                        <div class="item-email" style="color: #888;">Телефон: <?php echo htmlspecialchars(safe($order['phone'])); ?></div>
                                    </div>
                                </div>
                                <div class="app-details" style="background: #333; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    Опыт: <?php
                                            $expMap = ['beginner' => 'Новичок', 'intermediate' => 'Любитель', 'advanced' => 'Продвинутый', 'pro' => 'Профессионал'];
                                            echo $expMap[$order['experience']] ?? $order['experience'];
                                            ?>
                                    <div class="item-rating">Тариф: <?php echo htmlspecialchars(safe($order['tariff_name'])); ?></div>
                                    <div class="item-price-"> Цена: <?php echo number_format(safe($order['tariff_price']), 0, '', ' '); ?> ₽/мес</div>
                                    <div class="item-date">Дата рождения: <?php echo date('d.m.Y', strtotime(safe($order['birthdate']))); ?></div>

                                    <div class="item-gend">Пол: <?php echo safe($order['gender']) == 'male' ? 'Мужчина' : 'Женщина'; ?></div>
                                </div>
                                <div class="app-status <?php
                                                        echo safe($order['status']) == 'В обработке' ? 'status-pending' : ($order['status'] == 'Одобрен' ? 'status-approved' : 'status-declined');
                                                        ?>" style="display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
                                    Статус: <?php echo htmlspecialchars(safe($order['status'])); ?>
                                </div>
                                <form method="POST" class="admin-form" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #555;">
                                    <input type="hidden" name="order_id" value="<?php echo safe($order['id']); ?>">
                                    <select name="status" class="form-select" style="font-size: 20px; padding: 8px 12px; background: #555; color: white; border-radius: 5px; margin-right: 10px;">
                                        <option value="В обработке" style="font-size: 18px;" <?php echo safe($order['status']) == 'В обработке' ? 'selected' : ''; ?>>В обработке</option>
                                        <option value="Одобрен" style="font-size: 18px;" <?php echo safe($order['status']) == 'Одобрен' ? 'selected' : ''; ?>>Одобрен</option>
                                        <option value="Отклонен" style="font-size: 18px;" <?php echo safe($order['status']) == 'Отклонен' ? 'selected' : ''; ?>>Отклонен</option>
                                    </select>
                                    <button type="submit" name="update_membership_order" class="form-btn-small">Обновить</button>
                                    <a href="admin.php?delete_membership_order=<?php echo safe($order['id']); ?>&section=orders" class="btn-delete" onclick="return confirm('Удалить заказ?')">Удалить заказ</a>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Заявки на обратный звонок -->
            <div id="callbacks-section" class="admin-section" style="display: <?php echo $active_section == 'callbacks' ? 'block' : 'none'; ?>">
                <p>Заявки на обратный звонок (<?php echo count($callbacks); ?>)</p>
                <div class="admin-items-container">
                    <?php if (empty($callbacks)): ?>
                        <div class="empty-state">
                            <p>Заявок пока нет</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($callbacks as $callback): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <div>
                                        <div class="item-price" style="color: #ffffff;">Телефон: <?php echo htmlspecialchars(safe($callback['phone'])); ?></div>
                                        <div class="item-date" style="color: #888; font-size: 16px;">Дата: <?php echo date('d.m.Y H:i', strtotime(safe($callback['created_at']))); ?></div>
                                    </div>
                                </div>
                                <div class="app-status status-<?php echo safe($callback['status']) == 'В обработке' ? 'pending' : 'approved'; ?>">
                                    Статус: <?php echo htmlspecialchars(safe($callback['status'])); ?>
                                </div>
                                <form method="POST" class="admin-form">
                                    <input type="hidden" name="callback_id" value="<?php echo safe($callback['id']); ?>">
                                    <select name="status" class="form-select" style="margin-right: 10px; font-size: 20px;">
                                        <option value="В обработке" style="font-size: 18px;" <?php echo safe($callback['status']) == 'В обработке' ? 'selected' : ''; ?>>В обработке</option>
                                        <option value="Обработан" style="font-size: 18px;" <?php echo safe($callback['status']) == 'Обработан' ? 'selected' : ''; ?>>Обработан</option>
                                    </select>
                                    <button type="submit" name="update_callback" class="form-btn-small">Обновить</button>
                                    <a href="admin.php?delete_callback=<?php echo safe($callback['id']); ?>&section=callbacks" class="btn-delete" onclick="return confirm('Удалить заявку?')">Удалить заявку</a>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Управление отзывами -->
            <div id="reviews-section" class="admin-section" style="display: <?php echo $active_section == 'reviews' ? 'block' : 'none'; ?>">
                <p>Управление отзывами (<?php echo count($reviews); ?>)</p>
                <div class="admin-items-container">
                    <?php if (empty($reviews)): ?>
                        <div class="empty-state">
                            <p>Отзывов пока нет</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <div>
                                        <div class="item-user" style="color: #ff8ab5;"><?php echo htmlspecialchars(safe($review['name'])); ?></div>
                                        <?php if ($review['user_email']): ?>
                                            <div class="item-email" style="color: #888; font-size: 16px;"><?php echo htmlspecialchars(safe($review['user_email'])); ?></div>
                                        <?php endif; ?>
                                        <div class="item-date" style="color: #888; font-size: 16px;"><?php echo date('d.m.Y H:i', strtotime(safe($review['created_at']))); ?></div>
                                    </div>
                                </div>
                                <div class="review-rating"><?php echo str_repeat("★", safe($review['rating'])) . str_repeat("☆", 5 - safe($review['rating'])); ?></div>
                                <div class="item-content"><?php echo nl2br(htmlspecialchars(safe($review['comment']))); ?></div>
                                <?php if ($review['admin_comment']): ?>
                                    <div class="admin-response"><strong>Ваш комментарий:</strong> <?php echo nl2br(htmlspecialchars(safe($review['admin_comment']))); ?></div>
                                <?php endif; ?>
                                <form method="POST" class="admin-form">
                                    <input type="hidden" name="review_id" value="<?php echo safe($review['id']); ?>">
                                    <textarea name="admin_comment" class="form-textarea" placeholder="Комментарий администратора..."><?php echo htmlspecialchars(safe($review['admin_comment']) ?? ''); ?></textarea>
                                    <button type="submit" name="update_review" class="form-btn-small">Сохранить</button>
                                    <a href="admin.php?delete_review=<?php echo safe($review['id']); ?>&section=reviews" class="btn-delete" onclick="return confirm('Удалить отзыв?')">Удалить отзыв</a>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Заявки на тест-драйв -->
            <div id="applications-section" class="admin-section" style="display: <?php echo $active_section == 'applications' ? 'block' : 'none'; ?>">
                <p>Заявки на тест-драйв (<?php echo count($applications); ?>)
                <p>
                <div class="admin-items-container">
                    <?php if (empty($applications)): ?>
                        <div class="empty-state">
                            <p>Заявок пока нет</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <div>
                                        <div class="item-user"><?php echo htmlspecialchars(safe($app['name']) . ' ' . safe($app['surname'])); ?></div>
                                        <div class="item-email">Email: <?php echo htmlspecialchars(safe($app['email'])); ?></div>
                                        <div class="item-email">Телефон: <?php echo htmlspecialchars(safe($app['phone'])); ?></div>
                                    </div>
                                </div>


                                <div class="app-details">Опыт: <?php
                                                                $expMap = ['beginner' => 'Новичок', 'intermediate' => 'Любитель', 'advanced' => 'Продвинутый', 'pro' => 'Профессионал'];
                                                                echo safe($expMap[$app['experience']]) ?? safe($app['experience']);
                                                                ?>
                                    <div class="item-rating">Тариф: <?php echo htmlspecialchars(safe($app['tariff_name'])); ?></div>
                                    <div class="item-price-"> Цена: <?php echo number_format(safe($app['tariff_price']), 0, '', ' '); ?> ₽/день</div>
                                    <div class="item-date">Дата рождения: <?php echo date('d.m.Y', strtotime(safe($app['birthdate']))); ?></div>

                                    <div class="item-gend">Пол: <?php echo safe($app['gender']) == 'male' ? 'Мужчина' : 'Женщина'; ?></div>
                                </div>
                                <?php if (!empty($app['message'])): ?>
                                    <div class="item-content">Комментарий: <?php echo nl2br(htmlspecialchars(safe($app['message']))); ?></div>
                                <?php endif; ?>
                                <div class="app-status 
                                <?php
                                if ($app['status'] == 'Одобрена') echo 'status-approved';
                                elseif ($app['status'] == 'Отклонена') echo 'status-declined';
                                else echo 'status-pending';
                                ?>">
                                    Статус: <?php echo htmlspecialchars(safe($app['status'])); ?>
                                </div>
                                <?php if ($app['admin_response']): ?>
                                    <div class="admin-response"><strong>Ваш ответ:</strong> <?php echo nl2br(htmlspecialchars(safe($app['admin_response']))); ?></div>
                                <?php endif; ?>
                                <form method="POST" class="admin-form">
                                    <input type="hidden" name="app_id" value="<?php echo safe($app['id']); ?>">
                                    <select name="status" class="form-select">
                                        <option value="В рассмотрении" style="font-size: 14px;" <?php echo safe($app['status']) == 'В рассмотрении' ? 'selected' : ''; ?>>В рассмотрении</option>
                                        <option value="Одобрена" style="font-size: 14px;" <?php echo safe($app['status']) == 'Одобрена' ? 'selected' : ''; ?>>Одобрена</option>
                                        <option value="Отклонена" style="font-size: 14px;" <?php echo safe($app['status']) == 'Отклонена' ? 'selected' : ''; ?>>Отклонена</option>
                                    </select>
                                    <textarea name="admin_response" class="form-textarea" placeholder="Ответ администратора..."><?php echo htmlspecialchars(safe($app['admin_response']) ?? ''); ?></textarea>
                                    <button type="submit" name="update_application" class="form-btn-small">Сохранить</button>

                                    <a href="admin.php?delete_application=<?php echo safe($app['id']); ?>&section=applications" class="btn-delete" onclick="return confirm('Удалить заявку?')">Удалить заявку</a>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer class="NewFooter">
        <div class="FooterContainer">
            <div class="FooterCol FooterCol1">
                <div class="FooterLogoWrapper"><img src="assets/img/photo-logo.png" alt="" class="FooterImgLogo"><span class="FooterLogoName">BodyGym</span></div>
                <p class="FooterDesc">Пространство для тех, кто стремится к совершенству.</p>
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
                <div class="FooterSocials"><a href="#"><img src="assets/img/vk.png" alt="VK"></a><a href="#"><img src="assets/img/max-app-icon-on-a-transparent-background-free-png.webp" alt="Max"></a></div>
                <p class="FooterCopyright">© 2026 BodyGym. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        function switchSection(section) {
            // Обновляем URL без перезагрузки
            const url = new URL(window.location.href);
            url.searchParams.set('section', section);
            window.history.pushState({}, '', url);

            // Обновляем активные кнопки
            document.querySelectorAll('.admin-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.admin-tab').forEach(tab => {
                if (tab.textContent.includes(getSectionName(section))) {
                    tab.classList.add('active');
                }
            });

            // Показываем нужную секцию
            document.getElementById('orders-section').style.display = 'none';
            document.getElementById('callbacks-section').style.display = 'none';
            document.getElementById('reviews-section').style.display = 'none';
            document.getElementById('applications-section').style.display = 'none';
            document.getElementById(`${section}-section`).style.display = 'block';
        }

        function getSectionName(section) {
            const names = {
                'orders': 'Заказы абонементов',
                'callbacks': 'Заявки на обратный звонок',
                'reviews': 'Управление отзывами',
                'applications': 'Заявки на тест-драйв'
            };
            return names[section];
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

        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        });

        document.addEventListener('DOMContentLoaded', function() {
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