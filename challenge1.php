<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;
use OpenCloud\Compute\Constants\Network;

$inifile = parse_ini_file("credentials.ini");
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $inifile);

$compute = $client->computeService('cloudServersOpenStack', 'IAD');
$centos64 = $compute->image('f70ed7c7-b42e-4d77-83d8-40fa29825b85');
$halfGigFlavor = $compute->flavor('2');

$server = $compute->server();

try {
  $response = $server->create(array(
    'name'      =>  'API Challenge 1',
    'image'     =>  $centos64,
    'flavor'    =>  '12345',//$halfGiGFlavor,
    'networks'  =>  array(
      $compute->network(Network::RAX_PUBLIC),
      $compute->network(Network::RAX_PRIVATE)
    )
  ));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {
  $responseBody = (string) $e->getResponse()->getBody();
  $statusCode   = $e->getResponse()->getStatusCode();
  $headers      = $e->getResponse()->getHeaderLines();
  
  echo sprintf("Status: %s\nBody: %s\nHeaders: %s", $statusCode, $responseBody, implode(', ', $headers));
}


?>
