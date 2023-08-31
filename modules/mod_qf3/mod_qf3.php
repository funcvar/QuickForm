<?php
/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */

defined('_JEXEC') or die;

require_once("components/com_qf3/qf3.php");

if (!$params->get('mod_type')) {
    echo QuickForm\qf::form((int)$params->get('id'));
} else {
    echo QuickForm\qf::cart(false);
}
