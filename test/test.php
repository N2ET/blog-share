<?php

require_once '../share/WizShare.php';

use n2et\typecho\WizShare as WizShare;

$share = new WizShare([
    'filePath' => __DIR__ . '/config.json'
]);

$url = 'http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS2N3TQ-1RHkxz2alO0S3T8VZn';
$url = 'http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS2MSVJN0brkAZ2T6BTY3oxXH2';
$apiUrl = 'http://212.64.4.237:8888/share/api/shares/3fq2Mg1w4x7G2vMgYz38eGWS0Yip_L38Hkwu2Ubqb327t8oP?clientType=web&clientVersion=4.0&lang=zh-cn';
$apiUrl = 'http://212.64.4.237:8888/share/api/shares/3fq2Mg1w4x7G2vMgYz38eGWS3DTPkK0RMAU823lmeC1Kedf-?clientType=web&clientVersion=4.0&lang=zh-cn';

//$share->shareWizPost('http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS3DTPkK0RMAU823lmeC1Kedf-');
//$share->sharePost('http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS0Yip_L38Hkwu2Ubqb327t8oP');
$apiUrl = 'http://212.64.4.237:8888/share/api/shares/3fq2Mg1w4x7G2vMgYz38eGWS2MSVJN0brkAZ2T6BTY3oxXH2?clientType=web&clientVersion=4.0&lang=zh-cn';
//$ret = $share->sharePost($apiUrl);
$ret = $share->sharePost($url);


//$ret = $share->newPostAttachments($apiUrl);

print var_dump($ret);