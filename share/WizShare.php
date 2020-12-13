<?php

namespace n2et\typecho;

require_once __DIR__ . './Share.php';

use n2et\typecho\Share as Share;

class WizShare extends Share {

    public function formatGetPostDataUrl ($url) {
        return $url;
    }

    public function formatGetPostData ($code, $response) {
        $json = NULL;
        if (!$code) {
            $json = json_decode($response);

            if ($json) {
                $json = get_object_vars($json);

                foreach ($json as $key=>$value ) {
                    if (is_object($value)) {
                        $json[$key] = get_object_vars($value);
                    }
                }
            }
        }

        return [
            'success' => !!$json,
            'response' => $response,
            'data' => $json,
            'curlCode' => $code
        ];
    }

    public function formatSharePostData($postResponse)
    {

        $data = $postResponse['data'];
        $docData = $data['doc'];
        $doc = $docData['html'];

        $ret = [
            'title' => $docData['title'],
            'post_type' => 'post',
            'description' => $doc,
            'fieldNames' => ['shareFrom'],
            'fieldTypes' => ['str'],
            'fieldValues' => ['wizNote'],
            'dateCreated' => $docData['created'] / 1000,
            'categories' => []
        ];

        $formattedData = $this->execHandlers('formatSharePostData', $ret);

        if (!empty($formattedData)) {
            $ret = $formattedData;
        }

        return $ret;
    }

    public function getAttachmentList ($doc, $base = '') {

        $urls = [];
        preg_match_all('/src="(index_files\/[^"]+)"/', $doc, $urls);

        if (empty($urls[1])) {
            return [];
        }

        $urls = $urls[1];

        $ret = [];

        foreach ($urls as $key=>$value) {
            $name = [];
            preg_match('/\/(?P<name>[^\/]+$)/', $value, $name);

            if (empty($name['name'])) {
                continue;
            }
            $name = $name['name'];

            $ret[] = [
                'oriFile' => $value,
                'file' => $base . $value,
                'name' => $name
            ];
        }

        return $ret;
    }

    public function getAttachmentFiles ($files) {
        $ret = [];

        foreach ($files as $file) {
            $data = file_get_contents($file['file']);
            $file['data'] = !$data ? NULL : $data;
            if (!$data) {
                $file['readFileResponseStatus'] = $http_response_header[0];
            }
            $ret[] = $file;
        }

        return $ret;
    }

    public function formatResourceBaseUrl ($data) {
        return $data['ksServerUrl'] . '/ks/share/resources/' .
        $data['kbGuid'] . '/' . $data['documentGuid'] . '/';
    }

    public function getPostAttachments ($url, $postResponse = NULL) {
        $ret = [
            'success' => 0,
            'message' => '',
            'response' => '',
            'files' => []
        ];
        $doc = '';

        if (empty($postResponse)) {
            $postResponse = $this->getPostData($url);
        }

        if ($postResponse['success']) {
            $doc = $postResponse['data']['doc']['html'];
        }

        if (empty($doc)) {
            $ret['message'] = 'getPostData filed!';
            $ret['response'] = $postResponse;
            return $ret;
        }

        $base = $this->formatResourceBaseUrl($postResponse['data']);

        $attachments = $this->getAttachmentList($doc, $base);
        $files = $this->getAttachmentFiles($attachments);

        $ret['files'] = $files;
        $ret['success'] = 1;

        return $ret;
    }

    public function newPostAttachments ($url, $postResponse = NULL) {
        $ret = [
            'success' => 0,
            'message' => '',
            'response' => NULL,
            'files' => []
        ];

        $attachmentsResponse = $this->getPostAttachments($url, $postResponse);
        if (!$attachmentsResponse['success']) {
            $ret['success'] = 0;
            $ret['message'] = 'getPostAttachments failed!';
            $ret['response'] = $attachmentsResponse['response'];
            return $ret;
        }

        $handledFiles = [];
        $unhandledFiles = [];
        $files = $attachmentsResponse['files'];

        foreach ($files as $key => $file) {

            if (empty($file['data'])) {
                $unhandledFiles[] = $file;
                continue;
            }

            $rpcRet = $this->client->execute('newMediaObject', [
                'name' => $file['name'],
                'bits' => $file['data']
            ]);

            if (!$rpcRet['success'] || empty($rpcRet['data'])) {
                $unhandledFiles[] = $file;
                continue;
            }

            $data = $rpcRet['data'];

            $handledFiles[] = [
                'name' => $file['name'],
                'oriFile' => $file['oriFile'],
                'file' => $file['file'],
                'targetName' => $data['file'],
                'targetUrl' => $data['url']
            ];

        }

        $success = count($unhandledFiles) === 0;
        $ret['success'] = $success;
        $ret['message'] = $success ? '' : 'some attachments upload failed!';
        $ret['handled'] = $handledFiles;
        $ret['unhandled'] = $unhandledFiles;

        return $ret;
    }

    public function handlePostAttachments ($postResponse, &$docData) {
        $data = $postResponse['data'];
        $doc = $data['doc']['html'];

        // 上传文件中的图片等资源
        $attachmentResponse = $this->newPostAttachments(NULL, $postResponse);

        if ($attachmentResponse['success'] && count($attachmentResponse['handled'])) {
            $res = $attachmentResponse['handled'];
            $patterns = [];
            $replacements = [];
            foreach ($res as $key => $value) {
                $patternItem = $value['oriFile'];

                $patternItem = preg_replace('/\//', '\/', $patternItem);
                $patternItem = preg_replace('/\./', '\.', $patternItem);
                $patternItem = "/$patternItem/";

                $patterns[] = $patternItem;
                $replacements[] = $value['targetUrl'];
            }

            $replaceCount = 0;

            if (count($patterns)) {
                // 替换资源路径，让资源可以正常显示
                $doc = preg_replace($patterns, $replacements, $doc, -1, $replaceCount);
                $docData['description'] = $doc;
            }

            return $attachmentResponse;
        }
    }

    public function sharePost($url)
    {
        $url = $this->getPostApiUrl($url);
        return parent::sharePost($url);
    }

    public function getPostApiUrl ($url) {
        $url = preg_replace('/wapp\/pages\/view\/share\/s/', 'share/api/shares', $url);
        $url = preg_replace('/\?clientType=.+$/', '', $url);
        $url = $url . '?clientType=n2etBlogShare&clientVersion=1.0&lang=zh-cn';
        return $url;
    }

}