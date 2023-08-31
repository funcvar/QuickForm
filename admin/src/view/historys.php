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
        parent::__construct('historys');
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
        $this->settitle('QuickForm. QF_EMAIL_HISTORY');

        $html = '<div class="qf3_toolbar">';
        $html .= $this->toolbarBtn('historys.csv', 'to CSV', ' green');
        $html .= $this->toolbarBtn('historys.delete', 'Delete', ' red');
        $html .= '</div>';
        echo $html;
    }

    protected function addFilters()
    {
        $html = '<div class="qf3_filters">';

        $html .= $this->filtersearch('historys.search');
        $html .= $this->filter('historys.st_status', $this->model->statusfields());
        $html .= $this->filter('historys.limit', [''=>'12', 24=>'24', 48=>'48', 100=>'100']);

        $html .= '</div>';
        echo $html;
    }

}
