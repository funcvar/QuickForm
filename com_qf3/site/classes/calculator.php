<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

abstract class qfCalculator
{
    public static function getCalculator($project, $data)
    {
        if(!$project->calculated) return false;

        $file = JPATH_COMPONENT.'/classes/calculator/'.$project->calculatorparams->calculatortype.'.php';
        if (file_exists($file)) {
            require_once($file);
        } else {
            die('calculator template not found');
        }

        $qfCalculator_tmpl =  new qfCalculator_tmpl;
        return $qfCalculator_tmpl->getTmpl($project, $data);
    }

    public function checkStr($str)
    {
        $str = str_replace(',', '.', $str);
        return preg_replace('/[^0-9()-.+<>!=:\?\*\/|%&]/', '', $str);
    }

    public static function qfErrormes($err = false)
    {
        static $errormes = array();
        if ($err) {
            $errormes[] = $err;
        } else {
            return $errormes;
        }
    }
}
