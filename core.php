<?php
$main_config['toast_state'] = false;
if ($main_config['DB_PORT'] != '') {
	$con = mysqli_connect($main_config['DB_HOST'], $main_config['DB_USER'], $main_config['DB_PASSWORD'], $main_config['DB_DATABASE'], $main_config['DB_PORT']);
} else {
	$con = mysqli_connect($main_config['DB_HOST'], $main_config['DB_USER'], $main_config['DB_PASSWORD'], $main_config['DB_DATABASE']);
}
//$con = new mysqli($main_config['DB_HOST'], $main_config['DB_USER'], $main_config['DB_PASSWORD'], $main_config['DB_DATABASE']);
if (!$con) {
    die('Could not Connect Database ! ');
}
$response = array();
$response["data"] = array();
$PAGEROUTEARRAY = array();
$DBDEBUGQUERY = "No Query";
$DBDATAARRAY = array();
$crudarr = array();
$viewdata = array();
$crudarr["value"] = array();
$crudarr["column"] = array();
$crudarr["delete_state"] = true;
define('KeydefaultEncrypt', $main_config['encrypt']);
define('KeydefaultEncryptCookies', $main_config['encrypt_cookies']);

function PARSE_URI()
{

    if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
        return '';
    }
    // parse_url() returns false if no host is present, but the path or query string
    // contains a colon followed by a number
    $uri = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
    $query = isset($uri['query']) ? $uri['query'] : '';
    $uri = isset($uri['path']) ? $uri['path'] : '';

    if (isset($_SERVER['SCRIPT_NAME'][0])) {
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
    }

    // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
    // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
    if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
        $query = explode('?', $query, 2);
        $uri = $query[0];
        $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
    } else {
        $_SERVER['QUERY_STRING'] = $query;
    }

    parse_str($_SERVER['QUERY_STRING'], $_GET);

    if ($uri === '/' or $uri === '') {
        return '/';
    }

    // Do some final cleaning of the URI and return it
    return _remove_relative_directory($uri);
}

function _remove_relative_directory($uri)
{
    $uris = array();
    $tok = strtok($uri, '/');
    while ($tok !== FALSE) {
        if ((!empty($tok) or $tok === '0') && $tok !== '..') {
            $uris[] = $tok;
        }
        $tok = strtok('/');
    }

    return implode('/', $uris);
}


function uri($pos)
{
    $a = PARSE_URI();
    if (strpos($a, '/')  === false) {
        if ($pos == 0) {
            return str_replace('%20', ' ', PARSE_URI());
        } else {
            return "";
        }
    } else {
        return str_replace('%20', ' ', explode("/", PARSE_URI()))[$pos];
    }
}



function apidata()
{
    if ($_POST['data'] != "") {
        $getdata  = json_decode($_POST['data'], true);
    } else {
        $getdata = json_decode(file_get_contents('php://input'), true);
    }
    return $getdata;
}


function uri_open()
{
    return uri(0);
}

function uri_id1()
{

    return uri(1);
}
function uri_id2()
{
    return uri(2);
}


function uri_id3()
{
    return uri(3);
}

function uri_id4()
{
    return uri(4);
}


function use_model($model)
{
    if (file_exists(__DIR__ . '/../model/' . $model . '.php')) {
        require __DIR__ . '/../model/' . $model . '.php';
    } else {
        echo "Include model not found";
        exit();
    }
}

function library_mail()
{
    require(__DIR__ . '/library/smtp/PHPMailer/PHPMailerAutoload.php');
}


function library_img()
{
    require(__DIR__ . '/library/imgplug/class.php');
}

function dnow()
{
    return date("Y-m-d H:i:s");
}

function response_callback($result, $message)
{
    global $response;
    if (count($response["data"]) == 0) {
        unset($response["data"]);
    }
    $response["code"] = $result;
    $response["message"] = $message;
    echo json_encode($response);
    exit();
}
function view_data($param, $data)
{
    global $viewdata;
    array_push($viewdata[$param] = $data);
}

function push_response($param, $data)
{
    global $response;
    array_push($response[$param] = $data);
}

