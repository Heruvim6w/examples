<?php

$pathToDirectory = __DIR__;

require_once("{$pathToDirectory}/classes/ApacheLogParser.php");
require_once("{$pathToDirectory}/classes/Results.php");

$results = new \Results();

if (!is_file($argv[1])) {
    echo 'File not found', "\n";
}
else {
    try {
        $apacheLogParser = new \ApacheLogParser($argv[1]);
    } catch (Exception $e) {
        echo $e->getMessage(), "\n";
        exit();
    }
    $lines = $apacheLogParser->handle();

    foreach ($lines as $line) {
        $results->handle($line);
    }

    $result = $results->prepareResult();
    echo json_encode($result), "\n";
}
//Просто коммент для студентов