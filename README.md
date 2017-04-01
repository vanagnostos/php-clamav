# php-clamav
php-clamav is a PHP interface to clamd / clamscan that allows you to scan files and directories using ClamAV.

Examples
========

```PHP
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
//    //'socket' => '/usr/local/var/run/clamav/clamd.sock'
//    // or
//    'host' => '127.0.0.1',
//    'port' => 3310,
//]);

info($clamd);
scan($clamd, ['../tests/clean.txt', '../tests/infected.txt', '../tests/']);

echo '<br />';

// Scan using clamd on remote host - clamd does not have access to files
// so they have to be streamed over the network    
// directory scan is not supported

$clamd->setDriver(
    $clamd->getDriverFactory()->createDriver([
        'driver' => 'clamd_remote',
        'host' => '192.168.5.12', 
        'port' => 3310,
    ])
);

info($clamd);
scan($clamd, ['../tests/clean.txt', '../tests/infected.txt']);
```

**This should output something like:**

> Ping: Ok  
> ClamAv Version: ClamAV 0.99.2/21473/Thu Mar 24 20:25:24 2016  
> Scanning ../tests/clean.txt:  
> ../tests/clean.txt is clean  
> Scanning ../tests/infected.txt:  
> ../tests/infected.txt is infected with Eicar-Test-Signature FOUND  
> Scanning ../tests/:  
> ../tests/infected.txt is infected with Eicar-Test-Signature FOUND  
> ../tests/archive.zip is infected with Eicar-Test-Signature FOUND  
  
> Ping: Ok  
> ClamAv Version: ClamAV 0.99.2/21473/Thu Mar 24 20:25:24 2016  
> Scanning ../tests/clean.txt:  
> ../tests/clean.txt is clean  
> Scanning ../tests/infected.txt:  
> ../tests/infected.txt is infected with Eicar-Test-Signature FOUND  
> Scanning ../tests/:  
> ../tests/infected.txt is infected with Eicar-Test-Signature FOUND  
> ../tests/archive.zip is infected with Eicar-Test-Signature FOUND  

> Ping: Ok  
> ClamAv Version: ClamAV 0.99.2/21473/Thu Mar 24 20:25:24 2016  
> Scanning ../tests/clean.txt:  
> ../tests/clean.txt is clean  
> Scanning ../tests/infected.txt:  
> ../tests/infected.txt is infected with Eicar-Test-Signature FOUND  