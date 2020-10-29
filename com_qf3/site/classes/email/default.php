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
        foreach ($data as $fild) {
            if (! isset($fild->hide) || ! $fild->hide) {
                if ($fild->teg == 'cloner') {
                    $html .= $this->getRowsCloner($fild);
                } elseif ($fild->teg == 'qftabs') {
                    $options = $fild->options;
                    for ($n = 0; $n < sizeof($options); $n ++) {
                        $html .= '<tr><td colspan="2">' . $this->mlangLabel($options[$n]->label) . '</td></tr>';
                        $html .= $this->getRows($fild->data[$n]);
                    }
                } elseif ($fild->teg == 'customHtml') {
                    $html .= '<tr><td colspan="2">' . $this->mlangLabel($fild->label) . '</td></tr>';
                } elseif ($fild->teg == 'customPhp' && !$fild->label) {
                    $html .= '<tr><td colspan="2">' . $this->mlangLabel($fild->value) . '</td></tr>';
                } elseif (isset($fild->hideone) && $fild->hideone) {
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getRows($fild->data);
                    }
                } else {
                    $html .= $this->getTr($fild);
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getRows($fild->data);
                    }
                }
            }
        }
        return $html;
    }

    protected function getRowsCloner($fild)
    {
        $html = '';
        static $n = 1;
        if (! empty($fild->data)) {
            $html .= '<tr><td colspan="2">';
            $html .= '<table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
            if ($fild->orient) {
                $i = 0;
                foreach ($fild->data as $row) {
                    if (! $i) {
                        $html .= '<tr>';
                        if (isset($fild->numbering) && $fild->numbering) {
                            $html .= '<th>' . $this->mlangLabel($fild->numbering) . '</th>';
                        }
                        foreach ($row as $item) {
                            if (! isset($item->hide) || ! $item->hide) {
                                if (! isset($item->hideone) || ! $item->hideone) {
                                    $html .= '<th>' . $this->mlangLabel($item->label) . '</th>';
                                }
                            }
                        }
                        $i ++;
                        $html .= '</tr>';
                    }
                    $html .= '<tr>';
                    if (isset($fild->numbering) && $fild->numbering) {
                        $html .= '<td style="padding-left:10px">' . $n . '</td>';
                        $n ++;
                    }
                    foreach ($row as $item) {
                        if (! isset($item->hide) || ! $item->hide) {
                            if (! isset($item->hideone) || ! $item->hideone) {
                                $html .= $this->getTdValCloner($item);
                            }
                        }
                    }
                    $html .= '</tr>';
                }
            } else {
                foreach ($fild->data as $row) {
                    $html .= '<tr><td colspan="2">';
                    if (isset($fild->numbering) && $fild->numbering) {
                        $html .= '<div style="font:120% serif;padding:10px 0px 5px 10px;">'.$fild->numbering.' '. $n . '</div>';
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

    protected function getTr($fild)
    {
        $html = '';
        $html .= '<tr>';
        $html .= '<td style="padding-left:10px">' . $this->mlangLabel($this->letLable($fild)) . '</td>';
        $html .= '<td style="padding-left:10px">' . $this->mlangLabel($fild->value) . '</td>';
        $html .= '</tr>';
        return $html;
    }


    protected function getTdValCloner($fild)
    {
        $html = '<td style="padding-left:10px">' . $this->mlangLabel($fild->value);

        if (isset($fild->data) && ! empty($fild->data)) {
            $html .= str_replace(array('<td','<tr','</td>','</tr>'), array('<span','<div',' </span>','</div>'), $this->getRows($fild->data));
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
