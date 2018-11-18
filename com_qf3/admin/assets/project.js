jQuery(document).ready(function($) {
  var modallink = function(){
    var box = $('#jform_formparams_modallink').closest('.control-group');
    if ($('#jform_formparams_modal').val()==1) {
      box.show(200);
    }
    else {
      box.hide(200);
    }
  }
  modallink();

  $('#jform_formparams_modal').change(function(){
    modallink();
  });

  var calculatordesk = function(){
    $('.cdesk').hide(100);
    var n = $('input[name="jform[calculatorparams][calculatortype]"]:checked', '#project-form').val();
    $('.cdesk_'+n).show(100);

    var box = $('#jform_calculatorparams_calcformula').closest('.control-group');
    if(n != '0' && n != 'default'){
      box.show(100);
    }
    else {
      box.hide(100);
    }
  }
  calculatordesk();

  $('input[name="jform[calculatorparams][calculatortype]"]').change(function(){
    calculatordesk();
  });

});

Joomla.submitbutton = function(task)
{
  if (task == 'project.cancel' || document.formvalidator.isValid(document.getElementById('project-form')))
  {
    Joomla.submitform(task, document.getElementById('project-form'));
  }
};
