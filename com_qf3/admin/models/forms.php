<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ModelForms extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__qf3_forms', 'a'));

		$query->select($db->quoteName('uc.name', 'editor'))
			->join(
				'LEFT',
				$db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out')
			);


		$query->where($db->quoteName('a.projectid') . ' = ' . (int)$app->input->getInt('projectid'));

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$query->where(
				$db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'))
			);
		}

		$orderCol = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function getProjectTitle()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$projectid = $app->input->getInt('projectid');
		$query = 'SELECT title'
		. ' FROM #__qf3_projects'
		. ' WHERE id = '.(int)$projectid;
		$db->setQuery($query);
		return $db->loadResult();
	}
}
