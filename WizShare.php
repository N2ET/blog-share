<?php

namespace n2et\typecho;

require_once './Share.php';

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

    public function formatSharePostData($data)
    {

        $data = $data['data'];
        $data = $data['doc'];

        $ret = [
            'title' => $data['title'],
            'post_type' => 'post',
            'description' => $data['html'],
            'fieldNames' => ['shareFrom'],
            'fieldTypes' => ['str'],
            'fieldValues' => ['wizNote'],
            'dateCreated' => $data['created'] / 1000
        ];

        return $ret;
    }

    public function sharePost($url)
    {
        return parent::sharePost($url);
    }

}