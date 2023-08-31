<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class shopModel extends baseModel
{
    protected $config;

    public function __construct()
    {
        $this->closelink = 'projects';
    }

    public function getItems() {
        return qf::conf()->getItems(qf::conf()->getpath('shop'));
    }

    public function save() {
        $data = filter_input(INPUT_POST, 'qffield', FILTER_SANITIZE_FULL_SPECIAL_CHARS , FILTER_REQUIRE_ARRAY);

        if(!empty($data)){
            foreach ($data as $k => &$v) {
                $v = stripslashes($v);
            }
            $cod = '<?php return \''.json_encode($data, (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)) . '\';';
            file_put_contents(qf::conf()->getpath('shop'), $cod);
        }
        else $this->errors[] =  'empty data';
    }
}
