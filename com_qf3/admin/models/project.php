<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;
use Joomla\Registry\Registry;

class Qf3ModelProject extends JModelAdmin
{
    public function getTable($type = 'Project', $prefix = 'Qf3Table', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            $registry = new Registry($item->calculatorparams);
            $item->calculatorparams = $registry->toArray();

            $registry = new Registry($item->emailparams);
            $item->emailparams = $registry->toArray();

            $registry = new Registry($item->formparams);
            $item->formparams = $registry->toArray();
        }
        return $item;
    }


    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_qf3.project', 'project', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_qf3.edit.project.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_qf3.project', $data);

        return $data;
    }

    protected function createCSSfile($data)
    {
        if ($name = $data['formparams']['createcssfile']) {
            $pats = explode('.', $name);
            preg_match('/[a-zA-Z0-9_]+/i', $pats[0], $matches);

            if ($matches[0] != $pats[0]) {
                $this->setError('File not created. Invalid file name: '.$pats[0].'.css');
                return false;
            }

            $name = '/components/com_qf3/assets/css/'.$matches[0].'.css';

            if (file_exists(JPATH_SITE.$name)) {
                $this->setError($name.'<br>This file already exists.');
                return false;
            }

            if (isset($data['formparams']['copycssfile'])) {
                if (!file_exists(JPATH_SITE.'/components/com_qf3/assets/css/default.css')) {
                    $this->setError('The default.css file is missing.');
                    return false;
                }
                $def = file_get_contents(JPATH_SITE.'/components/com_qf3/assets/css/default.css');
                file_put_contents(JPATH_SITE.$name, str_replace('default', $matches[0], $def));
            } else {
                file_put_contents(JPATH_SITE.$name, "/**
                @package
                *Joomla & QuickForm
                */".PHP_EOL);
            }

            return ($matches[0].'.css');
        }
        $this->setError('File not created. Empty file name');
        return false;
    }

    public function save($data)
    {
        if ($data['formparams']['csschoose'] == 'n') {
            if (!$res = $this->createCSSfile($data)) {
                return false;
            }
            $data['formparams']['cssform'] = $res;
        }

        unset($data['formparams']['csschoose']);
        unset($data['formparams']['createcssfile']);
        unset($data['formparams']['copycssfile']);

        return parent::save($data);
    }


    protected function canDelete($record)
    {
        if (parent::canDelete($record)) {
            $db = $this->getDbo();

            $db->setQuery(
                    'DELETE FROM '.$db->quoteName('#__qf3_forms') .
                    ' WHERE '.$db->quoteName('projectid').' = '.(int) $record->id
                );
            $db->query();

            if ($db->getErrorNum()) {
                $this->setError($db->getErrorMsg());
                return false;
            }

            return true;
        } else {
            return false;
        }
    }
}
