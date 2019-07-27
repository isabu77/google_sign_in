<?php
$basePath = dirname(__dir__) . DIRECTORY_SEPARATOR;
require_once $basePath . 'vendor/autoload.php';

require_once "google.php";

$url = "http://localhost:8080";

session_start();

// algoritme pour la connexion via google


// from https://www.yoctopuce.com/FR/interactive/OAuth2/ :
// 1 : instancier un objet Google_Client 
// avec les paramètres trouvés dans la "Developers Console".
$g_client = new Google_Client();

$g_client->setApplicationName("Mon application");

// ne pas oublier setAccessType('offline') 
// sinon Google ne fournira pas de "refresh token" lors de l'autorisation et il faudra demander 
// à nouveau à l'utilisateur d'autoriser notre script quand l'access token expirera. 

$g_client->setAccessType('offline');

$g_client->setClientId($clientid);
$g_client->setClientSecret($secret);
$g_client->setRedirectUri($url);
$g_client->setScopes("email");

// retourne une URL qui permet à l'utilisateur d'autoriser notre script. 
$auth_url = $g_client->createAuthUrl();

// 2 : $_GET['code'] contient l'authorisation code, retourné par Google
// authenticate($_GET['code']) échange ce code contre un access token et un refresh token
// Il faut impérativement sauver ces deux tokens, 
// sinon on ne pourra pas accéder à l'API Google lors de la prochaine exécution.

// ICI on le stocke dans $_SESSION["user"] 
$code = isset($_GET['code']) ? $_GET['code'] : NULL;
if (isset($code)){
    try{

        $token = $g_client->fetchAccessTokenWithAuthCode($code);
        // assigne le token à l'objet Google_Client
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

// Une fois que l'on a sauvé les tokens,
// (implémenter trois fonctions pour DB(clearTokenInDb, updateTokenInDb, getTokenFromDb)). 
// on redirige à nouveau l'utilisateur sur notre script 
// pour "nettoyer" l'URL du paramètre "code"
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
