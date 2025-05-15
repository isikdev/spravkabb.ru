<?php
session_start();

$db_host = 'localhost';
$db_user = 'u3133900_default';
$db_pass = 'lcf7OUD2K0hGB1Ri';
$db_name = 'u3133900_default';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$districts = [
    '1' => 'Сунтар',
    '2' => 'Нюрба',
    '3' => 'Верхневилюйск',
    '4' => 'Вилюйск'
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
