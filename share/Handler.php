<?php


namespace n2et\typecho;


class Handler
{
    static $sharedHandlers = [];
    static $sharedAlias = [];
    protected $handlers = NULL;
    protected $alias = NULL;

    public function __construct($options) {
        $this->handlers = [];
        $this->alias = [];
    }

    public function getHandlers($type, $share = true) {
        $handlers = array_key_exists($type, $this->handlers) ? $this->handlers[$type] : NULL;
        if (empty($handlers) && $share) {
            return Handler::getSharedHandlers($type);
        }

        return $handlers;
    }

    static function getSharedHandlers($type) {
        return array_key_exists($type, Handler::$sharedHandlers) ? Handler::$sharedHandlers[$type] : NULL;
    }

    // $fn 不需要通过引用传值？在remove比较的时候也不需要引用传值，使用===比较出来结果是true
    public function addHandler ($type, $fn, $alias = '') {
        $handlers = $this->getHandlers($type, false);

        if (empty($handlers)) {
            $this->handlers[$type] = [];
        }

        $this->handlers[$type][] = $fn;

        if (!empty($alias)) {
            $this->alias[$alias] = $type;
        }
    }

    static function addSharedHandler ($type, $fn, $alias = '') {
        $handlers = Client::getSharedHandlers($type);
        if (empty($handlers)) {
            Client::$sharedHandlers[$type] = [];
        }

        Client::$sharedHandlers[$type][] = $fn;

        if (!empty($alias)) {
            Client::$sharedAlias[$alias] = $type;
        }
    }

    public function execHandlers ($type, $data, $defaultHandler = NULL, $includeShared = true) {
        $target = $this->getAliasType($type, $includeShared);
        if (!empty($target)) {
            $type = $target;
        }

        return $this->execHandlersWithoutAlias($type, $data, $defaultHandler, $includeShared);
    }

    protected function getAliasType ($type, $includeShared = true) {

        $alias = array_key_exists($type, $this->alias) ? $this->alias[$type] : NULL;
        if (empty($alias) && $includeShared) {
            return array_key_exists($type, Handler::$sharedAlias) ? Handler::$sharedAlias[$type] : NULL;
        }

        return $alias;
    }

    protected function execHandlersWithoutAlias ($type, $data, $defaultHandler = NULL, $includeShared = true) {
        $handler = $this->getHandlers($type, $includeShared);
        if (empty($handler)) {

            if (!empty($defaultHandler)) {
                return $defaultHandler($type, $data);
            }

            return $data;
        }

        $ret = $data;
        foreach ($handler as $fn) {
            $nextData = $fn($type, $ret);
            if (!empty($nextData)) {
                $ret = $nextData;
            }
        }

        return $ret;
    }

    public function removeHandler ($type, $fn) {

    }

    public function __destruct() {

    }

}