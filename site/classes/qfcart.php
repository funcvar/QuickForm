<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

require_once __DIR__.'/buildemail.php';

class qfCart extends qfFilds
{
    public $shop;
    public $attachment;
    protected $cart;
    protected $ses;

    public function __construct()
    {
        parent::__construct();

        $this->cart = qf::ses()->get('qfcartbox', []);
        $this->shop = qf::conf()->get('', 'shop');

        if (qf::conf()->get('filesmod')) {
            $this->attachment = $this->shop['addfiles'];
        }
    }



    public function getMiniCartHtml()
    {
        $html = '';
        $data = array();

        if (! $this->cart) {
            $html .=  '<span class="qf_h qf_minicart_empty">QF_EMPTY_CART</span>';
        } else {
            $arr = array();

            foreach ($this->cart as $row) {
                $data[] = $row['formhash'];

                if(is_array($row['sum']) && sizeof($row['sum'])==1) {
                    $sum = $row['sum'][0][0];
                    $currency = $row['sum'][0][1]->unit;

                    if (! isset($arr[$currency])) {
                        $arr[$currency] = $sum*$row['qt'];
                    } else {
                        $arr[$currency] += $sum*$row['qt'];
                    }
                }
            }

            if(sizeof($arr) == 1) {
                $sum = round($arr[$currency], (int) $this->shop['fixed']);
                $sum = $this->format($sum);
                $sum = $this->shop['pcsdir'] ? $currency.' '.$sum : $sum.' '.$currency;
            } else {
                $sum = sizeof($this->cart);
            }

            $html .=  '<span class="qf_h qf_cart_pcs">'.Text::translate($this->shop['pcs']).'</span><span class="qf_h qf_cart_sum">'.$sum.'</span>';
        }

        if ($path = $this->shop['img']) {
            if (strpos($path, 'cart_0.png')) {
                $i = sizeof($this->cart);
                $path = str_replace('cart_0', 'cart_'.(($i>3) ? 3 : $i), $path);
            }
            $path =  '<img src="'.$path.'">';
        }
        $html .=  '<span class="qf_cart_img">'.$path.'</span>';
        $html .= '<span class="qf_cart_incart" data-incart="['.implode(',', $data).']"></span>';

        $html = Text::translate($html);

        return $html;
    }


    public function checkcss()
    {
        if($id = (int) $this->shop['contacts']) {
            require_once __DIR__.'/buildform.php';
            $QuickForm = new QuickForm();
            $project = $QuickForm->getProjectById($id);
            if ($project->params->cssform) {
                qf::addScript('css', 'css/'.$project->params->cssform);
                return str_replace('.css', '', $project->params->cssform);
            }
        }
    }


    protected function format($sum)
    {
        $fix = (int) $this->shop['fixed'];
        $fo =  (int) $this->shop['format'];

        if(! $fo) {
            return number_format($sum, $fix, ',', ' ');
        } elseif($fo == 1) {
            return number_format($sum, $fix, '.', ',');
        } else {
            return number_format($sum, $fix, '.', '');
        }
    }



