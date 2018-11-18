<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ModelStatistics extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'st_user', 'a.st_user',
				'st_date', 'a.st_date',
				'st_ip', 'a.st_ip',
				'st_title', 'a.st_title',
				'st_status', 'a.st_status',
			);
		}

		parent::__construct($config);
	}


	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$st_status = $this->getUserStateFromRequest($this->context.'.filter.st_status', 'filter_st_status', '');
		$this->setState('filter.st_status', $st_status);

		parent::populateState($ordering, $direction);
	}


	protected function getStoreId($id = '')
	{
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.st_status');

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__qf3_ps').' AS a');

		if (($st_status = $this->getState('filter.st_status'))!=='') {
			$query->where('a.st_status = '.(int) $st_status);
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$query->where(
				$db->quoteName('a.st_title') . ' LIKE ' . $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'))
			);
		}

		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

}
