<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
        * @license    GNU/GPL
        */
defined('_JEXEC') or die();
class qfCart
{
    public $back;
    public $shopParams;
    public $attachment;


    public function __construct()
    {
        $this->qf_params = JComponentHelper::getParams('com_qf3');

        $this->lang = JFactory::getLanguage();
        $this->lang->load('com_qf3');

        $this->app = JFactory::getApplication();
        $this->session = JFactory::getSession();


        require_once JPATH_SITE.'/administrator/components/com_qf3/models/shopmod.php';
        $model = JModelLegacy::getInstance('Shopmod', 'Qf3Model');
        $this->shopParams = $model->config();

        if ($this->qf_params->get('filesmod')) {
            $this->attachment = $this->shopParams['addfiles'];
        }
    }




    public function getMiniCartHtml()
    {
        $cart = $this->session->get('qfcartbox');
        $html = '';

        if (!$cart) {
            $cart = array();
            $html .=  '<span class="qf_minicart_empty">'.JText::_('QF_EMPTY_CART').'</span>';
        } else {
            $flag = false;
            $arr = array();

            foreach ($cart as $row) {
                if (sizeof($row['sum'])>1) {
                    $flag = 'multi price item';
                    break;
                } else {
                    $sum = $row['sum'][0][0];
                    $currency = $row['sum'][0][1]->unit;

                    if (!isset($arr[$currency])) {
                        $arr[$currency] = $sum*$row['qt'];
                    } else {
                        $arr[$currency] += $sum*$row['qt'];
                    }
                }
            }

            if (sizeof($arr) != 1) {
                $flag = 'multi currency cart';
            }

            if (!$flag) {
                $sum = number_format($arr[$currency], $row['sum'][0][1]->fixed, ',', ' ');
                $sum = $this->shopParams['pcsdir']?$currency.' '.$sum:$sum.' '.$currency;
            } else {
                $sum = sizeof($cart);
            }

            $html .=  '<span class="qf_cart_pcs">'.$this->mlangLabel($this->shopParams['pcs']).'</span><span class="qf_cart_sum">'.$sum.'</span>';
        }

        if ($path = $this->shopParams['img']) {
            if (strpos($path, 'cart_0.png')) {
                $i = sizeof($cart);
                $path = str_replace('cart_0', 'cart_'.(($i>3)?3:$i), $path);
            }
            $html .=  '<span class="qf_cart_img"><img src="'.$path.'"></span>';
        }

        return $html;
    }




    public function qfcartsubmit()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $cart = $this->session->get('qfcartbox');


        $msgtype = 'error';
        $link = $this->app->input->get('root', '', 'STRING');

        if (!$cart) {
            $msg = JText::_('COM_QF_EMPTY_CART');
            $this->app->redirect($link, $msg, $msgtype);
        }

        if ($this->attachment) {
            require_once JPATH_COMPONENT.'/classes/attachment.php';
            $attachment = new qfAttachment;
        }

        $num =0;
        $html = '';

        //check photo
        $fl = false;
        foreach ($cart as $row) {
            if ($this->get('cartimglink', $row['project']->params)) {
                $fl = true;
                break;
            }
        }

        $html .= '<table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';

