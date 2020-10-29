<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

class com_qf3InstallerScript
{
    public function install($parent)
    {
        $db = JFactory::getDBO();
        $status = new stdClass;
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;
        $status->modules = array();
        $status->plugins = array();
        $status->component = $manifest;

        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/';
            $installer = new JInstaller;
            $result = $installer->install($path);
            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
            $db->setQuery($query);
            $db->execute();
            $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
        }

        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            if (is_null($client)) {
                $client = 'site';
            }
            ($client == 'administrator') ? $path = $src.'/administrator/modules/'.$name : $path = $src.'/modules/'.$name;


            $installer = new JInstaller;
            $result = $installer->install($path);
            $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
        }

        $this->installationResults($status);
    }

    public function update($parent)
    {
        $db = JFactory::getDBO();
        $status = new stdClass;
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;
        $status->modules = array();
        $status->plugins = array();
        $status->component = $manifest;

        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/';
            $installer = new JInstaller;
            $result = $installer->install($path);
            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
            $db->setQuery($query);
            $db->execute();
            $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
        }

        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            if (is_null($client)) {
                $client = 'site';
            }
            ($client == 'administrator') ? $path = $src.'/administrator/modules/'.$name : $path = $src.'/modules/'.$name;


            $installer = new JInstaller;
            $result = $installer->install($path);
            $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
        }

        $this->updateResults($status);
    }

    public function uninstall($parent)
    {
				$db = JFactory::getDBO();
				$status = new stdClass;
				$manifest = $parent->getParent()->manifest;
				$status->modules = array();
				$status->plugins = array();
				$status->component = $manifest;

				$plugins = $manifest->xpath('plugins/plugin');

        foreach ($plugins as $plugin) {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($name)." AND folder = ".$db->Quote($group);
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions)) {
                foreach ($extensions as $id) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('plugin', $id);
                }
                $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
            }
        }

        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            $db = JFactory::getDBO();
            $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions)) {
                foreach ($extensions as $id) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('module', $id);
                }
                $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
            }
        }
        $this->uninstallationResults($status);
    }


    private function uninstallationResults($status)
    {
        $language = JFactory::getLanguage();
        $language->load('com_qf3');
        $install = '<span style="color:green">✔ '.JText::_('QF_REMOVED').'</span>';
        $notinstall = '<span style="color:red">'.JText::_('QF_NOT_REMOVED').'</span>';
        echo $this->setStyle();
        echo '<h2>' . JText::_('QF_REMOVAL_STATUS') . '</h2>'; ?>
			 <div class="qfdiv">
			 <table class="adminlist table table-striped">
					 <tbody>
						 <tr>
								 <th>component</th>
								 <th>version</th>
								 <th></th>
						 </tr>
						 <tr>
								 <td><?php echo $status->component->name; ?></td>
								 <td><?php echo $status->component->version; ?></td>
								 <td><?php echo $install; ?></td>
						 </tr>
						 <tr>
								 <th>module</th>
								 <th>client</th>
								 <th></th>
						 </tr>
						 <?php foreach ($status->modules as $module): ?>
						 <tr>
								 <td><?php echo $module['name']; ?></td>
								 <td><?php echo $module['client']; ?></td>
								 <td><?php echo $module['result']?$install:$notinstall; ?></td>
						 </tr>
						 <?php endforeach; ?>
						 <tr>
								 <th>plugin</th>
								 <th>group</th>
								 <th></th>
						 </tr>
						 <?php foreach ($status->plugins as $plugin): ?>
						 <tr>
								 <td><?php echo $plugin['name']; ?></td>
								 <td><?php echo $plugin['group']; ?></td>
								 <td><?php echo $plugin['result']?$install:$notinstall; ?></td>
						 </tr>
						 <?php endforeach; ?>
					 </tbody>
			 </table>
			 </div>
			 <div class="qfdivfooter">
				 <div class="qfdivfooterinner"><?php echo JText::_('QF_REMOVED_MESS'); ?>
				 </div>
			 </div>
    <?php
    }

    private function updateResults($status)
    {
        $language = JFactory::getLanguage();
        $language->load('com_qf3');
        $install = '<span style="color:green">✔ '.JText::_('QF_INSTALLED').'</span>';
        $notinstall = '<span style="color:red">'.JText::_('QF_NOT_INSTALLED').'</span>';
        echo $this->setStyle();
        echo '<h2>' . JText::_('QF_UPDATE_STATUS') . '</h2>'; ?>

				<div class="qfdiv">
				<table class="adminlist table table-striped">
            <tbody>
							<tr>
									<th>component</th>
									<th>version</th>
									<th></th>
							</tr>
              <tr>
                  <td><?php echo $status->component->name; ?></td>
									<td><?php echo $status->component->version; ?></td>
                  <td><?php echo $install; ?></td>
              </tr>
              <tr>
                  <th>module</th>
                  <th>client</th>
                  <th></th>
              </tr>
              <?php foreach ($status->modules as $module): ?>
              <tr>
                  <td><?php echo $module['name']; ?></td>
                  <td><?php echo $module['client']; ?></td>
                  <td><?php echo $module['result']?$install:$notinstall; ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <th>plugin</th>
                  <th>group</th>
                  <th></th>
              </tr>
              <?php foreach ($status->plugins as $plugin): ?>
              <tr>
                  <td><?php echo $plugin['name']; ?></td>
                  <td><?php echo $plugin['group']; ?></td>
                  <td><?php echo $plugin['result']?$install:$notinstall; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
				</div>
				<div class="qfdivfooter">
					<div class="qfdivfooterinner"><?php echo JText::_('QF_UPDATE_MESS_1'); ?> <a href="index.php?option=com_qf3&view=projects"><?php echo JText::_('QF_INSTALLATION_MESS_2'); ?></a>.
					</div>
				</div>
    <?php
    }

    private function installationResults($status)
    {
        $language = JFactory::getLanguage();
        $language->load('com_qf3');
        $install = '<span style="color:green">✔ '.JText::_('QF_INSTALLED').'</span>';
        $notinstall = '<span style="color:red">'.JText::_('QF_NOT_INSTALLED').'</span>';
        echo $this->setStyle();
        echo '<h2>' . JText::_('QF_INSTALLATION_STATUS') . '</h2>'; ?>

				<div class="qfdiv">
				<table class="adminlist table table-striped">
            <tbody>
							<tr>
									<th>component</th>
									<th>version</th>
									<th></th>
							</tr>
              <tr>
                  <td><?php echo $status->component->name; ?></td>
									<td><?php echo $status->component->version; ?></td>
                  <td><?php echo $install; ?></td>
              </tr>
              <tr>
                  <th>module</th>
                  <th>client</th>
                  <th></th>
              </tr>
              <?php foreach ($status->modules as $module): ?>
              <tr>
                  <td><?php echo $module['name']; ?></td>
                  <td><?php echo $module['client']; ?></td>
                  <td><?php echo $module['result']?$install:$notinstall; ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <th>plugin</th>
                  <th>group</th>
                  <th></th>
              </tr>
              <?php foreach ($status->plugins as $plugin): ?>
              <tr>
                  <td><?php echo $plugin['name']; ?></td>
                  <td><?php echo $plugin['group']; ?></td>
                  <td><?php echo $plugin['result']?$install:$notinstall; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
				</div>
				<div class="qfdivfooter">
					<div class="qfdivfooterinner"><?php echo JText::_('QF_INSTALLATION_MESS_1'); ?> <a href="index.php?option=com_qf3&view=projects"><?php echo JText::_('QF_INSTALLATION_MESS_2'); ?></a>.
					</div>
				</div>
    <?php
    }

    private function setStyle()
    {
        return '<style>
			.adminlist {
				max-width: 1000px;
				margin: 20px auto;
				border: 2px solid #368193;
				text-align: center;
			}
			.adminlist td {
				padding: 3px 0 10px;
				font-size: 1em;
			}
			.adminlist th {
				padding: 10px 0 3px;
				font-size: 0.8em;
				color: #a8a7a7;
			}
			.qfdiv, .qfdivfooter {
				background: #fff;
				margin: 20px 0;
				padding: 20px;
				border: 1px solid #ccc;
			}
			.qfdivfooterinner{
				max-width: 1000px;
				margin: 0 auto;
			}
			</style>';
    }
}
