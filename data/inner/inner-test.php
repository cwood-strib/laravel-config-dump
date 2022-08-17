<?php

function env(string $key, $fallback) {
}


function test($foo) {
    var_dump($foo);

    env("INNER_VALUE", "");
}