    public function qfcartsubmit()
    {
        $this->qfcheckToken();

        $this->msgtype = 'error';
        $link = $this->app->input->get('root', '', 'STRING');

        if (! $this->cart) {
            $this->msg = Text::_('QF_EMPTY_CART');
            $this->cartredirect($link);
        }

        if ($this->attachment) {
            require_once __DIR__.'/attachment.php';
            $attachment = new qfAttachment();
        }

        $num =0;
        $html = '';

        $html .= '<table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';

        $html .= '<tr>';
        $html .= '<td><span>QF_PRODUCT_SERVICE</span></td>';
        $html .= '<td><span>QF_PRICE</span></td>';
        $html .= '<td><span>QF_NUMBER</span></td>';
        $html .= '<td><span>QF_AMOUNT</span></td>';
        $html .= '</tr>';

        $rowssum = array();

        foreach ($this->cart as $row) {
            $html .= '<tr>';

            $html .= '<td>'.$this->getCartRow($row['data']).'</td>';

            $html .= '<td style="white-space: nowrap">';

            if(is_array($row['sum'])) {
                $sumsize = sizeof($row['sum']);

                foreach ($row['sum'] as $sum) {
                    if ($sumsize > 1) {
                        $html .= $sum[1]->label.' ';
                    }
                    $sum[0] = round($sum[0], (int) $this->shop['fixed']);
                    $html .= $this->format($sum[0]) . ' ' . $sum[1]->unit.'<br>';
                }
            } else {
                $sumsize = 0;
            }

            $html .= '</td>';

            $html .= '<td>';
            $html .= $row['qt'];
            $html .= '</td>';

            $html .= '<td style="white-space: nowrap">';
            if ($sumsize == 1) {
                $sum = round($row['sum'][0][0], (int) $this->shop['fixed']);
                $currency = $row['sum'][0][1]->unit;
                $html .= $this->format($row['qt']*$sum) . ' ' . $currency;
                if (! isset($rowssum[$currency])) {
                    $rowssum[$currency] = $row['qt']*$sum;
                } else {
                    $rowssum[$currency] += $row['qt']*$sum;
                }
            }
            $html .= '</td>';

            $html .= '</tr>';

            // files
            if ($this->attachment == 2) {
                $res = $attachment->getEmailAttachmentHtml($num);
                if ($res == 'ERR_REQ_FILES') {
                    $this->msg = Text::_('QF_ERR_REQ_FILES');
                    $this->cartredirect($link);
                }
                if ($res) {
                    $html .= '<tr><td colspan="5">' . $res . '</td></tr>';
                }
            }
            $num++;
            // end files
        }
        $html .= '</table>';

        // files
        if ($this->attachment == 1) {
            $res = $attachment->getEmailAttachmentHtml(-1);
            if ($res == 'ERR_REQ_FILES') {
                $this->msg = Text::_('QF_ERR_REQ_FILES');
                $this->cartredirect($link);
            }
            $html .= $res;
        }
        // end files

        $html .= '<br><table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
        if ($rowssum) {
            $html .= '<tr>';
            $html .= '<td>QF_PRIMARY_SUM</td>';
            $html .= '<td style="white-space: nowrap"><br>'.$this->format($rowssum[$currency]).' '.$currency.'<br></td>';
            $html .= '</tr>';

            // discounts
            $promocod = qf::ses()->get('qfpromocod', '');

            if ($promocod) {
                $codes = preg_replace('/\s/', '', $this->shop['promocod']);
                $codes = preg_split('/([^&%]+[&%])/', $codes, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                foreach ($codes as $cod) {
                    $pats = explode('-', $cod);
                    if ($pats[0]==$promocod && mb_strlen($pats[1], "UTF-8") > 1) {
                        $last = mb_substr($pats[1], -1);
                        $pat = mb_substr($pats[1], 0, -1);
                        if ($last == '&') {
                            $discount =  $pat;
                            $label = 'QF_DISCOUNT';
                        } else {
                            $discount =  $pat*$rowssum[$currency]/100;
                            $label = 'QF_DISCOUNT '. $pat . '%';
                        }

                        $html .= '<tr>';
                        $html .= '<td>' .$label.'</td>';
                        $html .= '<td style="white-space: nowrap"><br>-'.$this->format($discount).' '.$currency.'<br></td>';
                        $html .= '</tr>';
                        $rowssum[$currency] -= $discount;
                        break;
                    }
                }
            } elseif ($this->shop['discounts']) {
                $codes = preg_replace('/[^0-9\-%&]/', '', $this->shop['discounts']);
                $codes = preg_split('/([^&%]+[&%])/', $codes, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                foreach ($codes as $cod) {
                    $pats = explode('-', $cod);
                    if(isset($pats[2])) {
                        if ($pats[0] < $rowssum[$currency] && $rowssum[$currency] <= $pats[1]) {
                            $last = mb_substr($pats[2], -1);
                            $pat = mb_substr($pats[2], 0, -1);
                            if ($last == '&') {
                                $discount =  $pat;
                                $label = 'QF_DISCOUNT';
                            } else {
                                $discount =  $pat*$rowssum[$currency]/100;
                                $label = 'QF_DISCOUNT '. $pat . '%';
                            }

                            $html .= '<tr>';
                            $html .= '<td>' .$label.'</td>';
                            $html .= '<td style="white-space: nowrap"><br>-'.$this->format($discount).' '.$currency.'<br></td>';
                            $html .= '</tr>';
                            $rowssum[$currency] -= $discount;
                            break;
                        }
                    }
                }
            }
            // end discounts
        }

        $confirm = qf::ses()->get('qfcartconfirm', []);
        // Embeddable forms: payment, delivery, contact details, etc.
        if($confirm) {
            $html .= '<tr><td colspan="2"><br></td></tr>';
            foreach ($confirm as $row) {
                $html .= '<tr>';
                $html .= '<td>';
                $html .= '<h3>'.$row['project']->title.'</h3>';
                $html .= $this->getCartRow($row['data']);
                $html .= '</td>';
                $html .= '<td style="white-space: nowrap">';

                if ($row['sum'] && sizeof($row['sum']) == 1) {
                    $sum = round($row['sum'][0][0], (int) $this->shop['fixed']);
                    $currency = $row['sum'][0][1]->unit;
                    $html .= $this->format($sum) . ' ' . $currency;
                    if (! isset($rowssum[$currency])) {
                        $rowssum[$currency] = $sum;
                    } else {
                        $rowssum[$currency] += $sum;
                    }
                }
                $html .= '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '<tr><td colspan="2"><br></td></tr>';
        $html .= '<tr>';
        $html .= '<td><b>'.$this->shop['text_2'].'</b></td>';
        $html .= '<td style="white-space: nowrap">';
        foreach ($rowssum as $currency=>$sum) {
            $html .= '<b>'.$this->format($sum).' '.$currency.'</b><br>';
        }
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '</table>';

        $html = $this->shop['text_before'] . $html . $this->shop['text_after'];
        $html = Text::translate($html);

        $project = $this->defProject();

        $this->iscart = true;
        $this->back = false;

        if($this->shop['back']) {
            if (! qf::get('backlogin', $this->shop)) {
                $this->back = qf::user()->get('email');
            } else {
                if($confirm && qf::conf()->get('display') == 2) {
                    foreach ($confirm as $row) {
                        if($this->back) {
                            break;
                        }
                        foreach ($row['data'] as $field) {
                            if ($field->teg == 'useremail' && $field->value) {
                                $this->back = $field->value;
                                break;
                            }
                        }
                    }
                }
                if(! $this->back && qf::user()->get('email')) {
                    $this->back = qf::user()->get('email');
                }
            }

            $vizual = ! qf::get('backmod', $this->shop);
            if(($vizual && !qf::get('backfl', $_POST)) || !qf::conf()->get('cod')) {
                $this->back = false;
            }
        }

        $stat = $this->writeStat($project, $html);
        if (! $stat) {
            $this->msg = Text::_('QF_NOT_COMPLETED');
            $this->cartredirect($link);
        }

        $sent = $this->sendMail($project, $html, $stat);
        if (! $sent) {
            $this->msg = Text::_('QF_NOT_COMPLETED');
            $this->cartredirect($link);
        }

        $this->msg = Text::translate($this->shop['popmess']);
        $this->msgtype = 'message';

        qf::ses()->set('qfcartbox', []);
        qf::ses()->set('qfcartconfirm', []);
        qf::ses()->set('qfcartimg', []);
        qf::ses()->set('qfpromocod', '');

        if ($this->shop['redirect']) {
            $link = $this->shop['redirect'];
        }

        $this->cartredirect($link);
    }


    protected function cartredirect($url)
    {
        $this->app->enqueueMessage($this->msg, $this->msgtype);
        $this->app->redirect($url, false);
    }


    protected function defProject()
    {
        $project = new \stdClass();
        $project->id = 0;
        $project->params = new \stdClass();
        $project->params->history = $this->shop['history'];

        $project->params->toemail = $this->shop['toemail'];
        $project->params->subject = $this->shop['subject'];

        if (! $project->params->subject) {
            $project->params->subject = $_SERVER['HTTP_HOST'] .' '. Text::_('QF_ORDER') . ' №' .time();
        }

        $project->title = $project->params->subject;
        return $project;
    }



    public function confirmCart()
    {
        $this->qfcheckToken();

        $id = (int) qf::get('id', $_POST, 0);

        $project = $this->getProjectById($id);
        if (! $project) {
            return '';
        }

        $data = $this->getData($project->id);
        $project->calculated = $this->calculated && $project->params->calculatortype;
        $sum = qfCalculator::getCalculator($project, $data);

        $aid = array();

        if ($v = $this->shop['payment']) {
            $aid[0] = $v;
        }
        if ($v = $this->shop['delivery']) {
            $aid[1] = $v;
        }
        if ($v = $this->shop['contacts']) {
            $aid[2] = $v;
        }

        $confirm = qf::ses()->get('qfcartconfirm', []);

        foreach ($aid as $k=>$v) {
            if ($v == $id) {
                $confirm[$k] = array('data'=>$data, 'sum'=>$sum, 'project'=>$project);
            }
        }

        qf::ses()->set('qfcartconfirm', $confirm);
        return 'yes';
    }



    public function updateCart()
    {
        $this->qfcheckToken();

        $id = (int) qf::get('id', $_POST, 0);
        $formhash = (int) qf::get('formhash', $_POST, 0);

        $project = $this->getProjectById($id);

        if (! $project) {
            return '';
        }

        $data = $this->getData($project->id);
        if (! $this->iscart) {
            return '';
        }

        if($this->errormes) {
            $html = '<span class="qfhidemescart" style="display:none"><span class="qfmess">';
            foreach ($this->errormes as $err) {
                $html .= '<span class="qfmesserr">' . $err . '</span>';
            }
            $html .= '</span></span>';

            return $html . $this->getMiniCartHtml();
        }

        $project->calculated = $this->calculated && $project->params->calculatortype;

        $sum = qfCalculator::getCalculator($project, $data);
        $flag = false;

        foreach ($this->cart as $i=>$row) {
            if ($data == $row['data']) {
                $this->cart[$i]['qt'] = $row['qt']+1;
                $this->cart[$i]['sum'] = $sum;
                $flag = true;
                break;
            }
        }

        if (! $flag) {
            array_push($this->cart, array('qt'=>1, 'data'=>$data, 'sum'=>$sum, 'project'=>$project, 'formhash'=>$formhash));
        }

        qf::ses()->set('qfcartbox', $this->cart);
        return $this->getMiniCartHtml();
    }


    public function pageCart()
    {
        $html = '';
        $script = '<script>var qf_cart_fixed='.(int) $this->shop['fixed'].', qf_cart_format='.(int) $this->shop['format'].';';
        $rowssum = array();

        if (! $this->cart) {
            return  '<span class="qf_cart_empty">'.Text::_('QF_EMPTY_CART').'</span>';
        }

        if ($this->attachment) {
            require_once __DIR__.'/attachment.php';
            $attachment = new qfAttachment();
        }

        $html .= $this->shop['text_before_cart'];

        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<td class="qf_th"><span>QF_PRODUCT_SERVICE</span></td>';
        $html .= '<td class="qf_th"><span>QF_PRICE</span></td>';
        $html .= '<td class="qf_th"><span>QF_NUMBER</span></td>';
        $html .= '<td class="qf_th"><span>QF_AMOUNT</span></td>';
        $html .= '<td class="qf_th"><span></span></td>';
        $html .= '</tr>';

        $num =0;

        foreach ($this->cart as $row) {
            $html .= '<tr>';

            $html .= '<td class="qf_td_3">'.$this->getCartRow($row['data']).'</td>';

            $html .= '<td class="qf_td_4">';

            if(is_array($row['sum'])) {
                $sumsize = sizeof($row['sum']);

                foreach ($row['sum'] as $sum) {
                    if ($sumsize > 1) {
                        $html .= $sum[1]->label.' ';
                    }
                    $sum[0] = round($sum[0], (int) $this->shop['fixed']);
                    $html .= $this->format($sum[0]) . ' ' . $sum[1]->unit.'<br>';
                }
            } else {
                $sumsize = 0;
            }

            $html .= '</td>';

            $html .= '<td class="qf_td_5"><input type="number" value="'.$row['qt'].'" ></td>';

            $html .= '<td class="qf_td_6">';

            if ($sumsize == 1) {
                $sum = round($row['sum'][0][0], (int) $this->shop['fixed']);
                $currency = $row['sum'][0][1]->unit;
                $html .= $this->format($row['qt']*$sum) . ' ' . $currency;

                if (! isset($rowssum[$currency])) {
                    $rowssum[$currency] = $row['qt']*$sum;
                } else {
                    $rowssum[$currency] += $row['qt']*$sum;
                }
            }
            $html .= '</td>';

            $html .= '<td class="qf_td_1"><span>✕</span></td>';

            $html .= '</tr>';

            // files
            if ($this->attachment == 2) {
                $html .= '<tr>';
                $html .= '<td colspan="6"><div class="atch">' . $attachment->getCartAttachmentHtml($num) . '</div></td>';
                $html .= '</tr>';
            }
            // end files
            $num ++;
        }
        $html .= '</table>';

        // files
        if ($this->attachment == 1) {
            $html .= '<div class="atch">' . $attachment->getCartAttachmentHtml(-1) . '</div>';
        }
        // end files

        $html .= $this->shop['text_after_cart_1'];

        // discounts
        $promocod = trim($this->shop['promocod']);
        $discounts = trim($this->shop['discounts']);

        if ($discounts || $promocod) {
            $script .= 'var qf_txt_discount="QF_DISCOUNT";';
            if ($promocod) {
                $script .= 'var qf_txt_dis="QF_DISABLE", qf_txt_act="QF_ACTIVATE";';
            }

            if ($discounts) {
                $discounts = preg_replace('/[^0-9\-%&]/', '', $discounts);
                $script .= 'var qf_cart_discount="'.$discounts.'";';
            } else {
                $script .= 'var qf_cart_discount="";';
            }

            $html .= '<div class="qf_cart_discount">';

            if ($discounts && !$promocod) {
                if (isset($currency)) {
                    $html .= '<div class="discount_mess">QF_OFFER_DISCOUNTS_1 '.(int) $discounts.' '.$currency.'</div>';
                }
            } else {
                if ($discounts && $promocod) {
                    $html .= '<div class="discount_mess">QF_OFFER_DISCOUNTS_2</div>';
                }
                $html .= '<form class="discount_inp" autocomplete="off">';

                $promocod = qf::ses()->get('qfpromocod', '');
                if ($promocod) {
                    $html .= '<label>QF_PROMOCOD_TXT</label><input type="text" required name="disinp" value="'.$promocod.'">';
                    $html .= '<input type="button" value="QF_DISABLE" name="disbut" class="enabled">';
                } else {
                    $html .= '<label>QF_PROMOCOD_TXT</label><input type="text" required name="disinp" value="">';
                    $html .= '<input type="button" value="QF_ACTIVATE" name="disbut">';
                }
                $html .= '</form>';
            }

            $html .= '</div>';
        }
        //end discounts

        $html .= $script. '</script>';

        $html .= '<div class="qf_cart_foot">';
        $html .= '<div class="qf_cart_foot_l">';
        $html .= $this->embeddedForm('delivery');
        $html .= $this->embeddedForm('payment');
        $html .= '</div>';
        $html .= '<div class="qf_cart_foot_r">';
        $html .= $this->boxResultPrice($rowssum);
        $html .= $this->boxSubmit();
        $html .= '</div>';
        $html .= '</div>';

        $html .= $this->shop['text_after_cart_2'];

        return Text::translate($html);
    }



    public function qfcartpromocod()
    {
        $usercod = $this->app->input->get('cod', '', 'str');
        $codes = preg_replace('/\s/', '', $this->shop['promocod']);
        $codes = preg_split('/([^&%]+[&%])/', $codes, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($codes as $cod) {
            $pats = explode('-', $cod);
            if ($pats[0]==$usercod && $pats[1]) {
                qf::ses()->set('qfpromocod', $pats[0]);
                return $pats[1];
            }
        }
        qf::ses()->set('qfpromocod', '');
    }



    protected function boxSubmit()
    {
        $html = '<div class="qf_cart_btn">';
        if ($id = $this->shop['contacts']) {
            $html .= '<input name="qfcartnext2" type="button" class="btn qfcartsubmit" value="' . $this->shop['text_3'] . '" onclick="QFcart.cartnext()" />';
        } else {
            $html .= $this->boxSubmitS();
        }
        $html .= '</div>';
        return $html;
    }



    protected function boxSubmitS()
    {
        $html = '';
        if($this->shop['back']) {
            $vizual = ! qf::get('backmod', $this->shop);
            $loginonly = ! qf::get('backlogin', $this->shop);

            if ($vizual) {
                if ($loginonly) {
                    if (! qf::user()->get('email')) {
                        $html = '<div><input type="checkbox" disabled> QF_COPY (QF_AUTH_REQ)</div>';
                    } else {
                        $html = '<div><input name="backfl" type="checkbox" value="1" checked> QF_COPY</div>';
                    }
                } else {
                    $chk = $this->checkuseremailfield();
                    if (! $chk) {
                        $html = '<div style="color:red">Are you an admin? There is no useremail field. I don\'t know where to send a copy.</div>';
                    } elseif (! $chk[0]) {
                        $html = '<div style="color:red">Are you an admin? The useremail field must be required.</div>';
                    } else {
                        $html = '<div><input name="backfl" type="checkbox" value="1"> QF_COPY</div>';
                    }
                }
            } else {
                if ($loginonly) {
                    if (! qf::user()->get('email')) {
                        $html = '<div style="color:red">* QF_COPY_A</div>';
                    }
                } else {
                    $chk = $this->checkuseremailfield();
                    if (! $chk) {
                        $html = '<div style="color:red">Are you an admin? There is no useremail field. I don\'t know where to send a copy.</div>';
                    } elseif (! $chk[0]) {
                        $html = '<div style="color:red">Are you an admin? The useremail field must be required.</div>';
                    }
                }
            }
        }
        return '<form method="post" class="cart_form">'.$html.'<input name="task" type="hidden" value="qfcartsubmit"><input name="root" type="hidden" value="'.qf::getUrl().'"><input name="option" type="hidden" value="com_qf3">' . \JHtml::_('form.token') . '<input name="qfcartsubmit" type="button" class="qfcartsubmit" value="' . $this->shop['text_4'] . '" onclick="QFcart.cartsubmit(this.form)" /></form>';
    }

    protected function checkuseremailfield()
    {
        $ids = [$this->shop['contacts'], $this->shop['delivery'], $this->shop['payment']];
        foreach ($ids as $id) {
            if ((int) $id) {
                $data = $this->linerData($this->getData($id));
                foreach ($data as $field) {
                    if ($field->teg == 'useremail') {
                        return [qf::get('required', $field)];
                    }
                }
            }
        }
    }

    protected function boxResultPrice($rowssum)
    {
        $html = '';
        if ($rowssum) {
            $html .= '<h3>'.$this->shop['text_1'].'</h3>';
        }

        $html .= '<div id="qf_resultprice" data-text_price_total="'.$this->shop['text_2'].'">';
        foreach ($rowssum as $unit=>$sum) {
            $sum = round($sum, (int) $this->shop['fixed']);
            $html .= '<div class="qf_preresultprice"><label>'.$this->shop['text_5'].'</label><span class="qfpriceinner">'.$this->format($sum).'</span><span class="qfunitinner">'.$unit.'</span></div>';
            $html .= '<input name="qfprice[]" type="hidden" value="'.$sum.'" data-unit="'.$unit.'" />';
        }
        $html .= '</div>';
        return $html;
    }



    protected function embeddedForm($name)
    {
        $html = '';

        if ($id = $this->shop[$name]) {
            require_once __DIR__.'/buildform.php';
            $QuickForm = new QuickForm();

            $html .= '<div id="qf_'.$name.'">';
            $temp = $QuickForm->getQuickForm($id);
            $html .= '<h3>'.$QuickForm->project->title.'</h3>';
            $html .= $temp;
            $html .= '</div>';
        }
        return $html;
    }



    public function embeddedContacts()
    {
        $html = '';

        if ($id = $this->shop['contacts']) {
            require_once __DIR__.'/buildform.php';
            $QuickForm = new QuickForm();

            $html .= '<div id="qf_contacts" class="qfmodalform'.$this->checkcss().'"><div class="qfcartclose">✕</div>';
            $html .= '<div class="qf_contacts_inner">';
            $temp = $QuickForm->getQuickForm($id);
            $html .= '<h3>'.$QuickForm->project->title.'</h3>';
            $html .= $temp;
            $html .= $this->boxSubmitS();
            $html .= '</div>';
            $html .= '</div>';
        }
        return Text::translate($html);
    }



    protected function getCartRow($data)
    {
        $html = $this->getSimplRows($data);
        return '<div>'.implode('</div><div>', explode("\r\n", $html)).'</div>';
    }



    public function changeRowCart($i, $v)
    {
        if ($v) {
            if (! isset($this->cart[$i])) {
                return '';
            }

            $this->cart[$i]['qt'] = $v;
            qf::ses()->set('qfcartbox', $this->cart);
            return $this->pageCart();
        } else {
            return $this->removeRowCart($i);
        }
    }



    public function removeRowCart($i)
    {
        if (! isset($this->cart[$i])) {
            return '';
        }

        unset($this->cart[$i]);
        $k = 0;
        $new = array();
        foreach ($this->cart as $el) {
            $new[$k] = $el;
            $k ++;
        }

        qf::ses()->set('qfcartbox', $new);

        return $this->pageCart();
    }

}
