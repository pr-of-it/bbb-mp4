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
    passthru(escapeshellcmd($command), $code);

    if (0 !== $code) {
        halt('Fail executing console command. Exit status #' . $code);
    }
}