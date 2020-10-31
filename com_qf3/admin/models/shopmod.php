<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ModelShopmod extends JModelAdmin
{
    protected $arr;
    public $pathShopFile = JPATH_SITE.'/administrator/components/com_qf3/helpers/shopconfig.json';


    public function config() {
        $get_xml = simplexml_load_file(JPATH_SITE.'/administrator/components/com_qf3/models/forms/shopmod.xml');
        $this->XMLToArray($get_xml);

        $config = (array)$this->getItem();
        if(!$config) {
            return $this->arr;
        }

        foreach ($this->arr as $key => $value) {
            if(!isset($config[$key])) $config[$key] = $value;
        }

        return $config;
    }

    protected function XMLToArray($xml)
    {
        foreach ($xml as $k => $v) {
            if((is_object($v) || is_array($v)) && ($k != 'field')) $this->XMLToArray($v);
            elseif($k == 'field') {
                $name = isset($v['name'])?(string)$v['name']:'';
                $def = isset($v['default'])?(string)$v['default']:'';
                if($name){
                    $this->arr[$name]=$def;
                }
            }
        }
    }

    public function getItem($pk = null)
    {
        $file = $this->pathShopFile;
        if(file_exists($file)){
            return json_decode(file_get_contents($file));
        }
    }


    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_qf3.shopmod', 'shopmod', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_qf3.edit.shopmod.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_qf3.shopmod', $data);

        return $data;
    }

    public function createConfigFile($data = array()) {
        if(!empty($data)){
            file_put_contents($this->pathShopFile, json_encode($data, \JSON_UNESCAPED_UNICODE));
        }
        else return 'empty data';
    }


}
