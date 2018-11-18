/* @Copyright ((c) juice-lab.ru
v 4.0.1
 */

jQuery(document).ready(function($) {

  var radios = document.getElementsByName('jform[params][mod_type]');

  function getRadioVal(radios) {
    var val;
    for (var i = 0, len = radios.length; i < len; i++) {
      if (radios[i].checked) {
        val = radios[i].value;
        break;
      }
    }
    return val;
  }

  function changeset() {

    var v = 1*getRadioVal(radios);

    if (!v) {
      $('.cartfilds').closest('.control-group').hide();
      $('.formfilds').closest('.control-group').show();
      $('input[name="jform[params][id]"]').attr('required', true);
    }

    if (v == 1) {
      $('.formfilds').closest('.control-group').hide();
      $('.cartfilds').closest('.control-group').show();
      $('input[name="jform[params][id]"]').attr('required', false);
    }

  }


  changeset();

  $(radios).on('change', function() {
    changeset();
  })

});
