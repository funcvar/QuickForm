<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ControllerShopmod extends JControllerForm
{
    public function cancel() {
        $this->checkToken();
        header('Location: index.php?option=com_qf3');
    }


    public function qfapply() {
        $this->checkToken();
        $mess = $this->saveConfigFile();
        $this->setRedirect('index.php?option=com_qf3&view=shopmod', $mess);
    }

    public function qfsave() {
        $this->checkToken();
        $mess = $this->saveConfigFile();
        $this->setRedirect('index.php?option=com_qf3', $mess);
    }

    protected function saveConfigFile() {
		$model = $this->getModel();
		$data  = $this->input->post->get('jform', array(), 'array');
        return $model->createConfigFile($data);
    }

}
