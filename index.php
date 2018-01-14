<?php
 	require_once('connection.php');

 	if (isset($_GET['action'])) {
 	  $action     = $_GET['action'];
 	} else {
 	  $action     = 'home';
 	}

 	require_once('routes.php');
?>