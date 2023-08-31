<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;

\defined ('QF3_VERSION') or die;

class controller extends qf_admin {
  protected $model;
  protected $task;

  public function __construct () {
    $this->task = qf::gettask ();

    if ($this->task) {
      $this->execute ();
    }

    $this->display ();
  }

  public function display () {
    $view = $this->task ? explode ('.', $this->task)[0] : qf::gettask ('view', 'projects');
    $viewpath = QF3_ADMIN_DIR . 'src/view/' . $view . '.php';

    if (is_file ($viewpath)) {
      require_once(QF3_ADMIN_DIR . 'src/view/view.php');
      require_once($viewpath);
      new viewHtml ();
    }
  }

  protected function execute () {
    $arr = explode ('.', $this->task);
    $page = $arr[0];
    $task = isset($arr[1]) ? $arr[1] : '';

    $modelpath = QF3_ADMIN_DIR . 'src/model/' . $page . '.php';

    if (is_file ($modelpath)) {
      require_once(QF3_ADMIN_DIR . 'src/model/model.php');
      require_once($modelpath);
      $modelname = 'QuickForm\\' . $page . 'Model';
      $this->model = new $modelname ();
    }
    else {
      $this->redirect ('projects', false);
    }

    if ($task == 'close') {
      $this->redirect ($this->model->closelink, false);
    }
    elseif ($task == 'closetofields') {
      $this->redirect ($this->model->closetofields, false);
    }

    if (in_array ($task, array('save', 'saveclose', 'savecreate', 'saveconf', 'publish', 'changedef', 'activate', 'deactivate', 'delete', 'export', 'import', 'csv', 'savecopy'))) {
      $canDo = qf::user ()->authorise ('core.edit', 'com_qf3');

      if (! $canDo) {
        $this->model->errors[] = 'QF_NO_ACL';
        $this->redirect ('projects');
      }
    }

    if (in_array ($task, array('save', 'saveclose', 'savecreate', 'saveconf', 'savecopy'))) {
      $res = $this->model->save ();

      if ($task == 'save' || $task =='savecopy') {
        $this->redirect ($this->model->savelink . '&id=' . $res);
      }
      elseif ($task == 'saveclose') {
        $this->redirect ($this->model->closelink);
      }
      elseif ($task == 'savecreate') {
        $this->redirect ($this->model->savecreatelink);
      }
      elseif ($task == 'saveconf') {
        $this->redirect ($page);
      }
    }
    elseif ($task == 'filterdir') {
      $this->model->filterdir ();
      $this->redirect ($this->model->closelink, false);

    }
    elseif ($task == 'filterlist') {
      $this->model->filterlist ($page);
      $this->redirect ($this->model->closelink, false);

    }
    elseif ($task == 'publish') {
      $this->model->publish ($page);
      $this->redirect ($page);

    }
    elseif ($task == 'changedef') {
      $res = $this->model->changeDef ();
      $this->redirect ('projects&task=forms&projectid=' . $res);

    }
    elseif ($task == 'activate') {
      $this->model->activate ($page);
      $this->redirect ($page);

    }
    elseif ($task == 'deactivate') {
      $this->model->deactivate ($page);
      $this->redirect ($page);

    }
    elseif ($task == 'delete') {
      $this->model->delete ($page);
      $this->redirect ($this->model->closelink);

    }
    elseif ($task == 'export') {
      $this->model->export ();
      exit;

    }
    elseif ($task == 'importprojects') {
      qf::conf ()->get ('filesmod'); //gl
      $this->model->importprojects ();
      $this->redirect ($this->model->closelink);

    }
    elseif ($task == 'import') {
      $this->model->import ();
      exit;

    }
    elseif ($task == 'ajax') {
      $this->model->ajax ();
      exit;

    }
    elseif ($task == 'csv') {
      $this->model->csv ();
      exit;

    }
    elseif ($task == 'help') {
      $this->model->help ();
      exit;
    }
  }

  public function redirect ($page, $mes = array()) {
    if ($mes !== false) {
      if ($this->model)
        $mes = $this->model->getmessages ();
    }

    $ses = qf::ses ()->get ('quickform', []);
    $ses['messages'] = $mes;
    qf::ses ()->set ('quickform', $ses);

    parent::redirect ($page);
  }
}