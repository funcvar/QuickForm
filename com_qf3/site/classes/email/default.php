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
            $html .= '<h3>' . $project->title . '</h3>';
        }
        if ($project->emailparams->showurl) {
            $link = JFactory::getApplication()->input->get('root', '', 'STRING');
            $html .= 'QF_SOURCE' . ': <a href="' . $link . '">'.$link.'</a><br><br>';
        }
        $html .= '<table border="1" width="100%" style="max-width:800px;border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
        $html .= $this->getRows($data);
        if ($project->calculatorparams->calculatortype) {
            $html .= $this->getTrSum($calculator);
        }
        $html .= '</table>';
        return $html;
    }

    protected function getRows($data)
    {
        $html = '';
        foreach ($data as $field) {
            if ($field->hide != 1) {
                if ($field->teg == 'cloner') {
                    $html .= $this->getRowsCloner($field);
                } elseif ($field->teg == 'qftabs') {
                    $options = $field->options;
                    for ($n = 0; $n < sizeof($options); $n ++) {
                        $html .= '<tr><td colspan="2">' . $options[$n]->label . '</td></tr>';
                        $html .= $this->getRows($field->data[$n]);
                    }
                } elseif ($field->teg == 'customHtml') {
                    $html .= '<tr><td colspan="2">' . $field->label . '</td></tr>';
                } elseif ($field->teg == 'customPhp' && !$field->label) {
                    $html .= '<tr><td colspan="2">' . $field->value . '</td></tr>';
                } elseif ($field->hide == 3) {
                    if (isset($field->data) && ! empty($field->data)) {
                        $html .= $this->getRows($field->data);
                    }
                } else {
                    $html .= $this->getTr($field);
                    if (isset($field->data) && ! empty($field->data)) {
                        $html .= $this->getRows($field->data);
                    }
                }
            }
        }
        return $html;
    }

    protected function getRowsCloner($field)
    {
        $html = '';
        static $n = 1;
        if (! empty($field->data)) {
            $html .= '<tr><td colspan="2">';
            $html .= '<table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
            if ($field->orient) {
                $i = 0;
                foreach ($field->data as $row) {
                    if (! $i) {
                        $html .= '<tr>';
                        if (isset($field->numbering) && $field->numbering) {
                            $html .= '<th>' . $field->numbering . '</th>';
                        }
                        foreach ($row as $item) {
                            if ($item->hide != 1) {
                                if ($item->hide != 3) {
                                    $html .= '<th>' . $item->label . '</th>';
                                }
                            }
                        }
                        $i ++;
                        $html .= '</tr>';
                    }
                    $html .= '<tr>';
                    if (isset($field->numbering) && $field->numbering) {
                        $html .= '<td style="padding-left:10px">' . $n . '</td>';
                        $n ++;
                    }
                    foreach ($row as $item) {
                        if ($item->hide != 1) {
                            if ($item->hide != 3) {
                                $html .= $this->getTdValCloner($item);
                            }
                        }
                    }
                    $html .= '</tr>';
                }
            } else {
                foreach ($field->data as $row) {
                    $html .= '<tr><td colspan="2">';
                    if (isset($field->numbering) && $field->numbering) {
                        $html .= '<div style="font:120% serif;padding:10px 0px 5px 10px;">'.$field->numbering.' '. $n . '</div>';
                        $n ++;
                    }
                    $html .= '<table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
                    $html .= $this->getRows($row);
                    $html .= '</table>';
                    $html .= '</td></tr>';
                }
            }
            $html .= '</table>';
            $html .= '</td></tr>';
        }
        return $html;
    }

    protected function getTr($field)
    {
        $html = '';
        $html .= '<tr>';
        $html .= '<td style="padding-left:10px">' . $this->findLable($field) . '</td>';
        $html .= '<td style="padding-left:10px">' . $field->value . '</td>';
        $html .= '</tr>';
        return $html;
    }


    protected function getTdValCloner($field)
    {
        $html = '<td style="padding-left:10px">' . $field->value;

        if (isset($field->data) && ! empty($field->data)) {
            $html .= str_replace(array('<td','<tr','</td>','</tr>'), array('<span','<div',' </span>','</div>'), $this->getRows($field->data));
        }

        $html .= '</td>';

        return $html;
    }

    protected function getTrSum($calculator)
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

            $html .= '<tr>';
            $html .= '<td align="right" style="padding:10px">' . $arr[1]->label . '</td>';

            if ($arr[1]->pos) {
                $html .= '<td style="padding:10px">' . $sum . ' ' . $arr[1]->unit . '</td>';
            } else {
                $html .= '<td style="padding:10px">' . $arr[1]->unit . ' ' . $sum . '</td>';
            }

            $html .= '</tr>';
        }

        return $html;
    }
}
