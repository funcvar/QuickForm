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
        <div class="qfprojectpage">
            <div class="qftabs hor">
                <?php echo $this->form->renderForm(); ?>
            </div>
        </div>
    </form>
</div>
