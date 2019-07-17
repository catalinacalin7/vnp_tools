<html>
<head>
    <title>Funky App</title>
    <style>
        input[type=text] {
            width: 300px;
        }
    </style>
</head>
<body>
<?php

session_start();

if (isset($_GET['code'])) {
    $_SESSION['auth_code'] = $_GET['code'];
    FunkyAppInterface::drawGetTokenForm($_SESSION['app_key'], $_GET['code']);
} else if (isset($_POST['submitted_app_details'])) {
    $_SESSION['app_key'] = $_POST['app_key'];
    $_SESSION['app_secret'] = $_POST['app_secret'];
    $_SESSION['hubgets_url'] = rtrim($_POST['hubgets_url'], '/');
    $_SESSION['auth_url'] = $_SESSION['hubgets_url'] . '/oauth/authorize.php';
    $_SESSION['token_url'] = $_SESSION['hubgets_url'] . '/oauth/token.php';
    $_SESSION['redirect_url'] = 'https://' . $_SERVER['HTTP_HOST'] . '/hg_oauth/';

    FunkyAppInterface::redirectToHubgets($_SESSION['redirect_url'], $_SESSION['auth_url'], $_SESSION['app_key']);
} else if (isset($_POST['want_token'])) {
    $client = new HubgetsAppAuth(
        $_SESSION['app_key'],
        $_SESSION['app_secret'],
        $_SESSION['auth_url'],
        $_SESSION['token_url'],
        $_SESSION['redirect_url']
    );
    $token = $client->getToken($_SESSION['auth_code']);
    FunkyAppInterface::drawAccessToken($_SESSION['auth_code'], $token);
    session_destroy();
} else {
    FunkyAppInterface::drawInitialSubmitForm();
}

/**
 * HubgetsAppAuth class.
 * Generates Hubgets App tokens.
 */
class HubgetsAppAuth {

    /**
     * @var string
     */
    private $_appKey;

    /**
     * @var string
     */
    private $_appSecret;

    /**
     * @var string
     */
    private $_authUrl;

    /**
     * @var string
     */
    private $_tokenUrl;

    /**
     * @var string
     */
    private $_redirectUri;


    const GRANT_TYPE_CODE = 'code';
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';

    /**
     * HubgetsAppAuth constructor.
     * @param string $appKey
     * @param string $appSecret
     * @param string $authUrl
     * @param string $tokenUrl
     * @param string $redirectUri
     */
    public function __construct($appKey, $appSecret, $authUrl, $tokenUrl, $redirectUri) {
        $this->_appKey = $appKey;
        $this->_appSecret = $appSecret;
        $this->_authUrl = $authUrl;
        $this->_tokenUrl = $tokenUrl;
        $this->_redirectUri = $redirectUri;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getToken($code) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_tokenUrl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            array(
                'client_id' => $this->_appKey,
                'client_secret' => $this->_appSecret,
                'redirect_uri' => $this->_redirectUri,
                'grant_type' => self::GRANT_TYPE_AUTHORIZATION_CODE,
                'code' => $code
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}

/**
 * Class FunkyAppInterface
 */
class FunkyAppInterface {

    /**
     * Draws the initial form.
     */
    public static function drawInitialSubmitForm() {
        echo '<form action="index.php" method="post">';
        echo 'App Key<br><input type="text" name="app_key"><br>';
        echo 'App Secret<br><input type="text" name="app_secret"><br>';
        echo 'Hubgets URL<br><input type="text" name="hubgets_url"><br>';
        echo '<br>';
        echo '<input type="submit" name="submitted_app_details">';
        echo '</form>';
    }

    /**
     * @param string $appKey
     * @param string $authorizationCode
     */
    public static function drawGetTokenForm($appKey, $authorizationCode) {
        echo 'The following authorization code has been received from the app with ID '.$appKey.'<br>';
        echo $authorizationCode . '<br>';
        echo 'To get the token click on the button below.<br>';
        echo '<form action="index.php" method="post">';
        echo '<input type="submit" name="want_token">';
        echo '</form>';
    }

    /**
     * @param string $authorizationCode
     * @param string $accessToken
     */
    public static function drawAccessToken($authorizationCode, $accessToken) {
        echo 'Using the following authorization code<br>';
        var_export($authorizationCode);
        echo '<br>Received the following response<br>';
        var_export($accessToken);
    }

    /**
     * @param string $redirectUrl
     * @param string $hubgetsUrl
     * @param string $appKey
     */
    public static function redirectToHubgets($redirectUrl, $hubgetsAuthzUrl, $appKey) {
        $parameters = 'response_type=code&state=state&redirect_uri=' . urlencode($redirectUrl) . '&client_id=' . $appKey;
        /* Authorization endpoint */
        $redirectToHubgetsUrl = $hubgetsAuthzUrl .'?'. $parameters;
        header('Location: '.$redirectToHubgetsUrl);
    }
}

?>
</body>
</html>