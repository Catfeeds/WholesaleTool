<?php
/**
 * Created by PhpStorm.
 * User: qingCai
 * Date: 2018/8/7
 * Time: 10:43
 */
namespace app\common\tool;

class Curl
{
    private static $_ch;
    private static $_header;
    private static $_body;

    private static $_cookie  = [];
    private static $_options = [];
    private static $_url     = [];
    private static $_referer = [];

    /**
     * @description
     * @param        $queryUrl
     * @param string $param
     * @param string $method
     * @param bool   $is_json
     * @param bool   $is_urlcode
     * @return bool|mixed|string
     */
    public static function request($queryUrl, $param = [], $method = 'get', $is_json = true, $is_urlcode = true)
    {
        if (empty($queryUrl)) {
            return false;
        }
        $method = strtolower($method);
        $ret    = '';
        self::_init();

        switch ($method) {
            case 'get':
                $ret = self::_httpGet($queryUrl, $param);
                break;
            case 'post':
                $ret = self::_httpPost($queryUrl, $param, $is_urlcode);
                break;
            default:
                return false;
                break;
        }
        if (!empty($ret)) {
            if ($is_json) {
                return json_decode($ret, true);
            } else {
                return $ret;
            }
        }
        return true;
    }

    private static function _init()
    {
        self::$_ch = curl_init();

        curl_setopt(self::$_ch, CURLOPT_HEADER, true);
        curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt(self::$_ch, CURLOPT_FRESH_CONNECT, true);
    }

    public static function setOption($optArray = [])
    {
        foreach ($optArray as $opt) {
            curl_setopt(self::$_ch, $opt['key'], $opt['value']);
        }
    }

    private static function _close()
    {
        if (is_resource(self::$_ch)) {
            curl_close(self::$_ch);
        }

        return true;
    }

    private static function _httpGet($url, $query = [])
    {

        if (!empty($query)) {
            $url .= (strpos($url, '?') === false) ? '?' : '&';
            $url .= is_array($query) ? http_build_query($query) : $query;
        }

        curl_setopt(self::$_ch, CURLOPT_URL, $url);
        curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$_ch, CURLOPT_HEADER, 0);
        curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt(self::$_ch, CURLOPT_SSLVERSION, 1);

        $ret = self::_execute();
        self::_close();
        return $ret;
    }

    private static function _httpPost($url, $query = [], $is_urlcode = true)
    {
        if (is_array($query)) {
            foreach ($query as $key => $val) {
                if ($is_urlcode) {
                    $encode_key = urlencode($key);
                } else {
                    $encode_key = $key;
                }
                if ($encode_key != $key) {
                    unset($query[$key]);
                }
                if ($is_urlcode) {
                    $query[$encode_key] = urlencode($val);
                } else {
                    $query[$encode_key] = $val;
                }
            }
        }
        curl_setopt(self::$_ch, CURLOPT_URL, $url);
        curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$_ch, CURLOPT_HEADER, 0);
        curl_setopt(self::$_ch, CURLOPT_POST, true);
        curl_setopt(self::$_ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt(self::$_ch, CURLOPT_SSLVERSION, 1);

        $ret = self::_execute();
        self::_close();
        return $ret;
    }

    private static function _put($url, $query = [])
    {
        curl_setopt(self::$_ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        return self::_httpPost($url, $query);
    }

    private static function _delete($url, $query = [])
    {
        curl_setopt(self::$_ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return self::_httpPost($url, $query);
    }

    private static function _head($url, $query = [])
    {
        curl_setopt(self::$_ch, CURLOPT_CUSTOMREQUEST, 'HEAD');

        return self::_httpPost($url, $query);
    }

    private static function _execute()
    {
        $response = curl_exec(self::$_ch);
        $errno    = curl_errno(self::$_ch);

        if ($errno > 0) {
            throw new \Exception(curl_error(self::$_ch), $errno);
        }
        return $response;
    }
}
