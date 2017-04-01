<?php

/**
 * @param string $string
 * @return string
 */
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
}

/**
 * @param string $label
 * @param string $text
 */
function line($label, $text)
{
    if ($label) {
        echo '<strong>' . escape($label) . ':</strong> ';
    }
    if ($text) {
        echo escape($text);
    }
    echo '  <br />';
}

/**
 * @param \Avasil\ClamAv\Scanner $clamd
 */
function info(\Avasil\ClamAv\Scanner $clamd)
{
    line('Ping', ($clamd->ping() ? 'Ok' : 'Failed'));
    line('ClamAv Version', $clamd->version());
}

/**
 * @param \Avasil\ClamAv\Scanner $clamd
 * @param array $targets
 * @param string $mode
 */
function scan(\Avasil\ClamAv\Scanner $clamd, array $targets, $mode = 'scan')
{
    array_map(function ($target) use ($clamd, $mode) {

        line('Scanning ' . ($mode == 'scan' ? $target : 'buffer'), '');

        $result = $mode == 'scan' ? $clamd->scan($target) : $clamd->scanBuffer($target);

        if ($result->isClean()) {
            line('', $result->getTarget() . ' is clean');
            return;
        }

        foreach ($result->getInfected() as $file => $virus) {
            line('', $file . ' is infected with ' . $virus);
        }
    }, $targets);
}
