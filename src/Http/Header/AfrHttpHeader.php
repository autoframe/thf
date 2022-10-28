<?php

namespace Autoframe\Core\Http\Header;

trait AfrHttpHeader
{
    /**
     * @return array
     */
    public function getServerRequestHeaders(): array
    {
        if (function_exists('apache_request_headers')) {
            $aHeaders = apache_request_headers();
            if (empty($aHeaders)) {
                $aHeaders = [];
            }
        } else {
            $aHeaders = $this->readHeadersFromDollarServer();
        }
        return $aHeaders;
    }

    /**
     * @return array
     */
    protected function readHeadersFromDollarServer(): array
    {
        $aHeaders = [];
        $sPrefix = 'HTTP_';
        $iPrefixLen = strlen($sPrefix);
        foreach ($_SERVER as $sServerKey => $sValue) {
            if (substr($sServerKey, 0, $iPrefixLen) === $sPrefix) {
                $sHeaderName = substr($sServerKey, $iPrefixLen);
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $aHeaderNameParts = explode('_', trim($sHeaderName, ' _-'));
                if (count($aHeaderNameParts)) {
                    foreach ($aHeaderNameParts as $ak_key => $ak_val) {
                        $aHeaderNameParts[$ak_key] = ucfirst($ak_val);
                    }
                    $sHeaderName = implode('-', $aHeaderNameParts);
                }
                $aHeaders[$sHeaderName] = $sValue;
            }
        }
        return $aHeaders;
    }


    function hDownFile($f, $mode = 1, $downloadFilename = '')
    {
        // $mode=0 just the mime type and the length
        // $mode=1 attachement: If you want to encourage the client to download it instead of following the default behaviour. ;
        // $mode=2 inline: With inline, the browser will try to open the file within the browser.
        // $mode=2.5 inline with the file name header;
        // $mode=3 attachement with application/force-download
        //For example, if you have a PDF file and Firefox/Adobe Reader, an inline disposition will open the PDF within Firefox, whereas attachment will force it to download. If you're serving a .ZIP file, browsers won't be able to display it inline, so for inline and attachment dispositions, the file will be downloaded.

        if (!$downloadFilename) {
            $downloadFilename = basename($f);//get original filename
        }

        header('Content-Type: ' . get_mime_type($f));
        header('Content-Length: ' . filesize($f));
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s", filemtime($f)) . ' GMT');
        if ($mode == 2) {
            header('Content-Disposition: inline');
        }
        if ($mode == 2.5) {
            header('Content-Disposition: inline; filename=' . urlencode($downloadFilename));
        }
        if ($mode == 1 || $mode == 3) {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . urlencode($downloadFilename));
        }
        if ($mode == 3) {
            header('Content-Type: application/force-download');
        }

