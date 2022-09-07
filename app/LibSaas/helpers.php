<?php
function getPhoneNumber($text){

    //remove any whitespace
    $text = str_replace(' ','',$text);
    $text = trim($text);
    $text = str_replace(',','',$text);

    $array = str_split($text);
    $amount = array();
    $counter = 0;
    foreach ($array as $value)
    {
        if (is_numeric($value) || $value=='+')
        {
            @$amount[$counter] .= $value;
        }
        else
        {
            //$counter++;
        }


    }

    $price = @$amount[0];
    return purgeNumber($price);
}

function purgeNumber($number){
    $number = str_ireplace('+undefined0','0',$number);
    $number = str_ireplace('+undefined','0',$number);
    return $number;
}
function isImage($path)
{
    $a = getimagesize($path);
    $image_type = $a[2];

    if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
    {
        return true;
    }
    return false;
}

function boolToString($val){
    return (empty($val)) ? __('site.no'):__('site.yes');
}

function is_decimal( $val )
{
    return is_numeric( $val ) && floor( $val ) != $val;
}

function saveInlineImages($html){
    $savePath = 'uploads/'.WID.'/editor_images/'.date('m_Y');
    $saveUrl = url('/').'/'.$savePath;
    if(!file_exists($savePath)){
        mkdir($savePath,0777, true);
    }
    $dom = new \DOMDocument();

    @$dom->loadHTML($html);
    foreach($dom->getElementsByTagName('img') as $element){
        //This selects all elements
        $data = $element->getAttribute('src');



        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new \Exception('invalid image type');
            }

            $data = base64_decode($data);

            if ($data === false) {
                continue;
            }

            $fileName = time().rand(100,10000);
            file_put_contents($savePath."/{$fileName}.{$type}", $data);
            $element->setAttribute('src',$saveUrl.'/'.$fileName.'.'.$type);

        } else {
            continue;
        }



    }

    $body = "";
    foreach($dom->getElementsByTagName("body")->item(0)->childNodes as $child) {
        $body .= $dom->saveHTML($child);
    }

    return $body;


}

function saveSaasInlineImages($html){
    $savePath = SAAS_UPLOADS.'/editor_images/'.date('m_Y');
    $saveUrl = url('/').'/'.$savePath;
    if(!file_exists($savePath)){
        mkdir($savePath,0777, true);
    }
    $dom = new \DOMDocument();

    @$dom->loadHTML($html);
    foreach($dom->getElementsByTagName('img') as $element){
        //This selects all elements
        $data = $element->getAttribute('src');



        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new \Exception('invalid image type');
            }

            $data = base64_decode($data);

            if ($data === false) {
                continue;
            }

            $fileName = time().rand(100,10000);
            file_put_contents($savePath."/{$fileName}.{$type}", $data);
            $element->setAttribute('src',$saveUrl.'/'.$fileName.'.'.$type);

        } else {
            continue;
        }



    }

    $body = "";
    foreach($dom->getElementsByTagName("body")->item(0)->childNodes as $child) {
        $body .= $dom->saveHTML($child);
    }

    return $body;


}


function prevPage(){
    if(isset($_SERVER['HTTP_REFERER']))
    {
        return  $_SERVER['HTTP_REFERER'];
    }
    else{
        return 'javascript:history.go(-1)';
    }

}

function removeDirectory($path) {
    if(!is_dir($path)){
        return false;
    }
    $files = glob($path . '/*');
    foreach ($files as $file) {
        is_dir($file) ? removeDirectory($file) : unlink($file);
    }
    rmdir($path);
    return true;
}

function selfURL() {
    $s = empty($_SERVER["HTTPS"]) ? '' : (($_SERVER["HTTPS"] == "on") ? "s" : "");
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
}

function strleft($s1, $s2) {
    return substr($s1, 0, strpos($s1, $s2));
}

function safeUrl($url) {

    $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
    $url = trim($url, "-");
    $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
    $url = strtolower($url);
    $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
    return $url;
}

function safeFile($path){

    $info = pathinfo($path);

    $file = safeUrl($info['filename']);


    return $file.'.'.$info['extension'];
}

