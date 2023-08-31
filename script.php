<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

class com_qf3InstallerScript
{
    public function install($parent)
    {
        $src = $parent->getParent()->getPath('source');
        copy($src.'/demo/example3.css', JPATH_ROOT . '/components/com_qf3/assets/css/example3.css');
        $status = $this->qfinstall($parent);
        $this->installDemo();
    }

    public function update($parent)
    {
        $status = $this->qfinstall($parent);
    }

    public function qfinstall($parent) {
        $db = JFactory::getDBO();
        $manifest = $parent->getParent()->manifest;
        $status = array();
        $src = $parent->getParent()->getPath('source');

        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            $installer = new JInstaller;
            $result = $installer->install($src.'/plugins/'.(string)$plugin->attributes()->plugin);
            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote((string)$plugin->attributes()->plugin)." AND folder=".$db->Quote((string)$plugin->attributes()->group);
            $db->setQuery($query);
            $db->execute();
            $status[] = array('name' => 'plugin ' . (string)$plugin->attributes()->name, 'result' => $result);
        }

        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $installer = new JInstaller;
            $result = $installer->install($src.'/modules/'.(string)$module->attributes()->module);
            $status[] = array('name' => 'module ' . (string)$module->attributes()->name, 'result' => $result);
        }


        return $status;

    }


    public function uninstall($parent) {
        $db = JFactory::getDBO();
        $status = array();
        $manifest = $parent->getParent()->manifest;

        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote((string)$plugin->attributes()->plugin)." AND folder = ".$db->Quote((string)$plugin->attributes()->group);
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions))
            {
                foreach ($extensions as $id)
                {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('plugin', $id);
                }
                $status[] = array('name' => 'plugin ' . (string)$plugin->attributes()->name, 'result' => $result);
            }

        }
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module)
        {
            $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote((string)$module->attributes()->module)."";
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions))
            {
                foreach ($extensions as $id)
                {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('module', $id);
                }
                $status[] = array('name' => 'module ' . (string)$module->attributes()->name, 'result' => $result);
            }

        }
        $db->setQuery("DELETE FROM #__menu WHERE menutype = 'qf_hidden_admin'");
        $db->execute();

    }




    private function installDemo() {
        // Example.

        $res = $this->qfinsert(array(
                'id' => 1,
                'title' => 'Example 3. The simplest calculator.',
                'params' => '{"cssform":"example3.css","jsform":"qf3.js","thnq_message":"Hi.","tmpl":"default","showtitle":1,"showurl":1,"calculatortype":"multipl","calcformula":"sv = {2.1}+0;\r\n2.2 = 2*3.14*{sv};\r\n2.3 = 3.14*{sv}*{sv};\r\n\r\ncv = {3.0}+0;\r\n3.1 = 4*{cv};\r\n3.2 = {cv}*{cv};\r\n\r\n1.3 = ({2.3}+{3.2})*{1.2}","history":1}'
        ), 'projects');

        if($res) {
            $this->qfinsert(array(
                'id' => 1,
                'title' => 'main',
                'fields' => '[{"teg":"input[radio]","fildnum":"1","orient":"1","label":"What do you calculate?","options":[{"related":"2","label":"Circle"},{"related":"3","label":"Square"}]},{"fildnum":4,"teg":"customHtml","label":"<div class=\"qf_centr\">at 1 euro per sq. m.</div>"},{"teg":"qf_number","fildnum":"2","orient":"1","custom":" min=\"0\" value=\"1\"","math":"v","label":"Quantity"},{"teg":"calculatorSum","fildnum":"3","unit":"€","fixed":"2","label":"Total cost:"}]',
                'projectid' => 1,
                'def' => 1
            ), 'forms');

            $this->qfinsert(array(
                'id' => 2,
                'title' => 'Circle',
                'fields' => '[{"teg":"qf_range","fildnum":"1","custom":"value=\"38\"","math":"v","label":"Circle radius, m"},{"fildnum":4,"teg":"customHtml","label":"<hr>"},{"teg":"calculatorSum","fildnum":"2","pos":"1","unit":"m","fixed":"1","format":"2","class":"","label":"Perimeter:"},{"teg":"calculatorSum","fildnum":"3","pos":"1","unit":"m<sup>2</sup>","fixed":"1","format":"2","label":"Circle area:"},{"fildnum":5,"teg":"customHtml","label":"<hr>"}]',
                'projectid' => 1,
                'def' => 0
            ), 'forms');

            $this->qfinsert(array(
                'id' => 3,
                'title' => 'Square',
                'fields' => '[{"teg":"qf_range","fildnum":"0","custom":"value=\"38\"","math":"v","label":"Square side, m"},{"fildnum":3,"teg":"customHtml","label":"<hr>"},{"teg":"calculatorSum","fildnum":"1","pos":"1","unit":"m","fixed":"1","format":"2","class":"","label":"Perimeter:"},{"teg":"calculatorSum","fildnum":"2","pos":"1","unit":"m<sup>2</sup>","fixed":"1","format":"2","label":"Square area:"},{"fildnum":4,"teg":"customHtml","label":"<hr>"}]',
                'projectid' => 1,
                'def' => 0
            ), 'forms');

        }

    }

    protected function qfinsert($columns, $tbl) {
        $db = JFactory::getDBO();
        $v_key='';
        $v_value='';
        foreach($columns as $key=>$value){
            $v_key.=",$key";
            $v_value.=is_int($v_value)?','.$value:','.$db->quote($value);
        }
        $v_key=substr($v_key, 1);
        $v_value=substr($v_value, 1);

        $db->setQuery('INSERT INTO #__qf3_'.$tbl.' ('.$v_key.') VALUES ('.$v_value.')');
        $db->execute();
        return $db->insertid();
    }


    public function preflight($type, $parent)
    {
        $filename = JPATH_ADMINISTRATOR .'/components/com_qf3/qf3.xml';

        if(is_file($filename)) {
            $db = JFactory::getDBO();

          $db->setQuery("UPDATE #__menu SET menutype = 'main' WHERE title = 'QF_ATTACHMENT_SETTINGS'");
          $db->execute();
          $db->setQuery("UPDATE #__menu SET menutype = 'main' WHERE title = 'QF_SHOP_SETTINGS'");
          $db->execute();
                }

        return true;
    }

    public function postflight($type, $parent)
    {
        $old[] = JPATH_ADMINISTRATOR .'/components/com_qf3/language/en_GB.php';
        $old[] = JPATH_ADMINISTRATOR .'/components/com_qf3/language/ru_RU.php';
        $old[] = JPATH_ROOT .'/components/com_qf3/language/ru_RU.php';
        $old[] = JPATH_ROOT .'/components/com_qf3/language/en_GB.php';
        foreach($old as $lng) {
          if (is_file($lng)) unlink($lng);
        }

        $db = JFactory::getDBO();
        $cnf = array('filesmod' => 0, 'shopmod' => 0);

        $filename = JPATH_ROOT .'/components/com_qf3/qf3.php';

        if(is_file($filename)) {
            require_once($filename);
            $cnf['filesmod'] = QuickForm\qf::conf()->get('filesmod');
            $cnf['shopmod'] = QuickForm\qf::conf()->get('shopmod');
        }

        if(! $cnf['filesmod']) {
            $db->setQuery("UPDATE #__menu SET menutype = 'qf_hidden_admin' WHERE title = 'QF_ATTACHMENT_SETTINGS'");
            $db->execute();
        }

        if(! $cnf['shopmod']) {
            $db->setQuery("UPDATE #__menu SET menutype = 'qf_hidden_admin' WHERE title = 'QF_SHOP_SETTINGS'");
            $db->execute();
        }
    }

}
