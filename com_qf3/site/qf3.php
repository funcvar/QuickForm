<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

header ("Content-type: text/html; charset=utf-8");

$app = JFactory::getApplication();
$task = $app->input->get('task');

if($task =='ajax'){
	require_once JPATH_COMPONENT.'/helpers/ajax.php';
	exit;
}
elseif($task =='qfcartsubmit') {
	require_once JPATH_COMPONENT.'/classes/qfcart.php';
	$qfCart = new qfCart;
	$qfCart->qfcartsubmit();
}
else{

	require_once JPATH_COMPONENT.'/classes/buildemail.php';
	$qfFilds = new qfFilds;

	$qfFilds->qfcheckToken();

	$msgtype = 'error';
	$link = $app->input->get('root', '', 'STRING');
	$id = $app->input->get('id', 0, 'INT');

	$project = $qfFilds->getProjectById($id);
	if(empty($project)){
		$msg = JText::sprintf('COM_QF_NOT_PROJECT', $id);
		$app->redirect($link, $msg, $msgtype);
	}

	$html = $qfFilds->getResultHtml($project);

	if(!$qfFilds->submited){
		$msg = JText::_('COM_QF_CANNOT_BE_SENT');
		$app->redirect($link, $msg, $msgtype);
	}

	if ($err = $qfFilds->getErrormes()) {
		$msg = implode('<br>', $err);
		$app->redirect($link, $msg, $msgtype);
	}

	$stat = $qfFilds->writeStat($project, $html);
	if(!$stat){
		$msg = JText::_('COM_QF_NOT_COMPLETED');
		$app->redirect($link, $msg, $msgtype);
	}

	$sent = $qfFilds->sendMail($project, $html, $stat);
	if(!$sent){
		$msg = JText::_('COM_QF_EMAIL_WAS_NOT_SENT');
		$app->redirect($link, $msg, $msgtype);
	}

	$msg = $qfFilds->mlangLabel($project->formparams->thnq_message);
	$msgtype = 'message';

	if($qfFilds->redirect) $link = $qfFilds->redirect;

	$app->redirect($link, $msg, $msgtype);
}
