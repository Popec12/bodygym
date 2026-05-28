<?php

session_start();

// Включение отладки (отключить на продакшене)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========== БЕЗОПАСНОСТЬ: CSRF-защита ==========
// Создаём уникальный токен для защиты форм, если его ещё нет
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Функция для вставки скрытого поля CSRF в любую форму
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Функция для проверки CSRF токена (вызывать в начале обработки POST-запросов)
function verify_csrf()
{
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности: CSRF атака обнаружена');
    }
}

// ========== БЕЗОПАСНОСТЬ: защита от XSS ==========
// Функция для безопасного вывода данных
function safe($data)
{
    // Если передан null, возвращаем пустую строку
    if ($data === null) {
        return '';
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ========== БЕЗОПАСНОСТЬ: защита от ботов (honeypot) ==========
// Функция для вставки скрытого поля-ловушки для ботов
function honeypot_field()
{
    return '<div style="position: absolute; left: -9999px; top: -9999px;">
                <input type="text" name="honeypot" value="" tabindex="-1" autocomplete="off">
            </div>';
}

// Функция для проверки, не бот ли отправил форму
function verify_honeypot()
{
    if (!empty($_POST['honeypot'])) {
        die('Обнаружена автоматическая отправка формы');
    }
}

// ========== Подключение к БД ==========
$host = 'MySQL-8.0';
$dbname = 'bodygym';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// ========== Вспомогательные функции ==========
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function getCurrentUserId()
{
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function getUserInfo($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveMembershipOrder($pdo, $data)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO membership_orders 
            (user_id, name, surname, email, phone, birthdate, gender, experience, tariff_name, tariff_price, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'В обработке', NOW())");

        $result = $stmt->execute([
            $data['user_id'],
            $data['name'],
            $data['surname'],
            $data['email'],
            $data['phone'],
            $data['birthdate'],
            $data['gender'],
            $data['experience'],
            $data['tariff_name'],
            $data['tariff_price']
        ]);
        return $result;
    } catch (PDOException $e) {
        error_log("Ошибка saveMembershipOrder: " . $e->getMessage());
        return false;
    }
}

function saveCallbackRequest($pdo, $phone)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO callback_requests (phone, status, created_at) VALUES (?, 'В обработке', NOW())");
        return $stmt->execute([$phone]);
    } catch (PDOException $e) {
        error_log("Ошибка saveCallbackRequest: " . $e->getMessage());
        return false;
    }
}