function push_response_data($data)
{
    global $response;
    array_push($response["data"], $data);
}
function push_view($param, $data)
{
    global $view_data;
    array_push($view_data[$param], $data);
}

function get_view($data)
{
    global $view_data;
    return $view_data[$data];
}

function link_to($link)
{
    global $main_config;
    $string = $main_config['base_url'] . $link;
    echo $string;
}

function base_theme()
{
    global $main_config;
    $string = $main_config['base_theme'];
    return $string;
}

function base_url()
{
    global $main_config;
    $string = $main_config['base_url'];
    return $string;
}

function CheckAuth()
{
    $Authentication = GetCookies("Authentication");
    if ($Authentication) {
        return true;
    } else {
        return false;
    }
}

// ENCRYPT
function curl_post($url, $fields = "")
{
    $headers = array(
        'priority : high',
        'Content-Type: application/json'
    );

    // Open connection
    $ch = curl_init();
    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
        $result = curl_error($ch);
        //  die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

// ENCRYPT
function curl_get($url, $fields = "")
{

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: 7278cbfa54b525f4d1d4356926d4a4e8"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return "Request error #:" . $err;
    } else {
        return $response;
    }
}


// ENCRYPT
function send_fcm($registatoin_ids, $msg, $title, $data)
{
    global $main_config;
    $msgdata = array();
    $notification = array();
    $notification['title'] = $title;
    $notification['body'] = $msg;
    $msgdata['data']['title'] = $title;
    $msgdata['data']['message'] = $msg;
    $msgdata['data']['data'] = $data;
    $msgdata['data']['timestamp'] = date("d-m-Y");
    $message = $msgdata;

    // Set POST variables

    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = array(
        'registration_ids' => $registatoin_ids,
        'data' => $message,
        'notification' => $notification,
    );
    $headers = array(
        'Authorization: key=' . $main_config[fcm_key],
        'Content-Type: application/json'
    );

    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
        $result = curl_error($ch);
        //  die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function encrypt($sData, $sKey = KeydefaultEncrypt)
{
    $sResult = '';
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }

    return encode_base64($sResult);
}

function decrypt($sData, $sKey = KeydefaultEncrypt)
{
    $sResult = '';
    $sData = decode_base64($sData);
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }

    return $sResult;
}

function encode_base64($sData)
{
    $sBase64 = base64_encode($sData);
    return strtr($sBase64, '+/', '-_');
}

function decode_base64($sData)
{
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64);
}

// Cookies function

function setMyCookies($name = "", $value = "")
{
    if (isset($name)) {
        if (isset($value)) {
            $name = mychangeChar1(myEncrypt($name));
            $value = myEncrypt($value);
        }

        setcookie($name, $value, time() + 86400, "/"); // Cookie aktif dalam kurun waktu 24 jam
    }
}

function getMyCookies($name = "")
{
    if (isset($name)) {
        $name = mychangeChar1(myEncrypt($name));
        $value = myDecrypt($_COOKIE[$name]);
    }

    return $value;
}

function clearMyCookies($name = "")
{
    if (isset($name)) {
        $name = mychangeChar1(myEncrypt($name));
        setcookie($name, "", time() + 86400, "/"); // Cookie aktif dalam kurun waktu 24 jam
    }
}

function SetCookies($name = "", $value = "")
{
    if (isset($name)) {
        if (isset($value)) {
            $name = mychangeChar1(myEncrypt($name));
            $value = myEncrypt($value);
        }

        setcookie($name, $value, time() + 86400, "/"); // Cookie aktif dalam kurun waktu 24 jam
    }
}

function GetCookies($name = "")
{
    if (isset($name)) {
        $name = mychangeChar1(myEncrypt($name));
        $value = myDecrypt($_COOKIE[$name]);
    }

    return $value;
}

function ClearCookies($name = "")
{
    if (isset($name)) {
        $name = mychangeChar1(myEncrypt($name));
        setcookie($name, "", time() + 86400, "/"); // Cookie aktif dalam kurun waktu 24 jam
    }
}

// Membuat fungsi untuk mengacak nilai

