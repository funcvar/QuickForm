<?php
/**
* @package		Joomla
* @Copyright ((c) bigemot.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldDonation extends JFormField
{
    protected $type = 'donation';

    protected function getInput()
    {
        $donation_code = '<script type="text/javascript">jQuery(document).ready(function($) {$(\'#jform_cod\').parent()[0].style.visibility=($(\'#jform_display2\')[0].checked)?\'\':\'hidden\';$(\'#jform_cod-lbl\').parent()[0].style.visibility=($(\'#jform_display2\')[0].checked)?\'\':\'hidden\';$(\'#qflinck\')[0].style.visibility=($(\'#jform_display2\')[0].checked)?\'\':\'hidden\';$(\'#jform_display input\').click(function() {$(\'#jform_cod\').parent()[0].style.visibility=(this.id==\'jform_display2\')?\'\':\'hidden\';$(\'#jform_cod-lbl\').parent()[0].style.visibility=(this.id==\'jform_display2\')?\'\':\'hidden\';$(\'#qflinck\')[0].style.visibility=($(\'#jform_display2\')[0].checked)?\'\':\'hidden\';});$(\'#jform_cod-lbl\')[0].innerHTML=$(\'#jform_cod-lbl\')[0].innerHTML;});</script>';

        $lang = JFactory::getLanguage()->getTag();
        if($lang == 'ru-RU'){
          $link = '<a href="http://plasma-web.ru/dev/quickform" target="_blank" id="qflinck">';
        }
        else{
          $link = '<a href="http://plasma-web.ru/en/dev/quickform" target="_blank" id="qflinck">';
        }

        return $donation_code.$link.JText::_('QF_ENTER_CODE').' plasma-web.ru</a>';
    }

    protected function getLabel()
    {
        return;
    }


}
