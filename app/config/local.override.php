<?php

function d() {
    $args = func_get_args();
    if (count($args) == 0) {
        var_dump(reset($args));
    } else {
        var_dump($args);
    }
}
