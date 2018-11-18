<?php
/**
* @package		Joomla
* @Copyright ((c) bigemot.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldVersion extends JFormField
{
    protected $type = 'version';

    protected function getInput()
    {
      $xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_qf3/qf3.xml');
      $version = (string)$xml->version;
      $donation_code = $version;

      $lang = JFactory::getLanguage()->getTag();
      if($lang == 'ru-RU'){
        $link = '<a href="http://plasma-web.ru/dev/quickform" target="_blank">plasma-web.ru</a>';
      }
      else{
        $link = '<a href="http://plasma-web.ru/en/dev/quickform" target="_blank">plasma-web.ru</a>';
      }

      return $donation_code.'<br />url: '.$link;

    }

    protected function getLabel()
    {
        return 'QuickForm 3 version:';
    }


}
 ?>
