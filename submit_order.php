<?php
session_start();
require_once 'config_bodygym.php';


header('Content-Type: application/json');

// Функция для проверки существует ли таблица, и создания если нет
function ensureTablesExist($pdo)
{
    try {
        // Проверяем таблицу membership_orders
        $stmt = $pdo->query("SHOW TABLES LIKE 'membership_orders'");
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE membership_orders (
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
                status VARCHAR(50) DEFAULT 'В обработке',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            error_log("Таблица membership_orders создана");
        }

        // Проверяем таблицу callback_requests
        $stmt = $pdo->query("SHOW TABLES LIKE 'callback_requests'");
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE callback_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                phone VARCHAR(20) NOT NULL,
                status VARCHAR(50) DEFAULT 'В обработке',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);
            error_log("Таблица callback_requests создана");
        }

        // Проверяем таблицу applications на наличие новых полей
        $stmt = $pdo->query("SHOW TABLES LIKE 'applications'");
        if ($stmt->rowCount() > 0) {
            $newColumns = ['birthdate', 'gender', 'experience', 'tariff_name', 'tariff_price'];
            foreach ($newColumns as $col) {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM applications WHERE Field = '$col'");
                if ($checkStmt->rowCount() == 0) {
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
                    error_log("Добавлено поле $col в таблицу applications");
                }
            }
        }

        return true;
    } catch (PDOException $e) {
        error_log("Ошибка создания таблиц: " . $e->getMessage());
        return false;
    }
}

// Создаём таблицы если их нет
ensureTablesExist($pdo);

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();  // <--- ДОБАВИТЬ
    verify_honeypot();  // <--- ДОБАВИТЬ

    $action = $_POST['action'] ?? '';

    if ($action === 'submit_order') {
        // Сохранение заказа абонемента
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $experience = $_POST['experience'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $tariff_name = $_POST['tariff_name'] ?? '';
        $tariff_price = (int)($_POST['tariff_price'] ?? 0);
        $user_id = isLoggedIn() ? getCurrentUserId() : null;

        // Валидация
        $errors = [];
        if (strlen($name) < 2) $errors[] = 'Имя слишком короткое';
        if (strlen($surname) < 2) $errors[] = 'Фамилия слишком короткая';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Неверный email';
        if (strlen($phone) < 11) $errors[] = 'Неверный телефон';
        if (empty($birthdate)) $errors[] = 'Не указана дата рождения';
        if (empty($gender)) $errors[] = 'Не указан пол';
        if (empty($experience)) $errors[] = 'Не указан опыт';

        // Проверка возраста
        if (!empty($birthdate)) {
            $birth = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($birth)->y;
            if ($age < 16) $errors[] = 'Возраст должен быть не менее 16 лет';
            if ($birth > $today) $errors[] = 'Дата рождения не может быть в будущем';
            $minDate = new DateTime('1900-01-01');
            if ($birth < $minDate) $errors[] = 'Дата рождения не может быть раньше 1900 года';
        }

        if (empty($errors)) {
            try {
                $data = [
                    'user_id' => $user_id,
                    'name' => $name,
                    'surname' => $surname,
                    'email' => $email,
                    'phone' => $phone,
                    'birthdate' => $birthdate,
                    'gender' => $gender,
                    'experience' => $experience,
                    'tariff_name' => $tariff_name,
                    'tariff_price' => $tariff_price
                ];

                if (saveMembershipOrder($pdo, $data)) {
                    $response = ['success' => true, 'message' => 'Заявка успешно отправлена! Менеджер свяжется с вами.'];
                } else {
                    $response = ['success' => false, 'message' => 'Ошибка при сохранении заявки'];
                }
            } catch (PDOException $e) {
                error_log('Ошибка сохранения заказа: ' . $e->getMessage());
                $response = ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
            }
        } else {
            $response = ['success' => false, 'message' => implode(', ', $errors)];
        }
    } elseif ($action === 'callback') {
        // Сохранение заявки на обратный звонок
        $phone = trim($_POST['phone'] ?? '');

        if (strlen($phone) >= 11) {
            try {
                if (saveCallbackRequest($pdo, $phone)) {
                    $response = ['success' => true, 'message' => 'Спасибо! Скоро мы вам перезвоним.'];
                } else {
                    $response = ['success' => false, 'message' => 'Ошибка при сохранении заявки'];
                }
            } catch (PDOException $e) {
                error_log('Ошибка сохранения callback: ' . $e->getMessage());
                $response = ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
            }
        } else {
            $response = ['success' => false, 'message' => 'Введите корректный номер телефона'];
        }
    }
}

echo json_encode($response);
