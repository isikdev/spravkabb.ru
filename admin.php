<?php
require_once 'config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'approve_contact' && $id > 0) {
        $query = "UPDATE contacts SET status = 'active' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Контакт успешно одобрен и опубликован';
        } else {
            $error_message = 'Ошибка при одобрении контакта: ' . mysqli_error($conn);
        }
    } else if ($action === 'reject_contact' && $id > 0) {
        $query = "DELETE FROM contacts WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Контакт успешно отклонен и удален';
        } else {
            $error_message = 'Ошибка при отклонении контакта: ' . mysqli_error($conn);
        }
    } else if ($action === 'approve_deletion' && $id > 0) {
        $query = "SELECT contact_id FROM deletion_requests WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $contact_id = $row['contact_id'];
            
            $query = "UPDATE contacts SET status = 'deleted' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $contact_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $query = "UPDATE deletion_requests SET status = 'approved' WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                
                $success_message = 'Запрос на удаление одобрен, контакт помечен как удаленный';
            } else {
                $error_message = 'Ошибка при удалении контакта: ' . mysqli_error($conn);
            }
        } else {
            $error_message = 'Запрос на удаление не найден';
        }
    } else if ($action === 'reject_deletion' && $id > 0) {
        $query = "UPDATE deletion_requests SET status = 'rejected' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Запрос на удаление отклонен';
        } else {
            $error_message = 'Ошибка при отклонении запроса на удаление: ' . mysqli_error($conn);
        }
    }
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending_contacts';

$pending_contacts = [];
$query = "SELECT * FROM contacts WHERE status = 'pending' ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $pending_contacts[] = $row;
}

$deletion_requests = [];
$query = "SELECT dr.*, c.name as contact_name, c.phone as contact_phone 
          FROM deletion_requests dr 
          JOIN contacts c ON dr.contact_id = c.id 
          WHERE dr.status = 'pending' 
          ORDER BY dr.created_at DESC";

$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $deletion_requests[] = $row;
}

$recent_activities = [];
$query = "SELECT c.id, c.name, c.status, c.created_at, 'contact' as type,
          CASE 
            WHEN c.status = 'active' THEN 'Добавлен контакт'
            WHEN c.status = 'deleted' THEN 'Удален контакт'
            ELSE 'Ожидает проверки'
          END as action_text
          FROM contacts c
          WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          
          UNION
          
          SELECT dr.id, c.name, dr.status, dr.created_at, 'deletion_request' as type,
          CASE 
            WHEN dr.status = 'approved' THEN 'Одобрено удаление'
            WHEN dr.status = 'rejected' THEN 'Отклонено удаление'
            ELSE 'Запрос на удаление'
          END as action_text
          FROM deletion_requests dr
          JOIN contacts c ON dr.contact_id = c.id
          WHERE dr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          
          ORDER BY created_at DESC
          LIMIT 10";

