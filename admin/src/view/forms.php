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
    protected $projectid;
    protected $projectTitle;

    public function __construct()
    {
        $this->projectid = (int) $this->get('projectid', $_GET);
        if(! $this->projectid) {
            echo '<script>document.location.href = "index.php?option=com_qf3&view=projects";</script>';
            exit;
        }
        parent::__construct('forms');
        $this->display();
    }

    public function display()
    {
        $this->items = $this->getItems();
        // $this->pagination = $this->getPagination();
        $this->projectTitle  = $this->getModel()->getProjectTitle();

        $this->addToolbar();
        $this->addFilters();

        parent::display();
    }

    protected function addToolbar()
    {
        $this->settitle('QuickForm. QF_FIELD_GROUPS');

        $html = '<div class="qf3_toolbar">';
        $html .= $this->toolbarBtn('form.add', 'QF_ADD_NEW', ' green');
        $html .= $this->toolbarBtn('forms.delete', 'Delete', ' red');
        $html .= $this->toolbarBtn('forms.help', 'Help', '');
        $html .= '</div>';
        echo $html;
    }

    protected function addFilters()
    {
        $html = '<div class="qf3_filters">';
        $html .= $this->filtersearch('forms.search');
        $html .= '</div>';
        echo $html;
    }
}
