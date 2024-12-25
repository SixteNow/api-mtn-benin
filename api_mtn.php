<?php
// Charger l'autoloader de Composer pour charger automatiquement les dépendances
require '../vendor/autoload.php';

use Ramsey\Uuid\Uuid; // Utilisation de la bibliothèque Ramsey UUID pour générer des identifiants uniques

// Fonction pour effectuer une requête API avec cURL
function makeApiRequest($url, $headers, $body = null, $method = 'POST')
{
    $curl = curl_init($url); // Initialisation de la session cURL avec l'URL de la requête

    // Configuration des options de la requête cURL
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,  // Retourner la réponse
        CURLOPT_HTTPHEADER => $headers,  // Ajouter les en-têtes de la requête
        CURLOPT_SSL_VERIFYPEER => false, // Désactiver la vérification SSL
        CURLOPT_VERBOSE => true,        // Activer les logs cURL pour le débogage
        CURLOPT_STDERR => fopen('php://stderr', 'w'), // Rediriger les erreurs cURL vers stderr
    ]);

    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); // Requête POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);      // Ajouter le corps de la requête
    } elseif ($method === 'GET') {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");   // Requête GET
    }

    $response = curl_exec($curl); // Exécution de la requête
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Récupérer le code de statut HTTP
    $error = curl_error($curl); // Récupérer l'erreur cURL
    curl_close($curl); // Fermer la session cURL

    // Retourner la réponse, le code HTTP et l'erreur
    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'error' => $error
    ];
}

// Fonction pour générer un utilisateur
function createApiUser($uuid, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser";
    $headers = [
        'X-Reference-Id: ' . $uuid,
        'Content-Type: application/json',
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
    ];
    $body = json_encode(['providerCallbackHost' => "http://example.com/callback"]);
    return makeApiRequest($url, $headers, $body);
}

// Fonction pour récupérer les informations de l'utilisateur
function getApiUser($uuid, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$uuid}";
    $headers = [
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
    ];
    return makeApiRequest($url, $headers, null, 'GET');
}

// Fonction pour générer la clé API
function generateApiKey($uuid, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$uuid}/apikey";
    $headers = [
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        'Content-Length: 0',
    ];
    return makeApiRequest($url, $headers);
}

// Fonction pour obtenir un token d'accès
function getAccessToken($uuid, $apiKey, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/collection/token/";
    $headers = [
        "Authorization: Basic " . base64_encode("$uuid:$apiKey"),
        "Ocp-Apim-Subscription-Key: " . $subscriptionKey,
        "Content-Type: application/json",
    ];
    return makeApiRequest($url, $headers);
}

// Fonction pour demander un paiement
function requestPayment($uuid, $accessToken, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay";
    $headers = [
        "Authorization: Bearer $accessToken",
        "X-Reference-Id: $uuid",
        "X-Target-Environment: sandbox",
        "Ocp-Apim-Subscription-Key: $subscriptionKey",
        "Content-Type: application/json",
    ];
    $body = json_encode([
        "amount" => "1",
        "currency" => "EUR",
        "externalId" => $uuid,
        "payer" => [
            "partyIdType" => "MSISDN",
            "partyId" => "2290166547412"
        ],
        "payerMessage" => "Abonnement",
        "payeeNote" => "Merci pour votre paiement"
    ]);
    return makeApiRequest($url, $headers, $body);
}

// Fonction pour obtenir le solde du compte
function getAccountBalance($accessToken, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/account/balance";
    $headers = [
        "Authorization: Bearer $accessToken",
        "X-Target-Environment: sandbox",
        "Ocp-Apim-Subscription-Key: $subscriptionKey",
    ];
    return makeApiRequest($url, $headers, null, 'GET');
}

// Fonction pour vérifier le statut de la transaction
function checkTransactionStatus($accessToken, $transactionReferenceId, $subscriptionKey)
{
    $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/$transactionReferenceId";
    $headers = [
        "Authorization: Bearer $accessToken",
        "X-Target-Environment: sandbox",
        "Ocp-Apim-Subscription-Key: $subscriptionKey",
    ];
    return makeApiRequest($url, $headers, null, 'GET');
}

// EXEMPLES

// Clé d'abonnement API
$subscriptionKey = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; //Mettez votre clé primaire ou secondaire ici
$uuid = Uuid::uuid4()->toString(); // Générer un UUID unique
$accessToken = "";

// Créer un utilisateur
$response = createApiUser($uuid, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Corps de la réponse :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}

// Récupérer les informations de l'utilisateur
$response = getApiUser($uuid, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Corps de la réponse :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}

// Générer une clé API
$response = generateApiKey($uuid, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Corps de la réponse :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}

// Obtenir le token d'accès
$apiKey = json_decode($response['response'])->apiKey ?? ''; // Clé API générée précédemment
$response = getAccessToken($uuid, $apiKey, $subscriptionKey);

if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Token d'accès :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";

    // Decoder la réponse et obtenir le token d'accès
    $responseData = json_decode($response['response']);

    //Vérifier si le token existe dans la reponse
    if (isset($responseData->access_token)) {
        $accessToken = $responseData->access_token;
        echo "Token d'accès: " . $accessToken;
    } else {
        echo "Token non trouvé dans la réponse.";
    }
}



// Demander un paiement
$response = requestPayment($uuid, $accessToken, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Corps de la réponse :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}

// Obtenir le solde
$response = getAccountBalance($accessToken, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Solde du compte :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}

// Vérifier le statut de la transaction
$response = checkTransactionStatus($accessToken, $uuid, $subscriptionKey);
if ($response['error']) {
    echo "Erreur cURL : " . $response['error'];
} else {
    echo "Code HTTP : " . $response['httpCode'] . "<br>";
    echo "Corps de la réponse :<pre>" . json_encode(json_decode($response['response']), JSON_PRETTY_PRINT) . "</pre>";
}
