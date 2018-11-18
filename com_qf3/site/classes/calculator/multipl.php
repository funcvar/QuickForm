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
        $formuls = array();
        $sumarr = array();

        $original = explode(';', str_replace(' ', '', $project->calculatorparams->calcformula));
        foreach ($original as $formul) {
            $pats = explode('=', trim($formul));
            $formuls[$pats[0]] = $pats[1];
        }

        $arr = $this->CalcArray($data);
        $i = 0;

        foreach ($arr[1] as $k => $v) {
            $str = preg_replace_callback('/{(.*?)}/', function ($m) use ($arr) {
                $rep = isset($arr[0][$m[1]])?$arr[0][$m[1]]:'';
                return str_replace('{'.$m[1].'}', $rep, $m[0]);
            }, $formuls[$k]);

            $str = $this->checkStr($str);

            try {
                $sum = eval('$res=('. $str .');return $res;');
            } catch (Throwable $t) {
                $sum = 0;
                parent::qfErrormes('calculator error: '.$str);
            } catch (Exception $e) {
                $sum = 0;
                parent::qfErrormes('calculator error: '.$str);
            }

            $sum = round($sum, (int)$v[3]);

            if (!isset($_POST ['qfprice'][$i])) {
                parent::qfErrormes(JText::_('COM_QF_EMAIL_ERROR_CALCULATOR'));

            } else {
                if ($sum != $_POST ['qfprice'] [$i]) {
                    parent::qfErrormes(JText::_('COM_QF_EMAIL_ERROR_SUM') . ': ' . $sum . ' != ' . $_POST ['qfprice'] [$i]);
                }
            }

            // $sum = number_format($sum, (int)$v[3], ',', ' ');

            $sumarr [$i] = array($sum, $v);
            $i++;
        }

        return $sumarr;
    }


    protected function CalcArray($data)
    {
        $patsarray = array();
        $setsarray = array();

        foreach ($data as $fild) {
            if ($fild->teg == 'cloner' || $fild->teg == 'qftabs') {
                foreach ($fild->data as $row) {
                    $arr = $this->CalcArray($row);
                    $patsarray = array_merge($patsarray, $arr[0]);
                    $setsarray = array_merge($setsarray, $arr[1]);
                }
            } elseif ($fild->teg == 'calculatorSum') {
                $setsarray[$fild->fildid] = array(
                        $fild->label,
                        $fild->unit,
                        $fild->pos,
                        $fild->fixed
                );
            } else {
                if (isset($fild->math) && $fild->math !== '') {
                    $patsarray[$fild->fildid] = str_replace('v', $fild->value, $fild->math);
                }
                if (isset($fild->data) && ! empty($fild->data)) {
                    $arr = $this->CalcArray($fild->data);
                    $patsarray = array_merge($patsarray, $arr[0]);
                    $setsarray = array_merge($setsarray, $arr[1]);
                }
            }
        }

        return array(
                $patsarray,
                $setsarray
        );
    }
}
