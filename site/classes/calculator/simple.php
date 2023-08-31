<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;
\defined('QF3_VERSION') or die;

class qfCalculator_tmpl extends qfCalculator
{
    public function getTmpl($project, $data)
    {
        $this->formuls = array();
        $sumarr = array();

        $original = explode(';', str_replace(' ', '', $project->params->calcformula));
        foreach ($original as $formul) {
            $pats = preg_split('/^([^=]+)=/', trim($formul),-1,PREG_SPLIT_DELIM_CAPTURE);
            $this->formuls[$pats[1]] = $pats[2];
        }

        $arr = $this->CalcArray($data);
        $i = 0;

        foreach ($arr[1] as $k => $v) {
            $str = $this->checkStr($this->converter($k, $arr));

            try {
                $sum = eval('$res=('. $str .');return $res;');
            } catch (Throwable $t) {
                $sum = 0;
                parent::qfErrormes('calculator error: '.$str);
            } catch (Exception $e) {
                $sum = 0;
                parent::qfErrormes('calculator error: '.$str);
            }

            $sum = round($sum, (int) $v->fixed);

            if (!isset($_POST ['qfprice'][$i])) {
                parent::qfErrormes(Text::_('QF_ERROR_CALCULATOR'));

            } else {
                if ($sum != $_POST ['qfprice'] [$i]) {
                    parent::qfErrormes(Text::_('QF_EMAIL_ERROR_SUM') . ': ' . $sum . ' != ' . htmlspecialchars($_POST ['qfprice'] [$i]));
                }
            }

            $sumarr [$i] = array($sum, $v);
            $i++;
        }

        return $sumarr;
    }

    protected function converter($fieldid, $arr)
    {
        $str = '';
        if(isset($this->formuls[$fieldid])){
            $str = preg_replace_callback('/{(.*?)}/', function ($m) use ($arr) {
                $rep = isset($arr[0][$m[1]])?$arr[0][$m[1]]:'('.$this->converter($m[1], $arr).')';
                return str_replace('{'.$m[1].'}', $rep, $m[0]);
            }, $this->formuls[$fieldid]);
        }
        return str_replace('()','',$str);
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
                $setsarray[$fild->fieldid] = $fild;
            } else {
                if (isset($fild->math) && $fild->math !== '') {
                  if(str_replace(array('text','number','range','hidden'), '', $fild->teg) != $fild->teg){
                    $patsarray[$fild->math] = $fild->value;
                  }
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
