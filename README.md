# bbb-mp4
BigBlueButton Record Process

# Команды

## extractCursorEvents
    php extractCursorEvents.php --src=events.xml --dst=events.new.xml > cursor.events

Считывает курсорные события из файла events.xml. Создает новый файл events.new.xml без них. Курсорные же события
выводятся в stdout как CSV "timestamp,x,y"

## generateCursorPng
    php generateCursorPng.php --src=./cursor.events --dst=./cursor/ --width=1280 --height=720 --diameter=10

Создает на базе файла событий курсора в формате CSV последовательность файлов в формате PNG с изображением курсора.
Задается папка, куда будет сохранена последовательность изображений, их ширина и высота и размер пятна курсора

# Окна (они же области экрана)
## NotesWindow
## BroadcastWindow
## PresentationWindow
Окно показа слайдов
## VideoDock
## ChatWindow
Окно чата
## UsersWindow
Список пользователей
## ViewersWindow
## ListenersWindow