jQuery(document).ready(function($) {
    var modallink = function() {
        var box = $('#jform_formparams_modallink').closest('.control-group');
        if ($('#jform_formparams_modal').val() == 1) {
            box.show(200);
        } else {
            box.hide(200);
        }
    }
    modallink();

    $('#jform_formparams_modal').change(function() {
        modallink();
    });

    var csschoose = function() {
        var n = $('input[name="jform[formparams][csschoose]"]:checked', '#project-form').val();
        var box1 = $('#jform_formparams_cssform').closest('.control-group');
        var box2 = $('#jform_formparams_createcssfile').closest('.control-group');
        var box3 = $('#jform_formparams_copycssfile').closest('.control-group');
        if (n == 'y') {
            box1.show(200);
            box2.hide(200);
            box3.hide(200);
        } else {
            box1.hide(200);
            box2.show(200);
            box3.show(200);
        }
    }
    csschoose();

    $('input[name="jform[formparams][csschoose]"]').change(function() {
        csschoose();
    });

    var calculatordesk = function() {
        $('.cdesk').hide(100);
        var n = $('input[name="jform[calculatorparams][calculatortype]"]:checked', '#project-form').val();
        $('.cdesk_' + n).show(100);

        var box = $('#jform_calculatorparams_calcformula').closest('.control-group');
        if (n != '0' && n != 'default') {
            box.show(100);
        } else {
            box.hide(100);
        }
    }
    calculatordesk();

    $('input[name="jform[calculatorparams][calculatortype]"]').change(function() {
        calculatordesk();
    });


    var disfiles = function () {
        var fld1 = $('select[name="jform[addfiles]"]');
        var fld2 = $('input[name="jform[reqfiles]"]');
        var fld3 = $('input[name="jform[accept]"]:checked');
        var fld4 = $('input[name="jform[whitelist]"]');
        fld2.closest('.control-group').hide();
        fld3.closest('.control-group').hide();
        fld4.closest('.control-group').hide();
        if(1*fld1.val()) {
            fld2.closest('.control-group').show();
            fld3.closest('.control-group').show();
            if(1*fld3.val()) {
                fld4.closest('.control-group').show();
            }
        }
        fld1.add($('input[name="jform[accept]"]')).on('change',function(){
            disfiles();
        })
    }
    disfiles();

});

Joomla.submitbutton = function(task) {
    var form = document.forms.qfadminForm;
    var cancel = (task && task.indexOf('.cancel') > 0)?1:0;
    if (cancel || document.formvalidator.isValid(form)) {
        Joomla.submitform(task, form);
    }
};
