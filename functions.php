<?php
/*
 * Output an error to stderr
 *
 * @param string $message
 */
function halt($message = null)
{
    if (null !== $message) {
        $message .= "\n";
        var_dump($message);
        fwrite(STDERR, $message);
    }
    exit(1);
}

/*
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

/*
 * Extracts data from a CSV-file and returns an array of CSV data elements
 *
 * @param string $src
 * @param array $fieldNames
 *
 * @return array
 */
function extractCSV(string $src, array $fieldNames = null)
{
    $file = fopen($src, 'r');

    if (false === $file) {
        halt('Unable to open a file with CSV data for reading');
    }

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