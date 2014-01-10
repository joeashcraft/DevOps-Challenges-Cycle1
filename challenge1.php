<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;
use OpenCloud\Compute\Constants\Network;
use OpenCloud\Compute\Constants\ServerState;


$inifile = parse_ini_file("~/.rackspace_cloud_credentials");
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $inifile);

$compute = $client->computeService('cloudServersOpenStack', 'IAD');
$centos64 = $compute->image('f70ed7c7-b42e-4d77-83d8-40fa29825b85');
$halfGigFlavor = $compute->flavor('2');
$server = $compute->server();

try {
  $server->create(array(
    'name'      =>  'API Challenge 1',
    'image'     =>  $centos64,
    'flavor'    =>  $halfGigFlavor,
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

echo sprintf("Creating server '%s'\n", $server->name);
echo sprintf("Admin Password: '%s'\n", $server->adminPass);

$server->waitFor(ServerState::ACTIVE, 600, null);

if ($server->status() == "ACTIVE") {
  echo "Server build completed successfully!\n";
} else {
  echo "Server build failed\n";
  exit();
}

echo sprintf("Public IP: %s\n", $server->accessIPv4);

?>


