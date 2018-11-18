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

      $html .= $this->getSimplRows($data);

      if ($project->calculatorparams->calculatortype) {
          $html .= $this->getSum($calculator);
      }

      return $html;

    }

    protected function getSimplRows($data)
    {
        $html = '';
        foreach ($data as $fild) {
            if (! isset($fild->hide) || ! $fild->hide) {
                if ($fild->teg == 'cloner') {
                    foreach ($fild->data as $row) {
                        $html .= $this->getSimplRows($row);
                    }
                } elseif ($fild->teg == 'qftabs') {
                    $options = $fild->options;
                    for ($n = 0; $n < sizeof($options); $n ++) {
                        $html .= '<br>' . $this->mlangLabel($options[$n]->label) . '<br>';
                        $html .= $this->getSimplRows($fild->data[$n]);
                    }
                } elseif ($fild->teg == 'customHtml') {
                    $html .= $this->mlangLabel($fild->label) . '<br>';
                } elseif ($fild->teg == 'customPhp') {
                    if ($fild->label) {
                        $html .= $this->mlangLabel($fild->label) . '<br>';
                    }
                    $html .= $this->mlangLabel($fild->value) . '<br>';
                } else {
                    $html .= $this->mlangLabel($this->letLable($fild)) . ' : ';
                    // if ($fild->teg == 'input[checkbox]') {
                    //     $html .= $fild->value ? (JText::_('JYES') . '<br>') : (JText::_('JNO') . '<br>');
                    // } else {
                        $html .= $this->mlangLabel($fild->value) . '<br>';
                    // }
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getSimplRows($fild->data);
                    }
                }
            }
        }
        return $html;
    }

    protected function getSum($calculator)
    {
        $html = '';
        foreach ($calculator as $arr) {
            $sum = number_format($arr [0], (int) $arr [1] [3], ',', ' ');
            $html .= $arr [1] [0] . ' ';
            if ($arr [1] [2]) {
                $html .= $sum . ' ' . $arr [1] [1] . '<br>';
            } else {
                $html .= $arr [1] [1] . ' ' . $sum . '<br>';
            }
        }

        return $html;
    }

}