function uniqueName($path){
    $info = pathinfo($path);

    return uniqid().uniqid().'.'.$info['extension'];
}

function getCronUrl($url){
    try{
        ini_set('default_socket_timeout',1);
        file_get_contents($url);
    }
    catch(\Exception $ex)
    {

    }
}

function get_domain($url)
{
    try{
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
    }
    catch(\Exception $ex){

    }

    return false;
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

function limitLength($string,$maxLength){
    $string = strip_tags($string);
    if(strlen($string) <= $maxLength){
        return strip_tags($string);
    }
    else{
        return strip_tags(substr($string,0,$maxLength).'...');
    }

}

function br2nl($text){

    $breaks = array("<br />","<br>","<br/>");
    $text = str_ireplace($breaks, "\r\n", $text);
    return $text;
}

function resizeImage($filename, $width, $height,$basePath) {

    $dirImage = 'public/tmp/';
    $baseDir = 'public/';
    if (!file_exists($baseDir . $filename) || !is_file($baseDir . $filename)) {

        return;
    }


    $info = pathinfo($filename);

    $extension = $info['extension'];

    $old_image = $filename;
    $new_image = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

    if (!file_exists($dirImage . $new_image) || (filemtime($baseDir . $old_image) > filemtime($dirImage . $new_image))) {
        $path = '';

        $directories = explode('/', dirname(str_replace('../', '', $new_image)));

        foreach ($directories as $directory) {
            $path = $path . '/' . $directory;

            if (!file_exists($dirImage . $path)) {
                @mkdir($dirImage . $path, 0777);
            }
        }

        $image = new \App\Lib\Image($baseDir . $old_image);

        $image->resize($width, $height);
        $image->save($dirImage . $new_image);
    }


    return $basePath.'/tmp/'. $new_image;
}

function avatar($gender){
    if($gender=='m'){
        return asset('img/man.jpg');
    }
    else{
        return asset('img/woman.jpg');
    }
}

function userPic($userId){
    $user = \App\User::find($userId);
    if(!empty($user->picture) && file_exists($user->picture)){
        return $user->picture;
    }
    else{
        return avatar($user->gender);
    }
}


function gender($gender){
    if($gender=='m'){
        return __('site.male');
    }
    else{
        return __('site.female');
    }
}

function setting($key){

    $env = app(\Hyn\Tenancy\Environment::class);

    if ($fqdn = optional($env->hostname())->fqdn) {
        $setting = \App\Setting::where('key',trim(strtolower($key)))->first();
    }
    else{
        $setting = \App\Models\Setting::where('key',trim(strtolower($key)))->first();
    }


    if($setting){
        return trim($setting->value);
    }
    else{
        return false;
    }
}

function getFileMimeType($file){
    if(!function_exists('mime_content_type')) {

        function mime_content_type($filename) {

            $mime_types = array(

                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

            $ext = strtolower(array_pop(explode('.',$filename)));
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            }
            elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            }
            else {
                return 'application/octet-stream';
            }
        }
    }
    return mime_content_type($file);
}


function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        return false;
        //throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function getDepartment(){
    $id = session('department');

    if(empty($id) || !\App\Department::find($id)){
        return false;
    }

    return  \App\Department::find($id);
}

function isDeptAdmin($user){
    $admin = $user->departments()->where('department_id',getDepartment()->id)->first()->pivot->department_admin;

    if($admin==1){
        return true;
    }
    else{
        return false;
    }
}



function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

/*function price($amount){
    return number_format($amount,2);
}*/

function limit($limit){
    return empty($limit)? __('saas.unlimited'):$limit;

}

function defaultCurrency(){
    $currency = \App\Models\Currency::orderBy('is_default','desc')->first();
    if(!$currency){
        $currency = \App\Models\Currency::create([
            'country_id'=>235,
            'is_default'=>1,
            'exchange_rate'=>1
        ]);
    }

    return $currency;
}