        $handle = @fopen($f, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                ob_start();
                echo $buffer;
                ob_end_flush();
            }
            if (!feof($handle)) {
                echo "Error: unexpected file read fail\r\n";
            }
            fclose($handle);
        } else {
            h503('The requested file can\'t be open!');
        }
        die();
    }

    public static function get_ntlm_link($user, $pass, $link)
    {
        return file_get_contents($user . '@' . $pass . ':' . $link);
    }

    public static function redirect300($code = 302, $location = '', $strip_params = false, $build_query = array())
    {
        //http_response_code(302); //https://www.php.net/manual/en/function.http-response-code.php

        if ($location == '') {
            $location = $_SERVER['REQUEST_URI'];
        }
        if (count($_POST) && ($code == '' || $code == 302)) {
            $code = 303;
        }
        if ($strip_params) {
            $location = explode('?', $location);
            $location = $location[0];
        }

        if (count($build_query)) {
            $loc_tmp = explode('?', $location);
            if (isset($loc_tmp[1]) && $loc_tmp[1]) {
                parse_str($loc_tmp[1], $curent);
            } else {
                $curent = array();
            }

            foreach ($build_query as $key => $val) {
                $curent[$key] = $val;
            }
            $location = $loc_tmp[0] . '?' . http_build_query($curent);
        }

        if (headers_sent() === false) {
            switch ($code) {
                case 301:
                    header("HTTP/1.1 301 Moved Permanently", true, $code);
                    break; /* Moved Permanently - Forget the old page existed. Convert to GET */
                case 302:
                    header("HTTP/1.1 302 Found", true, $code);
                    break; /* Found - Use the same method (GET/POST) to request the specified page.*/
                case 303:
                    header("HTTP/1.1 303 See Other", true, $code);
                    break; /* Use GET to request the specified page. Use this to redirct after a POST. */
                case 304:
                    header("HTTP/1.1 304 Not Modified", true, $code);
                    break; /* use cache */
                case 305:
                    header("HTTP/1.1 305 Use Proxy", true, $code);
                    break;
                case 306:
                    header("HTTP/1.1 306 unused", true, $code);
                    break;
                case 307:
                    header("HTTP/1.1 307 Temporary Redirect", true, $code);
                    break;/*use for GET/HEAD; ELSE: intreb user de redirect; nu folosi cu forms!*/
            }
            if ($code != 304) {
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            }
            header("Location: $location");
        } else {// Show the HTML?
            //$location=htmlspecialchars($location);
            $locationq = str_replace("'", "\'", $location);
            echo "<div style='background-color: #000; height: 100%; left: 0px; position: absolute; top: 0px; width: 100%;z-index: 98;'><div style='position: absolute; text-align: center; top: 0px; width: 95%; z-index: 99; background-color: #333; border: 2px solid #444; left: 0px; margin: 5px; padding: 3px; color:#FFF; '><p>Please Click: <a href='$location' style='color:#AAF;'>" . $location . "</a> (302 Redirection: Page Found!)</p></div>\n</div>";
            echo '<script language="JavaScript">document.location.replace("' . $locationq . '"); document.location="' . $locationq . '"; window.location="' . $locationq . '";</script>';
        }
        exit();
    }


    public static function json_encode_header($data, $die = 1)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        if ($die) {
            exit;
        }
    }

    public static function thf_server_protocol()
    {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on') {
            $isSecure = true;
        }
        return ($isSecure ? 'https' : 'http');
    }

    public static function thf_protocol_host_port()
    {
        if (thf_server_protocol() == 'https') {
            $host = 'https://' . $_SERVER['HTTP_HOST'];
            $host .= ($_SERVER['SERVER_PORT'] == 443 ? '' : ':' . $_SERVER['SERVER_PORT']);
        } else {
            $host = 'http://' . $_SERVER['HTTP_HOST'];
            $host .= ($_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']);
        }
        return $host;
    }


    public static function gzip_enc()
    {
        if (session_id() != NULL) return true;
        $ua = $_SERVER['HTTP_USER_AGENT']; // quick escape for non-IEs
        if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ') || false !== strpos($ua, 'Opera')) {
            return false;
        }
        $version = (float)substr($ua, 30);// no regex = faaast
        return ($version < 6 || ($version == 6 && false === strpos($ua, 'SV1')));
    }

    //gzip_enc() or ob_start("ob_gzhandler"); //inainte primul output


    public static function valid_header($url)
    {//folosesc pt a deschide un url ... evit 404 sau 500... in caz post & search...???
        $headers = @get_headers($url);
        if (!is_array($headers)) {
            return 0;
        }
        foreach ($headers as $val) {
            if (substr_count($val, 'HTTP') == 1 && substr_count($val, '200') == 1) {
                return 1;
            }
        }//header 200 return 1 else return 0
        return 0;
    }

    public static function do_post_request($url, $data = array(), $optional_headers = NULL, $die = 0)
    { //$data = str curatzat sau array necuretzat!
        //array: $data['query1']='merge& < ~ !';
        //str: $data= 'query1='.urlencode().'&'.'query2='.urlencode('merge& < ~ !')
        if (is_array($data)) {
            $i = 0;
            $data2 = '';
            foreach ($data as $key => $val) {
                $data2 .= ($i > 0 ? '&' : '') . $key . '=' . urlencode($val);
                $i++;
            }
            $data = $data2;
        }
        $params = array('http' => array('method' => 'POST', 'content' => $data));
        if ($optional_headers !== NULL) {
            $params['http']['header'] = $optional_headers;
        } /* "Accept-language: en\r\n" . "Cookie: foo=bar\r\n" */
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            echo "Problem with $url, $php_errormsg <br />\r\n";
            if ($die) {
                die;
            }
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            echo "Problem reading data from $url, $php_errormsg <br />\r\n";
            if ($die) {
                die;
            }
        }
        return $response;
    }

    public static function http_header_send($url, $method = 'GET', $optional_headers = NULL, $content = NULL, $follow_location = 0, $list_sent_headers = 0, $protocol = 'http')
    {
        //permise: $optional_headers = str(multiline)\r\n | array('prop: val','prop: val');     $content=str | post array($key=>$val,$key=>$val);
        if (is_array($content)) {
            $i = 0;
            $d2 = '';
            foreach ($content as $key => $val) {
                $d2 .= ($i > 0 ? '&' : '') . $key . '=' . urlencode($val);
                $i++;
            }
            $content = $d2;
        }
        $params[$protocol]['method'] = $method;
        $params[$protocol]['follow_location'] = $follow_location; //Follow Location: .. redirects.
        $params[$protocol]['ignore_errors'] = 1;  //Fetch the content even on failure status codes.
        if ($content !== NULL) {
            $params[$protocol]['content'] = $content;
        }
        if ($optional_headers !== NULL) {
            $params['http'][$protocol] = $optional_headers;
        } /* "Accept-language: en\r\n" . "Cookie: foo=bar\r\n" */
        //	prea($params);
        $ctx = stream_context_create($params);
        if ($list_sent_headers) {
            prea($params);
            prea($ctx);
        }
        $fp = fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            return "Problem with $url, $php_errormsg <br />\r\n";
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            return "Problem reading data from $url, $php_errormsg <br />\r\n";
        }
        //	prea($http_response_header);  The $http_response_header array is similar to the get_headers() function. When using the HTTP wrapper, $http_response_header will be populated with the HTTP response headers. $http_response_header will be created in the local scope.
        return array('header' => $http_response_header, 'content' => $response);
    }

    /*
    http://www.php.net/manual/en/context.http.php

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>array("Accept-language: en",
                               "Cookie: foo=bar",
                               "Custom-Header: value")
      )
    );

    $context = stream_context_create($opts); */


    public static function curl_header_send($url, $method = 'GET', $coookies = NULL, $post_content = NULL, $follow_location = 0, $list_sent_headers = 0, $ref = '')
    {
        $params = array(
            'url' => $url, //'http://google.ro'
            'host' => '',
            'header' => '',
            'method' => $method, // 'GET','POST','HEAD'
            'referer' => $ref,
            'cookie' => $coookies, // data1=abcd; data2=efgh;
            'post_fields' => $post_content, // 'var1=value&var2=value
            //		['login' => '',]
            //      ['password' => '',]
            'timeout' => 20);//max 20 sec exec
        //		prea($params);
        return make_curl_request($params, $follow_location, $list_sent_headers);
    }

    public static function make_curl_request($params, $follow_location = 0, $list_sent_headers = 0)
    {
        /*    $params = array('url' => 'http://www.google.com',
         *                    'host' => '',
         *                   'header' => '',
         *                   'method' => 'GET', // 'POST','HEAD'
         *                   'referer' => '',
         *                   'cookie' => '', // data1=abcd; data2=efgh;
         *                   'post_fields' => '', // 'var1=value&var2=value
         *                    ['login' => '',]
         *                    ['password' => '',]
         *                   'timeout' => 20
         *                   );
         */
        $ch = curl_init();
        $user_agent = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0';
        $header = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Charset: ISO-8859-1,UTF-8;q=0.7,*;q=0.7',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
        );

        if (isset($params['host']) && $params['host']) $header[] = "Host: " . $host;
        if (isset($params['header']) && $params['header']) $header[] = $params['header'];

        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_VERBOSE, 1);
        @curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($params['method'] == "HEAD") {
            @curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($params['referer']) {
            @curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
        }
        @curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        if (isset($params['cookie']) && strlen($params['cookie']) > 0) {
            @curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
        }
        if ($params['method'] == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post_fields']);
        }
        @curl_setopt($ch, CURLOPT_URL, $params['url']);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (isset($params['login']) && isset($params['password'])) {
            @curl_setopt($ch, CURLOPT_USERPWD, $params['login'] . ':' . $params['password']);
        }
        @curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
        //////////////////////////////////////////////////////////


        $response = curl_exec($ch);
        $error = curl_error($ch);
        $result = array('header' => '',
            'body' => '',
            'curl_error' => '',
            'http_code' => '',
            'last_url' => '');
        if ($error != "") {
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr($response, $header_size);
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);


        //	if ($result['curl_error'])    throw new Exception($result['curl_error']);
        //	if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
        //	if (!$result['body'])        throw new Exception("Body of file is empty");
        return $result;
    }

    public static function header_no_cache()
    {
        header('Cache-Control:	no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); // HTTP/1.1
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }

    function no_cache()
    {
        return header_no_cache();
    }

    public static function header_do_cache($seconds_to_cache = 2592000)
    {
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$seconds_to_cache");
    }


    public static function h301(string $loc)
    {
        http_response_code(301);
        header('Location: ' . $loc);
        die;
    }

    public static function h302($loc = '')
    {
        header('HTTP/1.1 302 Found', true, 302);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    //303redirect dupa ce postez ceva la un script pt rezultat....
    public static function h303($loc = '')
    {
        header('HTTP/1.1 303 See Other', true, 303);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    //307 miscat temporar ; intreb utilizatorul daca nu fac un request GET or HEAD; a nu se folosii forms
    public static function h307($loc = '')
    {
        header("HTTP/1.1 307 Temporary redirect", true, 307);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    public static function h404($msg = '', $exit = true)
    {
        header('HTTP/1.1 404 Not Found', true, 404);
        echo $msg != '' ? $msg : '<h1>Page not found! 404</h1>' . $_SERVER['REQUEST_URI'];
        if (is_int($exit)) {
            die($exit);
        } elseif ($exit) {
            die();
        };
    }

    public static function h405($html = 'HTTP/1.1 405 Method Not Allowed', $exit = true)
    {
        if (headers_sent() === false) {
            header("HTTP/1.1 405 Method Not Allowed", true, 405);
        }
        echo $html;
        if (is_int($exit)) {
            die($exit);
        } elseif ($exit) {
            die();
        }
    }

    public static function h410($msg = '')
    {
        header('HTTP/1.1 410 Gone', true, 410);
        echo ($msg != '') ? $msg : '<h1>Page GONE! 410</h1>';
        die;
    }    //sters definitiv, remove from user cache


    public static function e500($str = '')
    {
        @header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
        @header('Status: 500 Internal Server Error');
        @header('Retry-After: 60');
        echo '<title>' . $_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error</title>
	<style>
	*{background:none !important;}
	</style>
	';
        if ($str == '') {
            $str = '<h1> HTTP/1.1 500 Internal Server Error</h1><h3>Sorry, something went wrong. Please contact your IT provider.</h3>';
        } else {
            if (substr_count($str, '<') < 1 && substr_count($str, '>') < 1) {
                $str = "<h1> $str </h1>";
            }

        }
        //echo 'THF backtrace: on Line:<strong>'.__LINE__.'</strong> in File:<strong>'.__FILE__.'</strong> and Func:<strong>'.__FUNCTION__ .'</strong><h3>Extended:</h3>';
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        $repalce = thfString::extract_between($trace, 'e500(', ") called at ")[0];
        $trace = str_replace($repalce, '', $trace);
        $trace = str_replace("\r", '', $trace);
        $trace = str_replace("\n", "\n\n\n", $trace);

        echo($str . '<br />T:' . date('Y-m-d H:i:s') . "<hr>\n"); //	prea(get_defined_vars());		prea(get_declared_classes());		prea(get_declared_interfaces());		prea(get_defined_functions());
        die(nl2br($trace));
    }


    public static function h503($str = '')
    {
        $now = date('Y-m-d H:i:s');
        header('HTTP/1.1 503 Service Temporarily Unavailable', true, 503); //header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 60');
        if ($str == '') {
            $str = "\r\n\r\n <h1>Lucrari de mentenanta...</h1><h3>Revenim in curand!</h3>" . $now;
        } else {
            $str = "\r\n\r\n<h1> $str </h1><br />" . $now;
        }
        echo $str;
        die;
    }

    public static function pic_404($param = 0)
    {
        header("HTTP/1.1 404 Not Found");
        $filename = rpath() . 'upload/pics/0_404.jpg';
        if (!is_file($filename)) {
            die('404 NOT FOUND! NOT EVEN ERROR PICTURE IS PRESENT....');
        }
        $size = @filesize($filename);
        header('Content-Type: ' . get_mime_type($filename));
        header("Content-Length: " . $size);
        $handle = @fopen($filename, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                ob_start();
                echo $buffer;
                ob_end_flush();
            }
            if (!feof($handle)) {
                echo "Error: unexpected file read fail\r\n";
            }
            fclose($handle);
        }
        die();
    }


    public static function file_cache($file_path, $cached_control = 'public', $must_revalidate = false, $cache_expire = 2678400)
    {
        if (!is_file($file_path)) {
            header("HTTP/1.1 404 Not Found");
            die("<h1>404 Not found!</h1><br/>The file you are looking for is not available.");
        }
        //$cached_control='private';//not cachable
        //if(!is_numeric($cache_expire)){$cache_expire=60*60*24;}//o zi
        $headers = apache_request_headers();
        if (isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] == 'no-cache' ||
            isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache' ||
            isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0'
        ) { //the browser requested a clean page
            $cached_control = 'private';
            $cache_expire = 0;
            unset($headers['If-Modified-Since'], $headers['If-None-Match']);
        }

        $last_modf = filemtime($file_path);
        $size = filesize($file_path);
        $gmt_last_modf = gmdate("D, d M Y H:i:s", $last_modf) . ' GMT';
        $gmt_exp = gmdate("D, d M Y H:i:s", time() + $cache_expire) . ' GMT';
        $eTag = dechex(crc32($size)) . '-th-' . dechex(crc32($file_path)) . '-or-' . dechex(crc32($last_modf));

        //header('THF-Parameters: IfModifiedSince='.$headers['If-Modified-Since'].', IfNoneMatch='.$headers['If-None-Match']);

        header('Cache-Control: ' . $cached_control . ', max-age=' . $cache_expire . ($must_revalidate ? ', must-revalidate' : ''));//
        header('Pragma: ' . ($cached_control == 'private' ? 'no-cache' : 'cache'));
        header('ETag: "' . $eTag . '"');
        header('Last-Modified: ' . $gmt_last_modf);
        header('Expires: ' . $gmt_exp);


        if (isset($headers['If-Modified-Since']) && isset($headers['If-None-Match']) && $headers['If-Modified-Since'] == $gmt_last_modf && $headers['If-None-Match'] == '"' . $eTag . '"') {
            header('HTTP/1.1 304 Not Modified');
            header("Content-Length: 0");
        } else {
            //header('Age: '.(time()-$last_modf));
            $info = pathinfo($file_path);
            $content_type = get_mime_type($file_path);
            if (in_array(strtolower($info['extension']), (array('html', 'js', 'css', 'csv', 'txt')))) {
                $content_type .= ';  charset=utf-8';
            }
            header('Content-Type: ' . $content_type);
            header("Content-Length: " . $size);
            if ($size < 1024 * 1024 * 5) {
                echo file_get_contents($file_path);
                die;
            }

            $handle = @fopen($file_path, 'r'); //buffered output
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    ob_start();
                    echo $buffer;
                    ob_end_flush();
                }
                if (!feof($handle)) {
                    echo "Error: unexpected file read fail\r\n";
                }
                fclose($handle);
            }
        }
        die();
    }

    public static function get_mime_type($filename)
    {
        $fileext = substr(strrchr($filename, '.'), 1);
        if (empty($fileext)) return (false);
        $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
        $lines = file(__DIR__ . DIRECTORY_SEPARATOR . 'f_mime.types');
        foreach ($lines as $line) {
            if (substr($line, 0, 1) == '#') continue; // skip comments
            $line = rtrim($line) . " ";
            if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
            return ($matches[1]);
        }
        return ('application/octet-stream'); // no match at all
    }


    public static function stream_file($filename, $cahe = -1, $cache_expire = -1, $use_etag = true)
    {// $cahe=-1 system default, 0=no, 1=yes
        if (!is_file($filename)) {
            self::h404();
        }

        $last_modf = filemtime($filename);
        $size = filesize($filename);
        if ($cache_expire < 0) {
            $cache_expire = 60 * 60 * 24;
        }//o zi


        $gmt_last_modf = gmdate("D, d M Y H:i:s", $last_modf) . ' GMT';
        $gmt_exp = gmdate("D, d M Y H:i:s", time() + $cache_expire) . ' GMT';

        if ($use_etag && $cahe) {
            $headers = apache_request_headers();
            $eTag = dechex(crc32($size)) . '-th-' . dechex(crc32($filename)) . '-or-' . dechex(crc32($last_modf));
            header('ETag: "' . $eTag . '"');
        }
        if ($use_etag && $cahe &&
            isset($headers['If-None-Match']) && isset($headers['If-None-Match']) == '"' . $eTag . '"' &&
            (isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $gmt_last_modf || !isset($headers['If-Modified-Since']))
        ) {
            header('HTTP/1.1 304 Not Modified'); // use cache
        } else {
            if ($cahe > 0) {
                header_do_cache($cache_expire);
            } elseif (!$cahe) {
                header_no_cache();
            } else {
            }//fara modificari cache; foloseste session_cache_limiter(); si session_cache_expire();
            header('Content-Type: ' . get_mime_type($filename));
            header("Content-Length: " . $size);
            header('Last-Modified: ' . $gmt_last_modf);
            $handle = @fopen($filename, 'r');
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    ob_start();
                    echo $buffer;
                    ob_end_flush();
                }
                if (!feof($handle)) {
                    echo "Error: unexpected file read fail\r\n";
                }
                fclose($handle);
            } else {
                self::h503('The requested file can\'t be open!');
            }
        }
        exit();
    }

}