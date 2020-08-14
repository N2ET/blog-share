<?php
    require_once '../share/WizShare.php';
    use n2et\typecho\WizShare as WizShare;

    $configFilePath = __DIR__ . '/config.json';

    $method = $_SERVER['REQUEST_METHOD'];

    $ret = [
        'success' => 0,
        'response' => NULL,
        'message' => ''
    ];

    if ($method === 'POST') {

        $str = file_get_contents('php://input');
        $data = json_decode($str);
        if (!empty($data)) {
            $data = get_object_vars($data);
            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    $data[$key] = get_object_vars($value);
                }
            }
        }
        $postUrl = array_key_exists('postUrl', $data) ? $data['postUrl'] : '';

        if (empty($postUrl)) {
            $ret['message'] = 'missing post url';
        } else {
            $share = new WizShare([
                'filePath' => $configFilePath
            ]);

            $postData = array_key_exists('data', $data) ? $data['data'] : NULL;

            $share->addHandler('formatSharePostData', function ($type, $data) use ($postData) {

                // 修改数据项，如标题，以界面中输入的为准
                if (!empty($postData)) {

                    foreach ($postData as $key => $value) {
                        if (!empty($value) && array_key_exists($key, $data)) {
                            $data[$key] = $value;
                        }
                    }

                }

                return $data;
            });

            $response = $share->sharePost($postUrl);

            $ret['success'] = $response['success'];
            if (!$ret['success']) {
                $ret['response'] = $response;
            }
        }

    }

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

    echo json_encode($ret);