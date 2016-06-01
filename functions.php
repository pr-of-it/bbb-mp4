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
function execute(string $command, $output = null)
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

function extractCsv($dataPathFile, array $fieldNames = null)
{
    $file = fopen($dataPathFile, 'r');

    if (false === $file) {
        halt('Unable to open file for reading');
    }

    $data = [];
    $mass = [];
    while ($csv = fgetcsv($file, 1024)) {

        if (null === $fieldNames) {
            $data[] = $csv;
        }

        if (null !== $fieldNames) {
            foreach ($fieldNames as $key => $value) {
                $mass[$value] = $csv[$key];
            }
            $data[] = $mass;
        }
    }

    fclose($file);
    return $data;
}