<?php
/*
 * Output an error to stderr
 *
 * @param string
 */
function halt($message = '')
{
    fwrite(STDERR , $message);
    exit(1);
}