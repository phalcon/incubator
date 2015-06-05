<?php

call_user_func(function () {
    $file = 'AbstractModel-PHP' . (version_compare(PHP_VERSION, '5.4.0') >= 0 ? '54' : '53') . '.php';

    require_once $file;
});
