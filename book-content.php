<?php
error_reporting(E_ALL);
$url = 'http://localhost:8080/book-mining/data/verses.html';
if (isset($_GET['url'])) {
	$url = $_GET['url'];
}

header("Content-type: application/json");
if (empty($url)) {
	$out = array(
		"status" 	=> 401,
		"message"	=> "No url specify"
	);
	
	echo json_encode($url);
	exit;
}


require("libs/functions.php");

include ('libs/mining.php');
$script = new \Mining\MiningComponent ();
$contentParts = $script->getBookContent($url);

echo json_encode($contentParts);
