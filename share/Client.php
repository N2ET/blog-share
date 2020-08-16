<?php
/**
 * Typecho xmlRpc Client
 */


namespace n2et\typecho;

require_once __DIR__ . './Handler.php';
use n2et\typecho\Handler as Handler;

class Client extends Handler
{
    protected $blogId = 1;
    protected $username = '';
    protected $pwd = '';
    protected $curl = NULL;
    protected $url = '';

    public function __construct ($options) {

        parent::__construct($options);

        $this->updateOptions($options);
        $this->init();
    }

    public function updateOptions ($options) {
        if (empty($options)) {
            return;
        }

        foreach (['username', 'pwd', 'url'] as $key) {
            if (!empty($options[$key])) {
                $this->$key = $options[$key];
            }
        }

    }

    public function init () {

        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_HEADER, false); // 啥作用？
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml'
        ));
        $this->curl = $curl;
    }

    public function execute ($method, $params) {

        $data = $this->execHandlers('beforeExec', $params, function ($type, $data) {
            return $data;
        });
//        $data = $this->execHandlers($method.'/beforeExec', $data);

        $meta = [
            'url' => $this->url,
            'blogId' => $this->blogId,
            'username' => $this->username,
            'pwd' => $this->pwd
        ];

        $rpcData = $this->execHandlers($method, [
            'data' => $data,
            'meta' => $meta,
            'method' => $method
        ], function ($method, $args) {

            $meta = $args['meta'];
            $data = $args['data'];

            $fields = xmlrpc_encode_request($method, [
                $meta['blogId'],
                $meta['username'],
                $meta['pwd'],
                $data
            ], array('encoding'=>'UTF-8','escaping'=>'markup'));

            return $fields;
        });

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $rpcData);
        $response = curl_exec($this->curl);
        $data = xmlrpc_decode($response);

        $code = curl_errno($this->curl);

        return [
            'success' => $code === 0,
            'code' => $code,
            'response' => $response,
            'data' => $data
        ];
    }

    public function __destruct () {
        parent::__destruct();

        curl_close($this->curl);
    }
}