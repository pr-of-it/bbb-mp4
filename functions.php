<?php
/*
 * Output an error to stderr
 *
 * @param string
 */
function halt($message = '')
{
    if (null != $message) {
        $message .= "\n";
        fwrite(STDERR , $message);
    }
    exit(1);
}

/*
 * Execute the console command and check the result of its execution
 *
 * @param string
 */
function execute(string $command) {
    $string = escapeshellcmd($command);
    exec($string, $output, $return_var);

    if (0 !== $return_var) {
        halt('File error');
    }
}