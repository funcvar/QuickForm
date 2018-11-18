<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3TableProject extends JTable
{
    public function __construct(& $db)
    {
        parent::__construct('#__qf3_projects', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = (string)$registry;
        }
        if (isset($array['calculatorparams']) && is_array($array['calculatorparams'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['calculatorparams']);
            $array['calculatorparams'] = (string)$registry;
        }
        if (isset($array['emailparams']) && is_array($array['emailparams'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['emailparams']);
            $array['emailparams'] = (string)$registry;
        }
        if (isset($array['formparams']) && is_array($array['formparams'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['formparams']);
            $array['formparams'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    public function store($updateNulls = false)
    {
        if (!$this->id) {
            $this->ordering = $this->getNextOrder();
        }
        return parent::store($updateNulls);
    }

    public function check()
    {
        if (trim($this->title) == '') {
            $this->setError(JText::_('Error. Title not found'));
            return false;
        }

        return true;
    }
}
