<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once __DIR__ . '/lib/PluginFileField.php';
require_once __DIR__ . '/lib/WaterMark.php';

class ImageHandlerResponse extends Typecho_Response
{
    public $responseResult = NULL;

    public function throwJson($message)
    {
        if ($this->responseResult !== NULL) {
            return;
        }
        $this->responseResult = $message;
    }

    public function getResponseResult () {
        return $this->responseResult;
    }
}

class ImageHandlerUploader extends Widget_Upload
{
    public function __construct($request=NULL, $response=NULL, $params = NULL)
    {
        $request = new Typecho_Request();
        $response = new ImageHandlerResponse();
        parent::__construct($request, $response, $params);
    }

    public function uploadAttachment()
    {
        parent::upload();
        $ret = $this->response->getResponseResult();
        return $ret;
    }

    public function modifyAttachment($cid)
    {
        $this->request->setParam('cid', $cid);
        parent::modify();
        $ret = $this->response->getResponseResult();
        return $ret;
    }

    public function getAttachment($cid)
    {
        $this->request->setParam('cid', $cid);
        $edit = new Widget_Contents_Attachment_Edit($this->request, $this->response);
        try {
            $edit->execute();
        } catch (Typecho_Widget_Exception $e) {
            return NULL;
        }

        return [
            'cid' => $cid,
            'url' => $edit->row['attachment']->url,
            'path' => $edit->row['attachment']->path
        ];
    }
}

/**
 * image handler
 *
 * @package ImageHandler
 * @author n2et@qq.com
 * @version 1.0.0
 * @link https://objs.net
 */
class ImageHandler_Plugin implements Typecho_Plugin_Interface
{

    const FILE_FILED_NAME = '0_markFile';
    const BIG_FILE_FIELD_NAME = '1_bigMarkFile';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
//        Typecho_Plugin::factory('Widget_Upload')->upload = array('ImageHandler_Plugin', 'handleImage');
//        Typecho_Plugin::factory('Widget_XmlRpc')->upload = array('ImageHandler_Plugin', 'handleImage');

        Typecho_Plugin::factory('Widget_Upload')->beforeUpload = array('ImageHandler_Plugin', 'beforeUpload');
        Typecho_Plugin::factory('Widget_XmlRpc')->beforeUpload = array('ImageHandler_Plugin', 'beforeXmlRpcUpload');
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

        $form->setEncodeType(Typecho_Widget_Helper_Form::MULTIPART_ENCODE);

        $imageField = new Plugin_File_Field(self::FILE_FILED_NAME, NULL, [], _t('水印图片'));
        $bigImageField = new Plugin_File_Field(self::BIG_FILE_FIELD_NAME, NULL, [], _t('大尺寸水印图片'));
        $markTextField = new Typecho_Widget_Helper_Form_Element_Text('text', NULL, '',
            _t('水印文本，如存在图片则使用图片'));
        $limitField = new Typecho_Widget_Helper_Form_Element_Text('markLimit', NULL, '200 * 150',
            _t('添加水印的图片的最小尺寸（默认 200 * 150）'));
        $breakPointField = new Typecho_Widget_Helper_Form_Element_Text('markBreakPoint', NULL, '800 * 600',
            _t('水印的breakPoint（默认 800 * 600）'));
        $paddingHField = new Typecho_Widget_Helper_Form_Element_Text('markPaddingH', NULL, 10,
            _t('水印水平间距'));
        $paddingVField = new Typecho_Widget_Helper_Form_Element_Text('markPaddingV', NULL, 10,
            _t('水印垂直间距'));
        $opacityField = new Typecho_Widget_Helper_Form_Element_Text('opacity', NULL, 100,
            _t('透明度（1-100）'));
        $qualityField = new Typecho_Widget_Helper_Form_Element_Text('quality', NULL, 80,
            _t('图片质量（1-100）'));


