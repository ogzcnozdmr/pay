<?php

/**
 * Pay Hash
 * @param $hashParams
 * @param $storeKey
 * @return string
 */
function __pay_param_hash($hashParams, $storeKey) {
    echo "function param hash";
    echo "<pre>";
    print_r($hashParams);
    echo "</pre>";
    echo "store key = ".$storeKey."<br>";
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

    echo "hashval = ".$hashval."<br> - ";

    $calculatedHashValue = hash('sha512', $hashval);
    $return = base64_encode(pack('H*', $calculatedHashValue));
    return $return;
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