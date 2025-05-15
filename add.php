<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    
    if (empty($district_id) || !isset($districts[$district_id])) {
        $errors[] = 'Выберите район';
    }
    
    if (empty($category_id) || !isset($categories[$category_id])) {
        $errors[] = 'Выберите раздел';
    }
    
    if (empty($name)) {
        $errors[] = 'Введите имя контакта';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Имя контакта должно быть не более 100 символов';
    }
    
    if (empty($description)) {
        $errors[] = 'Введите краткое описание';
    } elseif (mb_strlen($description) > 255) {
        $errors[] = 'Краткое описание должно быть не более 255 символов';
    }
    
    if (empty($phone)) {
        $errors[] = 'Введите номер телефона';
    } elseif (mb_strlen($phone) > 20) {
        $errors[] = 'Номер телефона должен быть не более 20 символов';
    }
    
    if (empty($address)) {
        $errors[] = 'Введите адрес';
    } elseif (mb_strlen($address) > 255) {
        $errors[] = 'Адрес должен быть не более 255 символов';
    }
    
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Неверный формат изображения. Допустимые форматы: JPEG, PNG, GIF';
        } else {
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($_FILES['image']['size'] > $max_size) {
                $errors[] = 'Размер изображения не должен превышать 5MB';
            } else {
                $image_name = uniqid() . '_' . $_FILES['image']['name'];
                
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                $upload_path = 'uploads/' . $image_name;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $errors[] = 'Ошибка при загрузке изображения';
                    $image_name = '';
                }
            }
        }
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO contacts (district_id, category_id, name, description, phone, address, image, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisssss', $district_id, $category_id, $name, $description, $phone, $address, $image_name);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = 'Ошибка при добавлении контакта: ' . mysqli_error($conn);
            
            if (!empty($image_name) && file_exists('uploads/' . $image_name)) {
                unlink('uploads/' . $image_name);
            }
        }
    } else {
        if (!empty($image_name) && file_exists('uploads/' . $image_name)) {
            unlink('uploads/' . $image_name);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление контакта - Телефонный справочник</title>
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
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-4 md:p-6">
            <h2 class="text-xl md:text-2xl font-medium main-color-text mb-6 text-center">Добавление нового контакта</h2>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <p>Заявка на добавление контакта успешно отправлена! После проверки модератором контакт будет опубликован.</p>
                    <p class="mt-2"><a href="/" class="underline main-color-text">Вернуться на главную страницу</a></p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc pl-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="add.php" method="post" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="district_id" class="block text-gray-700 mb-1">Район <span class="text-red-500">*</span></label>
                            <select id="district_id" name="district_id" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <option value="">Выберите район</option>
                                <?php foreach ($districts as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= isset($_POST['district_id']) && $_POST['district_id'] == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="category_id" class="block text-gray-700 mb-1">Раздел <span class="text-red-500">*</span></label>
                            <select id="category_id" name="category_id" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <option value="">Выберите раздел</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="name" class="block text-gray-700 mb-1">Имя контакта (макс. 100 символов) <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" maxlength="100" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-gray-700 mb-1">Краткое описание (макс. 255 символов) <span class="text-red-500">*</span></label>
                        <textarea id="description" name="description" maxlength="255" rows="3" 
                                  class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-gray-700 mb-1">Номер телефона <span class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" 
                               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="address" class="block text-gray-700 mb-1">Адрес <span class="text-red-500">*</span></label>
                        <input type="text" id="address" name="address" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>" 
                               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-gray-700 mb-1">Фото (необязательно, макс. 5MB)</label>
                        <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif" 
                               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <p class="text-gray-500 text-sm mt-1">Допустимые форматы: JPEG, PNG, GIF</p>
                    </div>
                    
                    <div class="flex justify-between pt-4">
                        <a href="/" class="inline-block py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">
                            Отмена
                        </a>
                        <button type="submit" class="py-3 px-6 main-color text-white rounded-lg hover:bg-opacity-90 transition-all">
                            Добавить контакт
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Добавляем скрипт для мобильного меню в конец файла перед </body> -->
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
