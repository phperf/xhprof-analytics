<?php
// http://stackoverflow.com/questions/3684367/php-cli-how-to-read-a-single-character-of-input-from-the-tty-without-waiting-f
// http://stackoverflow.com/questions/4320081/clear-php-cli-output

function replaceOut($str)
{
    $numNewLines = substr_count($str, "\n");
    echo chr(27) . "[0G"; // Set cursor to first column
    echo $str;
    echo chr(27) . "[" . $numNewLines ."A"; // Set cursor up x lines
}

function redraw($c) {
    replaceOut("First Ln\nTime: " . time() . "\nThird Ln" . ord($c));

}

/*
while (true) {
    sleep(1);
}
*/

redraw('');

system("stty -icanon");
while ($c = fread(STDIN, 1)) {
    redraw($c);
}