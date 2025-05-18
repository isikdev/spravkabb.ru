<?php
require_once 'config.php';

$errors = [];
$success = false;
$contact = null;

$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($contact_id > 0) {
    $query = "SELECT * FROM contacts WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $contact_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $contact = mysqli_fetch_assoc($result);
}

if (!$contact) {
    $errors[] = 'Контакт не найден';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $contact) {
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    if (empty($reason)) {
        $errors[] = 'Пожалуйста, укажите причину удаления';
    } elseif (mb_strlen($reason) > 1000) {
        $errors[] = 'Причина удаления должна быть не более 1000 символов';
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO deletion_requests (contact_id, reason) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'is', $contact_id, $reason);
        
        if (mysqli_stmt_execute($stmt)) {
            // Явно фиксируем транзакцию, так как автокоммит отключен
            mysqli_commit($conn);
            $success = true;
        } else {
            $errors[] = 'Ошибка при отправке запроса на удаление: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запрос на удаление контакта - Телефонный справочник</title>
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
        /* Улучшения для мобильных устройств */
        @media (max-width: 640px) {
            input, select, button, textarea {
                font-size: 16px !important; /* Предотвращает масштабирование на iOS */
            }
            .container {
                width: 100% !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            h1 {
                font-size: 1.25rem !important;
            }
            .mobile-menu {
                display: block;
            }
            .desktop-menu {
                display: none;
            }
        }
        @media (min-width: 641px) {
            .mobile-menu {
                display: none;
            }
            .desktop-menu {
                display: block;
            }
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
                <h1 class="text-lg md:text-xl font-medium">БукиҺи</h1>
            </a>
            <!-- Десктопное меню -->
            <nav class="desktop-menu">
                <ul class="flex space-x-2 md:space-x-4">
                    <li><a href="/" class="hover:text-gray-200 text-sm md:text-base">Главная</a></li>
                    <li><a href="add.php" class="hover:text-gray-200 text-sm md:text-base">Добавить контакт</a></li>
                    <li><a href="support.php" class="hover:text-gray-200 text-sm md:text-base">Поддержка</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="admin.php" class="hover:text-gray-200 text-sm md:text-base">Админ</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- Мобильное меню (гамбургер) -->
            <div class="mobile-menu">
                <button id="menuButton" class="text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div id="mobileMenu" class="hidden fixed top-14 right-0 bg-white shadow-lg rounded-bl-lg w-2/3 z-30">
                    <div class="py-2 px-4 border-b main-color">
                        <span class="font-medium text-white">Меню</span>
                    </div>
                    <ul class="py-2">
                        <li><a href="/" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Главная</a></li>
                        <li><a href="add.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Добавить контакт</a></li>
                        <li><a href="support.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Поддержка</a></li>
                        <?php if (is_admin()): ?>
                            <li><a href="admin.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Админ</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
            <h2 class="text-xl md:text-2xl font-medium main-color-text mb-6 text-center">Запрос на удаление контакта</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc pl-4">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="text-center mt-6">
                    <a href="/" class="inline-block py-3 px-6 main-color text-white rounded-lg hover:bg-opacity-90 transition-all">
                        Вернуться на главную страницу
                    </a>
                </div>
            <?php elseif ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <p>Запрос на удаление контакта успешно отправлен! После рассмотрения модератором контакт будет удален.</p>
                    <p class="mt-2"><a href="/" class="underline main-color-text">Вернуться на главную страницу</a></p>
                </div>
            <?php else: ?>
                <div class="bg-gray-100 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium main-color-text"><?= htmlspecialchars($contact['name']) ?></h3>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($contact['description']) ?></p>
                    <p class="text-gray-800 font-medium mt-2"><?= htmlspecialchars($contact['phone']) ?></p>
                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($contact['address']) ?></p>
                </div>
                
                <form action="delete.php?id=<?= $contact_id ?>" method="post" class="space-y-4">
                    <div>
                        <label for="reason" class="block text-gray-700 mb-1">Причина удаления <span class="text-red-500">*</span></label>
                        <textarea id="reason" name="reason" rows="5" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required><?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
                        <p class="text-gray-500 text-sm mt-1">Пожалуйста, укажите причину, по которой вы считаете, что данный контакт должен быть удален</p>
                    </div>
                    
                    <div class="flex justify-between pt-4">
                        <a href="/" class="inline-block py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">
                            Отмена
                        </a>
                        <button type="submit" class="py-3 px-6 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all">
                            Отправить запрос на удаление
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Мобильное меню
        document.getElementById('menuButton').addEventListener('click', function() {
            var menu = document.getElementById('mobileMenu');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        });

        // Закрывать меню при клике вне его
        document.addEventListener('click', function(e) {
            var menuButton = document.getElementById('menuButton');
            var mobileMenu = document.getElementById('mobileMenu');
            
            if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target) && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
