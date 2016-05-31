<?php
/*
 * Output an error to stderr
 *
 * @param string
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
 * @param string
 */
function execute(string $command, $output = null)
{
    if (null !== $output) {
        $output = ' > ' . $output;
    }

    passthru(escapeshellcmd($command) . $output, $code);

    if (0 !== $code) {
        halt('Fail executing console command. Exit status #' . $code);
    }
}