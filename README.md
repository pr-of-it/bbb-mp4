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

## makeLayout
    php makeLayout.php --width=1280 --height=720 --dst=test.png > contents.coords

Создает изображение фона на базе ресурсов. Задается ширина и высота фона (опционально), путь к файлу, в котором
будет сохранено изображение. Координаты контента выводятся в stdout как CSV "название окна,x,y,width,height".

## makeDeskshareLayout
    php php makeDeskshareLayout.php --src=content.coords --dst=deskshare.png

Создает изображение фона для окна трансляции рабочего стола на базе координат, полученных в результате
выполнения makeLayout. Задается путь к результату работы makeLayout и путь к файлу, в котором будет сохранено
изображение.

## extractUserEvents
    php extractUserEvents.php --src=events.xml > user.events

Считывает события, связанные с входом и выходом пользователей в конференецию, из файла events.xml.
И выводит их в stdout как CSV "left/join,timestamp,userId,name".

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