<?php
error_reporting(E_ALL);

$bookUrl = 'http://localhost:8080/book-mining/data/books.html';
if (isset($_GET['url']) && !empty($_GET['url'])) {
	$bookUrl = $_GET['url'];
}

header("Content-type: application/json");

if (empty($bookUrl)) {
	$out = array(
		"status" 	=> 401,
		"message"	=> "No url specify"
	);
	
	echo json_encode($out);
	exit;
}

require("libs/functions.php");

include ('libs/mining.php');
$script = new \Mining\MiningComponent ();

$part = \Mining\MiningComponent::PART_BOTH;
if (isset($_GET['part'])) {
	$part = strtolower($_GET['part']);
	
	$restrict = array(
		"first"	=> \Mining\MiningComponent::PART_FIRST,
		"second"	=> \Mining\MiningComponent::PART_SECOND,
		"both"	=> \Mining\MiningComponent::PART_BOTH,
	);
	
	$part = $restrict[$part];
}

$books = $script->getBooks($bookUrl, $part);

header("Content-type: application/json");
echo json_encode($books);