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

});

Joomla.submitbutton = function(task) {
    if (task == 'project.cancel' || document.formvalidator.isValid(document.getElementById('project-form'))) {
        Joomla.submitform(task, document.getElementById('project-form'));
    }
};
