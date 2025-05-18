<?php
require_once 'config.php';

$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    // Clear admin session
    unset($_SESSION['admin']);
    header('Location: index.php');
    exit;
}

// Check if already logged in
if (is_admin()) {
    header('Location: admin.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Simple authentication - in a real app you'd use password_hash() and password_verify()
    if ($username === $admin_username && $password === $admin_password) {
        // Set admin session
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Неверное имя пользователя или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему - Телефонный справочник</title>
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
            input, select, button {
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
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-8 px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl md:text-2xl font-medium main-color-text mb-6 text-center">Вход в админ-панель</h2>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-700 mb-2">Имя пользователя</label>
                    <input type="text" id="username" name="username" 
                           class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="password" class="block text-gray-700 mb-2">Пароль</label>
                    <input type="password" id="password" name="password" 
                           class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                
                <div>
                    <button type="submit" class="w-full py-3 main-color text-white rounded-lg hover:bg-opacity-90 transition-all">
                        Войти
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="/" class="text-gray-600 hover:text-gray-800">Вернуться на главную</a>
                </div>
            </form>
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
