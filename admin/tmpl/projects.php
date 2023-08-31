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
            <th><?php echo $this->filterdir('published', 'QF_STATE_2') ?></th>
            <th class="qftitle"><?php echo $this->filterdir('title', 'Title') ?></th>
            <th><?php echo $this->filterdir('hits', 'QF_QUANTITY') ?></th>
            <th><?php echo $this->filterdir('access', 'QF_ACCESS_2') ?></th>
            <th><?php echo $this->filterdir('language', 'QF_LANGUAGE') ?></th>
            <th><?php echo Text::_('QF_PLUGIN_COD'); ?></th>
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
            <td>
                <?php echo $this->publishbtn($item) ?>
            </td>
            <td class="qftitle">
                <a href="index.php?option=com_qf3&view=projects&task=project.edit&id=<?php echo $item->id ?>"><?php echo $item->title ?></a> -> ( <a href="index.php?option=com_qf3&view=projects&task=forms&projectid=<?php echo $item->id ?>"><?php echo Text::_('QF_FIELDS'); ?></a> )
            </td>
            <td>
                <?php if($item->hits) echo $item->hits ?>
            </td>
            <td>
                <?php if($item->access) echo qf::getacs()[$item->access] ?>
            </td>
            <td>
                <?php if($item->language) echo $item->language ?>
            </td>
            <td>
                {QF3=<?php echo $item->id ?>}
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
