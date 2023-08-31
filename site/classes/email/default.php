<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;
\defined('QF3_VERSION') or die;

class qfEmail_tmpl extends qfEmail
{
    public function getTmpl($project, $data, $calculator)
    {
        $html = '<br><br><table border="0" width="100%" style="max-width:800px;border-collapse:collapse">';
        $html .= $this->getRows($data);
        if ($calculator) {
            $html .= $this->getTrSum($calculator);
        }
        $html .= '</table>';

        $strarr = explode('</tr><tr', $html);
        $last = $strarr[(count($strarr)-1)];
        $last = str_replace(' style="border-bottom: 1px solid #ccc"><td', '><td', $last);
        $strarr[(count($strarr)-1)] = $last;

        $html = implode('</tr><tr', $strarr);

        if ($project->params->showurl) {
            $link = filter_var(qf::get('root', $_POST), FILTER_SANITIZE_STRING);
            if (strpos($link, $_SERVER['HTTP_HOST']) !== false) {
              $html .= '<p style="color: #949494"><br>QF_SOURCE: <a style="color: #5f7580" href="' . $link . '">'.$link.'</a><br></p>';
            }
        }

        return $html;
    }

    protected function getRows($data)
    {
        $html = '';
        foreach ($data as $field) {
            if (! qf::get('value', $field)) {
                if ($field->hide == 2) {
                    $field->hide = 1;
                }
            }

            if ($field->hide != 1) {
                if ($field->teg == 'cloner') {
                    $html .= $this->getRowsCloner($field);
                } elseif ($field->teg == 'addercart' && $field->rows) {
                    $html .= '<tr><td colspan="2"><table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
                    $total = 0;
                    foreach ($field->rows as $row) {
                        $math = (float) $row->option->math * $row->option->qty;
                        $total += $math;
                        $html .= '<tr>';
                        $html .= '<td>'.$this->imgEmail($row->option).'</td><td>'.$row->option->label.'</td><td>'.$row->option->qty.'</td><td>'.qf::formatPrice($field, $math).'</td>';
                        $html .= '</tr>';
                    }
                    if ($total) {
                        $html .= '<tr><td colspan="3" align="right">QF_TOTAL</td><td>'.qf::formatPrice($field, $total).'</td></tr>';
                    }
                    $html .= '</table></td></tr>';
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

    protected function getTr($field)
    {
        return '<tr style="border-bottom: 1px solid #ccc"><td style="padding:5px 10px;border-right: 1px solid #ccc">' . $this->findLable($field) . '</td><td style="padding-left:10px">' . $field->value . '</td></tr>';
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
                            if (! qf::get('value', $item)) {
                                if ($item->hide == 2) {
                                    $item->hide = 1;
                                }
                            }

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
                        if (! qf::get('value', $item)) {
                            if ($item->hide == 2) {
                                $item->hide = 1;
                            }
                        }

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
