<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ControllerForms extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        if ($this->input->get('projectid')) {
            $this->view_list = 'forms&projectid='.$this->input->get('projectid');
        }
    }
    public function getModel($name = 'Form', $prefix = 'Qf3Model', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function setDefault()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $cid = $this->input->get('cid', '');
        $id = array_shift($cid);

        $model = $this->getModel();

        if ($model->setHome($id)) {
            $msg = JText::_('JTOOLBAR_REBUILD_SUCCESS');
            $type = 'message';
        } else {
            $msg = $this->getError();
            $type = 'error';
        }

        $projectid = $this->input->get('projectid');
        $this->setredirect('index.php?option=com_qf3&view=forms&projectid=' . $projectid, $msg, $type);
    }

    public function unsetDefault()
    {
        $projectid = $this->input->get('projectid');
        $this->setredirect('index.php?option=com_qf3&view=forms&projectid=' . $projectid, '', '');
    }
}
