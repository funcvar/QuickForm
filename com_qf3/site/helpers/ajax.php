<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

switch ($input->get('mod')) {
    case 'related':
    {
        header("Access-Control-Allow-Origin: *");
        require_once(JPATH_COMPONENT."/classes/buildform.php");
        $build = new QuickForm3;
        echo $build->ajaxHTML($input->get('id', 0, 'INT'));
    }
        break;
    case 'qfmodal':
    {
        header("Access-Control-Allow-Origin: *");
        require_once(JPATH_COMPONENT."/classes/buildform.php");
        $build = new QuickForm3;
        echo $build->getQuickForm($input->get('id', 0, 'INT'));
    }
        break;
    case 'ajaxCloner':
    {
        header("Access-Control-Allow-Origin: *");
        require_once(JPATH_COMPONENT."/classes/buildform.php");
        $build = new QuickForm3;
        echo $build->ajaxCloner($input->get('id', 0, 'INT'));
    }
        break;
    case 'sumCustom':
    {
        require_once JPATH_COMPONENT.'/classes/buildemail.php';
        $qfFilds = new qfFilds;
        echo $qfFilds->sumCustomAjax();
    }
        break;

    case 'qfajax':
    {
        require_once JPATH_COMPONENT.'/classes/buildemail.php';
        $qfFilds = new qfFilds;

        $qfFilds->qfcheckToken();

        $id = $input->get('id', 0, 'INT');

        $msg = '';

        $project = $qfFilds->getProjectById($id);
        if (empty($project)) {
            $msg = JText::sprintf('COM_QF_NOT_PROJECT', $id);
        }

        if (!$msg) {
            $html = $qfFilds->getResultHtml($project);
        }

        if (!$msg && !$qfFilds->submited) {
            $msg = JText::_('COM_QF_CANNOT_BE_SENT');
        }

        if (!$msg && $qfFilds->errormes) {
            $msg = implode('<br>', $qfFilds->errormes);
        }

        if (!$msg) {
            $stat = $qfFilds->writeStat($project, $html);
            if (!$stat) {
                $msg = JText::_('COM_QF_NOT_COMPLETED');
            }
        }

        if (!$msg) {
            $sent = $qfFilds->sendMail($project, $html, $stat);
            if (!$sent) {
                $msg = JText::_('COM_QF_EMAIL_WAS_NOT_SENT');
            }
        }


        if (!$msg) {
            $msg = $qfFilds->mlangLabel($project->formparams->thnq_message);
            $msgtype = 'message';
        } else {
            $msgtype = 'error';
        }

        echo '<div class="qfsubmitformres qf'.$msgtype.'"><div class="qfsubmitformrestitle">'.JText::_($msgtype).'</div><div class="qfsubmitformresbody">'.$msg.'</div><div class="qfsubmitformresclose">✕</div></div>';
    }
        break;
    case 'ajaxminicart':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->updateCart();

        echo $html;
    }
        break;
    case 'qfcart':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->pageCart();

        echo $html;
    }
        break;
    case 'qfcartremrow':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->removeRowCart((int)$input->get('num'));

        echo $html;
    }
        break;
    case 'qfcartchangerow':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->changeRowCart((int)$input->get('num'), (int)$input->get('val'));

        echo $html;
    }
        break;
    case 'updateminicart':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->getMiniCartHtml();

        echo $html;
    }
        break;
    case 'confirmCart':
    {
        require_once JPATH_COMPONENT.'/classes/qfcart.php';
        $qfCart = new qfCart;

        $html = $qfCart->confirmCart();

        echo $html;
    }
        break;
}
