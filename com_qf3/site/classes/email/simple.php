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
          $html .= "\r\n" . $this->mlangLabel($project->title) . "\r\n\r\n";
      }

      if ($project->emailparams->showurl) {
          $link = JFactory::getApplication()->input->get('root', '', 'STRING');
          $html .= $this->mlangLabel('QF_SOURCE') . ': ' . $link . "\r\n\r\n";
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
                        $html .= "\r\n" . $this->mlangLabel($options[$n]->label) . "\r\n";
                        $html .= $this->getSimplRows($fild->data[$n]);
                    }
                } elseif ($fild->teg == 'customHtml') {
                    $html .= $this->mlangLabel($fild->label) . "\r\n";
                } elseif ($fild->teg == 'customPhp') {
                    if ($fild->label) {
                        $html .= $this->mlangLabel($fild->label) . "\r\n";
                    }
                    $html .= $this->mlangLabel($fild->value) . "\r\n";
                } elseif (isset($fild->hideone) && $fild->hideone) {
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getSimplRows($fild->data);
                    }
                } else {
                    $html .= $this->mlangLabel($this->letLable($fild)) . ' : ';
                        $html .= $this->mlangLabel($fild->value) . "\r\n";
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
            if (!$arr[1]->format) {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, ',', ' ');
            } elseif ($arr[1]->format == 1) {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, '.', ',');
            } else {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, '.', '');
            }

            $html .= $arr[1]->label . ' ';
            if ($arr[1]->pos) {
                $html .= $sum . ' ' . $arr[1]->unit . "\r\n";
            } else {
                $html .= $arr[1]->unit . ' ' . $sum . "\r\n";
            }
        }

        return $html;
    }

}
