/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */
(() => {
  const $ = (s,c) => (c ?? document).querySelector(s),
  $$ = (s,c) => (c ?? document).querySelectorAll(s),
  adminurl = 'index.php?option=com_qf3&task=form.ajax&mod=';

  function JText(str) {
    const replaceOnDocument = (pattern, string, {
      target = document.body
    } = {}) => {
      [target, ...$$("*:not(script):not(noscript):not(style)", target)].forEach(({
        childNodes: [...nodes]
      }) => nodes.filter(({
        nodeType
      }) => nodeType === document.TEXT_NODE).forEach((textNode) => textNode.textContent = textNode.textContent.replace(pattern, string)));
    };
    req(adminurl + "text&str=" + str, (res) => {
      replaceOnDocument('{' + str + '}', res);
    });
    return '{' + str + '}';
  }

  function req(data, func) {
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
      if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
        return func(this.responseText);
      }
    }
    request.open('POST', window.location.href, true);
    if (typeof(data) === 'string') request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    request.send(data);
  }


  document.addEventListener("DOMContentLoaded", () => {

    document.forms.qfadminform.onsubmit = function() {
      return QFadmin.submitform();
    };

    $$('#formtbl > tbody tr').forEach((el) => {
      el.classList.add('draggable');
      QFadmin.fieldActivate(el);
    });

    $('.addfield').onclick = () => {
      var qfmenu = $('#qfmenu');
      qfmenu ? qfmenu.remove() : QFadmin.createmenu();
      return false;
    }

    QFadmin.drag();
  });

  return QFadmin = {
    tag: function(str) {
      var wrapper = document.createElement('div');
      wrapper.innerHTML = str;
      return {
        to: function(parent) {
          return parent.appendChild(wrapper.firstChild)
        }
      }
    },

    createmenu: function() {
      var menu = [{
        "one-off": ["username", "useremail", "userphone", "backemail", "submit"], "input": ["text", "checkbox", "radio", "hidden", "file", "reset", "button"], "HTML5": ["number", "tel", "email", "color", "date", "range", "url"], "QuickForm": ["customHtml", "customPhp", "calculatorSum", "recaptcha", "cloner", "qfincluder", "addToCart", "qftabs", "qfcalendar", {"stepper": ["stepperbox", "stepperbtns"]}, "spoiler", {"qfAdder": ["boxadder", "addercart"]}], "customized": ["qf_number", "qf_range", "qf_checkbox", "qf_file"]
      }, "select", "textarea"];

      function parsemenu(items) {
        var cl, constr, html = '';
        for (var n in items) {
          constr = items[n].constructor;
          cl = (n == 'input' || n == 'HTML5') ? ' class="inp"' : '';
          if (constr == String) html += '<li><a href="#">' + items[n] + '</a></li>';
          else if (constr == Array) html += '<li>' + n + '<ul' + cl + '>' + parsemenu(items[n]) + '</ul></li>';
          else if (constr == Object) html += parsemenu(items[n]);
        }
        return html;
      }

      var menu = QFadmin.tag('<ul id="qfmenu" class="draggable"><li class="before">×</li>' + parsemenu(menu) + '</ul>').to(document.body);
      var height = menu.clientHeight;
      menu.style.height = 0;
      menu.style.transition = 'height 0.3s';
      setTimeout(() => {
        menu.style.height = height + 'px';
      }, 10);

      $$('a', menu).forEach((el) => {
        el.onclick = () => {
          this.addfield(el);
          return false;
        }
      });

      $$('li.before', menu).forEach((el) => {
        el.onclick = () => {
          menu.remove();
          return false;
        }
      });

    },

    getBoxInner: function(dat) {
      switch (dat.teg) {
        case 'input[text]':
        case 'input[hidden]':
        case 'input[number]':
        case 'input[range]':
        case 'qf_number':
        case 'qf_range':
          return this.tmpls.getBoxText(dat);

        case 'input[radio]':
        case 'select':
          return this.tmpls.getBoxRadio(dat);

        case 'boxadder':
          return this.tmpls.getBoxAdder(dat);
        case 'addercart':
          return this.tmpls.getBoxAdderCart(dat);

        case 'input[checkbox]':
        case 'qf_checkbox':
          return this.tmpls.getBoxCheckbox(dat);

        case 'input[file]':
        case 'qf_file':
          return this.tmpls.getBoxFile(dat);

        case 'input[button]':
        case 'input[reset]':
          return this.tmpls.getBoxButton(dat);

        case 'customHtml':
          return this.tmpls.getBoxCustomHtml(dat);
        case 'submit':
          return this.tmpls.getBoxSubmit(dat);
        case 'addToCart':
          return this.tmpls.getBoxAddToCart(dat);
        case 'recaptcha':
          return this.tmpls.getBoxRecaptcha(dat);
        case 'calculatorSum':
          return this.tmpls.getBoxCalculatorSum(dat);
        case 'qfcalendar':
          return this.tmpls.getBoxQfcalendar(dat);
        case 'backemail':
          return this.tmpls.getBoxBackemail(dat);
        case 'qfincluder':
          return this.tmpls.getBoxIncluder(dat);
        case 'spoiler':
          return this.tmpls.getBoxSpoiler(dat);
        case 'cloner':
          return this.tmpls.getBoxCloner(dat);
        case 'qftabs':
          return this.tmpls.getBoxQftabs(dat);
        case 'customPhp':
          return this.tmpls.getBoxCustomPhp(dat);
        case 'stepperbox':
          return this.tmpls.getBoxStepper(dat);
        case 'stepperbtns':
          return this.tmpls.getBoxStepperbtns(dat);
        default:
          return this.tmpls.getBoxDef(dat);
      }
    },

    addfield: function(el) {
      var labelfield, tagname;
      var qftable = $('#formtbl > tbody');

      var tag = el.closest('ul').classList.contains('inp') ? 'input[' + el.innerHTML + ']' : el.innerHTML;

      if (tag == 'select' || tag == 'qftabs' || (tag.indexOf('radio') + 1))
        tagname = '<span class="smbtogl"></span> <a href="#" class="optionstogler">' + tag + '</a>';
      else tagname = tag;

      if (tag == 'customHtml') {
        labelfield = '<textarea class="qflabelclass">your code here...</textarea>';
      } else if (tag == 'recaptcha' || tag == 'cloner' || tag == 'qfincluder') {
        labelfield = '<input type="hidden" value="" class="qflabelclass" />';
      } else if (tag == 'backemail') {
        labelfield = '<input name="qfllabel" type="text" value="Send a copy of this message to your own address" class="qflabelclass" />';
      } else {
        labelfield = '<input name="qfllabel" type="text" value="" class="qflabelclass" />';
      }

      function newfieldnum() {
        var num = [];
        $$('tr', qftable).forEach((el) => {
          var dat = JSON.parse(el.dataset.settings || 0);
          if (dat) num.push(dat.fildnum);
        });
        if (num.length) return (Math.max.apply(Math, num) + 1);
        else return 0;
      }

      var newrow = document.createElement('TR');
      newrow.className = 'draggable';
      newrow.dataset.settings = '{"fildnum":"' + newfieldnum() + '","teg":"' + tag + '"}';
      newrow.innerHTML = '<td class="l_td"><div class="qfsmoll"></div>' + labelfield + '</td><td class="r_td">' + tagname + '</td><td class="atr_td"><a href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></td><td class="drag_td"><a href="#"><i class="fa fa-arrows" aria-hidden="true"></i></a></td><td class="del_td"><a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></td>';
      this.fieldActivate(qftable.appendChild(newrow));
    },

    fieldActivate: function(row) {
      function optionsBoxActivate(row, params) {
        $$('.optionRow', row).forEach((el) => {
          QFadmin.optionRowActivate(el, params);
        });

        if ($('.optionsBox',row).classList.contains('hid')) {
          $('.smbtogl',row).innerHTML = '►';
        } else {
          $('.smbtogl',row).innerHTML = '▼';
        }

        $('.optionstogler',row).onclick = () => {
          if ($('.optionsBox',row).classList.contains('hid')) {
            $('.smbtogl',row).innerHTML = '▼';
            $('.optionsBox',row).classList.remove('hid');
          } else {
            $('.smbtogl',row).innerHTML = '►';
            $('.optionsBox',row).classList.add('hid');
          }
          return false;
        }
      }

      function boxRowSetting(row) {
        function htmlentities(s) {
          var div = document.createElement('div');
          var text = document.createTextNode(s);
          div.appendChild(text);
          return div.innerHTML;
        }

        var dat = JSON.parse(row.dataset.settings || 0);
        var boxInner = QFadmin.getBoxInner(dat);
        var box = QFadmin.tag(QFadmin.getBoxBtns()).to(document.body);
        box.insertAdjacentHTML('beforeend', '<div class="boxtitle">' + htmlentities($('.qflabelclass',row).value).substr(0, 40) + '</div><div class="boxinner" data-teg="' + dat.teg + '"><div class="boxinnerleft">' + boxInner[0] + '</div><div class="boxinnerright">' + boxInner[1] + '</div></div>');
        QFadmin.activateBox(box, row);
      }

      var inner = $('.r_td',row).innerHTML;
      if ((inner.indexOf('select') + 1) || (inner.indexOf('radio') + 1) || (inner.indexOf('qftabs') + 1) || (inner.indexOf('boxadder') + 1)) {
        if (! $('.optionsBox',row)) {
          this.tag('<div class="optionsBox"></div>').to($('.l_td',row));
          this.tag(this.optionRowHtml()).to($('.optionsBox',row));
        }

        var params = [];
        if (inner.indexOf('qftabs') + 1) params = ['rel'];
        if (inner.indexOf('radio') + 1) params = ['rel','img','calc','rdvars'];
        if (inner.indexOf('boxadder') + 1) params = ['add','img'];
        if (inner.indexOf('select') + 1) params = ['rel','calc','rdvars'];
        optionsBoxActivate(row, params);
        }

      $('.del_td a',row).onclick = () => {
        row.remove();
        return false;
      }
      $('.atr_td a',row).onclick = () => {
        var boxsetting = $('.boxsetting');
        if (boxsetting) return boxsetting.remove();
        boxRowSetting(row);
        return false;
      }
      this.paintrow(row);
    },

    paintrow: function(row) {
      row = row.closest('tr');
      function _getAttr(str, attr) {
        var reg = new RegExp(attr + " *= *([\"])([^\"]+)\\1").exec(str);
        if (reg) return reg[2];
      }

      var dat = JSON.parse(row.dataset.settings || 0);

      if (dat.custom && dat.custom.indexOf('required') + 1) row.classList.add('req');
      else row.classList.remove('req');

      var inp = $('input',row);

      if (inp) {
        var flr,flm;
        if (dat.related) inp.classList.add('related');
        else inp.classList.remove('related');
        if (dat.math) inp.classList.add('calc');
        else inp.classList.remove('calc');

        if ($('.optionRow', row)) {
          $$('.optionRow', row).forEach((el)=>{
            var datel = JSON.parse(el.dataset.settings || 0);
            var inpel = $('input',el);

            if (datel.related) {
              flr=1;
              inpel.classList.add('related');
      }
            else inpel.classList.remove('related');

            if (datel.math) {
              flm=1;
              inpel.classList.add('calc');
            }
            else inpel.classList.remove('calc');

            $('img', el)?.remove();
            if (datel.img) {
              inpel.insertAdjacentHTML('beforeBegin', '<img src="'+datel.img+'">');
            }

          });

          if (flr) inp.classList.add('related');
          else inp.classList.remove('related');

          if (flm) inp.classList.add('calc');
          else inp.classList.remove('calc');
        }
      }

      var pl, smol = $('.qfsmoll', row);
      if (!smol) return;
      if (dat.custom && (pl = _getAttr(dat.custom, 'placeholder'))) {
        smol.innerHTML = pl;
      } else {
        smol.innerHTML = '';
      }
    },

    getBoxBtns: function() {
      return '<div class="boxsetting box1"><div class="box1_btns"><a href="#" class="boxsave"><i class="fa fa-plus" aria-hidden="true"></i></a><a href="#" class="boxdelete"><i class="fa fa-times" aria-hidden="true"></i></a></div></div>';
    },

    boxrelated: function(dat) {
      function fieldGroupTitle(id) {
        req(adminurl + "fieldGroupTitle&id=" + id, (res) => {
          $('.field_title_' + id).innerHTML = res;
        });
        return id;
      }

      return '<div class="boxbody dependent_fields"><div class="boxbodyinner"><br>group id: <input name="related" type="text" value="' + (dat.related ? dat.related : '') + '" class="qfrelatedclass" /><button class="qfbtnrelated">select</button><div class = "field_title field_title_' + fieldGroupTitle(dat.related) + '"></div><div class="boxbodyinnerhelp">' + JText('QF_DESCRELATED') + '</div></div></div>';
    },

    boxcalculator: function(dat) {
      return '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + JText('QF_DESCMAT') + '</div>math: <input name="math" type="text" value="' + (dat.math ? dat.math : '') + '" class="qfcalculatorclass" /></div></div>';
    },

    boximg: function (dat) {
      return '<div class="boxbody optionimg"><div class="boxbodyinner"><div>img src: <input name="img" type="text" value="' + (dat.img ? dat.img : '') + '" class="qfimgclass" /></div>'+this.tmpls.addcheckbox(dat, 'imginemail', 'show in email:', false)+'<div>width: <input name="imgw" type="text" value="' + (dat.imgw ? dat.imgw : '') + '" class="imgw" /> height: <input name="imgh" type="text" value="' + (dat.imgh ? dat.imgh : '') + '" class="imgw" /></div></div></div>';
    },

    boxadd: function (dat) {
      return '<div class="boxbody optionadd"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + JText('QF_DESCADD') + '</div>price: <input name="math" type="text" value="' + (dat.math ? dat.math : '') + '" /></div></div>';
    },

    boxover: function (dat,optionRow) {
      let html,ferst = ! optionRow.previousElementSibling;
      html = this.tmpls.addcheckbox(dat, 'checked', 'checked:', ferst);
      return '<div class="boxbody optionover"><div class="boxbodyinner"><div>' + html + '</div></div>';
    },

    activateBox: function(box, row) {
      function addSelectors(box1, box2) {
        var form = document.forms.qfadminform;
        var id = '';
        if ($('input[name=related]', box1).value) id = $('input[name=related]', box1).value;
        req(adminurl + "selectors&id=" + id + "&projectid=" + form.projectid.value, (res) => {
          box2.insertAdjacentHTML('beforeend', res);
          activateBox2(box2);
        });
      }

      function activateBox2(box2) {
        $('#filter_project', box2).onchange = (e) => {
          req(adminurl + "getForms&id=" + e.target.value, (res) => {
            $('#filter_form', box2).parentNode.innerHTML = res;
          });
        }
      }

      function boxsave(box, row) {
        var dat = {};
        var olddat = JSON.parse(row.dataset.settings || 0);
        dat.teg = olddat.teg;
        dat.fildnum = olddat.fildnum;
        $$('input, select, textarea', box).forEach((el) => {
          var name = el.name;
          if (el.type == 'checkbox') {
            if (el.checked) {
              eval('dat.' + name + ' = 1');
            } else {
              eval('dat.' + name + ' = 0');
            }
          } else if (el.type == 'radio') {
            if (el.checked) eval('dat.' + name + ' = el.value');
          } else {
            if (name) eval('dat.' + name + ' = el.value');
          }
        });
        row.dataset.settings = JSON.stringify(dat);
        box.remove();
        QFadmin.paintrow(row);
      }

      function boxsave2(box2, box1) {
        $('input[name=related]', box1).value = $('#filter_form', box2).value;
        $('.field_title', box1).innerHTML = $('#filter_form option:checked', box2).innerHTML;
        box2.remove();
      }

      $('.boxdelete', box).onclick = () => {
        box.remove();
        return false;
      }

      $('.boxsave',box).onclick = () => {
        if (box.classList.contains('box1')) boxsave(box, row);
        else if (box.classList.contains('box2')) boxsave2(box, row);
        return false;
      }

      $$('.boxmenu', box).forEach((el) => {
        el.onclick = () => {
          $$('.boxmenu', box).forEach((one) => one.classList.remove('activ'));
          $$('.boxbody', box).forEach((one) => one.classList.remove('activ'));
          el.classList.add('activ');
          var cl = el.className.replace(/\s|boxmenu|activ/g, '');
          $('.boxbody.' + cl, box).classList.add('activ');
          return false;
        }
      });

      $$('.qfbtnrelated', box).forEach((el) => {
        el.onclick = () => {
          var box2 = this.tag(this.getBoxBtns()).to(document.body);
          box2.classList.remove('box1');
          box2.classList.add('box2');
          addSelectors(box, box2);
          this.activateBox(box2, box);
          return false;
        }
      });

      $$('.customheder a', box).forEach((el) => {
        var aCase, aCasev = el.rel;
        el.onclick = () => {
          var area = $('.customfield', box);
          if (!['required', 'multiple', 'checked', 'readonly'].includes(aCasev)) aCase = aCasev + '=""';
          else aCase = aCasev;
          area.value = area.value + ' ' + aCase;
          return false;
        }
      });

      $$('.hidediv', box).forEach((el) => {
        var chen = function() {
          var checked = $('input[type="radio"]:checked', el.previousElementSibling);
          if (checked.value) el.style.display = '';
          else el.style.display = 'none';
        }
        chen();
        $$('input', el.previousElementSibling).forEach((el) => {
          el.onchange = () => chen();
        });
      });

      box.classList.add('draggable');
    },

    optionRowActivate: function(optionRow, params) {
      function insertOption(optionRow) {
        optionRow.insertAdjacentHTML('afterend', QFadmin.optionRowHtml());
        QFadmin.optionRowActivate(optionRow.nextSibling, params);
      }

      function boxOptionSetting(optionRow) {
        var dat = JSON.parse(optionRow.dataset.settings || 0);
        var box = QFadmin.tag(QFadmin.getBoxBtns()).to(document.body);

        var left = '', right = '';
        if (params.includes('add')) {
          left += '<div class="boxmenu optionadd">' + JText('QF_PARAMS') + '</div>';
          right += QFadmin.boxadd(dat);
        }
        if (params.includes('rel')) {
          left += '<div class="boxmenu dependent_fields">' + JText('QF_DEPENDENT_FIELDS') + '</div>';
          right += QFadmin.boxrelated(dat);
        }
        if (params.includes('calc')) {
          left += '<div class="boxmenu calculator">' + JText('QF_CALCULATOR') + '</div>';
          right += QFadmin.boxcalculator(dat);
        }
        if (params.includes('img')) {
          left += '<div class="boxmenu optionimg">' + JText('QF_IMG') + '</div>';
          right += QFadmin.boximg(dat);
        }
        if (params.includes('rdvars')) {
          left += '<div class="boxmenu optionover">' + JText('QF_PARAMS') + '</div>';
          right += QFadmin.boxover(dat,optionRow);
        }

        box.insertAdjacentHTML('beforeend', '<div class="boxtitle">' + $('input', optionRow).value + '</div><div class="boxinner"><div class="boxinnerleft">' + left + '</div><div class="boxinnerright">' + right + '</div></div>');

        $('.boxmenu', box).classList.add('activ');
        $('.boxbody', box).classList.add('activ');

        QFadmin.activateBox(box, optionRow);
      }


      $('.plus', optionRow).onclick = () => {
        insertOption(optionRow);
        return false;
      }
      $('.delete', optionRow).onclick = () => {
        var row = optionRow.closest('tr');
        if ($$('.optionRow', optionRow.parentNode).length > 1) optionRow.remove();
        this.paintrow(row);
        return false;
      }
      $('.setting', optionRow).onclick = () => {
        boxOptionSetting(optionRow);
        return false;
      }
      var label = $('.qflabelclass', optionRow.closest('.l_td'));
      if ($('input', optionRow).classList.contains('calc')) label.classList.add('calc');
      if ($('input', optionRow).classList.contains('related')) label.classList.add('related');
    },

    optionRowHtml: function() {
      return '<div class="optionRow" data-settings="{}"><input name="qfoption" type="text" value="" /><a href="#" class="setting"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a><a href="#" class="plus"><i class="fa fa-plus" aria-hidden="true"></i></a><a href="#" class="delete"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
    },

    submitform: function() {
      var bbox = $$('.boxsetting');
      bbox.forEach((el) => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => {
          el.style.opacity = '1';
        }, 500);
      });
      if (bbox.length) return false;

      var field = [];
      $$('#formtbl > tbody tr').forEach((el, i) => {
        var dat = JSON.parse(el.dataset.settings || 0);
        dat.label = $('.qflabelclass', el)?.value;

        if ($('.optionRow', el)) {
          var opt = [];
          $$('.optionRow', el).forEach((option, n) => {
            var tmp = JSON.parse(option.dataset.settings || 0);
            tmp.label = $('input[name="qfoption"]', option).value;
            opt[n] = tmp;
          });
          dat.options = opt;
        }

        field[i] = dat;
      });
      document.forms.qfadminform.fields.value = JSON.stringify(field);
      return true;
    },

    tmpls: {
      escapeHtml: function(text) {
        var map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) {
          return map[m];
        });
      },


      boxvisibility: function(dat) {
        var html = '<div class="boxbody visibility_in_email"><div class="boxbodyinner"><div><input type="radio" name="hide" value=""' + (!dat.hide ? ' checked' : '') + ' /> ' + JText('QF_BY_DEFAULT');

        if (['input[checkbox]', 'input[radio]', 'select', 'qf_checkbox'].includes(dat.teg)) {

          html += '<br><input type="radio" name="hide" value="1"' + (dat.hide == 1 ? ' checked' : '') + ' /> ' + JText('QF_HIDE_FIELD_AND_DEPENDENT_STRUCTURE');

          html += '<br><input type="radio" name="hide" value="3"' + (dat.hide == 3 ? ' checked' : '') + ' /> ' + JText('QF_HIDE_FIELD_BUT_SHOW_DEPENDENT_STRUCTURE');

          if (dat.teg.indexOf('checkbox')) {
            html += '<br><input type="radio" name="hide" value="2"' + (dat.hide == 2 ? ' checked' : '') + ' /> ' + JText('QF_HIDE_ONLY_IF_NOT_SELECTED');
          }
        } else {
          html += '<br><input type="radio" name="hide" value="1"' + (dat.hide == 1 ? ' checked' : '') + ' /> ' + JText('QF_HIDELETTER');
          html += '<br><input type="radio" name="hide" value="2"' + (dat.hide == 2 ? ' checked' : '') + ' /> ' + JText('QF_HIDE_ONLY_EMPTY');
        }

        return html + '</div></div></div>';
      },

      boxFileVisibility: function(dat) {
        return '<div class="boxbody visibility_in_email"><div class="boxbodyinner"><div>' + this.qfhide(dat) + this.qffiletoemail(dat) + this.qffiletoserver(dat) + '</div></div></div>';
      },

      boxcalendaroptions: function(dat) {
        return '<div class="boxbody options"><div class="boxbodyinner">' + this.addtext(dat, 'format', 'd-m-Y') + this.qfdouble(dat) + '</div></div>';
      },

      boxcalendardisabled: function(dat) {
        return '<div class="boxbody disabl"><div class="boxbodyinner">' + this.addcheckbox(dat, 'past', 'past date', false) + this.addcheckbox(dat, 'sa', 'Saturday', false) + this.addcheckbox(dat, 'su', 'Sunday', false) + '</div></div>';
      },

      boxsubmitoptions: function(dat) {
        return '<div class="boxbody options"><div class="boxbodyinner">' + JText('QF_REDEFINE') + ':<br><br>' + this.addtext(dat, 'redirect', '') + this.addtext(dat, 'email', '') + '</div></div>';
      },

      boxcounters: function(dat) {
        return '<div class="boxbody services"><div class="boxbodyinner">Ya.Metrika<div><span>TARGET_NAME:</span><input type="text" name="ycounter" value="' + (dat.ycounter ? dat.ycounter : '') + '" /></div></div></div>';
      },

      boxcustom: function(dat, arr) {
        var inner = '';
        if (!arr) arr = ['dirname', 'list', 'maxlength', 'pattern', 'readonly', 'size', 'title', 'onclick'];
        arr.forEach(function(el) {
          inner += ' <a href="#" rel="' + el + '">' + el + '</a>';
        });

        return '<br><div><div class="customheder">' + inner + ' etc.</div><textarea name="custom" class="customfield">' + (dat.custom ? this.escapeHtml(dat.custom) : '') + '</textarea></div>';
      },


      boxMenu: function(arr) {
        var h = '<div class="boxmenu params activ">' + JText('QF_PARAMS') + '</div>';
        for (var i = 0; i < arr.length; i++) {
          h += '<div class="boxmenu ' + arr[i] + '">' + JText(('QF_' + arr[i]).toUpperCase()) + '</div>';
        }
        return h;
      },

      boxdescription: function(str) {
        return '<div class="boxbody description">' + JText(str) + '</div>';
      },

      boxparams: function(dat, html) {
        var form = document.forms.qfadminform;
        var num = form.id.value ? form.id.value + '.' + dat.fildnum : '';
        return '<div class="boxbody params activ"><div class="boxbodyinner"><div><span>fieldid: </span>' + num + '<input type="hidden" name="fildnum" value="' + dat.fildnum + '" /><hr></div>' + html + '</div></div>';
      },


      getBoxText: function(dat) {
        if (['input[text]', 'input[hidden]'].includes(dat.teg)) var arr = ['autocomplete', 'class', 'pattern', 'placeholder', 'required', 'value'];
        else var arr = ['class', 'max', 'min', 'required', 'step', 'value'];
        var html = ((dat.teg == 'qf_number') ? this.qforient(dat) : '') + this.boxcustom(dat, arr);
        var boxBody = this.boxparams(dat, html) + this.boxvisibility(dat) + QFadmin.boxcalculator(dat);
        return [this.boxMenu(['visibility_in_email', 'calculator']), boxBody];
      },

      getBoxCheckbox: function(dat) {
        var html = this.qfpos(dat) + this.boxcustom(dat, ['class', 'checked', 'placeholder', 'required']);
        var boxBody = this.boxparams(dat, html) + QFadmin.boxrelated(dat) + this.boxvisibility(dat) + QFadmin.boxcalculator(dat);
        return [this.boxMenu(['dependent_fields', 'visibility_in_email', 'calculator']), boxBody];
      },

      getBoxRadio: function(dat) {
        var html = ((dat.teg.indexOf('radio') + 1) ? this.qforient(dat) : '') + this.boxcustom(dat, ['class', 'placeholder', 'required']);
        var boxBody = this.boxparams(dat, html) + this.boxvisibility(dat);
        return [this.boxMenu(['visibility_in_email']), boxBody];
      },

      getBoxAdder: function(dat) {
        var html = this.qfpos(dat) + this.addtext(dat, 'unit', '') + this.addtext(dat, 'fixed', '0') + this.qfformat(dat);
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_ADDER');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxAdderCart: function(dat) {
        var html = this.qfpos(dat) + this.addtext(dat, 'unit', '') + this.addtext(dat, 'fixed', '0') + this.qfformat(dat);
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_ADDER_CART');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxFile: function(dat) {
        var html = this.qfpos(dat) + this.addtext(dat, 'extens', 'jpg,gif,png') + this.boxcustom(dat, ['accept', 'class', 'multiple', 'required']);
        var boxBody = this.boxparams(dat, html) + this.boxFileVisibility(dat) + this.boxdescription('QF_QFFILE');
        return [this.boxMenu(['visibility_in_email', 'description']), boxBody];
      },

      getBoxCustomHtml: function(dat) {
        var html = this.addcheckbox(dat, 'qfshowf', 'QF_SHOWF', true) + this.addcheckbox(dat, 'qfshowl', 'QF_SHOWL', false);
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_CUSTOMHTML');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxSubmit: function(dat) {
        var html = this.boxcustom(dat, ['class', 'title', 'value']);
        var boxBody = this.boxparams(dat, html) + this.boxsubmitoptions(dat) + this.boxcounters(dat);
        return [this.boxMenu(['options', 'services']), boxBody];
      },

      getBoxButton: function(dat) {
        var html = this.boxcustom(dat, ['class', 'value']);
        var boxBody = this.boxparams(dat, html);
        return [this.boxMenu([]), boxBody];
      },

      getBoxAddToCart: function(dat) {
        var html = this.boxcustom(dat, ['class', 'value']);
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_DESCADDTOCART');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxRecaptcha: function(dat) {
        var html = this.addtext(dat, 'class', '');
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_DESCRECAPTCHA');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxCalculatorSum: function(dat) {
        var html = this.qfpos(dat) + this.addtext(dat, 'unit', '') + this.addtext(dat, 'fixed', '0') + this.qfformat(dat) + this.addtext(dat, 'class', '');
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_CALCSUM');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxQfcalendar: function(dat) {
        var html = this.qfhide(dat) + this.boxcustom(dat, ['class', 'placeholder', 'value', 'required']);
        var boxBody = this.boxparams(dat, html) + this.boxcalendaroptions(dat) + this.boxcalendardisabled(dat) + QFadmin.boxcalculator(dat) + this.boxdescription('QF_DESCCALENDAR');
        return [this.boxMenu(['options', 'disabl', 'calculator', 'description']), boxBody];
      },

      getBoxBackemail: function(dat) {
        var html = this.qfpos(dat) + this.addcheckbox(dat, 'backhide', 'QF_HIDE', false) + this.boxcustom(dat, ['class', 'checked', 'required']);
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_BACKEMAIL');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxIncluder: function(dat) {
        var boxBody = this.boxparams(dat, '') + QFadmin.boxrelated(dat) + this.boxdescription('QF_DESCINCLUDER');
        return [this.boxMenu(['dependent_fields', 'description']), boxBody];
      },

      getBoxSpoiler: function(dat) {
        var html = this.qfspoiler(dat) + this.addtext(dat, 'class', '');
        var boxBody = this.boxparams(dat, html) + QFadmin.boxrelated(dat) + this.boxdescription('hi');
        return [this.boxMenu(['dependent_fields', 'description']), boxBody];
      },

      getBoxCloner: function(dat) {
        var html = this.qforient(dat) + this.addcheckbox(dat, 'sum', 'QF_SUMCLONER', false) + this.addtext(dat, 'max', '') + this.addtext(dat, 'numbering', '');
        var boxBody = this.boxparams(dat, html) + QFadmin.boxrelated(dat);
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + JText('QF_DESCMATCL') + '</div>' + this.addtext(dat, 'clonerstart', '') + this.addtext(dat, 'clonerend', '') + '</div></div>';
        boxBody += this.boxdescription('QF_DESCCLONER');
        return [this.boxMenu(['dependent_fields', 'calculator', 'description']), boxBody];
      },

      getBoxQftabs: function(dat) {
        var html = this.qforient(dat) + this.qfhide(dat) + this.addtext(dat, 'class', '');
        var boxBody = this.boxparams(dat, html) + this.boxdescription('QF_DESCTABS');
        return [this.boxMenu(['description']), boxBody];
      },

      getBoxCustomPhp: function(dat) {
        var boxBody = this.boxparams(dat, '') + this.qfcustomphp1(dat) + this.qfcustomphp2(dat);
        return [this.boxMenu(['form', 'email']), boxBody];
      },

      getBoxStepper: function(dat) {
        var html = this.addtext(dat, 'class', '');
        var boxBody = this.boxparams(dat, html) + QFadmin.boxrelated(dat) + this.boxdescription('QF_DESCSTEPPER');
        return [this.boxMenu(['dependent_fields', 'description']), boxBody];
      },

      getBoxStepperbtns: function(dat) {
        var html = this.addtext(dat, 'class', '') + this.addtext(dat, 'prev', '') + this.addtext(dat, 'next', '');
        var boxBody = this.boxparams(dat, html) + QFadmin.boxrelated(dat) + this.boxdescription('QF_STEPPERBTNS');
        return [this.boxMenu(['dependent_fields', 'description']), boxBody];
      },

      getBoxDef: function(dat) {
        var html = this.boxcustom(dat, ['autocomplete', 'class', 'pattern', 'placeholder', 'value', 'required']);
        var boxBody = this.boxparams(dat, html) + this.boxvisibility(dat);
        return [this.boxMenu(['visibility_in_email']), boxBody];
      },

      addtext: function(dat, atr, v) {
        return '<div><span>' + atr + ':</span><input type="text" name="' + atr + '" value="' + ((atr in dat) ? this.escapeHtml(dat[atr]) : v) + '" /></div>';
      },

      addcheckbox: function(dat, atr, text, checked) {
        return '<div><input type="checkbox" name="' + atr + '" value="1"' + ((atr in dat) ? (dat[atr] ? ' checked' : '') : (checked ? ' checked' : '')) + ' /><span> ' + JText(text) + '</span></div>';
      },

      qfhide: function(dat) {
        return '<div><input type="checkbox" name="hide" value="1"' + (dat.hide ? ' checked' : '') + ' /><span> ' + JText('QF_HIDELETTER') + '</span></div>';
      },

      qffiletoemail: function(dat) {
        return '<div><input type="checkbox" name="filetoemail" value="1"' + (('filetoemail' in dat) ? (dat.filetoemail ? ' checked' : '') : ' checked') + ' /><span> ' + JText('QF_FILETOEMAIL') + '</span></div>';
      },

      qffiletoserver: function(dat) {
        return '<div><input type="checkbox" name="filetoserver" value="1"' + (dat.filetoserver ? ' checked' : '') + (!qffilesmod ? ' disabled' : '') + ' /><span> ' + JText('QF_FILETOSERVER') + '</span></div>';
      },

      qfspoiler: function(dat) {
        return '<div><input type="radio" name="splr" value=""' + (!dat.splr ? ' checked' : '') + ' /> visible <input type="radio" name="splr" value="1"' + (dat.splr==1 ? ' checked' : '') + ' /> hidden <input type="radio" name="splr" value="2"' + (!qfcde ? ' disabled' : '') + (dat.splr==2 ? ' checked' : '') + ' /> modal</div>';
      },

      qfpos: function(dat) {
        return '<div><span>label:</span><input type="radio" name="pos" value=""' + (!dat.pos ? ' checked' : '') + ' /> before <input type="radio" name="pos" value="1"' + (dat.pos ? ' checked' : '') + ' /> after</div>';
      },

      qforient: function(dat) {
        return '<div><input type="radio" name="orient" value=""' + (!dat.orient ? ' checked' : '') + ' /> vertical <input type="radio" name="orient" value="1"' + (dat.orient ? ' checked' : '') + ' /> horizontal</div>';
      },

      qfcustomphp1: function(dat) {
        return '<div class="boxbody form"><div><textarea name="customphp1" class="customphp">' + (('customphp1' in dat) ? this.escapeHtml(dat.customphp1) : '<div>example 1:</div>\r\n&lt;?php echo "Hello world!"; ?&gt;') + '</textarea></div></div>';
      },

      qfcustomphp2: function(dat) {
        return '<div class="boxbody email"><div><textarea name="customphp2" class="customphp">' + (('customphp2' in dat) ? this.escapeHtml(dat.customphp2) : '<div>example 2:</div>\r\n&lt;?php echo "Hello World"; ?&gt;') + '</textarea></div></div>';
      },

      qfformat: function(dat) {
        return '<div><span>format:</span><select name="format"><option value="0"' + (!dat.format ? ' selected' : '') + '>1 250 500,75</option><option value="1"' + (dat.format == 1 ? ' selected' : '') + '>1,250,500.75</option><option value="2"' + (dat.format == 2 ? ' selected' : '') + '>1250500.75</option></select></div>';
      },

      qfdouble: function(dat) {
        return '<div><input type="radio" name="double" value=""' + (!dat.double ? ' checked' : '') + ' /> single <input type="radio" name="double" value="1"' + (dat.double ? ' checked' : '') + ' /> double</div><div class="hidediv"><span>label 1:</span><input type="text" name="leb1" value="' + (dat.leb1 ? this.escapeHtml(dat.leb1) : '') + '" /><span>label 2:</span><input type="text" name="leb2" value="' + (dat.leb2 ? this.escapeHtml(dat.leb2) : '') + '" /><span>value 1:</span><input type="text" name="val1" value="' + (dat.val1 ? this.escapeHtml(dat.val1) : '') + '" /><span>value 2:</span><input type="text" name="val2" value="' + (dat.val2 ? this.escapeHtml(dat.val2) : '') + '" /></div>';
      },

    },

    drag: function() {
      var dragObject = {};

      function onMouseDown(e) {
        if (e.which != 1) return;
        if (!(e.target.tagName == 'DIV' || e.target.closest('.drag_td') || e.target.closest('ul'))) return;
        var elem = e.target.closest('.draggable');
        if (!elem) return;
        if (elem.tagName == 'TR') {
          $$('td', elem).forEach((el) => {
            el.style.width = el.offsetWidth + 'px';
          });
          let formdiv = $('.formdiv').getBoundingClientRect();
          dragObject.fh = Math.round(formdiv.top + pageYOffset);
          dragObject.fl = Math.round(formdiv.left + pageXOffset);
        }
        dragObject.elem = elem;
        dragObject.downX = e.pageX;
        dragObject.downY = e.pageY;
        return false;
      }

      function onMouseMove(e) {
        if (!dragObject.elem) return;

        if (!dragObject.avatar) {
          var moveX = e.pageX - dragObject.downX;
          var moveY = e.pageY - dragObject.downY;

          if (Math.abs(moveX) < 3 && Math.abs(moveY) < 3) {
            return;
          }

          dragObject.avatar = true;

          var box = dragObject.elem.getBoundingClientRect();
          dragObject.shiftX = dragObject.downX - box.left - pageXOffset;
          dragObject.shiftY = dragObject.downY - box.top - pageYOffset;
          startDrag(e);
        }

        if (dragObject.elem.tagName == 'TR') {
          dragObject.elem.style.left = dragObject.downX - dragObject.shiftX - dragObject.fl + 'px';
          dragObject.elem.style.top = e.pageY - dragObject.shiftY - dragObject.fh + 'px';
        } else {
          dragObject.elem.style.left = e.pageX - dragObject.shiftX + 'px';
        dragObject.elem.style.top = e.pageY - dragObject.shiftY + 'px';
        }

        return false;
      }

      function onMouseUp(e) {
        if (dragObject.avatar) {
          finishDrag(e);
        }
        dragObject = {};
      }

      function finishDrag(e) {
        if (dragObject.elem.tagName == 'TR') {
          var dropElem = findDroppable(e);
          if (!dropElem) {
            dropElem = dragObject.elem;
          }
          dropElem.style.position = '';
          dropElem.style.left = '';
          dropElem.style.top = '';
          dropElem.style.zIndex = '';
        }
      }

      function startDrag(e) {
        dragObject.elem.style.zIndex = 9999;
        dragObject.elem.style.position = 'absolute';
      }

      function findDroppable(event) {
        dragObject.elem.hidden = true;
        var elem = document.elementFromPoint(event.clientX, event.clientY);
        dragObject.elem.hidden = false;
        if (elem == null) {
          return null;
        }
        var dropElem = elem.closest('#formtbl > tbody tr');
        if (dropElem) {
          return dropElem.parentNode.insertBefore(dragObject.elem, dropElem.nextSibling);
        }

        if (elem.closest('#formtbl > thead')) {
          dropElem = $('#formtbl > tbody tr');
          return dropElem.parentNode.insertBefore(dragObject.elem, dropElem);
        }
      }

      document.onmousemove = onMouseMove;
      document.onmouseup = onMouseUp;
      document.onmousedown = onMouseDown;
      return this;
    },
  }

})();
