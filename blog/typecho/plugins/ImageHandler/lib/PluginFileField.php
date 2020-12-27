<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Plugin_File_Field extends Typecho_Widget_Helper_Form_Element
{

    /**
     * 默认值
     * 5.6之前的版本array不能为const
     */
    public static $DEFAULT_VALUE = array(
        'url' => '',
        'cid' => ''
    );

    /**
     * 图片展示的默认样式
     */
    public static $DEFAULT_STYLE = array(
        'width' => '100px'
    );

    /**
     * 显示上传的图片元素
     *
     * @var Typecho_Widget_Helper_Layout
     * @access protected
     */
    protected $img;

    /**
     * 保存图片的url
     *
     * @var Typecho_Widget_Helper_Layout
     * @access protected
     */
    protected $urlField;

    /**
     * 保存图片的id
     *
     * @var Typecho_Widget_Helper_Layout
     * @access protected
     */
    protected $idField;

    /**
     * 初始化当前输入项
     *
     * @access public
     * @param string $name 表单元素名称
     * @param array $options 配置项
     * @return Typecho_Widget_Helper_Layout
     */
    public function input($name = NULL, array $options = NULL)
    {

        $style = array_merge(self::$DEFAULT_STYLE, $options && array_key_exists('style', $options) ? $options['style'] : []);
        $id =  $name . '-0-' . self::$uniqueId;
        $wrap = new Typecho_Widget_Helper_Layout('div');
        $img = new Typecho_Widget_Helper_Layout('img', array(
            'style' => 'width: ' . $style['width']. ';'
        ));
        $input = new Typecho_Widget_Helper_Layout('input', array(
            'id' => $id,
            'name' => $name,
            'type' => 'file',
            'class' => 'file'
        ));
        $idField = new Typecho_Widget_Helper_Layout('input', array(
            'type' => 'hidden',
            'style' => 'display: none;',
            'name' => $this->filedName('id')
        ));
        $urlField = new Typecho_Widget_Helper_Layout('input', array(
            'type' => 'hidden',
            'style' => 'display: none;',
            'name' => $this->filedName('url')
        ));

        $wrap->addItem($img);
        $wrap->addItem($input);
        $wrap->addItem($idField);
        $wrap->addItem($urlField);
        $this->container($wrap);
        $this->label->setAttribute('for', $id);

        $this->inputs[] = $input;
        $this->img = $img;
        $this->idField = $idField;
        $this->urlField = $urlField;

        return $input;
    }

    /**
     * 设置表单项默认值
     *
     * @access protected
     * @param mixed $value 表单项默认值
     * @return void
     */
    protected function _value($value)
    {
        if (empty($value)) {
            return;
        }
        $value = array_merge(self::$DEFAULT_VALUE, $value);
        $this->img->setAttribute('src', htmlspecialchars($value['url']));
        $this->idField->setAttribute('value', htmlspecialchars($value['cid']));
        $this->urlField->setAttribute('value', htmlspecialchars($value['url']));
    }

    /**
     * 获取表单元素的
     *
     * @access protected
     * @param $type
     * @return string
     */
    public function filedName ($type) {
        return $this->name . '_' . $type;
    }

    public static function getFileData ($name) {
        $idName = $name . '_id';
        $urlName = $name . '_url';
        return array(
            'cid' => key_exists($idName, $_POST) ? $_POST[$idName] : '',
            'url' => key_exists($urlName, $_POST) ? $_POST[$urlName] : ''
        );
    }
}