function myEncrypt($sData, $sKey = KeydefaultEncryptCookies)
{
    $sResult = '';
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }

    return my_encode_base64($sResult);
}

function myDecrypt($sData, $sKey = KeydefaultEncryptCookies)
{
    $sResult = '';
    $sData = my_decode_base64($sData);
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }

    return $sResult;
}

function my_encode_base64($sData)
{
    $sBase64 = base64_encode($sData);
    return strtr($sBase64, '+/', '-_');
}

function my_decode_base64($sData)
{
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64);
}

// Akhir fungsi
// Mengganti karakter huruf yang merusak perintah pemrograman

function mychangeChar1($value)
{
    if (!empty($value)) {
        $value = str_replace("&", "__and", $value);
        $value = str_replace("'", "__ptk", $value);
        $value = str_replace("$", "__dlr", $value);
        $value = str_replace("@", "__at", $value);
        $value = str_replace("(", "__bk1", $value);
        $value = str_replace(")", "__bk2", $value);
        $value = str_replace("[", "__kt1", $value);
        $value = str_replace("]", "__kt2", $value);
        $value = str_replace("{", "__kr1", $value);
        $value = str_replace("}", "__kr2", $value);
        $value = str_replace("|", "__lrs", $value);
        $value = str_replace("\\", "__gr1", $value);
        $value = str_replace("/", "__gr2", $value);
        $value = str_replace("?", "__tny", $value);
        $value = str_replace("!", "__sru", $value);
        $value = str_replace("#", "__pgr", $value);
        $value = str_replace("%", "__prc", $value);
        $value = str_replace("*", "__kl", $value);
        $value = str_replace("=", "__sm", $value);
        $value = str_replace(":", "__ttk2", $value);
        $value = str_replace(";", "__ttkk", $value);
        $value = str_replace(";", "__pls", $value);
    }

    return $value;
}

function mychangeChar2($value)
{
    if (!empty($value)) {
        $value = str_replace("__and", "&", $value);
        $value = str_replace("__ptk", "'", $value);
        $value = str_replace("__dlr", "$", $value);
        $value = str_replace("__at", "@", $value);
        $value = str_replace("__bk1", "(", $value);
        $value = str_replace("__bk2", ")", $value);
        $value = str_replace("__kt1", "[", $value);
        $value = str_replace("__kt2", "]", $value);
        $value = str_replace("__kr1", "{", $value);
        $value = str_replace("__kr2", "}", $value);
        $value = str_replace("__lrs", "|", $value);
        $value = str_replace("__gr1", "\\", $value);
        $value = str_replace("__gr2", "/", $value);
        $value = str_replace("__tny", "?", $value);
        $value = str_replace("__sru", "!", $value);
        $value = str_replace("__pgr", "#", $value);
        $value = str_replace("__prc", "%", $value);
        $value = str_replace("__kl", "*", $value);
        $value = str_replace("__sm", "=", $value);
        $value = str_replace("__ttk2", ":", $value);
        $value = str_replace("__ttkk", ";", $value);
        $value = str_replace("__pls", ";", $value);
    }

    return $value;
}

// DB FUNC

function deleteimg($dir, $name)
{
    if (unlink($dir . $name)) {
        return true;
    } else {
        return false;
    }
}

function upload($dir, $post, $name)
{
    $file_type_foto = array(
        'jpg',
        'jpeg',
        'png',
        'gif'
    );
    $folder_foto = $dir;
    if (move_uploaded_file($_FILES[$post]['tmp_name'], $folder_foto . $name)) {
        return true;
    } else {
        return false;
    }
}

function datafield($field, $value = "")
{
    global $DBDATAARRAY;
    if ($value == "") {
        $set = array(
            'field' => $field,
            'value' => post($field)
        );
    } else {
        $set = array(
            'field' => $field,
            'value' => $value
        );
    }
    array_push($DBDATAARRAY, $set);
}

function clearfield()
{
    global $con;
    global $DBDATAARRAY; // break references
    $DBDATAARRAY = array();
}

function post($string)
{
    global $con;
    return  mysqli_escape_string($con, $_POST[$string]);
}
function get($string)
{
    global $con;
    return  mysqli_escape_string($con, $_GET[$string]);
}

