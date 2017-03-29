<?php

// autoloader
require '../vendor/autoload.php';

// helper functions used in this example
require_once './functions.php';

// Scan using clamscan or clamdscan available on localhost
// clamscan must have access to the scanned files

$clamd = new \Avasil\ClamAv\Scanner([
    'driver' => 'clamscan',
    // clamscan or clamdscan,
    // clamdscan much faster but clamd daemon must be running
    'executable' => '/usr/local/bin/clamdscan'
]);

info($clamd);
scan($clamd, ['../tests/clean.txt', '../tests/infected.txt', '../tests/']);

echo '<br />';

// Scan using clamd on localhost
// clamd must have access to the scanned files

$clamd->setDriver(
    $clamd->getDriverFactory()->createDriver([
        'driver' => 'clamd_local',
        'socket' => '/usr/local/var/run/clamav/clamd.sock',
        // or
        // 'host' => '127.0.0.1',
        // 'port' => 3310,
    ])
);

// Or create new one
//$clamd = new \Avasil\ClamAv\Scanner([
//    'driver' => 'clamd_local',
//    'host' => '127.0.0.1',
//    // port or socket is required
//    'port' => 3310,
//    //'socket' => '/usr/local/var/run/clamav/clamd.sock'
//]);

info($clamd);
scan($clamd, ['../tests/clean.txt', '../tests/infected.txt', '../tests/']);
