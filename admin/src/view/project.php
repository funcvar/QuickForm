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
        parent::__construct('project');
        $this->display();
    }

    public function display()
    {
        $this->items = $this->getItems();
        $this->form = $this->getForm();
        $this->addToolbar();

        parent::display();
    }

    protected function addToolbar()
    {
        $id = $this->get('id', $this->items);
        if($id) $this->settitle('QuickForm. QF_EDIT_PROGECT');
        else  $this->settitle('QuickForm. QF_ADD_PROGECT');

        $html = '<div class="qf3_toolbar">';
        $html .= $this->toolbarBtn('project.save', 'Save', ' green');
        $html .= $this->toolbarBtn('project.saveclose', 'QF_SAVE_CLOSE', ' green');
        $html .= $this->toolbarBtn('project.savecreate', 'QF_SAVE_CREATE', ' green');
        $html .= $this->toolbarBtn('projects.close', 'Close', ' red');
        if($id) $html .= $this->toolbarBtn('project.closetofields', 'QF_TOOLBAR_CLOSE', ' red');
        $html .= '</div>';
        echo $html;
    }

}
