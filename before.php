<?php

if ($err = error_get_last()) {
    Alert::error($err['message'] ?? 'Unknown error.');
}

Lot::type('application/json');

$i = 60 * 60 * 24; // Cache output for a day
Lot::set(200 === ($status = Lot::status()) && !$err && empty($_GET['cache']) ? [
    'cache-control' => 'max-age=' . $i . ', private',
    'expires' => gmdate('D, d M Y H:i:s', time() + $i) . ' GMT',
    'pragma' => 'private'
] : [
    'cache-control' => 'max-age=0, must-revalidate, no-cache, no-store',
    'expires' => '0',
    'pragma' => 'no-cache'
]);

$a = [];

foreach ($alert as $k => $v) {
    $a[$v[2]['type']][] = $v[1];
}

$c = State::get(null, true);

$site_data = [];

foreach (explode(',', $_GET['site'] ?? 'are,can,description,has,is,not,title') as $k) {
    // Exclude empty key
    if ("" === trim($k)) {
        continue;
    }
    // Exclude sensitive data
    if ('.' === $k[0] || '_' === $k[0] || 'pass' === $k || 'token' === $k) {
        continue;
    }
    $site_data[$k] = o($c[$k] ?? null);
}

State::set('layout-data', array_replace([
    // Notification(s)
    'alert' => o($a),
    // Meta
    'generator' => 'Mecha ' . VERSION,
    // Document status
    'status' => $status,
    // Document title
    't' => $t->reverse->join(' | '),
    'url' => [
        'current' => $url->current,
        'next' => $pager->next ?? null,
        'prev' => $pager->prev ?? null
    ]
], $site_data));

if (200 === $status) {
    $page_data = [];
    foreach (explode(',', $_GET['page'] ?? 'description,id,link,time,title,url,x') as $k) {
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
    State::set('layout-data.page', $page_data);
    if (!empty($c['x']['tag']) && !empty($tag) && !empty($c['is']['tags'])) {
        $tag_data = [];
        foreach (explode(',', $_GET['tag'] ?? 'description,id,time,title,url,x') as $k) {
            // Exclude empty key
            if ("" === trim($k)) {
                continue;
            }
            // Exclude sensitive data
            if ('.' === $k[0] || '_' === $k[0] || 'comments' === $k || 'pass' === $k || 'path' === $k || 'token' === $k) {
                continue;
            }
            $v = $tag->{$k} ?? null;
            $tag_data[$k] = is_object($v) && method_exists($v, '__toString') ? (string) $v : ("" === $v ? null : o($v));
        }
        State::set('layout-data.tag', $tag_data);
    }
}