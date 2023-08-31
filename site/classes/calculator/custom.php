<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

defined('_JEXEC') or die();

class qfCalculator_tmpl extends qfCalculator
{
    public function getTmpl($project, $data)
    {
        $customQFcalculator='';
        $cod = '<?php '.$project->params->calcformula.' ?>';

        $tmpfname = tempnam(sys_get_temp_dir(), "qf");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $cod, strlen($cod));
        fclose($handle);
        if (is_file($tmpfname)) {
            include $tmpfname;
            unlink($tmpfname);
        }

        if (!$customQFcalculator) {
            return;
        }

        $calculatorSum = $customQFcalculator($project, $data);
        $arr = $this->CalcArray($data);
        $sumarr = array();

        foreach ($calculatorSum as $k => $v) {
            foreach ($arr as $kk => $vv) {
                if ($k == $kk) {
                    $sumarr[] = array($v, $vv);
                }
            }
        }

        return $sumarr;
    }


    protected function CalcArray($data)
    {
        $resarr = array();

        foreach ($data as $field) {
            if ($field->teg == 'cloner' || $field->teg == 'qftabs') {
                foreach ($field->data as $row) {
                    $arr = $this->CalcArray($row);
                    $resarr = array_merge($resarr, $arr);
                }
            } elseif ($field->teg == 'calculatorSum') {
                $resarr[$field->fieldid] = $field;
            } else {
                if (isset($field->data) && ! empty($field->data)) {
                    $arr = $this->CalcArray($field->data);
                    $resarr = array_merge($resarr, $arr);
                }
            }
        }

        return $resarr;
    }
}