function price($amount,$forcedCurrencyId=null){

    $amount = floatval(strip_tags($amount));

    $currencyId = session('currency_id');

    if(empty($currencyId)){
        $currencyId = defaultCurrency()->id;
    }

    //check if user is logged in
    if(\Illuminate\Support\Facades\Auth::check()){
        $user = \Illuminate\Support\Facades\Auth::user();
        if($user->subscriber()->exists())
        {
            $currencyId =  $user->subscriber->currency_id;
        }

    }

    if($forcedCurrencyId){
        $currencyId= $forcedCurrencyId;
    }

    $currency = \App\Models\Currency::find($currencyId);

    if(empty($currency->is_default)){
        $amount = $amount * $currency->exchange_rate;
    }



    if(is_decimal($amount)){
        return $currency->country->symbol_left.number_format($amount,2);
    }
    else{
        return $currency->country->symbol_left.number_format($amount);
    }

}

function priceRaw($amount,$forcedCurrencyId=null){

    //get currency in use
    $currencyId = session('currency_id');

    if($forcedCurrencyId){
        $currencyId= $forcedCurrencyId;
    }

    $currency = \App\Models\Currency::find($currencyId);

    if(empty($currency->is_default)){
        $amount = $amount * $currency->exchange_rate;
    }

    return $amount;

}

function showDate($format,$timestamp){
    if(!empty($timestamp)){
        return date($format,$timestamp);
    }
    else{
        return '';
    }
}

function paymentSetting($methodId,$key){

    $value = \App\Models\PaymentMethodField::where('payment_method_id',$methodId)->where('key',$key)->first()->value;
    return $value;
}

function getPaypalClient(){
    $clientId = paymentSetting(1,'client_id');
    $secret = paymentSetting(1,'secret');

    $paypal = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $clientId,
            $secret
        )
    );

    return $paypal;
}

function getMonthStr($offset)
{
    return date("M", strtotime("$offset months"));
}

function getPageAsync($url, $params = array('noparam'=>'true'), $type='GET')
{
    //echo "Attempting to get $url <br/>";

    $post_params= [];
    foreach ($params as $key => $val) {
        if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    if(isset($parts['query'])){


        $cusParams = $parts['query'];

        // Data goes in the path for a GET request
        if('GET' == $type) $parts['path'] .= '?'.$cusParams;
    }
    $out = "$type ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    // Data goes in the request body for a POST request
    if ('POST' == $type && isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
}


function sanitizeInput($html){

    $dom = new DOMDocument();

    $dom->loadHTML($html);

    $script = $dom->getElementsByTagName('script');

    $remove = [];
    foreach($script as $item)
    {
        $remove[] = $item;
    }

    foreach ($remove as $item)
    {
        $item->parentNode->removeChild($item);
    }

    $html = $dom->saveHTML();
    return $html;
}

function sasset($path){
    return asset('saas/'.$path);
}

function notifyAdmin($subj,$message){
    mail('info@caremate.app',$subj,$message);
}

function verifyOwner($model){
    $user = \Illuminate\Support\Facades\Auth::user();
    if($user->id != $model->user_id){
        abort(401);
    }
}

function dropWebsiteTables($wid){
    $db=[
        'host'=>config('database.connections.tenants.host'),
        'database'  => config('database.connections.tenants.database'),
        'username'  => config('database.connections.tenants.username'),
        'password'  => config('database.connections.tenants.password'),
    ];

    $conn = new \PDO("mysql:host=".$db['host'].";dbname=".$db['database'], $db['username'], $db['password']);
// set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $database = $db['database'];
    //attempt to delete all data from table

    $dsql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME LIKE '{$wid}_%'";

    $q = $conn->query($dsql);
    $q->setFetchMode(PDO::FETCH_ASSOC);

    while($row  = $q->fetch()) {
        $tbl = $row['TABLE_NAME'];
        $sql = "DELETE FROM `$tbl`";

        $conn->exec($sql);
    }

    //now drop all tables
    $conn->exec('SET FOREIGN_KEY_CHECKS = 0');

    $q = $conn->query($dsql);
    $q->setFetchMode(PDO::FETCH_ASSOC);
    while($row  = $q->fetch()) {
        $tbl = $row['TABLE_NAME'];
        $sql = "DROP TABLE `$tbl`";

        $conn->exec($sql);
    }

}
