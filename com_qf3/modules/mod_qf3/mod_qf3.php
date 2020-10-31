<?php
/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */

defined('_JEXEC') or die;

require_once("components/com_qf3/classes/buildform.php");
$qf = new QuickForm3();

if (!$params->get('mod_type')) {
    echo $qf->getQuickForm((int)$params->get('id'));
} else {
    echo $qf->getShopModule($params);
}
