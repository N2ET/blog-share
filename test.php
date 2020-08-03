<?php

require_once './WizShare.php';

use n2et\typecho\WizShare as WizShare;

$share = new WizShare([
    'filePath' => __DIR__ . '/config.json'
]);

//$share->shareWizPost('http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS3DTPkK0RMAU823lmeC1Kedf-');
//$share->sharePost('http://212.64.4.237:8888/wapp/pages/view/share/s/3fq2Mg1w4x7G2vMgYz38eGWS0Yip_L38Hkwu2Ubqb327t8oP');
$ret = $share->sharePost('http://212.64.4.237:8888/share/api/shares/3fq2Mg1w4x7G2vMgYz38eGWS0Yip_L38Hkwu2Ubqb327t8oP?clientType=web&clientVersion=4.0&lang=zh-cn');
print $ret['response'];