from PIL import Image
import os

def create_pwa_icons(source_image_path, output_dir, scale=0.8):
    # Создаем директорию для иконок, если она не существует
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    # Размеры иконок для PWA
    icon_sizes = [72, 96, 128, 144, 152, 192, 384, 512]
    
    # Открываем исходное изображение
    original_img = Image.open(source_image_path)
    
    # Создаем иконки всех необходимых размеров
    for size in icon_sizes:
        # Создаем новое изображение с прозрачным фоном нужного размера
        new_img = Image.new('RGBA', (size, size), (255, 255, 255, 0))
        
        # Масштабируем исходное изображение до указанного масштаба
        scaled_size = int(size * scale)
        scaled_img = original_img.resize((scaled_size, scaled_size), Image.LANCZOS)
        
        # Вычисляем координаты для центрирования
        x_offset = (size - scaled_size) // 2
        y_offset = (size - scaled_size) // 2
        
        # Вставляем масштабированное изображение в центр прозрачного фона
        new_img.paste(scaled_img, (x_offset, y_offset), scaled_img if scaled_img.mode == 'RGBA' else None)
        
        # Формируем имя выходного файла
        output_filename = f"icon-{size}x{size}.png"
        output_path = os.path.join(output_dir, output_filename)
        
        # Сохраняем изображение
        new_img.save(output_path, "PNG")
        print(f"Создана иконка: {output_path}")
    
    # Создаем дополнительную иконку для ярлыка "Добавить"
    if 192 in icon_sizes:
        output_path = os.path.join(output_dir, "add-icon.png")
        
        # Создаем новое изображение с прозрачным фоном для ярлыка
        add_img = Image.new('RGBA', (192, 192), (255, 255, 255, 0))
        
        # Масштабируем исходное изображение для ярлыка
        scaled_size = int(192 * scale)
        scaled_add_icon = original_img.resize((scaled_size, scaled_size), Image.LANCZOS)
        
        # Вычисляем координаты для центрирования
        x_offset = (192 - scaled_size) // 2
        y_offset = (192 - scaled_size) // 2
        
        # Вставляем масштабированное изображение в центр
        add_img.paste(scaled_add_icon, (x_offset, y_offset), scaled_add_icon if scaled_add_icon.mode == 'RGBA' else None)
        
        add_img.save(output_path, "PNG")
        print(f"Создана иконка для ярлыка: {output_path}")

    print("Генерация иконок завершена!")

if __name__ == "__main__":
    # Путь к исходному изображению
    source_image = "img/logo.jpg"  # Используем logo.jpg
    
    # Директория для сохранения иконок
    icons_directory = "pwa/icons"
    
    # Масштаб изображения (0.8 = 80% от оригинального размера)
    scale = 0.8
    
    # Генерируем иконки с масштабированием
    create_pwa_icons(source_image, icons_directory, scale)
