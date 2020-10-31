<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

class qfCalculator_tmpl extends qfCalculator
{
    protected $sumdata = false;

    public function getTmpl($project, $data)
    {
        $str = $this->getSumFrom($data);
        $str = $this->checkStr($str);
        if(! $str) return array(0=>array(0, $this->sumdata));

        try {
            $sum = eval('$res=('. $str .');return $res;');
        } catch (Throwable $t) {
            $sum = 0;
            parent::qfErrormes('calculator error: '.$str);
        } catch (Exception $e) {
            $sum = 0;
            parent::qfErrormes('calculator error: '.$str);
        }

        $sum = round($sum, $this->sumdata->fixed);

        if (!isset($_POST ['qfprice'][0])) {
            parent::qfErrormes(JText::_('COM_QF_EMAIL_ERROR_CALCULATOR'));

        } else {
            if ($sum != $_POST ['qfprice'][0]) {
                parent::qfErrormes(JText::_('COM_QF_EMAIL_ERROR_SUM') . ': ' . $sum . ' != ' . $_POST ['qfprice'][0]);
            }
        }

        $sumarr[0] = array($sum, $this->sumdata);

        return $sumarr;
    }


    protected function getSumFrom($data)
    {
        $str = '';
        foreach ($data as $fild) {
            if ($fild->teg == 'cloner') {
                $str .= $fild->clonerstart;
                foreach ($fild->data as $row) {
                    $str .= $this->getSumFrom($row);
                }
                $str .= $fild->clonerend;
            } elseif ($fild->teg == 'qftabs') {
                foreach ($fild->data as $row) {
                    $str .= $this->getSumFrom($row);
                }
            } elseif ($fild->teg == 'calculatorSum') {
                $this->sumdata = $fild;
            } else {
                if (isset($fild->math) && $fild->math !== '') {
                    $str .= str_replace('v', $fild->value, $fild->math);
                }
                if (isset($fild->data) && ! empty($fild->data)) {
                    $str .= $this->getSumFrom($fild->data);
                }
            }
        }
        return $str;
    }
}
