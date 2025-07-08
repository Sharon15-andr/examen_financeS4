<?php
if (php_sapi_name()==='cli-server') {
    $file = __DIR__.$_SERVER['REQUEST_URI'];
    if (is_file($file)) return false;
}
require __DIR__.'/index.php';
