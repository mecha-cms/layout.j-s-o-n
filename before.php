<?php

if ($err = error_get_last()) {
    Alert::error($err['message'] ?? 'Unknown error.');
}

Lot::type('application/json');

$i = 60 * 60 * 24; // Cache output for a day
Lot::set(200 === ($status = Lot::status()) && !$err && empty($_GET['cache']) ? [
    'Cache-Control' => 'max-age=' . $i . ', private',
    'Expires' => gmdate('D, d M Y H:i:s', time() + $i) . ' GMT',
    'Pragma' => 'private'
] : [
    'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store',
    'Expires' => '0',
    'Pragma' => 'no-cache'
]);

$a = [];

foreach ($alert as $k => $v) {
    $a[$v[2]['type']][] = $v[1];
}

$c = State::get(null, true);

$site_data = [];

foreach (explode(',', $_GET['site'] ?? 'title,description,can,are,has,is,not') as $k) {
    // Exclude empty key
    if ("" === trim($k)) {
        continue;
    }
    // Exclude sensitive data
    if ('.' === $k[0] || '_' === $k[0] || 'token' === $k) {
        continue;
    }
    $site_data[$k] = o($c[$k] ?? null);
}

State::set('layout-data', array_replace([
    // Document status
    'status' => $status,
    // Notification(s)
    'alert' => o($a),
    // Document title
    't' => $t->reverse->join(' | '),
    // Meta
    'generator' => 'Mecha ' . VERSION,
    'url' => [
        'next' => $pager->next ?? null,
        'prev' => $pager->prev ?? null,
        'self' => $url->current
    ]
], $site_data));

if (200 === $status) {
    $page_data = [];
    foreach (explode(',', $_GET['page'] ?? 'id,title,description,time,link,url,x') as $k) {
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
        foreach (explode(',', $_GET['tag'] ?? 'id,title,description,time,url,x') as $k) {
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
