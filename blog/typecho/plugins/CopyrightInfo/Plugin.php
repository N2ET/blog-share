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
<div class=\"copyright-plugin\">
    <div class=\"copyright-plugin_desc\">转载请注明文章来源</div>
    <div class=\"copyright-plugin_link\">本文地址：\$postLink</div>    
</div>
<style type=\"text/css\">
    .copyright-plugin, .copyright-plugin * {
        margin: initial!important;
        line-height: 1.2!important;
    }
    
    .copyright-plugin {
        font-size: 14px!important;
        margin-top: 30px!important;
    }
    .copyright-plugin_desc {
        
    }
    .copyright-plugin_link {
    
    }
</style>
EOF;

        return $template;
    }

    public static function execTemplate ($template, $post)
    {

        $ret = '';

        $postLink = $post->permalink;

        eval('$ret = "' . $template . '";');

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

        $templateField = new Typecho_Widget_Helper_Form_Element_Textarea('template', NULL, self::getTemplate(),
            _('版权信息模板'));
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
     * @param $lastText string
     * @return string
     */
    public static function addCopyrightInfo($text, $post, $lastText)
    {

        $config = Typecho_Widget::widget('Widget_Options')->plugin('CopyrightInfo');
        $template = $config->template;

        $copyrightText = self::execTemplate($template, $post);

        if (!$lastText) {
            $lastText = $text;
        }

        $text = $lastText . $copyrightText;

        return $text;
    }

}
