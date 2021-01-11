<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * WizNote
 *
 * @package WizNote
 * @author n2et@qq.com
 * @version 1.0.0
 * @link https://www.objs.net
 */
class WizNote_Plugin implements Typecho_Plugin_Interface
{

    public static $shareFromKey = 'WizNote';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // file: var/Widget/Abstract/Contents.php
        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('WizNote_Plugin', 'formatContent');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {

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

    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){

    }

    /**
     * 使用iframe包裹笔记的html，防止跟博客样式冲突
     *
     * @access public
     * @return string
     */
    public static function formatContent($text, $post, $lastText)
    {

        if (!$lastText) {
            $lastText = $text;
        }

        $shareFromKey = $post->fields->shareFrom;
        if (empty($shareFromKey) || $shareFromKey != self::$shareFromKey) {
            return $lastText;
        }

        $id = $post->cid;
        $ret = <<<EOF
    <iframe id="wiznote-post_$id" style="border: none; width: 100%;">
        $lastText
    </iframe>
    <script type="template" style="display: none" id="wiznote-post_$id-text">
        $lastText
    </script>
    <script>
        (function () {
            var id = 'wiznote-post_$id';
            var iframe = document.querySelector('#' + id);
            var doc = iframe.contentWindow.document;
            var textDom = document.querySelector('#' + id + '-text');
            doc.documentElement.innerHTML = textDom.innerHTML.toString();
            textDom.remove();
            function updateHeight () {
                iframe.style.height = doc.documentElement.scrollHeight + 'px'; 
            }
            updateHeight();
            doc.body.querySelectorAll('img').forEach(function (node) {
                node.addEventListener('load', function () {
                    updateHeight(); 
                }); 
            });
        }());
    </script>
EOF;

        return $ret;
    }
}
