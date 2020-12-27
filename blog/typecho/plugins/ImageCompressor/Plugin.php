<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once __DIR__ . '/lib/Compressor.php';

/**
 * ImageCompressor
 *
 * @package ImageCompressor
 * @author n2et@qq.com
 * @version 1.0.0
 * @link https://objs.net
 */
class ImageCompressor_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Upload')->beforeUpload = array('ImageCompressor_Plugin', 'beforeUpload');
        Typecho_Plugin::factory('Widget_XmlRpc')->beforeUpload = array('ImageCompressor_Plugin', 'beforeXmlRpcUpload');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

        $compressLevelField = new Typecho_Widget_Helper_Form_Element_Text('compressLevel', NULL, 9,
            _t('压缩Level（0-9，0表示不压缩）'));
        $form->addInput($compressLevelField);
    }

//    public static function configHandle($settings, $isInit)
//    {
//
//        if (!$isInit) {
//
//            $fileFieldName = self::FILE_FILED_NAME;
//            $bigfileFieldName = self::BIG_FILE_FIELD_NAME;
//
//            // 对file进行排序，Widget_Upload将使用array_pop的顺序处理文件
//            asort($_FILES);
//
//            $fileData = self::handleMarkFile($fileFieldName);
//            $bigFileData = self::handleMarkFile($bigfileFieldName);
//
//            $settings[$fileFieldName] = $fileData;
//            $settings[$bigfileFieldName] = $bigFileData;
//        }
//
//        Widget_Plugins_Edit::configPlugin('ImageHandler', $settings);
//    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 处理XmlRpc附件上传
     *
     * @param $imgInfo array
     * @return array
     * @throws Typecho_Exception
     */
    public static function beforeXmlRpcUpload($imgInfo)
    {

        // gif获取的图片，$imgInfo['type'] 为png
        if (!preg_match('/^image\//', $imgInfo['mime']) || $imgInfo['mime'] == 'image/gif') {
            return $imgInfo;
        }

        $config = Typecho_Widget::widget('Widget_Options')->plugin('ImageCompressor');

        $imgCompressor = new ImgCompressor();

        try {
            $newImagePath = $imgInfo['path']; //. 'compress.' . $imgInfo['type'];
            $newImageName = $imgInfo['name'];
            $result = $imgCompressor->set(
                __TYPECHO_ROOT_DIR__ . $imgInfo['path'],
                __TYPECHO_ROOT_DIR__ . $newImagePath
            )->compress((int)$config->compressLevel)->get();

            $imgInfo['path'] = $newImagePath;
            $imgInfo['name'] = $newImageName;
            $imgInfo['size'] = filesize(__TYPECHO_ROOT_DIR__ . $newImagePath);

        } catch (Exception $e) {
            print_r($e);
        }

        return $imgInfo;
    }

    public static function beforeUpload($img)
    {
        return self::beforeXmlRpcUpload($img);
    }


}
