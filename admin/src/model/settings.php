<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class settingsModel extends baseModel
{
    protected $config;

    public function __construct()
    {
        $this->closelink = 'projects';
    }

    public function getItems() {
        return qf::conf()->getItems(qf::conf()->getpath());
    }

    public function save() {
        $db = \JFactory::getDBO();

        $data = filter_input(INPUT_POST, 'qffield', FILTER_SANITIZE_FULL_SPECIAL_CHARS , FILTER_REQUIRE_ARRAY);

        if(!empty($data)){
            foreach ($data as $k => &$v) {
                if($k == 'filesmod') {
                    $type = (int) $v ? 'main' : 'qf_hidden_admin';
                    $db->setQuery("UPDATE #__menu SET menutype = '". $type ."' WHERE title = 'QF_ATTACHMENT_SETTINGS'");
                    $db->execute();
                }
                if($k == 'shopmod') {
                    $type = (int) $v ? 'main' : 'qf_hidden_admin';
                    $db->setQuery("UPDATE #__menu SET menutype = '". $type ."' WHERE title = 'QF_SHOP_SETTINGS'");
                    $db->execute();
                }
                $v = stripslashes($v);
            }
            $cod = '<?php return \''.json_encode($data, (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)) . '\';';
            file_put_contents(qf::conf()->getpath(), $cod);
        }
        else $this->errors[] =  'empty data';
    }
}
