<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;

$inifile = parse_ini_file("credentials.ini");

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $inifile);

?>
