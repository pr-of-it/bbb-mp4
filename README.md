# bbb-mp4
BigBlueButton Record Process

# Необходимые зависимости

## \Runn\Core
## \Runn\Fs
## \Runn\Serialization

# Конфигурационный файл
Содержит следующую информацию:
    
    'paths' => [
        'resources'   => __DIR__ . '/resources',
        'source'      => __DIR__ . '/source',
        'destination' => __DIR__ . '/result',
    ],
Абсолютные пути расположения директории ресурсов BBB, директории с файлами вебинара и директории назначения, в которую будут помещены результаты работы

    'video' => [
        'width'  => 1280,
        'height' => 720,
    ],
Желаемые размеры видео

    'log' => true
Нужно ли выводить сообщения, сопровождающие процесс

# Основной класс - \BBB\Process


## Конструктор
В качестве аргумента получает объект \Runn\Core\Config

    new \BBB\Process($config);

## Основной метод

    $process->run();
    