<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class JFormFieldDisabledfiles extends JFormField
{
    protected $type = 'Disabledfiles';

    protected function getInput()
    {
        $qf_config = JComponentHelper::getParams('com_qf3');
        $dis = '';

        if (!$qf_config->get('filesmod')) {
            $dis = 'disabled';
            $this->value = 0;
        }

        $options[] = JHTML::_('select.option', '0', JText::_('JNO'));
        $options[] = JHTML::_('select.option', '1', JText::_('QF_ADD_FILES1'));
        $options[] = JHTML::_('select.option', '2', JText::_('QF_ADD_FILES2'));

        return JHtml::_('select.genericlist', $options, $this->name, $dis, 'value', 'text', $this->value, $this->id);
    }
}
