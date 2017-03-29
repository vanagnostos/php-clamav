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
    echo '<br />';
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
 * @param array $paths
 */
function scan(\Avasil\ClamAv\Scanner $clamd, array $paths)
{
    array_map(function ($path) use ($clamd) {

        line('Scanning ' . $path, '');

        $infected = $clamd->scan($path);

        if (!$infected) {
            line('', $path . ' is clean');
            return;
        }

        foreach ($clamd->scan($path) as $file => $virus) {
            line('', $file . ' is infected with ' . $virus);
        }
    }, $paths);
}
