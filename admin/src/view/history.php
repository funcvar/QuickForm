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
        parent::__construct('history');
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
        $this->settitle('QuickForm. QF_EMAIL_HISTORY');

        $html = '<div class="qf3_toolbar">';
        $html .= $this->toolbarBtn('history.save', 'Save', ' green');
        $html .= $this->toolbarBtn('history.saveclose', 'QF_SAVE_CLOSE', ' green');
        $html .= $this->toolbarBtn('history.close', 'Close', ' red');
        $html .= '</div>';
        echo $html;
    }

}
