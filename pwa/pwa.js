// Регистрация service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    // Используем setTimeout для отложенной регистрации Service Worker, чтобы не блокировать основную загрузку
    setTimeout(() => {
      navigator.serviceWorker.register('/pwa/sw.js')
        .then(registration => {
          console.log('Service Worker зарегистрирован успешно:', registration.scope);
        })
        .catch(error => {
          console.error('Ошибка регистрации Service Worker:', error);
        });
    }, 3000); // Откладываем регистрацию на 3 секунды
  });
}

// Определение типа устройства/ОС
function detectDevice() {
  const userAgent = window.navigator.userAgent.toLowerCase();
  const isIOS = /iphone|ipad|ipod/.test(userAgent);
  const isAndroid = /android/.test(userAgent);

  if (isIOS) return 'ios';
  if (isAndroid) return 'android';
  return 'other';
}

// Создание UI для установки в зависимости от устройства
function createInstallUI() {
  // Не показываем баннер, если он уже создан
  if (document.getElementById('pwa-install-container')) {
    return;
  }

  const deviceType = detectDevice();
  const installContainer = document.createElement('div');
  installContainer.id = 'pwa-install-container';
  installContainer.className = 'fixed bottom-0 left-0 right-0 bg-white shadow-lg p-4 z-50 transition-transform duration-300 transform translate-y-full';

  // Общий класс для всех кнопок установки
  const installBtnClass = 'pwa-install-btn px-4 py-2 main-color text-white rounded-lg';

  // Создаем разные UI в зависимости от типа устройства
  if (deviceType === 'ios') {
    installContainer.innerHTML = `
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <img src="/pwa/icons/icon-72x72.png" alt="БукиҺи" class="w-10 h-10 mr-3 rounded-xl">
          <div>
            <p class="font-medium text-gray-800">Установите БукиҺи</p>
            <p class="text-sm text-gray-600">Добавьте на главный экран</p>
          </div>
        </div>
        <div class="flex space-x-2">
          <button id="pwa-install-ios-button" class="${installBtnClass} bg-blue-600">Как установить</button>
          <button id="pwa-close-button" class="p-2 text-gray-500">&times;</button>
        </div>
      </div>
    `;
  } else {
    // Для Android и всех остальных устройств используем универсальный интерфейс
    installContainer.innerHTML = `
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <img src="/pwa/icons/icon-72x72.png" alt="БукиҺи" class="w-10 h-10 mr-3 rounded-xl">
          <div>
            <p class="font-medium text-gray-800">Установите БукиҺи</p>
            <p class="text-sm text-gray-600">Быстрый доступ к справочнику</p>
          </div>
        </div>
        <div class="flex space-x-2">
          <button id="pwa-install-button" class="${installBtnClass}">Установить</button>
          <button id="pwa-close-button" class="p-2 text-gray-500">&times;</button>
        </div>
      </div>
    `;
  }

  // Добавляем баннер к документу
  document.body.appendChild(installContainer);

  // Обработчик закрытия баннера
  const closeButton = document.getElementById('pwa-close-button');
  if (closeButton) {
    closeButton.addEventListener('click', () => {
      document.getElementById('pwa-install-container').classList.add('transform', 'translate-y-full');
      localStorage.setItem('pwaBannerClosed', Date.now());

      // Удаляем баннер из DOM после анимации
      setTimeout(() => {
        const banner = document.getElementById('pwa-install-container');
        if (banner) banner.remove();
      }, 300);
    });
  }

  // Показываем баннер с анимацией
  setTimeout(() => {
    installContainer.classList.remove('translate-y-full');
  }, 100);

  // Если iOS и отсутствует поддержка beforeinstallprompt
  if (deviceType === 'ios') {
    setupIOSInstall();
  } else {
    // Для Android и других устройств, если есть сохранённое событие beforeinstallprompt
    // Активируем кнопку установки если есть внешняя функция
    const installButton = document.getElementById('pwa-install-button');
    if (installButton && typeof enableInstallButton === 'function' && typeof window.isPWAInstallSupported === 'function') {
      if (window.isPWAInstallSupported()) {
        enableInstallButton(installButton);
      } else {
        // Если нет события beforeinstallprompt, добавляем обработчик для показа инструкций
        installButton.addEventListener('click', function () {
          if (typeof showInstallInstructions === 'function') {
            showInstallInstructions();
          }
        });
      }
    }
  }
}

// Настройка инструкции по установке для iOS
function setupIOSInstall() {
  const installButton = document.getElementById('pwa-install-ios-button');

  if (installButton) {
    installButton.addEventListener('click', () => {
      showIOSInstallModal();
    });
  }
}

// Отображение модального окна с инструкциями по установке для iOS
function showIOSInstallModal() {
  // Удаляем существующее модальное окно если оно есть
  const existingModal = document.getElementById('ios-install-modal');
  if (existingModal) existingModal.remove();

  // Создаем новое модальное окно
  const modal = document.createElement('div');
  modal.id = 'ios-install-modal';
  modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';

  // Упрощаем инструкции и уменьшаем количество изображений
  modal.innerHTML = `
    <div class="bg-white rounded-lg w-11/12 max-w-lg p-5 max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-medium">Как установить на iOS</h3>
        <button id="close-ios-modal" class="p-2 text-gray-500">&times;</button>
      </div>
      <div class="space-y-4">
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center mr-3">1</div>
          <p>Нажмите на кнопку «Поделиться» в браузере Safari</p>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center mr-3">2</div>
          <p>Выберите «На экран «Домой»»</p>
        </div>
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center mr-3">3</div>
          <p>Нажмите «Добавить» в правом верхнем углу</p>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  // Добавляем обработчик закрытия
  const closeButton = document.getElementById('close-ios-modal');
  if (closeButton) {
    closeButton.addEventListener('click', () => {
      document.getElementById('ios-install-modal').remove();
    });
  }

  // Закрытие модалки при клике вне окна
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.remove();
    }
  });
}

// Проверка, должны ли мы показывать баннер установки
function shouldShowInstallBanner() {
  // Не показываем, если приложение уже установлено
  if (window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true ||
    localStorage.getItem('appInstalled') === 'true') {
    return false;
  }

  // Не показываем, если пользователь недавно закрыл баннер (в течение 2 недель)
  const lastClosed = localStorage.getItem('pwaBannerClosed');
  if (lastClosed) {
    const twoWeeksInMs = 14 * 24 * 60 * 60 * 1000;
    if (Date.now() - parseInt(lastClosed) < twoWeeksInMs) {
      return false;
    }
  }

  return true;
}

// Инициализируем PWA интерфейс после полной загрузки страницы
window.addEventListener('load', () => {
  // Отложенный запуск PWA функциональности, чтобы не блокировать основной рендеринг
  setTimeout(() => {
    if (shouldShowInstallBanner()) {
      // Добавляем значительную задержку для лучшего UX
      setTimeout(() => {
        createInstallUI();
      }, 5000); // Показываем через 5 секунд после загрузки
    }
  }, 1000);
}); 