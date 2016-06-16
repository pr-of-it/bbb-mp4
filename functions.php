<?php

define('DS', DIRECTORY_SEPARATOR);

/**
 * Output a message to stdout with correct end of line
 * 
 * @param string $message
 */
function writeLn(string $message)
{
    echo $message . PHP_EOL;
}

/**
 * Output an error to stderr
 *
 * @param string $message
 */
function halt($message = null)
{
    if (null !== $message) {
        $message .= "\n";
        fwrite(STDERR, $message);
    }
    exit(1);
}

/**
 * Executes the console command and checks the result of its execution
 *
 * @param string $command
 * @param string $output
 */
function execute(string $command, string $output = null)
{
    $command = escapeshellcmd($command);

    if (null !== $output) {
        $command .= ' > ' . $output;
    }

    passthru($command, $code);

    if (0 !== $code) {
        halt('Fail executing console command. Exit status #' . $code);
    }
}

/**
 * Extracts data from a CSV-file and returns an array of CSV data elements
 *
 * @param string $src
 * @param array $fieldNames
 *
 * @return array
 */
function extractCSV(string $src, array $fieldNames = null)
{
    if (!is_readable($src)) {
        halt('File with CSV data does not exist or is not readable');
    }

    $file = fopen($src, 'r');

    $data = [];
    while ($csv = fgetcsv($file, 1024)) {

        if (null === $fieldNames) {
            $data[] = $csv;
        } else {
            $row = [];
            foreach ($fieldNames as $key => $value) {
                $row[$value] = $csv[$key];
            }
            $data[] = $row;
        }
    }
    
    fclose($file);
    return $data;
}

/**
 * Generate number of images based on array with user-list
 *
 * @param string $dstDir
 * @param int $width
 * @param int $height
 * @param array $userList
 *
 * @return array
 */
function generateUserListImage(string $dstDir, int $width, int $height, int $time, array $userList)
{
    if (!file_exists($dstDir)) {
        mkdir($dstDir);
    }
    $css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');

    $layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);
    $layout->setDimensions($width, $height, 0);
    $layout->addCustomWindow(['name' => 'UserList', 'x' => 0, 'y' => 0, 'w' => 1, 'h' => 1]);

    foreach ($userList as $userName) {
        $layout->addWindowText('UserList', $userName);
    }
    
    $layout->setMarkedWindows(['UserList']);
    $layout->generatePng($dstDir . DS . 'list.' . $time . '.png', true, false);
}