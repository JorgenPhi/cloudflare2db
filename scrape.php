#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

echo 'Cloudflare Stats -> Database v1.0'.PHP_EOL;
echo '========================================'.PHP_EOL;

if (!defined('PDO::ATTR_DRIVER_NAME')) {
    fancyDie('PDO isn\'t installed!  It is installed by default in PHP 5.1.0 and newer, you should upgrade your PHP version.  You can install PDO manually by running the command: pear install pdo'.PHP_EOL);
}

if (!file_exists('config.json')) {
    die('[FATAL ERROR] Unable to locate config.json file.'.PHP_EOL);
}

$config = @json_decode(file_get_contents('config.json'));

if (!is_object($config)) {
    die('[FATAL ERROR] Malformed config.json file.'.PHP_EOL);
}

if (!file_exists('vendor/autoload.php')) {
    die('[FATAL ERROR] You need to run: composer install'.PHP_EOL);
}

require 'vendor/autoload.php';

$dbclient = new InfluxDB\Client($config->influxhost, $config->influxport);
$database = $dbclient->selectDB('cloudflare');

$key     = new Cloudflare\API\Auth\APIKey($config->email, $config->apikey);
$adapter = new Cloudflare\API\Adapter\Guzzle($key);
$zones    = new Cloudflare\API\Endpoints\Zones($adapter);



foreach ($config->zones as $zone) {
    $points = array();
	try {
        // Grab zoneID from API
		$zoneID = $zones->getZoneID($zone);
	} catch (GuzzleHttp\Exception\ClientException $ex) {
	    die('[FATAL ERROR] Could not get zoneID. Are your API credentials correct?'.PHP_EOL);
	} catch (Cloudflare\API\Endpoints\EndpointException $ex) {
        echo '[ERROR] '.$zone.' does not exist in this cloudflare account. Skipping!'.PHP_EOL;
        continue;
    }
    echo 'Processing '.$zone.'...'.PHP_EOL;
    $stats = $zones->getAnalyticsDashboard($zoneID, '-1440', '0', true); // This varaible affects stat precision. Do -525600 to get full stats initially, then set to -1440
    $stats = json_decode(json_encode($stats), true);

    foreach ($stats['timeseries'] as $stat) {
        array_push($points, 
            new InfluxDB\Point(
                'requests',
                $stat['requests']['all'],
                ['host' => $zone],
                ['ssl_encrypted' => $stat['requests']['ssl']['encrypted'], 'bandwidth' => $stat['bandwidth']['all']],
                strtotime($stat['since'])
            ));
    }

    $result = $database->writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
}