        $form->addInput($imageField);
        $form->addInput($bigImageField);
        $form->addInput($markTextField);
        $form->addInput($limitField);
        $form->addInput($breakPointField);
        $form->addInput($qualityField);
        $form->addInput($opacityField);
        $form->addInput($paddingHField);
        $form->addInput($paddingVField);
    }

    public static function configHandle($settings, $isInit)
    {

        if (!$isInit) {

            $fileFieldName = self::FILE_FILED_NAME;
            $bigfileFieldName = self::BIG_FILE_FIELD_NAME;

            // 对file进行排序，Widget_Upload将使用array_pop的顺序处理文件
            asort($_FILES);

            $fileData = self::handleMarkFile($fileFieldName);
            $bigFileData = self::handleMarkFile($bigfileFieldName);

            $settings[$fileFieldName] = $fileData;
            $settings[$bigfileFieldName] = $bigFileData;
        }

        Widget_Plugins_Edit::configPlugin('ImageHandler', $settings);
    }

    public static function handleMarkFile ($fileFieldName) {
        $fileData = Typecho_Widget_Helper_Form_Element_File::getFileData($fileFieldName);
        $upload = new ImageHandlerUploader();
        $info = !empty($fileData['cid']) ? $upload->getAttachment($fileData['cid']) : NULL;

        if (array_key_exists($fileFieldName, $_FILES) && $_FILES[$fileFieldName]['name']) {

            $ret = false;
            if ($info) {
                $ret = $upload->modifyAttachment($fileData['cid']);
            } else {
                $ret = $upload->uploadAttachment();
            }

            if ($ret) {
                $newInfo = $upload->getAttachment($ret[1]['cid']);
                $fileData['url'] = $ret[0];
                $fileData['cid'] = $ret[1]['cid'];
                $fileData['path'] = $newInfo['path'];
            }
        } else if ($info) {
            $fileData['path'] = $info['path'];
        }

        return $fileData;
    }

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
        $config = Typecho_Widget::widget('Widget_Options')->plugin('ImageHandler');
        $fileFieldName = self::FILE_FILED_NAME;
        $bigfileFieldName = self::BIG_FILE_FIELD_NAME;

        $markFilePath = !empty($config->$fileFieldName['path']) ? self::getPath($config->$fileFieldName['path']) : '';
        if (!$markFilePath || !file_exists($markFilePath)) {
            return $imgInfo;
        }

        $bigMarkFilePath = !empty($config->$bigfileFieldName['path']) ? self::getPath($config->$bigfileFieldName['path']) : '';

        $waterMark = new N2etWaterMark([
            'markFile' => [
                'path' => $markFilePath
            ],
            'bigMarkFile' => [
                'path' => $bigMarkFilePath ? $bigMarkFilePath : ''
            ],

            'markPaddingH' => (int) $config->markPaddingH,
            'markPaddingV' => (int) $config->markPaddingV,
            'markBreakPoint' => self::formatArrayFileValue($config->markBreakPoint),
            'markLimit' => self::formatArrayFileValue($config->markLimit)
        ]);
        $imgPath = self::getPath($imgInfo['path']);
        $waterMark->markImage($imgPath, (int) $config->opacity, $imgPath);
        $imgInfo['size'] = filesize($imgPath);

        return $imgInfo;
    }

    public static function formatArrayFileValue ($str, $divider = '*', $toInt = true)
    {
        $str = preg_replace('/\s*/', '', $str);
        $ret = explode($divider, $str);

        if ($toInt) {
            foreach ($ret as $key => $value) {
                $ret[$key] = (int) $value;
            }
        }

        return $ret;
    }

    public static function getPath($path)
    {
        return __TYPECHO_ROOT_DIR__ . $path;
    }

    public static function beforeUpload($img)
    {
        return $img;
    }

    public static function handleImage($img)
    {
        $attachment = $img->attachement;
        if ($attachment->isImage) {

        }
    }
}
