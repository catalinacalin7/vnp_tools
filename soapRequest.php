<?php
/* Disable cache */
ini_set('soap.wsdl_cache_enabled', 0);

/* Server IP/Hostname */
//$IP = "1.2.3.4";
$IP = 'changeme.domain.com';

/* Version of the VoipNow product */
$VERSION = '5.2.5';

/* Authentication token*/
$TOKEN = 'CHANGEME';

/* Create SOAP client based on WSDL, with trace for debugging */
$streamContext = stream_context_create(array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
));
$wsdl = "https://" . $IP . "/soap2/schema/" . $VERSION . "/voipnowservice.wsdl";
$options = array(
    'trace' => 1,
    'exceptions' => true,
    'stream_context' => $streamContext
);
$client = new SoapClient($wsdl, $options);

/* Create authentication headers */
$auth = new stdClass();
$auth->accessToken = $TOKEN;

$userCredentials = new SoapVar($auth, SOAP_ENC_OBJECT, 'http://4psa.com/HeaderData.xsd/' . $VERSION);
$headerOAuth = new SoapHeader(
    'https://4psa.com/HeaderData.xsd/' . $VERSION,
    'userCredentials', $userCredentials, false
);
$client->__setSoapHeaders(array($headerOAuth));

$AddUserPayload = array(
  // FIXME - create User payload
);

$result = null;
try {

    $result = $client->__getFunctions();
    //$result = $client->AddUser($AddUserPayload);

} catch (SoapFault $exception) {
    echo "\nERROR: \n" . $exception->getMessage() . "\n\n";
}

echo "\nResult: \n\n";
if ($result) {
    var_dump((array)$result);
}

echo "\n\n--------------------------\n";
print_r($client->__getLastRequest());
echo "\n--------------------------\n";
print_r($client->__getLastResponse());
echo "\n--------------------------\n\n";
