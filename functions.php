<?php
/*
 * Output an error to stderr
 *
 * @param string
 */
function halt($message = '')
{
    if (null !== $message) {
        $message .= "\n";
    }
    fwrite(STDERR , $message);
    exit(1);
}