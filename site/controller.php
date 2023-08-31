<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class controller
{
    public function __construct()
    {
        if (isset($_POST['task']) && $_POST['task']) {
            $this->execute();
        }
    }

    protected function execute()
    {
        switch ($_POST['task']) {
          case 'ajax.related':
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            echo $this->check('form')->ajaxHTML($id);
            exit;
          case 'ajax.qfmodal':
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            echo $this->check('form')->getQuickForm($id);
            exit;
          case 'ajax.ajaxCloner':
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            echo $this->check('form')->ajaxCloner($id);
            exit;
          case 'ajax.adderadd':
            echo $this->check('form')->ajaxAddAdder();
            exit;
          case 'ajax.adderdel':
            echo $this->check('form')->ajaxDelAdder();
            exit;
          case 'ajax.adderplus':
            echo $this->check('form')->ajaxPlusAdder();
            exit;
          case 'ajax.sumCustom':
            echo $this->check('email')->sumCustomAjax();
            exit;
          case 'ajax.qfajax':
            $res = $this->check('email')->submitForm();
            echo '<div class="qfajaxres qf'.$res[1].'"><div class="qfajaxtitle">'.Text::_($res[1]).'</div><div class="qfclose">×</div><div class="qfajaxbody">'.$res[0].'</div></div>';
            exit;
          case 'qfsubmit':
            $qfFilds = $this->check('email');
            $res = $qfFilds->submitForm();

            if (! $link = $qfFilds->redirect) {
                $link = isset($_POST['root']) ? (string) $_POST['root'] : '/';
            }
            $qfFilds->formredirect($link, $res[0], $res[1]);
            exit;
          case 'qfcartsubmit':
            $this->check()->qfcartsubmit();
            break;
          case 'ajax.ajaxminicart':
            echo $this->check()->updateCart();
            exit;
          case 'ajax.qfcart':
            echo $this->check()->pageCart();
            exit;
          case 'ajax.qfcartremrow':
            $num = isset($_POST['num']) ? (int) $_POST['num'] : 0;
            echo $this->check()->removeRowCart($num);
            exit;
          case 'ajax.qfcartchangerow':
            $num = isset($_POST['num']) ? (int) $_POST['num'] : 0;
            $val = isset($_POST['val']) ? (int) $_POST['val'] : 0;
            echo $this->check()->changeRowCart($num, $val);
            exit;
          case 'ajax.updateminicart':
            echo $this->check()->getMiniCartHtml();
            exit;
          case 'ajax.confirmCart':
            echo $this->check()->confirmCart();
            exit;
          case 'ajax.qfcartpromocod':
            echo $this->check()->qfcartpromocod();
            exit;
          case 'ajax.showAttachmentBox':
            echo $this->check('filesmod')->showAttachmentBox();
            exit;
          case 'ajax.sessionLoading':
            echo $this->check('filesmod')->sessionLoading();
            exit;
          case 'ajax.attachment_del_img':
            echo $this->check('filesmod')->attachment_del_img();
            exit;
          case 'ajax.cartcontacts':
            echo $this->check()->embeddedContacts();
            exit;
        }
    }

    protected function check($mod = 'shopmod') {
        if($mod == 'form') {
            if (qf::conf()->get('cors')) {
              header("Access-Control-Allow-Origin: *");
            }
            require_once(QF3_PLUGIN_DIR . 'classes/buildform.php');
            return new QuickForm;
        } elseif($mod == 'email') {
            require_once QF3_PLUGIN_DIR . 'classes/buildemail.php';
            return new qfFilds;
        } elseif($mod == 'filesmod') {
            if (! qf::conf()->get('filesmod')) {
                return exit('attachment disabled');
            }
            require_once QF3_PLUGIN_DIR . 'classes/attachment.php';
            return new qfAttachment;
        }

        if (! qf::conf()->get('shopmod')) {
            return exit('shopmod disabled');
        }
        require_once QF3_PLUGIN_DIR . 'classes/qfcart.php';
        return new qfCart;
    }
}
