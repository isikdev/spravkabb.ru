<?php
require_once 'config.php';

$sql_commands = [
    "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        district_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address VARCHAR(255) NOT NULL,
        image VARCHAR(255),
        status ENUM('active', 'pending', 'deleted') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS deletion_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contact_id INT NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

$success = true;
$errors = [];

foreach ($sql_commands as $sql) {
    if (!mysqli_query($conn, $sql)) {
        $success = false;
        $errors[] = mysqli_error($conn);
    }
}

// Проверяем, существует ли пользователь admin
$check_admin = mysqli_query($conn, "SELECT id FROM admin_users WHERE username = 'admin'");
if (mysqli_num_rows($check_admin) > 0) {
    // Пользователь существует, обновляем пароль
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $update_sql = "UPDATE admin_users SET password = '$password_hash' WHERE username = 'admin'";
    if (!mysqli_query($conn, $update_sql)) {
        $success = false;
        $errors[] = "Не удалось обновить пароль администратора: " . mysqli_error($conn);
    }
} else {
    // Пользователь не существует, создаем его
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO admin_users (username, password) VALUES ('admin', '$password_hash')";
    if (!mysqli_query($conn, $insert_sql)) {
        $success = false;
        $errors[] = "Не удалось создать пользователя администратора: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройка базы данных - Телефонный справочник</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            -webkit-tap-highlight-color: transparent;
            background-color: #f5f7fa;
        }
        .main-color {
            background-color: #002060;
        }
        .main-color-text {
            color: <?= $main_color ?>;
        }
        .main-color-border {
            border-color: <?= $main_color ?>;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?= $main_color ?>',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <header class="main-color text-white p-3 shadow-md sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/" class="flex items-center">
                <img src="img/logo.jpg" alt="БукиҺи" class="h-6 w-6 mr-2 rounded-full">
                <h1 class="text-lg md:text-xl font-medium">БукиҺи - НАСТРОЙКА</h1>
            </a>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-medium main-color-text mb-6 text-center">Настройка базы данных</h2>
            
            <?php if ($success && empty($errors)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <p>База данных успешно настроена! Теперь вы можете использовать телефонный справочник.</p>
                    <div class="flex flex-col md:flex-row justify-center mt-4 gap-4">
                        <a href="/" class="inline-block py-3 px-6 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-all text-center">
                            Перейти на главную страницу
                        </a>
                        <a href="login.php" class="inline-block py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all text-center">
                            Войти в админ-панель
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <p>Произошли ошибки при настройке базы данных:</p>
                    <ul class="list-disc pl-4 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4">
                        <a href="/" class="inline-block py-3 px-6 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-all">
                            Вернуться на главную страницу
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 