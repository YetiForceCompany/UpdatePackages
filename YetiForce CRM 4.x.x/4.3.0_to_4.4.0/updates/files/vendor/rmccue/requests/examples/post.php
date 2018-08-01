<?php

// First, include Requests
include '../library/Requests.php';

// Next, make sure Requests can load internal classes
Requests::register_autoloader();

// Now let's make a request!
$request = Requests::post('http://httpbin.org/post', [], ['mydata' => 'something']);

// Check what we received
var_dump($request);
