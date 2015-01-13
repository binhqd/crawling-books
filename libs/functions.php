<?php
function dump($obj, $exit = true) {
	echo "<pre>";
	var_dump($obj);
	if ($exit) exit;
}