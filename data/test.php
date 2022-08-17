<?php

function env(string $key, $fallback) {
}


function test($foo) {
    var_dump($foo);

    env("CALL_THIS", "");
}

