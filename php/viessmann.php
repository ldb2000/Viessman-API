<?php
$client_id = '79742319e39245de5f91d15ff4cac2a8';
$client_secret = '8ad97aceb92c5892e102b093c7c083fa';

$isiwebuserid = 'email@mail';   //to be modified
$isiwebpasswd = 'mdp';          //to be modified

$authorizeURL = 'https://iam.viessmann.com/idp/v1/authorize';
$token_url = 'https://iam.viessmann.com/idp/v1/token';
$apiURLBase = 'https://api.viessmann-platform.io';
$general = '/general-management/installations?expanded=true&';

$debug = true;

$callback_uri = "vicare://oauth-callback/everest";

// $authorization = base64_encode("$client_id:$client_secret");
// debug_msg ("autho=$authorization",$debug);
$code = getCode();
debug_msg("code=$code", $debug);
$access_token = getAccessToken($code);
debug_msg("access token= $access_token", $debug);



$resource = getResource($access_token, $apiURLBase . $general);
debug_msg("resource: $resource", $debug);

$installation = json_decode($resource, true)["entities"][0]["properties"]["id"];
echo "Installation=$installation\n";
$gw = json_decode($resource, true)["entities"][0]["entities"][0]["properties"]["serial"];
echo "Gateway=$gw\n";

$resource = getResource($access_token, "https://api.viessmann-platform.io/operational-data/installations/$installation/gateways/$gw/devices/0/features/heating.sensors.temperature.outside");
debug_msg("resource: $resource", $debug);
echo strval(json_decode($resource, true)["properties"]["value"]["value"]);

function getCode()
{
    global $client_id, $authorizeURL, $callback_uri;
    global $isiwebuserid, $isiwebpasswd;
    $url = "$authorizeURL?client_id=$client_id&scope=openid&redirect_uri=$callback_uri&response_type=code";
    $header = array("Content-Type: application/x-www-form-urlencoded");
    $curloptions = array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$isiwebuserid:$isiwebpasswd",
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_POST => true,
    );
    $curl = curl_init();
    curl_setopt_array($curl, $curloptions);
    $response = curl_exec($curl);
    curl_close($curl);
    $matches = array();
    $pattern = '/code=(.*)"/';
    preg_match_all($pattern, $response, $matches);
    return ($matches[1][0]);
}

function getAccessToken($authorization_code)
{
    global $token_url, $client_id, $client_secret, $callback_uri;
    global $isiwebuserid, $isiwebpasswd;
    $header = array("Content-Type: application/x-www-form-urlencoded;charset=utf-8");
    $params = array(
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "code" => $authorization_code,
        "redirect_uri" => $callback_uri,
        "grant_type" => "authorization_code");

    $curloptions = array(
        CURLOPT_URL => $token_url,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => rawurldecode(http_build_query($params)));

    $curl = curl_init();
    curl_setopt_array($curl, $curloptions);
    $response = curl_exec($curl);
    curl_getinfo($curl);
    curl_close($curl);

    if ($response === false) {
        echo "Failed\n";
        echo curl_error($curl);

    } elseif (!empty(json_decode($response)->error)) {
        echo "Error:\n";
        echo $authorization_code;
        echo $response;
    }

    return json_decode($response)->access_token;
}

//    we can now use the access_token as much as we want to access protected resources
function getResource($access_token, $api)
{
    echo "ok\n";
    $header = array("Authorization: Bearer {$access_token}");
    var_dump($header);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $api,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    //return json_decode($response, true);
    return ($response);
}

function debug_msg($message, $debug)
{
    if ($debug) {
        echo "$message\n";
    }
}
