<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ViewForm extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (!$this->item->id) {
            $this->item->projectid = JFactory::getApplication()->input->getInt("projectid");
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHtml::_('script', 'administrator/components/com_qf3/assets/script.js', array('version' => 'auto'));
        JHtml::_('stylesheet', 'administrator/components/com_qf3/assets/style.css', array('version' => 'auto'));

        $this->addToolbar();

        return parent::display($tpl);
    }


    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

        $canDo = JHelperContent::getActions('com_qf3');
        JToolbarHelper::title($isNew ? JText::_('QF_ADD_FIELDS') : JText::_('QF_EDIT_FIELDS'), 'bookmark banners');

        if ($isNew) {
            if ($isNew && $canDo->get('core.create')) {
                JToolbarHelper::apply('form.apply');
                JToolbarHelper::save('form.save');
            }

            JToolbarHelper::cancel('form.cancel');
        } else {
            if (!$checkedOut) {
                if ($canDo->get('core.edit')) {
                    JToolbarHelper::apply('form.apply');
                    JToolbarHelper::save('form.save');
                    JToolbarHelper::save2copy('form.save2copy');
                }
            }

            JToolbarHelper::cancel('form.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolBarHelper::custom('addfild', 'iconaddfild', '', '', false);

        JToolbarHelper::divider();
        JToolBarHelper::help('help', true);
    }
}
