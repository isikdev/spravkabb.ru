<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поддержка - Телефонный справочник</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="theme-color" content="#002060">
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
        color: <?=$main_color ?>;
    }

    .main-color-border {
        border-color: <?=$main_color ?>;
    }

    /* Улучшения для мобильных устройств */
    @media (max-width: 640px) {

        input,
        select,
        button {
            font-size: 16px !important;
            /* Предотвращает масштабирование на iOS */
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

    .contact-link {
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        transform: translateY(-2px);
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
    <!-- PWA скрипты - сначала install.js, затем pwa.js -->
    <script src="/pwa/install.js" defer></script>
    <script src="/pwa/pwa.js" defer></script>
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div id="mobileMenu" class="hidden fixed top-14 right-0 bg-white shadow-lg rounded-bl-lg w-2/3 z-30">
                    <div class="py-2 px-4 border-b main-color">
                        <span class="font-medium text-white">Меню</span>
                    </div>
                    <ul class="py-2">
                        <li><a href="/" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Главная</a></li>
                        <li><a href="add.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Добавить
                                контакт</a></li>
                        <li><a href="support.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Поддержка</a>
                        </li>
                        <?php if (is_admin()): ?>
                        <li><a href="admin.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Админ</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-6 md:py-8 px-4">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-medium main-color-text mb-6 text-center">Служба поддержки</h2>

            <div class="space-y-6">
                <!-- Телефон -->
                <div class="bg-gray-50 p-4 rounded-lg flex items-center contact-link">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium main-color-text">Телефон поддержки</h3>
                        <a href="tel:+79224567097" class="text-lg font-medium text-blue-700 hover:underline">8 (922)
                            456-70-97</a>
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="bg-gray-50 p-4 rounded-lg flex items-center contact-link">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.677.116-.173.231-.145.39-.087s1.011.477 1.184.564c.173.087.289.131.332.202.043.72.043.419-.101.824z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium main-color-text">WhatsApp</h3>
                        <a href="https://wa.me/79224567097" target="_blank"
                            class="text-lg font-medium text-green-600 hover:underline">Написать в WhatsApp</a>
                    </div>
                </div>

                <!-- Telegram -->
                <div class="bg-gray-50 p-4 rounded-lg flex items-center contact-link">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.05.01-.22-.08-.32-.09-.09-.23-.06-.34-.03-.14.03-2.38 1.52-3.58 2.51-.34.23-.65.34-.93.34-.3 0-.59-.13-.87-.26-.6-.26-1.14-.38-1.11-.81.02-.2.22-.39.61-.59.75-.4 1.54-.74 2.08-1 1.87-.85 3.8-1.77 4.76-2.37.82-.52 1.61-1.02 2.25-1.03.29 0 .67.18.71.53.04.31-.11.61-.16.77z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium main-color-text">Telegram</h3>
                        <a href="https://t.me/+79224567097" target="_blank"
                            class="text-lg font-medium text-blue-500 hover:underline">Написать в Telegram</a>
                    </div>
                </div>

                <!-- Email -->
                <div class="bg-gray-50 p-4 rounded-lg flex items-center contact-link">
                    <div class="bg-red-100 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium main-color-text">Email</h3>
                        <a href="mailto:julus5092@gmail.com"
                            class="text-lg font-medium text-red-500 hover:underline">julus5092@gmail.com</a>
                    </div>
                </div>
            </div>
        </div>
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

        if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target) && !mobileMenu.classList.contains(
                'hidden')) {
            mobileMenu.classList.add('hidden');
        }
    });
    </script>
</body>

</html>