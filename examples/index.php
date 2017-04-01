<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

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
scan($clamd, ['../examples/clean.txt', '../examples/infected.txt', '../examples/']);

//////////////////////////////////////////////////////////////////////////////

echo '<br />';

// Scan using clamd on local host
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
//    //'socket' => '/usr/local/var/run/clamav/clamd.sock'
//    // or
//    'host' => '127.0.0.1',
//    'port' => 3310,
//]);

info($clamd);
scan($clamd, ['../examples/clean.txt', '../examples/infected.txt', '../examples/']);

//////////////////////////////////////////////////////////////////////////////

echo '<br />';

// Scan using clamd on remote host
// directory scan is not supported

$clamd->setDriver(
    $clamd->getDriverFactory()->createDriver([
        'driver' => 'clamd_remote',
        'host' => '127.0.0.1',
        'port' => 3310,
    ])
);

info($clamd);
scan($clamd, ['../examples/clean.txt', '../examples/infected.txt']);
scan(
    $clamd,
    [
        'Lorem Ipsum Dolor',
        'Lorem Ipsum Dolor X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*'
    ],
    'buffer'
);
