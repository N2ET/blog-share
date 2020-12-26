<?php
/**
 * 为图片添加水印、压缩图片
 *
 * @package N2etWaterMark
 * @author n2et@qq.com
 * @version 1.0.0
 * @link https://objs.net
 */

// https://www.php.net/manual/en/function.imagecopymerge.php
function imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    // creating a cut resource
    $cut = imagecreatetruecolor($src_w, $src_h);

    // copying relevant section from background to the cut resource
    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

    // copying relevant section from watermark to the cut resource
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

    // insert cut resource to destination image
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
}


class N2etWaterMark
{
    const defaultConfig = [
        'markPosition' => 9,

//        'markFilePath' => '',
//        'bigMarkFilePath' => '',

        'markLimit' => [9999, 9999],
        'markBreakPoint' => [800, 400],

        'markPaddingH' => 10,
        'markPaddingV' => 10,
        'quality' => 100,

        'markFile' => [
            'path' => '',
            'fileInfo' => NULL,
            'markImg' => NULL
        ],
        'bigMarkFile' => [
            'path' => '',
            'fileInfo' => NULL,
            'markImg' => NULL
        ],

//        'markFileInfo' => NULL,
//        'markImg' => NULL,
//        'bigMarkFileInfo' => NULL,
//        'bigMarkImg' => NULL
    ];

    protected $config;

    public function __construct($config = [])
    {

        $this->config = array_merge(self::defaultConfig, $config);

        $this->init();
    }

    public function init ()
    {
//        if ($this->markFilePath) {
//            $this->markFileInfo = $this->getImgInfo($this->markFilePath);
//            $createImgFn = $this->getCreateImgFn($this->markFilePath);
//            $this->markImg = $createImgFn($this->markFilePath);
//        }
//
//        if ($this->bigMarkFilePath) {
//            $this->bigMarkFileInfo = $this->getImgInfo($this->bigMarkFilePath);
//            $createImgFn = $this->getCreateImgFn($this->bigMarkFilePath);
//            $this->bigMarkImg = $createImgFn($this->bigMarkFilePath);
//        } else {
//            $this->bigMarkFileInfo = $this->markFileInfo;
//            $this->bigMarkImg = $this->markImg;
//        }

        if (!empty($this->markFile['path'])) {
            $this->markFile = $this->initMarkImage($this->markFile);
        }

        if (!empty($this->bigMarkFile['path'])) {
            $this->bigMarkFile = $this->initMarkImage($this->bigMarkFile);
        } else {
            $this->bigMarkFile = $this->markFile;
        }

    }

    protected function initMarkImage ($markData) {
//        $data = [];
        if ($markData['path']) {
            $path = $markData['path'];
            $markData['fileInfo'] = $this->getImgInfo($path);
            $createImgFn = $this->getCreateImgFn($path);
            $markData['markImg'] = $createImgFn($path);
//            array_merge([], $markData, $data);
        }

        return $markData;
    }

    public function getMarkConfig ($path) {
        $breakPoint = $this->markBreakPoint;
        $info = $this->getImgInfo($path);
        $configKey = 'markFile';

        if ($breakPoint[0] * $breakPoint[1] < $info['width'] * $info['height']) {
            $configKey = 'bigMarkFile';
        }

        return $this->$configKey;
    }

    public function markImageWithoutLimit ($path, $pct = 100, $outputPath = false)
    {
        $markConfig = $this->getMarkConfig($path);
        $createImgFn = $this->getCreateImgFn($path);
        $img = $createImgFn($path);
        $position = $this->getMarkPosition($path);

//        imagecopymerge($img, $this->markImg, $position['left'], $position['top'],
//            0, 0, $this->markFileInfo['width'], $this->markFileInfo['height'], $pct);
        imagecopymergeAlpha($img, $markConfig['markImg'], $position['left'], $position['top'],
            0, 0, $markConfig['fileInfo']['width'], $markConfig['fileInfo']['height'], $pct);

        if ($outputPath === true) {
            $outputPath = $path;
        }

//        if ($this->quality !== 100) {
//            $img = imagejpeg($img, NULL, $this->quality);
//        }

        if ($outputPath) {
            $info = $this->getImgInfo($path);
            $this->saveImg($img, $outputPath, $info['type']);
            $this->destroyImg($img);
        }

        return [
            'file' => $outputPath ? $outputPath : '',
            'img' => !$outputPath ? $img : NULL
        ];
    }

    public function markImage ($path, $pct = 100, $outputPath = false) {
//        $markConfig = $this->getMarkConfig($path);

        return $this->markImageWithoutLimit($path, $pct, $outputPath);
    }

    public function saveImg ($img, $outputPath, $type) {
        $saveFn = 'image' . $type;
        $saveFn($img, $outputPath);
    }

    public function getCreateImgFn ($path)
    {
        $info = $this->getImgInfo($path);
        return 'imagecreatefrom' . $info['type'];
    }

    public function textMarkImage ()
    {

    }

    public function getMarkPosition ($path)
    {
        $markConfig = $this->getMarkConfig($path);
        $ret = [
            'left' => 0,
            'top' => 0
        ];
        $info = $this->getImgInfo($path);
        $markFileInfo = $markConfig['fileInfo'];

        $left = 0;
        $right = 0;
        switch ($this->markPosition)
        {
            case 9:
            default:
                $left = $info['width'] - $markFileInfo['width'] - $this->markPaddingH;
                $top = $info['height'] - $markFileInfo['height'] - $this->markPaddingV;
        }

        $ret['left'] = $left;
        $ret['top'] = $top;

        return $ret;
    }

    public function getImgInfo ($path)
    {
        $ret = [
            'width' => 0,
            'height' => 0,
            'type' => ''
        ];

        $info = getimagesize($path);

        $ret['width'] = $info[0];
        $ret['height'] = $info[1];

        $ret['type'] = [
            1 => 'gif',
            2 => 'jpeg',
            3 => 'png',
            6 => 'bmp'
        ][$info[2]];

        return $ret;
    }

    public function __destruct()
    {
        if ($this->markImg) {
            $this->destroyImg($this->markImg);
        }
    }

    public function destroyImg($img) {
        imagedestroy($img);
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->config)) {
            return NULL;
        }
        return $this->config[$name];
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }
}