<?= self::before(); ?>
<?php

$a = [];

$props = explode(',', $_GET['pages'] ?? 'description,id,link,time,title,url,x');

foreach ($pages as $page) {
    $page_data = [];
    foreach ($props as $k) {
        // Exclude empty key
        if ("" === trim($k)) {
            continue;
        }
        // Exclude sensitive data
        if ('.' === $k[0] || '_' === $k[0] || 'comments' === $k || 'pass' === $k || 'path' === $k || 'token' === $k) {
            continue;
        }
        $v = $page->{$k} ?? null;
        $page_data[$k] = is_object($v) && method_exists($v, '__toString') ? (string) $v : ("" === $v ? null : o($v));
    }
    $a[] = $page_data;
}

State::set('layout-data.pages', $a);

?>
<?= self::after(); ?>