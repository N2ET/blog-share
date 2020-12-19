<?php

namespace n2et\typecho;

require_once __DIR__ . '/Handler.php';
use n2et\typecho\Handler as Handler;

Handler::addSharedHandler('metaWeblog.newPost', function ($method, $args) {
    $meta = $args['meta'];
    $data = $args['data'];

    xmlrpc_set_type($data['dateCreated'], 'datetime');

    $fiels = xmlrpc_encode_request($method, [
        $meta['blogId'],
        $meta['username'],
        $meta['pwd'],
        $data,
        true
    ], array('encoding'=>'UTF-8','escaping'=>'markup')); // 不加escaping会乱码

    return $fiels;
}, 'newPost');

Handler::addSharedHandler('metaWeblog.newMediaObject', function ($method, $args) {
    $meta = $args['meta'];
    $data = $args['data'];

    xmlrpc_set_type($data['bits'], 'base64');

    $data['bytes'] = $data['bits'];
    xmlrpc_set_type($data['bytes'], 'base64');
    $fiels = xmlrpc_encode_request($method, [
        $meta['blogId'],
        $meta['username'],
        $meta['pwd'],
        $data,
        true
    ], array('encoding'=>'UTF-8','escaping'=>'markup')); // 不加escaping会乱码

    return $fiels;
}, 'newMediaObject');