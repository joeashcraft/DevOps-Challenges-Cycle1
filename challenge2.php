<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;
use OpenCloud\Compute\Constants\Network;
use OpenCloud\Compute\Constants\ServerState;

global $argv;

function usage(){
  echo "\nUSAGE: ./challenge2.php <name of servers> <number of servers>\n";
  echo "Create 1-3 servers named after user input.\n";
}

// check arguments exist
if (count($argv) == 3) {
    $baseServerName = (string) filter_var($argv[1], FILTER_SANITIZE_STRING);
    $numServers     = (int)    filter_var($argv[2], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 3)));
} else { 
  echo "Wrong number of arguments\n"; 
  exit(usage());
}

// check arguments are valid
if ($numServers == 0) {
  echo "<number of servers> must be an positive integer 3 or less\n";
  exit(usage());
}

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, parse_ini_file(getenv('HOME')."/.rackspace_cloud_credentials"));

$servers = array();
$compute = $client->computeService('cloudServersOpenStack', 'IAD');
$keypair = $compute->keypair();
$centos64 = $compute->image('f70ed7c7-b42e-4d77-83d8-40fa29825b85');
$halfGigFlavor = $compute->flavor('2');
$defaultNetworks = array($compute->network(Network::RAX_PUBLIC), $compute->network(Network::RAX_PRIVATE));

// create keypair
try {
  $keypair = $keypair->create(array(
     'name' => $baseServerName
  ));

} catch (\Guzzle\Http\Exception\BadResponseException $e) {
  $responseBody = (string) $e->getResponse()->getBody();
  $statusCode   = $e->getResponse()->getStatusCode();
  $headers      = $e->getResponse()->getHeaderLines();
  echo sprintf("Status: %s\nBody: %s\nHeaders: %s\n", $statusCode, $responseBody, implode(', ', $headers));
}

// find private key
$myKeypair = json_decode($keypair->getBody())->keypair;
echo "Private Key: \n";
echo $myKeypair->private_key;


$i = (int) 0;
while ($i < $numServers) {
  $serverName = (string) "$baseServerName" . ($i+1);

  // create server with key
  try {
    $servers[] = $compute->server();
    $servers[$i]->create(array(
      'name'      =>  $serverName,
      'image'     =>  $centos64,
      'flavor'    =>  $halfGigFlavor,
      'networks'  =>  $defaultNetworks,
      'keypair'   =>  $baseServerName
    
    ));
  } catch (\Guzzle\Http\Exception\BadResponseException $e) {
    $responseBody = (string) $e->getResponse()->getBody();
    $statusCode   = $e->getResponse()->getStatusCode();
    $headers      = $e->getResponse()->getHeaderLines();
  
    echo sprintf("Status: %s\nBody: %s\nHeaders: %s\n", $statusCode, $responseBody, implode(', ', $headers));
  }

  // provide servername
  echo sprintf("Creating server '%s'\n", $servers[$i]->name);

  $i++;
} // end while

// wait for public ip
foreach($servers as $machine) {
  $machine->waitFor(ServerState::ACTIVE, 600, null);
  if ($machine->status() == "ACTIVE") {
    echo sprintf("Server '%s' built successfully!\n", $machine->name);
    echo sprintf("Public IP: %s\n\n", $machine->accessIPv4);
  } else {
    echo sprintf("Server build failed for '%s'\n", $machine->name);
    echo sprintf("Server status '%s'", $machine->status());
  }
}


?>


