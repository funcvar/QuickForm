/**
 * @package		Joomla & QuickForm
 * @Copyright ((c) juice-lab.ru
 * @license    GNU/GPL
 */

jQuery(document).ready(function($) {

    var fixHelper;
    fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('#formtbl').find('tbody').sortable({
        handle: '.atr_td',
        helper: fixHelper
    });

    var tableRef = document.getElementById('formtbl').getElementsByTagName('tbody')[0];
    $('tr', tableRef).each(function() {
        fieldActivate(this);
    });

    var formid = document.forms.adminForm.id.value;


    $('#toolbar-iconaddfild').find('button').click(function() {
        var qfmenu = $('#qfmenu');
        qfmenu.length ? qfmenu.remove() : domenu();
        return false;
    });

    function domenu() {
        var els = ['select', 'textarea', 'submit'];
        var els1 = ['userName', 'userEmail', 'userPhone', 'backemail'];
        var els2 = ['text', 'checkbox', 'radio', 'hidden', 'file', 'reset', 'button'];
        var els3 = ['number', 'tel', 'email', 'color', 'date', 'range', 'url'];
        var els4 = ['customHtml', 'customPhp', 'calculatorSum', 'recaptcha', 'cloner', 'qfincluder', 'addToCart', 'qftabs'];
        var els5 = ['qf_number', 'qf_range','qf_checkbox'];

        var rows = function(arr, cl) {
            var html = '';
            arr.forEach(function(el) {
                html += '<li><a href="#" class="' + cl + '">' + el + '</a></li>';
            });
            return html;
        };

        var html = '<ul id="qfmenu">';
        html += '<li><a href="#" class="noqfinp">one-off</a><ul>';
        html += rows(els1, 'qf1');
        html += '</ul></li>';
        html += '<li><a href="#" class="noqfinp">input</a><ul>';
        html += rows(els2, 'qfinp');
        html += '</ul></li>';
        html += '<li><a href="#" class="noqfinp">HTML5</a><ul>';
        html += rows(els3, 'qfinp');
        html += '</ul></li>';
        html += ' <li><a href="#" class="noqfinp">QuickForm</a><ul>';
        html += rows(els4, 'qf4');
        html += '</ul></li>';
        html += ' <li><a href="#" class="noqfinp">customized</a><ul>';
        html += rows(els5, 'qf5');
        html += '</ul></li>';
        html += rows(els, 'qf0');
        html += '</ul>';

        $('#toolbar-iconaddfild').append(html);
        $('#qfmenu').mouseleave(function() {
            $(this).remove();
        }).find('a').click(function() {
            addfield(this);
            $('#qfmenu').remove();
            return false;
        });
    }

    function addfield(el) {
        var teg, leb;
        if (el.className == 'noqfinp') {
            return false;
        }

        if (el.className == 'qfinp') {
            teg = 'input[' + el.innerHTML + ']';
        } else teg = el.innerHTML;
        var rteg = teg;

        if (el.innerHTML == 'select' || el.innerHTML == 'radio' || el.innerHTML == 'qftabs') {
            teg = '<span class="smbtogl"></span> <a href="#" class="optionstogler">' + teg + '</a>';
        }

        var newRow = tableRef.insertRow(tableRef.rows.length);

        if (rteg == 'customHtml') {
            leb = '<textarea class="qflabelclass">your code here...</textarea>';
        } else if (rteg == 'recaptcha' || rteg == 'cloner' || rteg == 'qfincluder') {
            leb = '<input type="hidden" value="" class="qflabelclass" />';
        } else if (rteg == 'backemail') {
            leb = '<input name="qfllabel" type="text" value="Send a copy of this message to your own address" class="qflabelclass" />';
        } else {
            leb = '<input name="qfllabel" type="text" value="" class="qflabelclass" />';
        }

        newRow.innerHTML = '<td class="l_td">' + leb + '</td><td class="r_td">' + teg + '</td><td class="atr_td"><a href="#"><img src="components/com_qf3/assets/setting.png"></a></td><td class="del_td"><a href="#"><img src="components/com_qf3/assets/delete.png"></a></td>';
        $(newRow).data('settings', {
            "fildnum": newfieldnum(),
            'teg': rteg
        });
        fieldActivate(newRow);
    }

    function fieldActivate(row) {
        var inner = $('.r_td', row).html();
        if ((inner.indexOf('select') + 1) || (inner.indexOf('radio') + 1) || (inner.indexOf('qftabs') + 1)) {
            if (!$('.optionsBox', row).length) {
                $('.l_td', row).append('<div class="optionsBox"></div>');
                optionRowHtml().appendTo($('.optionsBox', row));
            }
            if(inner.indexOf('qftabs') + 1){
              optionsBoxActivate(row, false);
            }
            else{
              optionsBoxActivate(row, true);
            }
        }

        $('.del_td a', row).click(function() {
            $(row).remove();
            return false;
        });
        $('.atr_td a', row).click(function() {
            if ($('.boxsetting').length) return $('.boxsetting').remove();
            boxRowSetting(row);
            return false;
        });
    }

    function optionsBoxActivate(row, calc) {
        $('.optionRow', row).each(function() {
            optionRowActivate(this, calc);
        });

        if ($('.optionsBox', row).hasClass('hid')) {
            $('.smbtogl', row).html('►');
        } else {
            $('.smbtogl', row).html('▼');
        }

        $('.optionstogler', row).click(function() {
            if ($('.optionsBox', row).hasClass('hid')) {
                $('.smbtogl', row).html('▼');
                $('.optionsBox', row).removeClass('hid');
            } else {
                $('.smbtogl', row).html('►');
                $('.optionsBox', row).addClass('hid');
            }
            return false;
        });
    }

    function optionRowActivate(optionRow, calc) {
        $('.plus', optionRow).click(function() {
            insertOption(optionRow, calc);
            return false;
        });
        $('.delete', optionRow).click(function() {
            var optionsBox = $(optionRow).parent();
            if ($('.optionRow', optionsBox).length > 1) $(optionRow).remove();
            return false;
        });
        $('.setting', optionRow).click(function() {
            boxOptionSetting(optionRow, calc);
            return false;
        });
        if ($('input', optionRow).hasClass('calc')) $(optionRow).closest('.l_td').find('.qflabelclass').addClass('calc');
        if ($('input', optionRow).hasClass('related')) $(optionRow).closest('.l_td').find('.qflabelclass').addClass('related');
    }

    function insertOption(optionRow, calc) {
        var option = optionRowHtml().insertAfter($(optionRow));
        optionRowActivate(option, calc);
    }


    function optionRowHtml() {
        return $('<div class="optionRow" data-settings="{}"><input name="qfoption" type="text" value="" /><a href="#" class="setting"><img src="components/com_qf3/assets/setting.png"></a><a href="#" class="plus"><img src="components/com_qf3/assets/plus.png"></a><a href="#" class="delete"><img src="components/com_qf3/assets/delete.png"></a></div>');
    }

    function boxOptionSetting(optionRow, calc) {
        var dat = $(optionRow).data('settings');
        var box = getBoxBtns().appendTo($('body'));
        if(calc){
          box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div><div class="boxmenu">calculator</div></div><div class="boxinnerright"><div class="boxbody related-fields activ">' + getForRelated(dat) + '</div><div class="boxbody calculator">' + getForCalculator(dat) + '</div></div></div>');
        }
        else{
          box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div></div><div class="boxinnerright"><div class="boxbody related-fields activ">' + getForRelated(dat) + '</div></div></div>');
        }
        activateBox(box, optionRow);
    }

    function boxRowSetting(row) {
        var dat = $(row).data('settings');
        var boxInner = getBoxInner(dat);
        var box = getBoxBtns().appendTo($('body'));
        box.append('<div class="boxtitle">' + htmlentities($('.qflabelclass', row).val()).substr(0, 40) + '</div><div class="boxinner" data-teg="' + dat.teg + '"><div class="boxinnerleft">' + boxInner[0] + '</div><div class="boxinnerright">' + boxInner[1] + '</div></div>');
        activateBox(box, row);
    }

    function htmlentities(s) {
        var div = document.createElement('div');
        var text = document.createTextNode(s);
        div.appendChild(text);
        return div.innerHTML;
    }

    function escapeHtml(text) {
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
    }

    function getBoxInner(dat) {
        if (dat.teg == 'input[text]') return getBoxText(dat);
        else if (dat.teg == 'input[radio]') return getBoxRadio(dat);
        else if (dat.teg == 'input[checkbox]') return getBoxCheckbox(dat);
        else if (dat.teg == 'qf_checkbox') return getBoxCheckbox(dat);
        else if (dat.teg == 'submit') return getBoxSubmit(dat);
        else if (dat.teg == 'customHtml') return getBoxCustomHtml(dat);
        else if (dat.teg == 'input[number]') return getBoxText(dat);
        else if (dat.teg == 'recaptcha') return getBoxRecaptcha(dat);
        else if (dat.teg == 'input[hidden]') return getBoxText(dat);
        else if (dat.teg == 'input[range]') return getBoxText(dat);
        else if (dat.teg == 'input[button]') return getBoxButton(dat);
        else if (dat.teg == 'input[reset]') return getBoxButton(dat);
        else if (dat.teg == 'calculatorSum') return getBoxCalculatorSum(dat);
        else if (dat.teg == 'cloner') return getBoxCloner(dat);
        else if (dat.teg == 'backemail') return getBoxBackemail(dat);
        else if (dat.teg == 'customPhp') return getBoxCustomPhp(dat);
        else if (dat.teg == 'qfincluder') return getBoxIncluder(dat);
        else if (dat.teg == 'addToCart') return getBoxAddToCart(dat);
        else if (dat.teg == 'qftabs') return getBoxQftabs(dat);
        else if (dat.teg == 'qf_number') return getBoxQfstepper(dat);
        else if (dat.teg == 'qf_range') return getBoxQfslider(dat);
        else if (dat.teg == 'input[file]') return getBoxFile(dat);

        else return getBoxDef(dat);
    }



    function activateBox(box, row) {
        $('.boxdelete', box).click(function() {
            box.remove();
            return false;
        });

        $('.boxsave', box).click(function() {
            boxsave(box, row);
            return false;
        });

        $('.boxmenu', box).click(function() {
            $('.boxmenu', box).add($('.boxbody', box)).removeClass('activ');
            $(this).addClass('activ');
            $('.' + $(this).html(), box).addClass('activ');
            return false;
        });

        $('.customheder a', box).each(function() {
            var aCase = this.innerHTML;
            var area = $('.customfield', box);
            $(this).click(function() {
                area.val(area.val() + ' '+aCase+'=""');
                return false;
            });
        });

        if ('draggable' in box) box.draggable();
    }



    function boxsave(box, row) {
        var dat = $(row).data('settings');
        $('input, textarea', box).each(function() {
            var name = this.name;
            if (this.type == 'checkbox') {
                if (this.checked) {
                    eval('dat.' + name + ' = 1');
                } else {
                    eval('dat.' + name + ' = 0');
                }
            } else if (this.type == 'radio') {
                if (this.checked) eval('dat.' + name + ' = this.value');
            } else {
                if (name) eval('dat.' + name + ' = this.value');
            }
        });
        box.remove();
    }

    function getForRelated(dat) {
        return '<div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCRELATED') + '</div><br>field group ID: <input name="related" type="text" value="' + (dat.related ? dat.related : '') + '" class="qfrelatedclass" /></div>';
    }

    function getForCalculator(dat) {
        return '<div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMAT') + '</div>math: <input name="math" type="text" value="' + (dat.math ? dat.math : '') + '" class="qfcalculatorclass" /></div>';
    }

    function getForCustom(dat) {
        var inner = '';
        var arr = ['autocomplete', 'dirname', 'list', 'maxlength', 'pattern', 'max', 'min', 'readonly', 'step', 'size', 'value', 'onclick'];
        arr.forEach(function(el) {
            inner += ' '+el.link("#")+',';
        });

        return '<div class="boxbody custom"><div class="boxbodyinner"><div class="customheder">' +inner+ ' etc.</div><textarea name="custom" class="customfield">' + (dat.custom ? escapeHtml(dat.custom) : '') + '</textarea></div></div>';
    }

    function getBoxBtns() {
        return $('<div class="boxsetting"><a href="#" class="boxdelete"><img src="components/com_qf3/assets/delete.png"></a><a href="#" class="boxsave"><img src="components/com_qf3/assets/save.png"></a></div>');
    }

    function getBoxBackemail(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div><div class="boxmenu">description</div>';
        var boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfshowf(dat) + qfpos(dat) + qfreg(dat) + qfaddDat(dat, 'class') + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_BACKEMAIL') + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxDef(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxText(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">calculator</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += '<div class="boxbody calculator">' + getForCalculator(dat) + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxCheckbox(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">related-fields</div><div class="boxmenu">calculator</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfchecked(dat) + qfhide(dat) + qfhidech(dat) + qfpos2(dat) + qfaddDat(dat, 'class') + qfend();
        boxBody += '<div class="boxbody related-fields">' + getForRelated(dat) + '</div><div class="boxbody calculator">' + getForCalculator(dat) + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxRadio(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfhide(dat) + qforient(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxFile(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfhide(dat) + qfpos2(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxButton(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div>';
        var boxBody = qfst() + qffildnum(dat) + qfbtnclass(dat) + qfaddDat(dat, 'value') + qfend();
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxCustomHtml(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">description</div>';
        var boxBody = qfst() + qffildnum(dat) + qfshowf(dat) + qfshowl(dat) + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_CUSTOMHTML') + '</div>';
        return [menu, boxBody];
    }
    function getBoxCustomPhp(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">form</div><div class="boxmenu">email</div>';
        var boxBody = qfst() + qffildnum(dat) + qfend();
        boxBody += '<div class="boxbody form">' + qfcustomphp1(dat) + '</div>';
        boxBody += '<div class="boxbody email">' + qfcustomphp2(dat) + '</div>';
        return [menu, boxBody];
    }
    function getBoxCalculatorSum(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">description</div>';
        var boxBody = qfst() + qffildnum(dat) + qfpos(dat) + qfaddDat(dat, 'unit') + qffixed(dat) + qfaddDat(dat, 'class') + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_CALCSUM') + '</div>';
        return [menu, boxBody];
    }
    function getBoxRecaptcha(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">description</div>';
        var boxBody = qfst() + qffildnum(dat) + qfcaptcha(dat) + qfaddDat(dat, 'class') + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_DESCRECAPTCHA') + '</div>';
        return [menu, boxBody];
    }
    function getBoxCloner(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">related-fields</div><div class="boxmenu">calculator</div><div class="boxmenu">description</div>';
        boxBody = qfst() + qffildnum(dat) + qforient(dat) + qfsum(dat) + qfaddDat(dat, 'max') + qfaddDat(dat, 'numbering') + qfend();
        boxBody += '<div class="boxbody related-fields">' + getForRelated(dat) + '</div>';
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMATCL') + '</div>' + qfaddDat(dat, 'clonerstart')+ qfaddDat(dat, 'clonerend')+'</div></div>';
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_DESCCLONER') + '</div>';
        return [menu, boxBody];
    }
    function getBoxIncluder(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">related-fields</div><div class="boxmenu">calculator</div><div class="boxmenu">description</div>';
        boxBody = qfst() + qffildnum(dat) + qfend();
        boxBody += '<div class="boxbody related-fields">' + getForRelated(dat) + '</div>';
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMATINCLUDER') + '</div>'+ qfaddDat(dat, 'condition')+ qfaddDat(dat, 'start')+ qfaddDat(dat, 'end')+'</div></div>';
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_DESCINCLUDER') + '</div>';
        return [menu, boxBody];
    }
    function getBoxAddToCart(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div><div class="boxmenu">description</div>';
        var boxBody = qfst() + qffildnum(dat) + qfaddDat(dat, 'class') + qfcartvalue(dat) + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_DESCADDTOCART') + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxQftabs(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">description</div>';
        boxBody = qfst() + qffildnum(dat) + qforient(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfend();
        boxBody += '<div class="boxbody description">' + QuickForm.JText('QF_DESCTABS') + '</div>';
        return [menu, boxBody];
    }
    function getBoxQfstepper(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">calculator</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qforient(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += '<div class="boxbody calculator">' + getForCalculator(dat) + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxQfslider(dat) {
        var menu, boxBody;
        menu = '<div class="boxmenu activ">params</div><div class="boxmenu">calculator</div><div class="boxmenu">custom</div>';
        boxBody = qfst() + qffildnum(dat) + qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfend();
        boxBody += '<div class="boxbody calculator">' + getForCalculator(dat) + '</div>';
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }
    function getBoxSubmit(dat) {
        var menu = '<div class="boxmenu activ">params</div><div class="boxmenu">custom</div>';
        var boxBody = qfst() + qffildnum(dat) + qfbtnclass(dat) + qfsubmitvalue(dat) + qfaddDat(dat, 'redirect') + qfend();
        boxBody += getForCustom(dat);
        return [menu, boxBody];
    }

    function qfst() {
        return '<div class="boxbody params activ"><div class="boxbodyinner">';
    }
    function qfend() {
        return '</div></div>';
    }

    function qffildnum(dat) {
        var num = formid?formid+'.'+dat.fildnum:'';
        return '<div><span class="leb">fieldid: </span>'+num+'<input type="hidden" name="fildnum" value="' + dat.fildnum + '" /><hr></div>';
    }
    function qfrequired(dat) {
        return '<div><input type="checkbox" name="required" value="1"' + (dat.required ? ' checked' : '') + ' /><span class="leb"> ' + QuickForm.JText('QF_REQUIRED') + '</span></div>';
    }
    function qfshowf(dat) {
        return '<div><input type="checkbox" name="qfshowf" value="1"' + (('qfshowf' in dat) ? (dat.qfshowf ? ' checked' : '') : ' checked') + ' /><span class="leb"> ' + QuickForm.JText('QF_SHOWF') + '</span></div>';
    }
    function qfshowl(dat) {
        return '<div><input type="checkbox" name="qfshowl" value="1"' + (dat.qfshowl ? ' checked' : '') + ' /><span class="leb"> ' + QuickForm.JText('QF_SHOWL') + '</span></div>';
    }
    function qfpos(dat) {
        return '<div><input type="radio" name="pos" value=""' + (!dat.pos ? ' checked' : '') + ' /> text before <input type="radio" name="pos" value="1"' + (dat.pos ? ' checked' : '') + ' /> text after</div>';
    }
    function qfreg(dat) {
        return '<div><input type="radio" name="reg" value=""' + (!dat.reg ? ' checked' : '') + ' /> show all <input type="radio" name="reg" value="1"' + (dat.reg ? ' checked' : '') + ' /> show logged</div>';
    }
    function qfaddDat(dat, atr) {
        return '<div><span class="lebt">'+atr+':</span><input type="text" name="'+atr+'" value="' + (dat[atr] ? escapeHtml(dat[atr]) : '') + '" /></div>';
    }
    function qfhide(dat) {
        return '<div><input type="checkbox" name="hide" value="1"' + (dat.hide ? ' checked' : '') + ' /><span class="leb"> ' + QuickForm.JText('QF_HIDELETTER') + '</span></div>';
    }
    function qfchecked(dat) {
        return '<div><input type="checkbox" name="checked" value="1"' + (dat.checked ? ' checked' : '') + ' /><span class="leb"> checked</span></div>';
    }
    function qfhidech(dat) {
        return '<div><input type="checkbox" name="hidech" value="1"' + (dat.hidech ? ' checked' : '') + ' /><span class="leb"> ' + QuickForm.JText('QF_HIDEUNCHECKED') + '</span></div>';
    }
    function qfpos2(dat) {
        return '<div><input type="radio" name="pos" value=""' + (!dat.pos ? ' checked' : '') + ' /> text before <input type="radio" name="pos" value="1"' + (dat.pos ? ' checked' : '') + ' /> text after</div>';
    }
    function qforient(dat) {
        return '<div><input type="radio" name="orient" value=""' + (!dat.orient ? ' checked' : '') + ' /> vertical <input type="radio" name="orient" value="1"' + (dat.orient ? ' checked' : '') + ' /> horizontal</div>';
    }
    function qfbtnclass(dat) {
        var cl = '';
        if (typeof(dat.class) == 'undefined') cl = 'btn btn-primary';
        return '<div><span class="lebt">class:</span><input type="text" name="class" value="' + (dat.class ? dat.class : cl) + '" /></div>';
    }
    function qfcustomphp1(dat) {
        return '<div><textarea name="customphp1" class="customphp">' + (('customphp1' in dat) ? escapeHtml(dat.customphp1) : '<div>example 1:</div>\r\n&lt;?php echo "Hello world!"; ?&gt;') + '</textarea></div>';
    }
    function qfcustomphp2(dat) {
        return '<div><textarea name="customphp2" class="customphp">' + (('customphp2' in dat) ? escapeHtml(dat.customphp2) : '<div>example 2:</div>\r\n&lt;?php echo "Hello World"; ?&gt;') + '</textarea></div>';
    }
    function qffixed(dat) {
        return '<div><span class="lebt">fixed:</span><input type="text" name="fixed" value="' + (dat.fixed ? dat.fixed : 0) + '" /></div>';
    }
    function qfcaptcha(dat) {
        return '<div><input type="radio" name="show" value=""' + (!dat.show ? ' checked' : '') + ' /> show all <input type="radio" name="show" value="1"' + (dat.show ? ' checked' : '') + ' /> only guest</div>';
    }
    function qfsum(dat) {
        return '<div><input type="checkbox" name="sum" value="1"' + (dat.sum ? ' checked' : '') + ' /><span class="leb"> ' + QuickForm.JText('QF_SUMCLONER') + '</span></div>';
    }
    function qfcartvalue(dat) {
        return '<div><span class="lebt">value:</span><input type="text" name="value" value="' + (('value' in dat) ? escapeHtml(dat.value) : 'QF_ADDTOCART') + '" /></div>';
    }
    function qfsubmitvalue(dat) {
        return '<div><span class="lebt">value:</span><input type="text" name="value" value="' + (('value' in dat) ? escapeHtml(dat.value) : 'QF_SUBMIT') + '" /></div>';
    }

    function newfieldnum() {
        var num = [];
        $('tr', tableRef).each(function() {
            var dat = $(this).data('settings');
            if (dat) num.push(dat.fildnum);
        });
        if (num.length) return (Math.max.apply(Math, num) + 1);
        else return 0;
    }

});


JSON.stringify = JSON.stringify || function(obj) {
    var t = typeof(obj);
    if (t != "object" || obj === null) {
        if (t == "string") obj = '"' + obj + '"';
        return String(obj);
    } else {
        var n, v, json = [],
            arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n];
            t = typeof(v);
            if (t == "string") v = '"' + v + '"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

var QuickForm = {
    JText: function(str) {
        jQuery.ajax({
            url: "index.php?option=com_qf3&task=ajax&mod=jtext&str=" + str,
            success: function(msg) {
                var phrase = 'JText' + str;
                jQuery(":contains(" + phrase + ")").not(":has(:contains(" + phrase + "))").each(function() {
                    var that = jQuery(this);
                    var html = that.html();
                    html = html.replace(new RegExp(phrase, 'gi'), msg);
                    that.html(html);
                });
            }
        });
        return 'JText' + str;
    },

    submitform: function(task, form) {
        var field = [];
        var tableRef = document.getElementById('formtbl').getElementsByTagName('tbody')[0];
        jQuery('tr', tableRef).each(function(i, el) {
            var dat = jQuery(this).data('settings');
            dat.label = jQuery('.qflabelclass', this).val();

            if (dat.teg == 'select' || dat.teg == 'input[radio]' || dat.teg == 'qftabs') {
                var opt = [];
                jQuery('.optionRow', this).each(function(ii, el) {
                    var tmp = jQuery(this).data('settings');
                    tmp.label = jQuery('input[name="qfoption"]', this).val();
                    opt[ii] = tmp;
                });
                dat.options = opt;
            }

            field[i] = dat;
        });
        document.forms.adminForm.fields.value = JSON.stringify(field);
        Joomla.submitform(task, form);
    }
};
