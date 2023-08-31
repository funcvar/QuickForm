<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

defined('_JEXEC') or die;

defined('QF3_PLUGIN_URL') or define('QF3_PLUGIN_URL', \JURI::root().'components/com_qf3/');
defined('QF3_PLUGIN_DIR') or define('QF3_PLUGIN_DIR', JPATH_ROOT.'/components/com_qf3/');
defined('QF3_ADMIN_DIR') or define('QF3_ADMIN_DIR', JPATH_ROOT.'/administrator/components/com_qf3/');

$xml = simplexml_load_file(QF3_ADMIN_DIR .'qf3.xml');
defined('QF3_VERSION') or define('QF3_VERSION', (string) $xml->version);
\JFactory::getLanguage()->load('com_qf3', QF3_ADMIN_DIR);

require_once(QF3_PLUGIN_DIR . 'classes.php');

class qf_admin
{
    protected $messages=array();
    protected $errors=array();
    protected $closelink;

    public function __construct()
    {
        require_once(QF3_ADMIN_DIR . 'src/controller.php');
        new controller();
    }

    public function get($name, $obj, $def='')
    {
        if (is_array($obj)) return isset($obj[$name]) ? $obj[$name] : $def; // php < 7.2
        return isset($obj->$name) ? $obj->$name : $def;
    }

    public function addScript($type, $file)
    {
        if ($type == 'css') {
            \JHtml::_('stylesheet', 'administrator/components/com_qf3/assets/' . $file, array('version' => QF3_VERSION));
        } elseif ($type == 'js') {
            \JHtml::_('script', 'administrator/components/com_qf3/assets/' . $file, array('version' => QF3_VERSION));
        }
    }

    public function getmessages()
    {
        $mess = array();
        if ($this->errors) {
            $mess['err'] = $this->errors;
        }
        if ($this->messages) {
            $mess['mes'] = $this->messages;
        }
        return $mess;
    }

	public function getLanguages()
	{
		$new[''] =  'Language';
		$languages = \JLanguageHelper::getLanguages();
		foreach($languages as $language) {
            $lang = str_replace('-', '_', $language->lang_code);
			$new[$lang] = $language->title_native;
		}
		return $new;
	}

    public function redirect($page)
    {
        header("Location: index.php?option=com_qf3&view=$page");
        exit;
    }

}

new qf_admin();