$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_activities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Телефонный справочник</title>
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
            th, td {
                padding: 6px 4px !important;
                font-size: 0.75rem !important;
            }
            .overflow-x-auto {
                max-width: 100vw;
                margin-left: -10px;
                margin-right: -10px;
                padding-left: 10px;
                padding-right: 10px;
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
            <h1 class="text-xl md:text-2xl font-bold">ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
            <!-- Десктопное меню -->
            <nav class="desktop-menu">
                <ul class="flex space-x-2 md:space-x-4">
                    <li><a href="/" class="hover:text-gray-200 text-sm md:text-base">Главная</a></li>
                    <li><a href="admin.php" class="hover:text-gray-200 text-sm md:text-base">Админ</a></li>
                    <li><a href="login.php?logout=1" class="hover:text-gray-200 text-sm md:text-base">Выйти</a></li>
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
                        <li><a href="admin.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Админ</a></li>
                        <li><a href="login.php?logout=1" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Выйти</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-4 md:py-8 px-4">
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="?tab=pending_contacts" class="<?= $tab === 'pending_contacts' ? 'border-b-2 main-color-border main-color-text' : 'text-gray-500 hover:text-gray-700' ?> py-4 px-6 font-medium text-center flex-1">
                        Новые контакты
                        <?php if (count($pending_contacts) > 0): ?>
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($pending_contacts) ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?tab=deletion_requests" class="<?= $tab === 'deletion_requests' ? 'border-b-2 main-color-border main-color-text' : 'text-gray-500 hover:text-gray-700' ?> py-4 px-6 font-medium text-center flex-1">
                        Запросы на удаление
                        <?php if (count($deletion_requests) > 0): ?>
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($deletion_requests) ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?tab=recent_activity" class="<?= $tab === 'recent_activity' ? 'border-b-2 main-color-border main-color-text' : 'text-gray-500 hover:text-gray-700' ?> py-4 px-6 font-medium text-center flex-1">
                        Недавние действия
                    </a>
                </nav>
            </div>
            
            <div class="p-6">
                <?php if ($tab === 'pending_contacts'): ?>
                    <h2 class="text-xl font-medium mb-4">Новые контакты (ожидают проверки)</h2>
                    
                    <?php if (empty($pending_contacts)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <p>Нет новых контактов для проверки</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Описание</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Район</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Раздел</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($pending_contacts as $contact): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($contact['name']) ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500 truncate max-w-xs"><?= htmlspecialchars($contact['description']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($contact['phone']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($districts[$contact['district_id']]) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($categories[$contact['category_id']]) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500"><?= date('d.m.Y H:i', strtotime($contact['created_at'])) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <form action="admin.php?action=approve_contact&id=<?= $contact['id'] ?>" method="post" class="inline">
                                                        <button type="submit" class="text-green-600 hover:text-green-900">Одобрить</button>
                                                    </form>
                                                    <form action="admin.php?action=reject_contact&id=<?= $contact['id'] ?>" method="post" class="inline" 
                                                          onsubmit="return confirm('Вы уверены, что хотите отклонить этот контакт?');">
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Отклонить</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($tab === 'deletion_requests'): ?>
                    <h2 class="text-xl font-medium mb-4">Запросы на удаление контактов</h2>
                    
                    <?php if (empty($deletion_requests)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <p>Нет запросов на удаление контактов</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($deletion_requests as $request): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between mb-3">
                                        <h3 class="text-lg font-medium main-color-text"><?= htmlspecialchars($request['contact_name']) ?></h3>
                                        <div class="text-gray-500 text-sm"><?= date('d.m.Y H:i', strtotime($request['created_at'])) ?></div>
                                    </div>
                                    <p class="text-gray-700 font-medium"><?= htmlspecialchars($request['contact_phone']) ?></p>
                                    <div class="mt-2 bg-white p-3 rounded border border-gray-200">
                                        <h4 class="text-sm font-medium text-gray-700 mb-1">Причина удаления:</h4>
                                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($request['reason'])) ?></p>
                                    </div>
                                    <div class="mt-4 flex justify-end space-x-3">
                                        <form action="admin.php?action=approve_deletion&id=<?= $request['id'] ?>" method="post" class="inline">
                                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all">
                                                Подтвердить удаление
                                            </button>
                                        </form>
                                        <form action="admin.php?action=reject_deletion&id=<?= $request['id'] ?>" method="post" class="inline">
                                            <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">
                                                Отклонить запрос
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($tab === 'recent_activity'): ?>
                    <h2 class="text-xl font-medium mb-4">Недавние действия</h2>
                    
                    <?php if (empty($recent_activities)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <p>Нет недавних действий</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="flex items-center p-3 border-b border-gray-100">
                                    <div class="w-12 h-12 flex-shrink-0 mr-4 rounded-full flex items-center justify-center <?= $activity['status'] === 'active' ? 'bg-green-100 text-green-600' : ($activity['status'] === 'deleted' || $activity['status'] === 'approved' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600') ?>">
                                        <?php if ($activity['status'] === 'active'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        <?php elseif ($activity['status'] === 'deleted' || $activity['status'] === 'approved'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($activity['action_text']) ?>: <?= htmlspecialchars($activity['name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('d.m.Y H:i', strtotime($activity['created_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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
            
            if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target) && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
