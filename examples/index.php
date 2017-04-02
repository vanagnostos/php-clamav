<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

// autoloader
require '../vendor/autoload.php';

// Scan using clamscan or clamdscan available on localhost, clamscan must have access to the scanned files.
// clamscan and clamdscan are supported, clamdscan much faster but clamd daemon must be running.
$config = ['driver' => 'clamscan', 'executable' => '/usr/local/bin/clamdscan'];

// Scan using clamd on local host, clamd must have access to the scanned files.
// $config = ['driver' => 'clamd_local', 'socket' => '/usr/local/var/run/clamav/clamd.sock'];
// $config = ['driver' => 'clamd_local', 'host' => '127.0.0.1', 'port' => 3310];

// Scan using clamd on remote host, directory scan is not supported.
// Files will be send over the network so large files could be an issue.
// $config = ['driver' => 'clamd_remote', 'host' => '127.0.0.1', 'port' => 3310];

$clamd = new \Avasil\ClamAv\Scanner($config);

echo 'Ping: ', ($clamd->ping() ? 'Ok' : 'Failed'), '<br />';

echo 'ClamAv Version: ', $clamd->version(), '<br />';

$toScan = [
    '../examples/files/clean.txt',
    '../examples/files/infected.txt',
    '../examples/files/',
    'Lorem Ipsum Dolor',
    'Lorem Ipsum Dolor X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*'
];

foreach ($toScan as $f) {
    if (file_exists($f)) {
        echo 'Scanning ', $f, '<br />';
        $result = $clamd->scan($f);
    } else {
        echo 'Scanning buffer', '<br />';
        $result = $clamd->scanBuffer($f);
    }
    if ($result->isClean()) {
        echo ' - ', $result->getTarget(), ' is clean', '<br />';
    } else {
        foreach ($result->getInfected() as $file => $virus) {
            echo ' - ', $file, ' is infected with ', $virus, '<br />';
        }
    }
}
