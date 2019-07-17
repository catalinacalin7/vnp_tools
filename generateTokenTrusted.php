<?php
$ADMIN_APP_ID = 'CHANGEME';
$ADMIN_APP_SECRET = 'CHANGEME';

$ORG_APP_ID = 'CHANGEME';
$ORG_APP_SECRET = 'CHANGEME';

$USE_APP_ID = $ADMIN_APP_ID;
$USE_APP_SECRET = $ADMIN_APP_ID;

$SERVER_HOSTNAME = 'CHANGEME';
$AUTHORIZE_ENDPOINT = 'oauth/authorize.php';
$TOKEN_ENDPOINT = 'oauth/token.php';

$GRANT_TYPE = 'client_credentials';

$requestParams = array(
    'grant_type' => $GRANT_TYPE,
    'client_id' => $USE_APP_ID,
    'client_secret' => $USE_APP_SECRET,
    'lifetime' => 7200
);

$curl = curl_init('https://'. $SERVER_HOSTNAME .'/'. $TOKEN_ENDPOINT);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $requestParams);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$curlResponse = curl_exec($curl);
if ($curlResponse === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die("\nError occurred during curl exec. Additional info: \n" . var_dump($info));
}

curl_close($curl);
echo "\nSuccess !\n";
$token = json_decode($curlResponse);
print_r($token);
echo "\n\n";

