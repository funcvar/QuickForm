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

<?php echo $this->form->renderField('title'); ?>

<div class="qfprojectpage">
    <div class="qftabs hor">
        <div class="qftabslabelsbox">
            <div class="qftabsitemlabel qftabactiv"><?php echo Text::_('QF_FORM_SETTINGS');?></div>
            <div class="qftabsitemlabel"><?php echo Text::_('QF_FIELDSET_EMAIL');?></div>
            <div class="qftabsitemlabel"><?php echo Text::_('QF_FIELDSET_CALCULATOR');?></div>
            <div class="qftabsitemlabel"><?php echo Text::_('QF_FIELDSET_ADVANCED');?></div>
        </div>

        <div class="qftabsitem">
            <?php echo $this->form->renderFieldset('formparams'); ?>
        </div>

        <div class="qftabsitem">
            <?php echo $this->form->renderFieldset('emailparams'); ?>
        </div>

        <div class="qftabsitem">
            <?php echo $this->form->renderFieldset('calculatorparams'); ?>
        </div>

        <div class="qftabsitem">
            <?php echo $this->form->renderFieldset('params'); ?>
        </div>
    </div>

    <div class="colright">
        <div>
            <div>
                <?php echo $this->form->renderFieldset('general'); ?>
            </div>
        </div>
    </div>
</div>

</form>
</div>
