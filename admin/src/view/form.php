<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;

\defined ('QF3_VERSION') or die;

class viewHtml extends baseView {
  protected $items;

  public function __construct () {
    parent::__construct ('form');
    $this->display ();
  }

  public function display () {
    $this->items = $this->getItems ();
    $this->form = $this->getForm ();

    if (! $this->items) {
      $this->items = new \stdClass ();
      $this->items->projectid = (int) $this->get ('projectid', $_GET);
      $this->items->id = 0;
      $this->items->def = 0;
    }

    $this->addScript ('js', 'form.js');
    $this->addScript ('css', 'form.css');

    $this->addToolbar ();

    parent::display ();
  }

  protected function addToolbar () {
    $id = (int) $this->get ('id', $this->items);
    if ($id)
      $this->settitle ('QuickForm. QF_EDIT_FIELDS');
    else
      $this->settitle ('QuickForm. QF_ADD_FIELDS');

    $html = '<div class="qf3_toolbar">';
    $html .= $this->toolbarBtn ('form.save', 'Save', ' green');
    $html .= $this->toolbarBtn ('form.saveclose', 'QF_SAVE_CLOSE', ' green');
    if ($id)
      $html .= $this->toolbarBtn ('form.savecopy', 'Save Copy', ' green');
    $html .= $this->toolbarBtn ('form.addfield', 'QF_ADD_FIELD', ' green');
    $html .= $this->toolbarBtn ('forms.close', 'Close', ' red');
    $html .= '</div>';
    echo $html;
  }

}