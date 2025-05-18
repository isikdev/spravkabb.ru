<?php
require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$district = isset($_GET['district']) ? (int)$_GET['district'] : 0;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Базовый запрос
$query = "SELECT * FROM contacts WHERE status = 'active'";
$count_query = "SELECT COUNT(*) as total FROM contacts WHERE status = 'active'";
$params = [];
$where_added = false;

// Добавляем параметры поиска с использованием параметризованных запросов
if (!empty($search)) {
    $search_param = "%$search%";
    $query .= " AND (name LIKE ? OR description LIKE ? OR phone LIKE ? OR address LIKE ?)";
    $count_query .= " AND (name LIKE ? OR description LIKE ? OR phone LIKE ? OR address LIKE ?)";
    // Добавляем параметр поиска 4 раза для каждого поля
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($district > 0) {
    $query .= " AND district_id = ?";
    $count_query .= " AND district_id = ?";
    $params[] = $district;
}

if ($category > 0) {
    $query .= " AND category_id = ?";
    $count_query .= " AND category_id = ?";
    $params[] = $category;
}

// Получаем общее количество результатов
$count_params = $params; // Копируем параметры для запроса количества
$total_results = db_query($count_query, $count_params, true);
$total = isset($total_results[0]['total']) ? (int)$total_results[0]['total'] : 0;
$pages = ceil($total / $limit);

// Добавляем ограничение по количеству
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Получаем контакты с использованием оптимизированной функции
$contacts = db_query($query, $params, true);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Телефонный справочник</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="theme-color" content="#002060">
    <meta name="description" content="Телефонный справочник с контактами и информацией">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="БукиҺи">
    <link rel="manifest" href="/pwa/manifest.json">
    <link rel="apple-touch-icon" href="/pwa/icons/icon-192x192.png">
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
        .contact-card {
            min-height: 100%;
        }

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

        .contact-photo {
            min-width: 80px !important;
        }

        .contact-info-section {
            padding: 10px !important;
        }
    }

    /* Стили для ПК версии */
    @media (min-width: 641px) {
        .mobile-menu {
            display: none;
        }

        .desktop-menu {
            display: block;
        }

        .contact-card {
            min-height: auto;
            height: auto;
        }

        .contact-info-section {
            padding: 0.75rem !important;
        }

        .contact-info-section h3 {
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }

        .contact-info-section p,
        .contact-info-section .text-sm {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.2;
        }

        .contact-info-section a {
            margin-top: 0.25rem;
        }

        .contact-photo {
            min-height: 80px;
        }

        .mt-3 {
            margin-top: 0.5rem;
        }

        .contact-grid {
            max-width: 1000px;
            margin: 0 auto;
        }
    }

    /* Мобильные стили */
    .contact-photo {
        min-height: 120px;
        min-width: 120px;
    }

    .contact-card {
        min-height: 120px;
    }
    </style>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '<?= $main_color ?>',
                },
                width: {
                    '3/10': '30%',
                    '7/10': '70%'
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
                        <li><a href="support.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Поддержка</a></li>
                        <?php if (is_admin()): ?>
                        <li><a href="admin.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Админ</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-6 px-4">
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-6">
            <form action="/" method="get" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="col-span-1 sm:col-span-2 md:col-span-3">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Поиск" value="<?= htmlspecialchars($search) ?>"
                            class="w-full pl-4 pr-10 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <button type="submit" class="absolute right-3 top-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <select name="district"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="0">Все районы</option>
                        <?php foreach ($districts as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $district == $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <select name="category"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="0">Все разделы</option>
                        <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $category == $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <button type="submit"
                        class="w-full bg-primary text-white py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">
                        Найти
                    </button>
                    <a href="add.php"
                        class="w-full block text-center bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all mt-2">
                        Добавить
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-3 md:gap-4 contact-grid">
            <?php if (empty($contacts)): ?>
            <div class="col-span-full text-center py-10">
                <h3 class="text-xl font-medium text-gray-600">Контакты не найдены</h3>
                <p class="text-gray-500 mt-2">Попробуйте изменить параметры поиска или добавьте новый контакт</p>
            </div>
            <?php else: ?>
            <?php foreach ($contacts as $contact): ?>
            <div
                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 contact-card flex my-2">
                <!-- Фото контакта (30% ширины) -->
                <div class="relative w-3/10 bg-gray-200 flex-shrink-0 contact-photo">
                    <?php if (!empty($contact['image']) && file_exists('uploads/' . $contact['image'])): ?>
                    <img src="uploads/<?= $contact['image'] ?>" alt="<?= htmlspecialchars($contact['name']) ?>"
                        class="w-full h-full object-cover lazy-load" 
                        loading="lazy"
                        data-src="uploads/<?= $contact['image'] ?>"
                        onclick="showImage('uploads/<?= $contact['image'] ?>')">
                    <?php else: ?>
                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Информация о контакте (70% ширины) -->
                <div class="p-4 w-7/10 flex flex-col contact-info-section">
                    <h3 class="text-lg font-medium main-color-text">
                        <?= htmlspecialchars(mb_substr($contact['name'], 0, 20)) ?></h3>
                    <p class="text-gray-600 text-sm mt-1">
                        <?= htmlspecialchars(mb_substr($contact['description'], 0, 40)) ?></p>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact['phone']) ?>"
                        class="text-blue-800 font-medium mt-2 hover:text-blue-900">
                        <?= htmlspecialchars($contact['phone']) ?>
                    </a>
                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($contact['address']) ?></p>

                    <div class="mt-3 flex justify-between text-sm">
                        <div>
                            <span
                                class="text-gray-500"><?= htmlspecialchars($districts[$contact['district_id']]) ?></span>
                            <span class="text-gray-400 mx-1">•</span>
                            <span
                                class="text-gray-500"><?= htmlspecialchars($categories[$contact['category_id']]) ?></span>
                        </div>
                        <a href="delete.php?id=<?= $contact['id'] ?>" class="text-red-500 hover:text-red-700 text-sm">
                            Запросить удаление
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($pages > 1): ?>
        <div class="flex justify-center mt-8">
            <div class="flex flex-wrap space-x-1">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&district=<?= $district ?>&category=<?= $category ?>"
                    class="px-3 md:px-4 py-2 bg-white border rounded-md hover:bg-gray-50">
                    &laquo;
                </a>
                <?php endif; ?>

                <?php
                    $start_page = max(1, $page - 1);
                    $end_page = min($pages, $page + 1);
                    
                    if ($start_page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search) ?>&district=<?= $district ?>&category=<?= $category ?>"
                    class="px-3 md:px-4 py-2 bg-white border rounded-md hover:bg-gray-50">1</a>
                <?php if ($start_page > 2): ?>
                <span class="px-2 md:px-4 py-2">...</span>
                <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&district=<?= $district ?>&category=<?= $category ?>"
                    class="px-3 md:px-4 py-2 <?= $i == $page ? 'main-color text-white' : 'bg-white border hover:bg-gray-50' ?> rounded-md">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($end_page < $pages): ?>
                <?php if ($end_page < $pages - 1): ?>
                <span class="px-2 md:px-4 py-2">...</span>
                <?php endif; ?>
                <a href="?page=<?= $pages ?>&search=<?= urlencode($search) ?>&district=<?= $district ?>&category=<?= $category ?>"
                    class="px-3 md:px-4 py-2 bg-white border rounded-md hover:bg-gray-50"><?= $pages ?></a>
                <?php endif; ?>

                <?php if ($page < $pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&district=<?= $district ?>&category=<?= $category ?>"
                    class="px-3 md:px-4 py-2 bg-white border rounded-md hover:bg-gray-50">
                    &raquo;
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex items-center justify-center z-50">
        <div class="max-w-4xl max-h-full p-4 relative">
            <div class="absolute inset-0 flex items-center justify-center" id="imageLoader">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
            </div>
            <img id="modalImage" src="" alt="Увеличенное изображение" class="max-w-full max-h-[80vh] hidden">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-white text-2xl">&times;</button>
        </div>
    </div>

    <script>
    function showImage(src) {
        // Показываем модальное окно с загрузчиком
        document.getElementById('imageModal').classList.remove('hidden');
        document.getElementById('imageLoader').classList.remove('hidden');
        document.getElementById('modalImage').classList.add('hidden');
        document.body.style.overflow = 'hidden';
        
        // Предварительно загружаем изображение
        const img = new Image();
        img.onload = function() {
            // Когда изображение загружено, скрываем загрузчик и показываем изображение
            document.getElementById('imageLoader').classList.add('hidden');
            document.getElementById('modalImage').classList.remove('hidden');
            document.getElementById('modalImage').src = src;
        };
        
        img.onerror = function() {
            // В случае ошибки загрузки
            closeModal();
            alert('Ошибка загрузки изображения');
        };
        
        // Начинаем загрузку изображения
        img.src = src;
    }

    function closeModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Установка таймаута перед очисткой src для корректного закрытия
        setTimeout(() => {
            document.getElementById('modalImage').src = '';
            document.getElementById('modalImage').classList.add('hidden');
        }, 100);
    }

    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Ленивая загрузка изображений
    document.addEventListener('DOMContentLoaded', function() {
        const lazyImages = document.querySelectorAll('.lazy-load');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-load');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        } else {
            // Для браузеров без поддержки IntersectionObserver
            let lazyLoadThrottleTimeout;
            
            function lazyLoad() {
                if (lazyLoadThrottleTimeout) {
                    clearTimeout(lazyLoadThrottleTimeout);
                }
                
                lazyLoadThrottleTimeout = setTimeout(function() {
                    const scrollTop = window.pageYOffset;
                    lazyImages.forEach(function(img) {
                        if (img.offsetTop < (window.innerHeight + scrollTop)) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy-load');
                        }
                    });
                    
                    if (lazyImages.length === 0) {
                        document.removeEventListener('scroll', lazyLoad);
                        window.removeEventListener('resize', lazyLoad);
                        window.removeEventListener('orientationChange', lazyLoad);
                    }
                }, 20);
            }
            
            document.addEventListener('scroll', lazyLoad);
            window.addEventListener('resize', lazyLoad);
            window.addEventListener('orientationChange', lazyLoad);
            
            // Запускаем сразу для видимых изображений при загрузке
            setTimeout(lazyLoad, 100);
        }
    });

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