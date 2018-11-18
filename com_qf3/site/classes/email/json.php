<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

class qfEmail_tmpl extends qfEmail
{
    public function getTmpl($project, $data, $calculator)
    {
        $html = '';
        if (! $project->emailparams->showtitle) {
            $html .= '<h3>' . $this->mlangLabel($project->title) . '</h3>';
        }

        if ($project->emailparams->showurl) {
            $link = JFactory::getApplication()->input->get('root', '', 'STRING');
            $html .= $this->mlangLabel('QF_SOURCE') . ': <a href="' . $link . '">'.$link.'</a><br><br>';
        }

        if ($project->calculatorparams->calculatortype) {
            $data ['sum'] = $calculator;
        }
        $html .= json_encode($data);
        return $html;
    }
}
