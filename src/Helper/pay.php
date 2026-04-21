<?php

/**
 * Pay Hash
 * @param $hashParams
 * @param $storeKey
 * @return string
 */
function __pay_param_hash($hashParams, $storeKey) {
    ksort($hashParams, SORT_FLAG_CASE|SORT_STRING);

    $hashval = '';
    foreach ($hashParams as $key => $value) {
        $escapedParamValue = str_replace('|', "\\|", str_replace("\\", "\\\\", $value));

        $lowerParam = strtolower($key);
        if ($lowerParam != 'hash' && $lowerParam != 'encoding') {
            $hashval .= ($escapedParamValue . '|');
        }
    }

    $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
    $hashval .= $escapedStoreKey;

    $calculatedHashValue = hash('sha512', $hashval);
    $return = base64_encode(pack('H*', $calculatedHashValue));
    return $return;
}

function __pay_param_hash_v2($hashParams, $storeKey) {
    $hash = hash_hmac('sha512', $hashParams, $storeKey, true);
    return base64_encode($hash);
}

/**
 * Project json encode
 * @param object|array|null $json
 * @return string
 */
function __pay_json_encode(object|array|null $json) : string
{
    return json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?: '';
}

/**
 * Project json decode
 * @param string|null $json
 * @param bool $type
 * @return array|object
 */
function __pay_json_decode(string|null $json, bool $type = false) : array | object
{
    return json_decode($json, $type) ?: ($type ? [] : new \stdClass());
}

/**
 * Ip Address
 * @return mixed
 */
function __pay_ip() {
    return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}

/**
 * Date Time
 * @return string
 */
function __pay_date_time() {
    list($ms, $sec) = explode(' ', microtime());
    $ms = substr($ms, 2, 3);
    return gmdate('Y-m-d\TH:i:s', $sec) . '.' . $ms;
}

/**
 * Get Random Hash Number
 * @param $n
 * @return string
 */
function __pay_random_number_base16($n = 128)
{
    $characters = '0123456789ABCDEF';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return strtoupper($randomString);
}