<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;

$inifile = parse_ini_file("credentials.ini");
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $inifile);

//var_dump($client);

$compute = $client->computeService('cloudServersOpenStack', 'IAD');

$images = $compute->imageList();
while ($image = $images->next()){
  if (strpos($image->name, 'CentOS 6.4') !== false) {
    $centos64 = $image;
    break;
  }
}
print_r($centos64);
//$centos64 = $compute->image('f70ed7c7-b42e-4d77-83d8-40fa29825b85');
?>
