<?php
$basePath = dirname(__dir__) . DIRECTORY_SEPARATOR;
require_once $basePath . 'vendor/autoload.php';

require_once "google.php";

$url = "http://localhost:8080";

session_start();

// algoritme pour la connexion via google
//$_SESSION["user"] si connecter 

$g_client = new Google_Client();
$g_client->setClientId($clientid);
$g_client->setClientSecret($secret);
$g_client->setRedirectUri($url);
$g_client->setScopes("email");

$auth_url = $g_client->createAuthUrl();

$code = isset($_GET['code']) ? $_GET['code'] : NULL;
if (isset($code)){
    try{
        $token = $g_client->fetchAccessTokenWithAuthCode($code);
        $g_client->setAccessToken($token);
    }catch(Exception $e){
        echo $e->getMessage();
    }

    try{
        $_SESSION["user"] = $g_client->verifyIdToken();
    }catch(Exception $e){
        echo $e->getMessage();
    }
}else{
    $_SESSION["user"] = NULL;
}

if (isset($_SESSION["user"])) {
    header('Location: ' . $url . "/protected.php");
}

include 'header.php';
?>



<div class="jumbotron p-4 p-md-5 text-white rounded bg-dark">
    <div class="col-md-6 px-0">
        <h1 class="display-4 font-italic">Protected Zone</h1>
        <p class="lead my-3"><a href="<?=$auth_url?>" class="btn btn-primary">Login Through Google </a></p>
    </div>
</div>

<?php
include 'footer.php';
