<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * CopyrightInfo
 *
 * @package CopyrightInfo
 * @author n2et@qq.com
 * @version 1.0.0
 * @link https://objs.net
 */
class CopyrightInfo_Plugin implements Typecho_Plugin_Interface
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
        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('CopyrightInfo_Plugin', 'addCopyrightInfo');
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

    public static function getTemplate ()
    {
        $template = <<<EOF
<div>
    <div>转载请注明文章来源</div>
    </div>本文地址 \$postLink</div>    
</div>
EOF;

    }

    public static function execTemplate ($template, $post)
    {
        $ret = '';

        $postLink = $post['link'];

        eval('$ret = ' . $template);

        return $ret;
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

        $templateField = new Typecho_Widget_Helper_Form_Element_Textarea('templateField', NULL, 9,
            self::getTemplate());
        $form->addInput($templateField);
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
     * 添加copyright信息
     *
     * @param $text string
     * @param $post object
     * @return array
     * @throws Typecho_Exception
     */
    public static function addCopyrightInfo($text, $post)
    {

        return $text;
    }

}
