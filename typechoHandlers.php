<?php

namespace n2et\typecho;

require_once './Handler.php';
use n2et\typecho\Handler as Handler;

Handler::addSharedHandler('metaWeblog.newPost', function ($method, $args) {
    $meta = $args['meta'];
    $data = $args['data'];

    $fiels = xmlrpc_encode_request($method, [
        $meta['blogId'],
        $meta['username'],
        $meta['pwd'],
        $data,
        true
    ], array('encoding'=>'UTF-8','escaping'=>'markup')); // 不加escaping会乱码

    return $fiels;
}, 'newPost');