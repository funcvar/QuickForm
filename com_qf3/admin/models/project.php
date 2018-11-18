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

    protected function prepareTable($table)
    {
        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
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
