<?php 


	

require_once "vendor/autoload.php";



$linkedIn=new Happyr\LinkedIn\LinkedIn('v3x1pj441nl9', '3ifR7tAuTKrzTUHH');

if ($linkedIn->isAuthenticated()) {
	//we know that the user is authenticated now. Start query the API
	$user=$linkedIn->api('v1/people/~:(firstName,lastName)');
	echo "Welcome ".$user['firstName'];

	exit();
} elseif ($linkedIn->hasError()) {
	echo "User canceled the login.";
	exit();
}

//if not authenticated

$url = $linkedIn->getLoginUrl();
header("Location: " . $url);

?>