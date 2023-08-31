<?php
/**
* @Copyright ((c) plasma-web.ru
		* @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class viewHtml extends baseView
{
	protected $items;

    public function __construct()
    {
        parent::__construct('projects');
        $this->display();
    }

    public function display()
    {
        $this->items = $this->getItems();
        $this->pagination = $this->getPagination();

        $this->addToolbar();
        $this->addFilters();

        parent::display();
    }

    protected function addToolbar()
    {
		$this->settitle('QF_PROGECTS_LIST');

        $html = '<div class="qf3_toolbar">';
        $html .= $this->toolbarBtn('project.add', 'QF_ADD_NEW', ' green');
        $html .= $this->toolbarBtn('projects.activate', 'QF_ACTIVATE', ' gray');
        $html .= $this->toolbarBtn('projects.deactivate', 'QF_DEACTIVATE', ' gray');
        $html .= $this->toolbarBtn('projects.delete', 'Delete', ' red');
				$html .= $this->toolbarBtn('projects.export', 'Export', ' gray');
				$html .= $this->toolbarBtn('projects.import', 'Import', ' gray');
        $html .= $this->toolbarBtn('projects.help', 'Help', '');
        $html .= '</div>';
        echo $html;
    }

    protected function addFilters()
    {
        $html = '<div class="qf3_filters">';

        $html .= $this->filtersearch('projects.search');
        $html .= $this->filter('projects.published', [''=>'QF_STATE', 1=>'QF_PUBLISHED', 0=>'QF_UNPUBLISHED']);
        $html .= $this->filter('projects.language', $this->getLanguages());
        $html .= $this->filter('projects.access', qf::getacs());
        $html .= $this->filter('projects.limit', [''=>'12', 24=>'24', 48=>'48']);

        $html .= '</div>';
        echo $html;
    }

}
