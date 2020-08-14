<?php

namespace n2et\typecho;

require_once __DIR__ . './Handler.php';
use n2et\typecho\Handler as Handler;

require_once __DIR__ . './Client.php';
use n2et\typecho\Client as Client;

require_once __DIR__ . './typechoHandlers.php';

class Share extends Handler {
    protected $filePath = '';
    protected $config = NULL;
    protected $client = NULL;
    protected $handlersMap = [
        'newPost' => 'newPost',
        'newMediaObject' => 'newMediaObject'
    ];

    public function __construct($options)
    {
        parent::__construct($options);

        $this->updateOptions($options);
        $this->readConfig();
        $this->initClient();
    }

    public function updateOptions($options) {
        if (empty($options)) {
            return;
        }

        $this->filePath = $options['filePath'];
    }

    public function readConfig () {
        $content = file_get_contents($this->filePath);
        $this->config = get_object_vars(
            json_decode($content)
        );
    }

    public function initClient () {
        $config = $this->config;
        $this->client = new Client($config);
    }

    public function formatGetPostDataUrl ($url) {
        return $url;
    }

    public function formatGetPostData ($code, $response) {

        return [
            'data' => NULL,
            'success' => $code === 0,
            'response' => $response,
            'curlCode' => $code
        ];
    }


    public function getPostData ($url) {
        $curl = curl_init(
            $this->formatGetPostDataUrl($url)
        );

        // 60错误
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $code = curl_errno($curl);
        curl_close($curl);

        $ret = $this->formatGetPostData($code, $response);
        return $ret;
    }

    public function formatSharePostData ($data) {
        return $data;
    }

    public function getHandlerName ($name) {
        return $this->handlersMap[$name];
    }

    public function handlePostAttachments ($postResponse, &$docData) {
        return NULL;
    }

    public function sharePost ($url) {
        $ret = [
            'success' => 0,
            'message' => '',
            'response' => NULL
        ];

        $data = $this->getPostData($url);

        if (!$data['success']) {
            $ret['message'] = 'getPostData failed!';
            $ret['response'] = $data['response'];
            return $ret;
        }

        $doc = $this->formatSharePostData($data);

        $attachmentsResponse = $this->handlePostAttachments($data, $doc);
        if (!empty($attachmentsResponse)) {
            $ret['attachments'] = $attachmentsResponse;
        }

        $rpcRet = $this->client->execute(
            $this->getHandlerName('newPost'),
            $doc
        );
        $postId = NULL;

        if ($rpcRet['success']) {
            $id = (int) $rpcRet['data'];
            if (is_int($id) && $id > 0) {
                $postId = $id;
            }
        }

        if ($postId) {
            $ret['success'] = 1;
        } else {
            $ret['message'] = 'share failed!';
        }

        $ret['response'] = $rpcRet['response'];
        $ret['data'] = $postId ? $postId : NULL;

        return $ret;
    }

}

