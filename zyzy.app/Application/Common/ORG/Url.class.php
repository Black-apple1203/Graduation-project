<?php

class Url {

    /**
     * 替换URL参数
     */
    static public function replace($url, $options) {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $query_arr);
        foreach ($options as $key=>$val) {
            if (array_key_exists($key, $query_arr)) {
                $query_arr[$key] = $options[$key];
            }
        }
        $return = http_build_query($query_arr);
        if (false !== strpos($url, '?')) {
            $return = array_shift(explode('?', $url)) . '?' . $return;
        }
        return $return;
    }
}