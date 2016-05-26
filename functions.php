<?php
/*
 * Output an error to stderr
 *
 * @param string
 */
function halt($message = '')
{
    if (isset($message)) {
        $message .= "\n";
        fwrite(STDERR, $message);
    }
    exit(1);
}

/*
 * Execute the console command and check the result of its execution
 *
 * @param string
 */
function execute(string $command)
{
    $string = escapeshellcmd($command);
    passthru($string, $return_var);

    if (0 !== $return_var) {
        halt('Fail executing console command. Exit status #' . $return_var);
    }
}