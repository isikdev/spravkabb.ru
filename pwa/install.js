// Объект события beforeinstallprompt
let deferredPrompt;
// Флаг поддержки установки через beforeinstallprompt
let isInstallSupported = false;

// Экспортируем функции для доступа из других скриптов
window.enableInstallButton = enableInstallButton;
window.showInstallInstructions = showInstallInstructions;
window.isPWAInstallSupported = () => isInstallSupported;

// Перехватываем событие beforeinstallprompt
window.addEventListener('beforeinstallprompt', (e) => {
    // Предотвращаем автоматическое появление стандартного диалога
    e.preventDefault();

    // Сохраняем событие, чтобы использовать его позже
    deferredPrompt = e;

    // Устанавливаем флаг поддержки установки
    isInstallSupported = true;

    console.log('BeforeInstallPrompt событие перехвачено');

    // Добавляем функциональность всем кнопкам установки
    const installButtons = document.querySelectorAll('.pwa-install-btn');
    installButtons.forEach(button => {
        enableInstallButton(button);
    });

    // Если кнопка install-button существует, включаем ее тоже
    const installButton = document.getElementById('pwa-install-button');
    if (installButton) {
        enableInstallButton(installButton);
    }

    // Активируем обычную кнопку установки для других устройств
    const manualInstallButton = document.getElementById('pwa-install-manual-button');
    if (manualInstallButton) {
        enableInstallButton(manualInstallButton);
    }
});

// Функция для добавления обработчика для кнопки установки
function enableInstallButton(button) {
    if (!button) return;

    // Показываем кнопку установки
    button.style.display = 'block';

    // Добавляем текст 'Установить' на кнопку если его нет
    if (button.innerText.trim() === '') {
        button.innerText = 'Установить';
    }

    // Очищаем предыдущие обработчики
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);

    // Добавляем обработчик события для кнопки
    newButton.addEventListener('click', async () => {
        if (!deferredPrompt) {
            console.log('Нет сохраненного события beforeinstallprompt');
            // Если нет события, но кнопка нажата, показываем инструкции
            showInstallInstructions();
            return;
        }

        try {
            // Показываем стандартный диалог установки
            deferredPrompt.prompt();

            // Ждем выбора пользователя
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`Пользователь выбрал: ${outcome}`);

            // Очищаем сохраненное событие
            deferredPrompt = null;

            // Если пользователь установил приложение, скрываем баннер
            if (outcome === 'accepted') {
                const installContainer = document.getElementById('pwa-install-container');
                if (installContainer) {
                    installContainer.classList.add('transform', 'translate-y-full');
                    setTimeout(() => {
                        if (installContainer && installContainer.parentNode) {
                            installContainer.remove();
                        }
                    }, 300);
                }
            }
        } catch (error) {
            console.error('Ошибка при показе диалога установки:', error);
            // При ошибке показываем стандартные инструкции
            showInstallInstructions();
        }
    });
}

// Показ инструкций по установке для браузеров без поддержки beforeinstallprompt
function showInstallInstructions() {
    const userAgent = navigator.userAgent.toLowerCase();

    if (/iphone|ipad|ipod/.test(userAgent)) {
        // Для iOS показываем специальную инструкцию
        if (typeof showIOSInstallModal === 'function') {
            showIOSInstallModal();
        } else {
            alert('Для установки в iOS: нажмите кнопку "Поделиться" и выберите "На экран Домой"');
        }
    } else if (/chrome/.test(userAgent)) {
        alert('Для установки в Chrome: нажмите на три точки в правом верхнем углу и выберите "Установить приложение"');
    } else if (/firefox/.test(userAgent)) {
        alert('Для установки в Firefox: нажмите на три точки в правом верхнем углу и выберите "Установить сайт как приложение"');
    } else if (/edge/.test(userAgent)) {
        alert('Для установки в Edge: нажмите на три точки в правом верхнем углу и выберите "Установить приложение"');
    } else if (/opera/.test(userAgent) || /opr/.test(userAgent)) {
        alert('Для установки в Opera: нажмите на три точки в правом верхнем углу и выберите "Установить"');
    } else if (/samsung/.test(userAgent)) {
        alert('Для установки в Samsung Internet: нажмите на кнопку меню и выберите "Добавить на главный экран"');
    } else {
        alert('Для установки приложения используйте функции вашего браузера. Обычно это можно сделать через меню браузера.');
    }
}

// Для обработки события appinstalled, когда приложение успешно установлено
window.addEventListener('appinstalled', (event) => {
    console.log('Приложение успешно установлено!');

    // Удаляем баннер установки, если он есть
    const installContainer = document.getElementById('pwa-install-container');
    if (installContainer) {
        installContainer.classList.add('transform', 'translate-y-full');
        setTimeout(() => {
            if (installContainer && installContainer.parentNode) {
                installContainer.remove();
            }
        }, 300);
    }

    // Очищаем сохраненное событие
    deferredPrompt = null;

    // Устанавливаем флаг в localStorage, что приложение установлено
    localStorage.setItem('appInstalled', 'true');
});

// Можно добавить функцию, которая будет проверять, поддерживает ли браузер PWA
function isPWASupported() {
    return 'serviceWorker' in navigator;
}

// Проверка, установлено ли уже приложение
function isAppInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        localStorage.getItem('appInstalled') === 'true';
}

// Слушаем изменение режима отображения
window.matchMedia('(display-mode: standalone)').addEventListener('change', (e) => {
    if (e.matches) {
        console.log('Приложение запущено в режиме "standalone"');
        // Можно выполнить дополнительные действия при запуске в режиме приложения
    }
});

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    // Ищем все кнопки установки при загрузке DOM и активируем их, если поддерживается установка
    if (isInstallSupported) {
        const installButtons = document.querySelectorAll('.pwa-install-btn');
        if (installButtons.length > 0) {
            installButtons.forEach(button => {
                enableInstallButton(button);
            });
        }

        // Также проверяем кнопки с конкретными ID
        const specificButtons = [
            document.getElementById('pwa-install-button'),
            document.getElementById('pwa-install-manual-button')
        ];

        specificButtons.forEach(button => {
            if (button) enableInstallButton(button);
        });
    }
}); 