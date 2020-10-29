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

        $html .= $this->getSimplhtmlRows($data);

        if ($project->calculatorparams->calculatortype) {
            $html .= $this->getSum($calculator);
        }

        return $html;
    }

    protected function getSimplhtmlRows($data)
    {
        $html = '';
        foreach ($data as $field) {
            if (! isset($field->hide) || ! $field->hide) {
                if ($field->teg == 'cloner') {
                    foreach ($field->data as $row) {
                        $html .= $this->getSimplhtmlRows($row);
                    }
                } elseif ($field->teg == 'qftabs') {
                    $options = $field->options;
                    for ($n = 0; $n < sizeof($options); $n ++) {
                        $html .= "\r\n" . $this->mlangLabel($options[$n]->label) . "\r\n";
                        $html .= $this->getSimplhtmlRows($field->data[$n]);
                    }
                } elseif ($field->teg == 'customHtml') {
                    $html .= $this->mlangLabel($field->label) . "\r\n";
                } elseif ($field->teg == 'customPhp') {
                    if ($field->label) {
                        $html .= $this->mlangLabel($field->label) . "\r\n";
                    }
                    $html .= $this->mlangLabel($field->value) . "\r\n";
                } elseif (isset($field->hideone) && $field->hideone) {
                    if (isset($field->data) && ! empty($field->data)) {
                        $html .= $this->getSimplhtmlRows($field->data);
                    }
                } else {
                    $html .= $this->mlangLabel($this->letLable($field)) . ' : ';
                    $html .= $this->mlangLabel($field->value) . "\r\n";
                    if (isset($field->data) && ! empty($field->data)) {
                        $html .= $this->getSimplhtmlRows($field->data);
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
