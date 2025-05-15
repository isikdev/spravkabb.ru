<?php
require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$district = isset($_GET['district']) ? (int)$_GET['district'] : 0;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM contacts WHERE status = 'active'";
$count_query = "SELECT COUNT(*) as total FROM contacts WHERE status = 'active'";
$params = [];
$where_added = false;

if (!empty($search)) {
    $search_clause = " (name LIKE '%".mysqli_real_escape_string($conn, $search)."%' OR 
                       description LIKE '%".mysqli_real_escape_string($conn, $search)."%' OR 
                       phone LIKE '%".mysqli_real_escape_string($conn, $search)."%' OR 
                       address LIKE '%".mysqli_real_escape_string($conn, $search)."%')";
    $query .= " AND ".$search_clause;
    $count_query .= " AND ".$search_clause;
}

if ($district > 0) {
    $query .= " AND district_id = ".(int)$district;
    $count_query .= " AND district_id = ".(int)$district;
}

if ($category > 0) {
    $query .= " AND category_id = ".(int)$category;
    $count_query .= " AND category_id = ".(int)$category;
}

// Получаем общее количество результатов
$count_result = mysqli_query($conn, $count_query);
$row = mysqli_fetch_assoc($count_result);
$total = $row['total'];
$pages = ceil($total / $limit);

// Добавляем ограничение по количеству
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// Получаем контакты
$result = mysqli_query($conn, $query);
$contacts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $contacts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Телефонный справочник</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .main-color {
            background-color: <?= $main_color ?>;
        }
        .main-color-text {
            color: <?= $main_color ?>;
        }
        .main-color-border {
            border-color: <?= $main_color ?>;
        }
        /* Улучшения для мобильных устройств */
        @media (max-width: 640px) {
            .contact-card {
                min-height: 100%;
            }
            .contact-image {
                height: 180px;
            }
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
    <header class="main-color text-white p-4 shadow-md sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl md:text-2xl font-bold">СПРАВОЧНИК</h1>
            <!-- Десктопное меню -->
            <nav class="desktop-menu">
                <ul class="flex space-x-2 md:space-x-4">
                    <li><a href="/" class="hover:text-gray-200 text-sm md:text-base">Главная</a></li>
                    <li><a href="add.php" class="hover:text-gray-200 text-sm md:text-base">Добавить контакт</a></li>
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
                        <?php if (is_admin()): ?>
                            <li><a href="admin.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Админ</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-8 px-4">
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6 md:mb-8">
            <form action="/" method="get" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="col-span-1 sm:col-span-2 md:col-span-3">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Поиск" value="<?= htmlspecialchars($search) ?>" 
                               class="w-full pl-4 pr-10 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <button type="submit" class="absolute right-3 top-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div>
                    <select name="district" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="0">Все районы</option>
                        <?php foreach ($districts as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $district == $id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <select name="category" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="0">Все разделы</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $category == $id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-primary text-white py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">
                        Найти
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <?php if (empty($contacts)): ?>
                <div class="col-span-full text-center py-10">
                    <h3 class="text-xl font-medium text-gray-600">Контакты не найдены</h3>
                    <p class="text-gray-500 mt-2">Попробуйте изменить параметры поиска или добавьте новый контакт</p>
                </div>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 contact-card flex flex-col">
                        <div class="relative contact-image h-40 md:h-48 bg-gray-200">
                            <?php if (!empty($contact['image']) && file_exists('uploads/' . $contact['image'])): ?>
                                <img src="uploads/<?= $contact['image'] ?>" alt="<?= htmlspecialchars($contact['name']) ?>" 
                                     class="w-full h-full object-cover" onclick="showImage('uploads/<?= $contact['image'] ?>')">
                            <?php else: ?>
                                <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 md:h-20 md:w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 flex-grow flex flex-col">
                            <h3 class="text-lg font-medium main-color-text truncate"><?= htmlspecialchars($contact['name']) ?></h3>
                            <p class="text-gray-600 text-sm mt-1 h-10 overflow-hidden"><?= htmlspecialchars($contact['description']) ?></p>
                            <p class="text-gray-800 font-medium mt-2"><?= htmlspecialchars($contact['phone']) ?></p>
                            <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($contact['address']) ?></p>
                            
                            <div class="mt-3 flex justify-between text-sm">
                                <span class="text-gray-500"><?= htmlspecialchars($districts[$contact['district_id']]) ?></span>
                                <span class="text-gray-500"><?= htmlspecialchars($categories[$contact['category_id']]) ?></span>
                            </div>
                            
                            <div class="mt-4 flex justify-end mt-auto">
                                <a href="delete.php?id=<?= $contact['id'] ?>" class="text-red-500 hover:text-red-700">
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
        <div class="max-w-4xl max-h-full p-4">
            <img id="modalImage" src="" alt="Увеличенное изображение" class="max-w-full max-h-[80vh]">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-white text-2xl">&times;</button>
        </div>
    </div>

    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
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
            
            if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target) && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
