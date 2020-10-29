<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

class qfCalculator_tmpl extends qfCalculator
{
    public function getTmpl($project, $data)
    {
        $cod = '<?php '.$project->calculatorparams->calcformula.' ?>';

        $config = JFactory::getConfig();
        $tmpfname = tempnam($config->get('tmp_path'), "qf");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $cod, strlen($cod));
        fclose($handle);
        if (is_file($tmpfname)) {
            ob_start();
            include $tmpfname;
        }
        unlink($tmpfname);

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
        $setsarray = array();

        foreach ($data as $field) {
            if ($field->teg == 'cloner' || $field->teg == 'qftabs') {
                foreach ($field->data as $row) {
                    $arr = $this->CalcArray($row);
                    $setsarray = array_merge($setsarray, $arr);
                }
            } elseif ($field->teg == 'calculatorSum') {
                $setsarray[$field->fildid] = $field;
            } else {
                if (isset($field->data) && ! empty($field->data)) {
                    $arr = $this->CalcArray($field->data);
                    $setsarray = array_merge($setsarray, $arr);
                }
            }
        }

        return $setsarray;
    }
}
