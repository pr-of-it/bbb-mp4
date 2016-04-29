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

## extractVoiceEvents
    php extractVoiceEvents.php --src=events.xml > voice.events

Считывает голосовые события из файла events.xml и выводит их в stdout как CSV "Start/Stop,timestamp,filename".

## makeSound
    php makeSound.php --src=voice.events --src-dir=./audio --dst=sound.wav

Создает на базе файла голосовых событий и фрагментов этих событий итоговый файл. Задается файл, куда нужно
сохранить результат и опционально путь к папке, где расположены фрагменты. К имени итогового файла будет
добавлена отметка времени начала первого фрагмента, например: 2419160065.sound.wav.

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