        $html .= '<tr>';
        if ($fl) {
            $html .= '<td class="qf_th">';
            $html .= '<span>'.JText::_('QF_PHOTO').'</span>';
            $html .= '</td>';
        }
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_PRODUCT_SERVICE').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_PRICE').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_NUMBER').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_AMOUNT').'</span>';
        $html .= '</td>';
        $html .= '</tr>';

        $rowssum = array();

        foreach ($cart as $row) {
            if ($langlink = $this->get('languagelink', $row['project']->params)) {
                $this->lang->load($langlink);
            }

            $html .= '<tr>';

            if ($fl) {
                $html .= '<td class="qf_td_2">';
                if ($img = $this->get('cartimglink', $row['project']->params)) {
                    if (!strpos($img, '//')) {
                        if (strpos($img, '/')===0) {
                            $img = substr($img, 1);
                        }
                        $img = JURI::root().$img;
                    }
                    $html .= '<img src="'.$img.'" width="90%">';
                }
                $html .= '</td>';
            }

            $html .= '<td class="qf_td_3">';
            $html .= '<h3>'.$row['project']->title.'</h3>';
            $html .= $this->getCartRow($row['data']);
            $html .= '</td>';

            $html .= '<td class="qf_td_4" style="white-space: nowrap">';

            $sumsize = sizeof($row['sum']);

            foreach ($row['sum'] as $sum) {
                if ($sumsize > 1) {
                    $html .= $sum[1]->label.' ';
                }
                $html .= number_format($sum[0], $sum[1]->fixed, ',', ' ') . ' ' . $sum[1]->unit.'<br>';
            }
            $html .= '</td>';

            $html .= '<td class="qf_td_5">';
            $html .= $row['qt'];
            $html .= '</td>';

            $html .= '<td class="qf_td_6" style="white-space: nowrap">';
            if ($sumsize == 1) {
                $sum = $row['sum'][0][0];
                $currency = $row['sum'][0][1]->unit;
                $html .= number_format($row['qt']*$sum, $row['sum'][0][1]->fixed, ',', ' ') . ' ' . $currency;
                if (!isset($rowssum[$currency])) {
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
                    $msg = JText::_('QF_ERR_REQ_FILES');
                    $this->app->redirect($link, $msg, $msgtype);
                }
                if ($res) {
                    $html .= '<tr>';
                    $html .= '<td colspan=5>';
                    $html .= $res;
                    $html .= '</td>';
                    $html .= '</tr>';
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
                $msg = JText::_('QF_ERR_REQ_FILES');
                $this->app->redirect($link, $msg, $msgtype);
            }
            $html .= $res;
        }
        // end files

        $confirm = $this->session->get('qfcartconfirm');

        $html .= '<br><table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
        foreach ($confirm as $row) {
            $html .= '<tr>';
            $html .= '<td>';
            $html .= $this->getCartRow($row['data']);
            $html .= '</td>';
            $html .= '<td style="white-space: nowrap">';

            if ($row['sum'] && isset($row['sum'][0][1]->fixed) && sizeof($row['sum']) == 1) {
                $sum = $row['sum'][0][0];
                $currency = $row['sum'][0][1]->unit;
                $html .= number_format($sum, $row['sum'][0][1]->fixed, ',', ' ') . ' ' . $currency;
                if (!isset($rowssum[$currency])) {
                    $rowssum[$currency] = $sum;
                } else {
                    $rowssum[$currency] += $sum;
                }
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        if ($rowssum) {
            // discounts
            $promocod = $this->session->get('qfpromocod');
            if ($promocod) {
                $codes = explode('%', $this->shopParams['promocod']);
                foreach ($codes as $cod) {
                    $cod = trim($cod);
                    if ($cod) {
                        $pats = explode('-', $cod);
                        if ($pats[0]==$promocod && $pats[1]) {
                            $discount =  (float)$pats[1]*$rowssum[$currency]/100;
                            $html .= '<tr>';
                            $html .= '<td>';
                            $html .= '<b>'.JText::_('QF_DISCOUNT'). ' ' .$pats[1].'%</b>';
                            $html .= '</td>';
                            $html .= '<td style="white-space: nowrap">';
                            $html .= '<b>'.$discount.' '.$currency.'</b>'.'<br>';
                            $html .= '</td>';
                            $html .= '</tr>';
                            $rowssum[$currency] -= $discount;
                            break;
                        }
                    }
                }
            } elseif ($this->shopParams['discounts']) {
                $discounts = preg_replace('/[^0-9\-%]/', '', $this->shopParams['discounts']);
                $discounts = explode('%', $discounts);
                foreach ($discounts as $discount) {
                    $pats = explode('-', $discount);
                    if ($pats[0] < $rowssum[$currency] && $rowssum[$currency] <= $pats[1]) {
                        $disc = (float)$pats[2]*$rowssum[$currency]/100;
                        $html .= '<tr>';
                        $html .= '<td>';
                        $html .= '<b>'.JText::_('QF_DISCOUNT'). ' ' .$pats[2].'%</b>';
                        $html .= '</td>';
                        $html .= '<td style="white-space: nowrap">';
                        $html .= '<b>'.$disc.' '.$currency.'</b>'.'<br>';
                        $html .= '</td>';
                        $html .= '</tr>';
                        $rowssum[$currency] -= $disc;
                        break;
                    }
                }
            }
            // end discounts

            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<b>'.JText::_($this->shopParams['text_2']).'</b>';
            $html .= '</td>';
            $html .= '<td style="white-space: nowrap">';
            foreach ($rowssum as $currency=>$sum) {
                $html .= '<b>'.$sum.' '.$currency.'</b>'.'<br>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $html = $this->shopParams['text_before'] . $html . $this->shopParams['text_after'];

        $project = $this->defProject();

        require_once JPATH_COMPONENT.'/classes/buildemail.php';
        $qfFilds = new qfFilds;

        $qfFilds->iscart = true;
        $qfFilds->back = $this->back;

        $stat = $qfFilds->writeStat($project, $html);
        if (!$stat) {
            $msg = JText::_('COM_QF_NOT_COMPLETED');
            $this->app->redirect($link, $msg, $msgtype);
        }

        $sent = $qfFilds->sendMail($project, $html, $stat);
        if (!$sent) {
            $msg = JText::_('COM_QF_NOT_COMPLETED');
            $this->app->redirect($link, $msg, $msgtype);
        }

        $msg = $this->mlangLabel($this->shopParams['popmess']);
        $msgtype = 'message';

        $this->session->set('qfcartbox', false);
        $this->session->set('qfcartconfirm', false);
        $this->session->set('qfcartimg', false);
        $this->session->set('qfpromocod', false);

        if ($this->shopParams['redirect']) {
            $link = $this->shopParams['redirect'];
        }

        $this->app->redirect($link, $msg, $msgtype);
    }




    protected function defProject()
    {
        $project = new stdClass;
        $project->id = 0;
        $project->params = new stdClass;
        $project->emailparams = new stdClass;
        $project->params->history = $this->shopParams['history'];

        $project->emailparams->toemail = $this->shopParams['toemail'];
        $project->emailparams->subject = $this->shopParams['subject'];

        if (!$project->emailparams->subject) {
            $project->emailparams->subject = $_SERVER['HTTP_HOST'] .' '. JText::_('QF_ORDER') . ' №' .time();
        }

        $project->title = $project->emailparams->subject;

        return $project;
    }




    public function confirmCart()
    {
        require_once JPATH_COMPONENT.'/classes/buildemail.php';
        $qfFilds = new qfFilds;

        $qfFilds->qfcheckToken();

        $id = $this->app->input->get('id', 0, 'INT');

        $project = $qfFilds->getProjectById($id);
        if (!$project) {
            return '';
        }

        $data = $qfFilds->getData($project->id);
        $project->calculated = $qfFilds->calculated && $project->calculatorparams->calculatortype;
        $sum = qfCalculator::getCalculator($project, $data);

        $aid = array();

        if ($v = $this->shopParams['payment']) {
            $aid[0] = $v;
        }
        if ($v = $this->shopParams['delivery']) {
            $aid[1] = $v;
        }
        if ($v = $this->shopParams['contacts']) {
            $aid[2] = $v;
        }

        $confirm = $this->session->get('qfcartconfirm');
        if (!$confirm) {
            $confirm = array();
        }

        foreach ($aid as $k=>$v) {
            if ($v == $id) {
                $confirm[$k] = array('data'=>$data, 'sum'=>$sum, 'project'=>$project);
            }
        }

        $this->session->set('qfcartconfirm', $confirm);

        return 'yes';
    }




    public function updateCart()
    {
        require_once JPATH_COMPONENT.'/classes/buildemail.php';
        $qfFilds = new qfFilds;

        $qfFilds->qfcheckToken();

        $id = $this->app->input->get('id', 0, 'INT');

        $project = $qfFilds->getProjectById($id);
        if (!$project) {
            return '';
        }

        $data = $qfFilds->getData($project->id);
        if (!$qfFilds->iscart) {
            return '';
        }
        $project->calculated = $qfFilds->calculated && $project->calculatorparams->calculatortype;

        $sum = qfCalculator::getCalculator($project, $data);
        $cart = $this->session->get('qfcartbox');
        $flag = false;

        if (!$cart) {
            $cart = array();
        }

        foreach ($cart as $i=>$row) {
            if ($data == $row['data']) {
                $cart[$i]['qt'] = $row['qt']+1;
                $cart[$i]['sum'] = $sum;
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            array_push($cart, array('qt'=>1, 'data'=>$data, 'sum'=>$sum, 'project'=>$project));
        }

        $this->session->set('qfcartbox', $cart);
        return $this->getMiniCartHtml();
    }




    public function pageCart()
    {
        $html = '';
        $rowssum = array();
        $cart = $this->session->get('qfcartbox');

        if (!$cart) {
            return  '<span class="qf_cart_empty">'.JText::_('QF_EMPTY_CART').'</span>';
        }

        if ($this->attachment) {
            require_once JPATH_COMPONENT.'/classes/attachment.php';
            $attachment = new qfAttachment;
        }

        //check photo
        $fl = false;
        foreach ($cart as $row) {
            if ($this->get('cartimglink', $row['project']->params)) {
                $fl = true;
                break;
            }
        }

        $html .= JText::_($this->shopParams['text_before_cart']);

        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<td class="qf_th">';
        if ($fl) {
            $html .= '<span>'.JText::_('QF_PHOTO').'</span>';
        }
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_PRODUCT_SERVICE').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_PRICE').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_NUMBER').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span>'.JText::_('QF_AMOUNT').'</span>';
        $html .= '</td>';
        $html .= '<td class="qf_th">';
        $html .= '<span></span>';
        $html .= '</td>';
        $html .= '</tr>';

        $num =0;

        foreach ($cart as $row) {
            if ($langlink = $this->get('languagelink', $row['project']->params)) {
                $this->lang->load($langlink);
            }

            $html .= '<tr>';

            $html .= '<td class="qf_td_2">';
            if ($img = $this->get('cartimglink', $row['project']->params)) {
                $html .= '<img src="'.$img.'" >';
            }
            $html .= '</td>';

            $html .= '<td class="qf_td_3">';
            if (!$row['project']->emailparams->showtitle) {
                $html .= '<h3>'.$this->mlangLabel($row['project']->title).'</h3>';
            }
            $html .= $this->getCartRow($row['data']);
            $html .= '</td>';

            $html .= '<td class="qf_td_4">';

            $sumsize = sizeof($row['sum']);

            foreach ($row['sum'] as $sum) {
                if ($sumsize > 1) {
                    $html .= $sum[1]->label.' ';
                }
                $html .= number_format($sum[0], $sum[1]->fixed, ',', ' ') . ' ' . $sum[1]->unit.'<br>';
            }
            $html .= '</td>';

            $html .= '<td class="qf_td_5">';
            $html .= '<input type="number" value="'.$row['qt'].'" >';
            $html .= '</td>';

            $html .= '<td class="qf_td_6">';

            if ($sumsize == 1) {
                $sum = $row['sum'][0][0];
                $currency = $row['sum'][0][1]->unit;
                $html .= number_format($row['qt']*$sum, $row['sum'][0][1]->fixed, ',', ' ') . ' ' . $currency;
                if (!isset($rowssum[$currency])) {
                    $rowssum[$currency] = $row['qt']*$sum;
                } else {
                    $rowssum[$currency] += $row['qt']*$sum;
                }
            }
            $html .= '</td>';

            $html .= '<td class="qf_td_1">';
            $html .= '<span>✕</span>';
            $html .= '</td>';

            $html .= '</tr>';

            // files
            if ($this->attachment == 2) {
                $html .= '<tr>';
                $html .= '<td colspan=5>';
                $html .= '<div class="atch">' . $attachment->getCartAttachmentHtml($num) . '</div>';
                $html .= '</td>';
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

        $html .= JText::_($this->shopParams['text_after_cart_1']);

        // discounts
        $promocod = trim($this->shopParams['promocod']);
        $discounts = trim($this->shopParams['discounts']);

        if ($discounts || $promocod) {
            $html .= '<script>var qf_txt_discount="'.JText::_('QF_DISCOUNT').'";';
            if ($promocod) {
                $html .= 'var qf_txt_dis="'.JText::_('QF_DISABLE').'", qf_txt_act="'.JText::_('QF_ACTIVATE').'";';
            }

            if ($discounts) {
                $discounts = preg_replace('/[^0-9\-%]/', '', $discounts);
                $html .= 'var qf_cart_discount="'.$discounts.'";';
            }
            else {
                $html .= 'var qf_cart_discount="";';
            }
            $html .= '</script>';

            $html .= '<div class="qf_cart_discount">';

            if ($discounts && !$promocod) {
                if (isset($currency)) {
                    $html .= '<div class="discount_mess">'.JText::_('QF_OFFER_DISCOUNTS_1').' '.(int) $discounts.' '.$currency.'</div>';
                }
            } else {
                if ($discounts && $promocod) {
                    $html .= '<div class="discount_mess">'.JText::_('QF_OFFER_DISCOUNTS_2').'</div>';
                }
                $html .= '<form class="discount_inp" autocomplete="off">';

                $promocod = $this->session->get('qfpromocod');
                if ($promocod) {
                    $html .= '<label>'.JText::_('QF_PROMOCOD_TXT').'</label><input type="text" required name="disinp" value="'.$promocod.'">';
                    $html .= '<input type="button" value="'.JText::_('QF_DISABLE').'" name="disbut" class="enabled">';
                } else {
                    $html .= '<label>'.JText::_('QF_PROMOCOD_TXT').'</label><input type="text" required name="disinp" value="">';
                    $html .= '<input type="button" value="'.JText::_('QF_ACTIVATE').'" name="disbut">';
                }
                $html .= '</form>';
            }

            $html .= '</div>';
        }
        //end discounts

        $html .= '<div class="qf_cart_foot">';
        $html .= '<div class="qf_cart_foot_l">';
        $html .= $this->getDelivery();
        $html .= $this->getPayment();
        $html .= $this->getContacts();
        $html .= '</div>';
        $html .= '<div class="qf_cart_foot_r">';
        $html .= $this->boxResultPrice($rowssum);
        $html .= $this->boxSubmit();
        $html .= '</div>';
        $html .= '</div>';

        $html .= JText::_($this->shopParams['text_after_cart_2']);

        return $html;
    }




    public function qfcartpromocod()
    {
        $usercod = $this->app->input->get('cod', '', 'str');
        $codes = explode('%', $this->shopParams['promocod']);
        foreach ($codes as $cod) {
            $cod = trim($cod);
            if ($cod) {
                $pats = explode('-', $cod);
                if ($pats[0]==$usercod && $pats[1]) {
                    $this->session->set('qfpromocod', $pats[0]);
                    return (float)$pats[1];
                }
            }
        }
        $this->session->set('qfpromocod', false);
    }




    protected function boxSubmit()
    {
        $html = '<div class="qf_cart_btn">';
        if ($id = $this->shopParams['contacts']) {
            $html .= '<input name="qfcartnext2" type="button" class="btn qfcartsubmit" value="' . JText::_($this->shopParams['text_3']) . '" onclick="QFcart.cartnext()" />';
        } else {
            $html .= $this->boxSubmitS();
        }
        $html .= '</div>';
        return $html;
    }




    protected function boxSubmitS()
    {
        $html = '';
        $html .= '<form method="post" class="cart_form"><input name="task" type="hidden" value="qfcartsubmit"><input name="root" type="hidden" value="'.JURI::current().'"><input name="option" type="hidden" value="com_qf3">' . JHtml::_('form.token') . '<input name="qfcartsubmit" type="button" class="qfcartsubmit" value="' . JText::_($this->shopParams['text_4']) . '" onclick="this.form.cartsubmit()" /></form>';
        return $html;
    }




    protected function boxResultPrice($rowssum)
    {
        $html = '';
        if ($rowssum) {
            $html .= '<h3>'.$this->mlangLabel($this->shopParams['text_1']).'</h3>';
        }
        $html .= '<div id="qf_resultprice" data-text_price_total="'.$this->mlangLabel($this->shopParams['text_2']).'">';
        foreach ($rowssum as $unit=>$sum) {
            $html .= '<input name="qfprice[]" type="hidden" value="'.$sum.'" data-unit="'.$unit.'" />';
        }
        $html .= '</div>';
        return $html;
    }




    protected function getContacts()
    {
        $html = '';

        if ($id = $this->shopParams['contacts']) {
            require_once JPATH_COMPONENT.'/classes/buildform.php';
            $QuickForm = new QuickForm3;

            $html .= '<div id="qf_contacts">';
            $html .= '<div class="qf_contacts_inner">';
            $temp = $QuickForm->getQuickForm($id);
            if (!$QuickForm->project->emailparams->showtitle) {
                $html .= '<h3>'.$this->mlangLabel($QuickForm->project->title).'</h3>';
            }
            $html .= $temp;
            $html .= $this->boxSubmitS();
            $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    }




    protected function getPayment()
    {
        $html = '';

        if ($id = $this->shopParams['payment']) {
            require_once JPATH_COMPONENT.'/classes/buildform.php';
            $QuickForm = new QuickForm3;

            $html .= '<div id="qf_payment">';
            $temp = $QuickForm->getQuickForm($id);
            if (!$QuickForm->project->emailparams->showtitle) {
                $html .= '<h3>'.$this->mlangLabel($QuickForm->project->title).'</h3>';
            }
            $html .= $temp;
            $html .= '</div>';
        }
        return $html;
    }




    protected function getDelivery()
    {
        $html = '';

        if ($id = $this->shopParams['delivery']) {
            require_once JPATH_COMPONENT.'/classes/buildform.php';
            $QuickForm = new QuickForm3;

            $html .= '<div id="qf_delivery">';
            $temp = $QuickForm->getQuickForm($id);
            if (!$QuickForm->project->emailparams->showtitle) {
                $html .= '<h3>'.$this->mlangLabel($QuickForm->project->title).'</h3>';
            }
            $html .= $temp;
            $html .= '</div>';
        }
        return $html;
    }




    protected function get($k, $ar, $def = false)
    {
        $ar = (object)$ar;
        if (isset($ar->$k)) {
            return $ar->$k;
        }
        return $def;
    }




    protected function getCartRow($data, $need_ul = true)
    {
        if ($need_ul) {
            $html = '<ul>';
        }
        foreach ($data as $fild) {
            if (! isset($fild->hide) || ! $fild->hide) {
                if ($fild->teg == 'cloner') {
                    foreach ($fild->data as $row) {
                        $html .= $this->getCartRow($row);
                    }
                } elseif ($fild->teg == 'qftabs') {
                    $options = $fild->options;
                    for ($n = 0; $n < sizeof($options); $n ++) {
                        // $html .= $this->mlangLabel($options[$n]->label) . '<br>';
                        $html .= $this->getCartRow($fild->data[$n]);
                    }
                } elseif ($fild->teg == 'customHtml') {
                    $html .= $this->mlangLabel($fild->label) . '<br>';
                } elseif ($fild->teg == 'customPhp') {
                    $html .= '<li>';
                    if ($fild->label) {
                        $html .= $this->mlangLabel($fild->label);
                    }
                    $html .= $this->mlangLabel($fild->value) . '</li>';
                } elseif (isset($fild->hideone) && $fild->hideone) {
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getCartRow($fild->data);
                    }
                } else {
                    $html .= '<li>';
                    $html .= $this->mlangLabel($this->letLable($fild)) . ' : ';
                    $html .= $this->mlangLabel($fild->value);
                    $html .= '</li>';
                    if (isset($fild->data) && ! empty($fild->data)) {
                        $html .= $this->getCartRow($fild->data);
                    }
                }
            }

            if ($fild->teg == 'backemail' && $fild->value) {
                $this->back = $fild->back;
            }
        }
        if ($need_ul) {
            $html .= '</ul>';
        }
        return $html;
    }




    public function changeRowCart($i, $v)
    {
        if ($v) {
            $cart = $this->session->get('qfcartbox');
            if (!isset($cart[$i])) {
                return '';
            }

            $cart[$i]['qt'] = $v;
            $this->session->set('qfcartbox', $cart);
            return $this->pageCart();
        } else {
            return $this->removeRowCart($i);
        }
    }




    public function removeRowCart($i)
    {
        $cart = $this->session->get('qfcartbox');
        if (!isset($cart[$i])) {
            return '';
        }

        unset($cart[$i]);
        $k = 0;
        $new = array();
        foreach ($cart as $el) {
            $new[$k] = $el;
            $k ++;
        }

        $this->session->set('qfcartbox', $new);
        return $this->pageCart();
    }




    protected function mlangLabel($val)
    {
        if (strpos($val, 'QF_')===0) {
            return JText::_($val);
        }
        return $val;
    }




    protected function letLable($field)
    {
        if (!$field->label) {
            if (isset($field->placeholder) && $field->placeholder) {
                return $field->placeholder;
            }
        }
        return $field->label;
    }
}
