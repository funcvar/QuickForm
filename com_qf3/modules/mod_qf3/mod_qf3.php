<?php
/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */

defined('_JEXEC') or die;

if (!$params->get('mod_type')) {
    require_once("components/com_qf3/classes/buildform.php");
    $qf = new QuickForm3();
    echo $qf->getQuickForm((int)$params->get('id'));
} else {
    JHtml::_('jquery.ui');

    if ($params->get('cartcss') != 'none') {
        JHtml::_('stylesheet', 'modules/mod_qf3/css/' . $params->get('cartcss'), array('version' => 'auto'));
    }

    JHtml::_('script', 'components/com_qf3/assets/js/qf3.js', array('version' => 'auto'));
    JHtml::_('script', 'modules/mod_qf3/js/qf_cart.js', array('version' => 'auto'));

    $comqf_params = JComponentHelper::getParams('com_qf3');

    require_once("components/com_qf3/classes/qfcart.php");
    $qfcart = new qfCart();
    echo '<script>var QF_TEXT_2 = "'.JText::_($comqf_params->get('text_2')).'";</script><div class="qf_cart_box">'.$qfcart->getMiniCartHtml().'</div>';
}
