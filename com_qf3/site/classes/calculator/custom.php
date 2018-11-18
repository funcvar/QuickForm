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
                    // $sum = number_format($v, (int)$vv[3], ',', ' ');
                    $sumarr[] = array($v, $vv);
                }
            }
        }

        return $sumarr;
    }


    protected function CalcArray($data)
    {
        $setsarray = array();

        foreach ($data as $fild) {
            if ($fild->teg == 'cloner' || $fild->teg == 'qftabs') {
                foreach ($fild->data as $row) {
                    $arr = $this->CalcArray($row);
                    $setsarray = array_merge($setsarray, $arr);
                }
            } elseif ($fild->teg == 'calculatorSum') {
                $setsarray[$fild->fildid] = array(
                        $fild->label,
                        $fild->unit,
                        $fild->pos,
                        $fild->fixed,
                        $fild->fildid
                );
            } else {
                if (isset($fild->data) && ! empty($fild->data)) {
                    $arr = $this->CalcArray($fild->data);
                    $setsarray = array_merge($setsarray, $arr);
                }
            }
        }

        return $setsarray;
    }
}
