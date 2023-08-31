<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;
?>

<form method="post" name="qfadminform" class="formstyle">

<table class="qftable">
    <thead>
        <tr>
            <th><input autocomplete="off" type="checkbox" name="cid[]" onclick="QFlist.checkAll(this)"></th>
            <th class="qftitle"><?php echo $this->filterdir('st_title', 'Title') ?></th>
            <th><?php echo $this->filterdir('st_status', 'Status') ?></th>
            <th><?php echo $this->filterdir('st_user', 'User') ?></th>
            <th><?php echo $this->filterdir('st_date', 'Date') ?></th>
            <th><?php echo $this->filterdir('st_ip', 'IP') ?></th>
            <th><?php echo $this->filterdir('id', 'ID') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($this->items as $item) {
        ?>
        <tr>
            <td>
                <input autocomplete="off" type="checkbox" name="cid[]" value="<?php echo $item->id ?>">
            </td>
            <td class="qftitle">
                <a href="index.php?option=com_qf3&view=historys&task=history.edit&id=<?php echo $item->id ?>"><?php echo $item->st_title ?></a>
            </td>
            <td>
                <?php $options = $this->model->statusfields();
                echo Text::_($options[$item->st_status]); ?>
            </td>
            <td>
                <a href="index.php?option=com_users&task=user.edit&id=<?php echo $item->st_user ?>"><?php echo \JFactory::getUser($item->st_user)->get('username');?></a>
            </td>
            <td>
                <span class="smol"><?php echo $item->st_date ?></span>
            </td>
            <td>
                <?php echo $item->st_ip ?>
            </td>
            <td>
                <?php echo $item->id ?>
            </td>
        </tr>
        <?php
    }
     ?>
 </tbody>
</table>
<?php echo $this->pagination ?>
</form>
