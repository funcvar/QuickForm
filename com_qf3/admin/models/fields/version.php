<?php
/**
* @package		Joomla
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldVersion extends JFormField
{
    protected $type = 'version';

    protected function getInput()
    {
      $xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_qf3/qf3.xml');

      $lang = JFactory::getLanguage()->getTag();
      if($lang == 'ru-RU'){
        $link = '<a href="http://plasma-web.ru/dev/quickform3" target="_blank">plasma-web.ru</a>';
      }
      else{
        $link = '<a href="http://plasma-web.ru/en/dev/quickform3" target="_blank">plasma-web.ru</a>';
      }

      return (string)$xml->version.'<br />url: '.$link;

    }

    protected function getLabel()
    {
        return 'QuickForm 3 version:';
    }


}
 ?>
