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

$books = $script->getBooks($bookUrl);

header("Content-type: application/json");
echo json_encode($books);