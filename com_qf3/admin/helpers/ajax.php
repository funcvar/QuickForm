<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
switch($input->get( 'mod' ))
{
	case 'jtext' :
	{
		echo	JText::_($input->get('str'));
	}
	break;

	case 'selectors' :
	{
		echo getSelectors($input->get('id'));
	}
	break;

	case 'getForms' :
	{
		echo getForms($input->get('id'));
	}
	break;

	case 'fieldGroupTitle' :
	{
		echo fieldGroupTitle($input->get('id'));
	}
	break;

}

function fieldGroupTitle($id) {
	if($id){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT title FROM #__qf3_forms WHERE id = ' . ( int ) $id);
		return JText::_($db->loadResult());
	}
	return '';
}

function getForms($id) {
	$db = JFactory::getDBO();
	$db->setQuery('SELECT id, title FROM #__qf3_forms WHERE projectid = ' . ( int ) $id);
	$forms = $db->loadObjectList();
	foreach($forms as $form) $sections[] = JHTML::_('select.option',  $form->id, JText::_($form->title));
	$sections[] = JHTML::_('select.option',  '', JText::_('QF_NO_SELECTED'));
	return  JHTML::_('select.genericlist', $sections, 'filter_form', '', 'value', 'text', '' );
}


function getSelectors($id) {
	$db = JFactory::getDBO();
	$html = '<div class="qfselectors">';

		if($id) {
			$db->setQuery('SELECT projectid FROM #__qf3_forms WHERE id = ' . ( int ) $id);
			$projectid = $db->loadResult();
		}
		else {
			$input = JFactory::getApplication()->input;
			$projectid = $input->get('projectid');
		}


		$html .= '<div>' . JText::_('QF_PROGECTS') . ': ';
			$sections = array();
			$db->setQuery('SELECT id, title FROM #__qf3_projects');
			$projects = $db->loadObjectList();
			foreach($projects as $project) $sections[] = JHTML::_('select.option',  $project->id, JText::_($project->title));
			$html .=  JHTML::_('select.genericlist', $sections, 'filter_project', '', 'value', 'text', $projectid );
		$html .= '</div>';

		$html .= '<div>' . JText::_('QF_FIELD_GROUPS') . ': ';
			$sections = array();
			$db->setQuery('SELECT id, title FROM #__qf3_forms WHERE projectid = ' . ( int ) $projectid);
			$forms = $db->loadObjectList();
			foreach($forms as $form) $sections[] = JHTML::_('select.option',  $form->id, JText::_($form->title));
			$sections[] = JHTML::_('select.option',  '', JText::_('QF_NO_SELECTED'));
			$html .=  JHTML::_('select.genericlist', $sections, 'filter_form', '', 'value', 'text', $id );
		$html .= '</div>';

	$html .= '</div>';
	return $html;
}