function insert($table)
{
    global $con;
    global $DBDATAARRAY;
    $li = sizeof($DBDATAARRAY);
    $saperator = "";
    $str_field = "";
    $str_data = "";
    $result = "0";
    for ($i = 0; $i < $li; $i++) {
        $str_field .= $saperator . $DBDATAARRAY[$i]["field"];
        $str_data .= $saperator . "'" . mysqli_escape_string($con, $DBDATAARRAY[$i]["value"]) . "'";
        $saperator = ",";
    }
    $str = "INSERT INTO $table($str_field)VALUES($str_data)";
    try {
        $result = mysqli_query($con, $str);
        clearfield();
    } catch (Exception $e) {
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $result;
}

function get_error()
{
    global $con;
    $result = mysqli_error($con);
    return $result;
}

function get_insert_id()
{
    global $con;
    $result = mysqli_insert_id($con);
    return $result;
}

function get_query()
{
    global $DBDEBUGQUERY;
    return $DBDEBUGQUERY;
}

function update2($table, $condition)
{
    global $con;
    global $DBDATAARRAY;
    $li = sizeof($DBDATAARRAY);
    $saperator = "";
    $str_field = "SET ";
    $str_data = "";
    $result = "0";
    if ($condition != "") $condition = "WHERE " . $condition;
    for ($i = 0; $i < $li; $i++) {
        $str_field .= "$saperator" . $DBDATAARRAY[$i]['field'] . "='" . mysqli_escape_string($con, $DBDATAARRAY[$i]['value']) . "'";
        $saperator = ",";
    }

    $str = "UPDATE $table $str_field $condition";
    try {
        $result = mysqli_query($con, $str);
        clearfield();
    } catch (Exception $e) {
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $result;
}

function delete2($table, $condition)
{
    global $con; //memanggil koneksi
    global $DBDATAARRAY;
    global $DBDEBUGQUERY;
    $li = sizeof($DBDATAARRAY);
    $saperator = "";
    $str_field = "SET ";
    $str_data = "";
    $result = "0";
    if ($condition != "") $condition = "WHERE " . $condition;
    $str = "DELETE FROM $table  $condition";
    try {
        $result = mysqli_query($con, $str);
    } catch (Exception $e) {
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $result;
}

function truncate_db($table)
{
    global $con; //memanggil koneksi
    global $DBDATAARRAY;
    global $DBDEBUGQUERY;
    $li = sizeof($DBDATAARRAY);
    $str = "TRUNCATE TABLE $table  ";
    $result = mysqli_query($con, $str);


    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    global $main_config;
    if ($main_config['DB_ERROR_LOG']) {
        if (get_error() != "") {
            global $error_report;
            push_error("Mysql error : " . get_error(), $error_report);
        }
    }
    return $result;
}


function selectsub($table, $condition = "", $DBDATAARRAY = "", $DBDATAARRAY2 = "")
{

    $condition = "WHERE " . $condition;
    if ($DBDATAARRAY2 == "") {
        $str = "(SELECT $DBDATAARRAY FROM $table $condition ) as $DBDATAARRAY";
    } else {

        $str = "(SELECT $DBDATAARRAY FROM $table $condition ) as $DBDATAARRAY2";
    }
    return $str;
}

function select2($table, $condition = "", $DBDATAARRAY = "")
{
    global $con;

    if ($DBDATAARRAY == "") {
        $DBDATAARRAY = "*";
    }
    if ($condition != "") $condition = "WHERE " . $condition;
    $str = "SELECT $DBDATAARRAY FROM $table $condition";
    try {
        $result = mysqli_query($con, $str);
        $rows = array();
        while ($row = mysqli_fetch_array($result)) {
            $rows[] = $row;
        }
    } catch (Exception $e) {
        return $e;
    }
    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $rows;
}

function select2_once($table, $condition = "", $DBDATAARRAY = "")
{
    global $con;

    if ($DBDATAARRAY == "") {
        $DBDATAARRAY = "*";
    }
    if ($condition != "") $condition = "WHERE " . $condition;
    $str = "SELECT $DBDATAARRAY FROM $table $condition";
    try {
        $result = mysqli_query($con, $str);
        $rows = array();
        while ($row = mysqli_fetch_array($result)) {
            $rows[] = $row;
        }
    } catch (Exception $e) {
        return $e;
    }
    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $rows[0];
}



function select2_db($table)
{
    global $con;
    $str = "SELECT * FROM $table ";
    try {
        $result = mysqli_query($con, $str);
        $rows = array();
        while ($row = mysqli_fetch_array($result)) {
            $rows[] = $row;
        }
    } catch (Exception $e) {
        return $e;
    }
    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $rows;
}



function update($condition, $table)
{
    global $con;
    global $DBDATAARRAY;
    $li = sizeof($DBDATAARRAY);
    $saperator = "";
    $str_field = "SET ";
    $str_data = "";
    $result = "0";
    if ($condition != "") $condition = "WHERE " . $condition;
    for ($i = 0; $i < $li; $i++) {
        $str_field .= "$saperator" . $DBDATAARRAY[$i]['field'] . "='" . mysqli_escape_string($con, $DBDATAARRAY[$i]['value']) . "'";
        $saperator = ",";
    }

    $str = "UPDATE $table $str_field $condition";
    try {
        $result = mysqli_query($con, $str);
        clearfield();
    } catch (Exception $e) {
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $result;
}

function delete($condition, $table)
{
    global $con; //memanggil koneksi
    global $DBDATAARRAY;
    global $DBDEBUGQUERY;
    $li = sizeof($DBDATAARRAY);
    $saperator = "";
    $str_field = "SET ";
    $str_data = "";
    $result = "0";
    if ($condition != "") $condition = "WHERE " . $condition;
    $str = "DELETE FROM $table  $condition";
    try {
        $result = mysqli_query($con, $str);
    } catch (Exception $e) {
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $result;
}

function select($DBDATAARRAY, $condition, $table)
{
    global $con;
    if ($condition != "") $condition = "WHERE " . $condition;
    $str = "SELECT $DBDATAARRAY FROM $table $condition";
    try {
        $result = mysqli_query($con, $str);
        $rows = array();
        while ($row = mysqli_fetch_array($result)) {
            $rows[] = $row;
        }
    } catch (Exception $e) {
        return $e;
    }

    global $DBDEBUGQUERY;
    $DBDEBUGQUERY = $str;
    return $rows;
}
// ROUTE APPLICATION

function pusher($data)
{
    require __DIR__ . '/library/pusher/vendor/autoload.php';

    $options = array(
        'cluster' => 'ap1',
        'useTLS' => true
    );
    $pusher = new Pusher\Pusher(
        '09037fbd9eb9c15c6b01',
        'e9980bfdea2db8c844c8',
        '1067252',
        $options
    );

    // $data['message'] = $data;
    $pusher->trigger('my-channel', 'my-event', $data);
}













function api_route()
{
    global $main_config;
    $act1 = uri_id1();
    $act2 = uri_id2();
    $act = uri_open();
    $act_1 = uri_open();
    $actlow_1 = strtolower($act_1);
    $act = ucfirst($actlow_1);
    $act_2 = uri_id1();
    $act_2 = str_replace("-", "_", $act_2);
    $actlow_2 = strtolower($act_2);
    $act1 = ucfirst($actlow_2);
    if (file_exists(__DIR__ . '/../app/' . $act . '.php')) {
        require_once 'app/' . $act . '.php';
        $define = new $act();
        if ($act1 != "") {
            if (method_exists($define, "$act1")) {
                $define->$act1();
            } else {
                return response_callback("500", "Opps something wrong, Please contact developer do fix it!");
            }
        } else {
            if (method_exists($define, "Index")) {
                $define->Index();
            } else {
                return response_callback("500", "Opps something wrong, Please contact developer do fix it!");
            }
        }
    } else {
        return response_callback("204", "Request Not Found");
    }
}

function init()
{
    if (uri_open() == '') {
        return response_callback("500", "Welcome, This is no place page !");
    } else {
        return api_route();
    }
}

init();
