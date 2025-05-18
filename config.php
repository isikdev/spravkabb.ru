<?php
session_start();

// Настройки базы данных
$db_host = 'localhost';
$db_user = 'u3133900_default';
$db_pass = 'lcf7OUD2K0hGB1Ri';
$db_name = 'u3133900_default';

// Создаем подключение к базе данных с расширенными настройками для улучшения производительности
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

// Устанавливаем кодировку соединения
mysqli_set_charset($conn, 'utf8mb4');

// Увеличиваем время ожидания запроса, но ограничиваем его разумным значением
mysqli_query($conn, "SET SESSION wait_timeout=300");

// Включаем автокоммит (делаем его явным для уверенности)
mysqli_autocommit($conn, true);

// Строка с query_cache_type удалена, так как эта функция устарела в MySQL 5.7
// и полностью удалена в MySQL 8.0

// Настройки сайта
$admin_username = 'admin';
$admin_password = 'admin123';

$districts = [
    '1' => 'Сунтар',
    '2' => 'Нюрба',
    '3' => 'Верхневилюйск',
    '4' => 'Вилюйск',
    '5' => 'Якутск'
];

$categories = [
    '1' => 'Авто',
    '2' => 'Общество',
    '3' => 'Отдых и туризм',
    '4' => 'Связь',
    '5' => 'Культура',
    '6' => 'Строительство',
    '7' => 'Медицина',
    '8' => 'Магазины',
    '9' => 'Образование',
    '10' => 'Сельское хозяйство',
    '11' => 'Услуги',
    '12' => 'Разное'
];

$main_color = '#032263';

function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Оптимизированная функция для выполнения запросов
function db_query($query, $params = [], $is_select = false) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        return false;
    }
    
    // Если есть параметры, привязываем их
    if (!empty($params)) {
        $types = '';
        $bind_params = [];
        
        // Определяем типы параметров
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bind_params[] = $param;
        }
        
        // Подготавливаем массив для bind_param
        $bind_names = [];
        for ($i = 0; $i < count($bind_params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $bind_params[$i];
            $bind_names[] = &$$bind_name;
        }
        
        // Добавляем тип в начало массива
        array_unshift($bind_names, $types);
        
        // Привязываем параметры
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    
    // Выполняем запрос
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }
    
    // Если это SELECT запрос, получаем результат
    if ($is_select) {
        $result = mysqli_stmt_get_result($stmt);
        $data = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $data;
    }
    
    // Для INSERT, UPDATE, DELETE возвращаем количество затронутых строк
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $affected_rows;
}

// Завершаем соединение при завершении скрипта
register_shutdown_function(function() {
    global $conn;
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
});