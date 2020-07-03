<?php

function camel_case($str, array $noStrip = []){
    // non-alpha and non-numeric characters become spaces
    $str =
        preg_replace('/[^a-z0-9'.implode("", $noStrip).']+/i', ' ', $str);
    $str = trim($str);
    // uppercase the first character of each word
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    $str = lcfirst($str);

    return $str;
}

function studly_case($str, array $noStrip = []){

    return ucfirst(camel_case($str, $noStrip));
}

function generate_token(){
    $str = str_shuffle(
        '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM'
    );

    return substr($str, rand(1, 58), 5);
}

function str_limit($str, $limit = 150){
    if (strlen($str) > $limit){
        $str = substr($str, 0, $limit) . '...';
    }
    return $str;
}

function rdot($str){

    return str_replace('.', '/', $str);
}