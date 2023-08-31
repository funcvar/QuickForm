<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;
?>

<style>
    #sidebar-wrapper {
        display: none;
    }
</style>

<div class="qf_form_style">
    <form method="post" name="qfadminform" class="formstyle">
        <?php echo $this->form->renderField('st_title'); ?>

        <div class="qfprojectpage">
            <div class="qf_sheet">
                <?php echo $this->items->st_form;  ?>
            </div>
            <div class="colright">
                <div>
                    <div>
                        <?php echo $this->form->renderFieldset('details'); ?>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
