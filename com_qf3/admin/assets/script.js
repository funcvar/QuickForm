/**
 * @package		Joomla & QuickForm
 * @Copyright ((c) plasma-web.ru
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
        handle: '.drag_td',
        helper: fixHelper
    });

    var tableRef = document.getElementById('formtbl').getElementsByTagName('tbody')[0];
    $('tr', tableRef).each(function() {
        fieldActivate(this);
    });

    var formid = document.forms.adminForm.id.value;


    $('#toolbar-iconaddfild').find('button').click(function() {
        var qfmenu = $('#qfmenu');
        qfmenu.length ? qfmenu.remove() : createmenu();
        return false;
    });

    function createmenu() {
        var menu = [{
            "one-off": ["username", "useremail", "userphone", "backemail", "submit"],
            "input": ["text", "checkbox", "radio", "hidden", "file", "reset", "button"],
            "HTML5": ["number", "tel", "email", "color", "date", "range", "url"],
            "QuickForm": ["customHtml", "customPhp", "calculatorSum", "recaptcha", "cloner", "qfincluder", "addToCart", "qftabs", "qfcalendar", {
                "stepper": ["stepperbox", "stepperbtns"]
            }],
            "customized": ["qf_number", "qf_range", "qf_checkbox", "qf_file"]
        }, "select", "textarea"];

        function parsemenu(data) {
            var n, v, html = '';
            for (n in data) {
                var cl = '';
                if (n == 'input' || n == 'HTML5') cl = ' class="inp"';
                v = data[n];
                if (typeof(v) == "string") html += '<li><a href="#">' + v + '</a></li>';
                else if (v.constructor == Array) html += '<li>' + n + '<ul' + cl + '>' + parsemenu(v) + '</ul></li>';
                else if (typeof(v) == "object") html += parsemenu(v);
            }
            return html;
        }

        var boxmenu = $('<ul id="qfmenu"><li class="before">×</li>' + parsemenu(menu) + '</ul>').appendTo('body');
        boxmenu.find('a').on('click',function() {
            if(!boxmenu.hasClass('dis')) addfield(this);
            return false;
        });
        $('#qfmenu li.before').on('click',function() {
            $('#qfmenu').remove();
            return false;
        });
        if ('draggable' in boxmenu) boxmenu.draggable({
            containment: "window",
            start: function(event, ui) {
                boxmenu.addClass('dis');
            },
            stop: function(event, ui) {
                setTimeout(function(){
                    boxmenu.removeClass('dis');
                }, 300);
            }
        });
    }

    function addfield(el) {
        var teg, leb, rteg;

        if ($(el).closest('ul')[0].className == 'inp')
            teg = 'input[' + el.innerHTML + ']';
        else teg = el.innerHTML;

        if (teg == 'select' || teg == 'input[radio]' || teg == 'qftabs')
            rteg = '<span class="smbtogl"></span> <a href="#" class="optionstogler">' + teg + '</a>';
        else rteg = teg;

        var newRow = tableRef.insertRow(tableRef.rows.length);

        if (teg == 'customHtml') {
            leb = '<textarea class="qflabelclass">your code here...</textarea>';
        } else if (teg == 'recaptcha' || teg == 'cloner' || teg == 'qfincluder') {
            leb = '<input type="hidden" value="" class="qflabelclass" />';
        } else if (teg == 'backemail') {
            leb = '<input name="qfllabel" type="text" value="Send a copy of this message to your own address" class="qflabelclass" />';
        } else {
            leb = '<input name="qfllabel" type="text" value="" class="qflabelclass" />';
        }

        newRow.innerHTML = '<td class="l_td"><div class="qfsmoll"></div>' + leb + '</td><td class="r_td">' + rteg + '</td><td class="atr_td"><a href="#">&#128736;</a></td><td class="drag_td"><a href="#">&#8661;</a></td><td class="del_td"><a href="#">&#10006;</a></td>';
        $(newRow).data('settings', {
            "fildnum": newfieldnum(),
            'teg': teg
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
            if (inner.indexOf('qftabs') + 1) {
                optionsBoxActivate(row, false);
            } else {
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
        paintrow($(row));
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
        return $('<div class="optionRow" data-settings="{}"><input name="qfoption" type="text" value="" /><a href="#" class="setting">&#128736;</a><a href="#" class="plus">+</a><a href="#" class="delete">&#10006;</a></div>');
    }

    function boxOptionSetting(optionRow, calc) {
        var dat = $(optionRow).data('settings');
        var box = getBoxBtns().appendTo($('body'));
        if (calc) {
            box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div><div class="boxmenu">calculator</div></div><div class="boxinnerright">' + boxrelated(dat, 1) + boxcalculator(dat) + '</div></div>');
        } else {
            box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div></div><div class="boxinnerright">' + boxrelated(dat, 1) + '</div></div>');
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
        switch (dat.teg) {
            case 'input[text]':
            case 'input[hidden]':
            case 'input[number]':
            case 'input[range]':
            case 'qf_number':
            case 'qf_range':
                return getBoxText(dat);

            case 'input[radio]':
            case 'select':
                return getBoxRadio(dat);

            case 'input[checkbox]':
            case 'qf_checkbox':
                return getBoxCheckbox(dat);

            case 'input[file]':
            case 'qf_file':
                return getBoxFile(dat);

            case 'input[button]':
            case 'input[reset]':
                return getBoxButton(dat);

            case 'customHtml':
                return getBoxCustomHtml(dat);
            case 'submit':
                return getBoxSubmit(dat);
            case 'addToCart':
                return getBoxAddToCart(dat);
            case 'recaptcha':
                return getBoxRecaptcha(dat);
            case 'calculatorSum':
                return getBoxCalculatorSum(dat);
            case 'qfcalendar':
                return getBoxQfcalendar(dat);
            case 'backemail':
                return getBoxBackemail(dat);
            case 'qfincluder':
                return getBoxIncluder(dat);
            case 'cloner':
                return getBoxCloner(dat);
            case 'qftabs':
                return getBoxQftabs(dat);
            case 'customPhp':
                return getBoxCustomPhp(dat);
            case 'stepperbox':
                return getBoxStepper(dat);
            case 'stepperbtns':
                return getBoxStepperbtns(dat);
            default:
                return getBoxDef(dat);
        }
    }



    function activateBox(box, row) {
        $('.boxdelete', box).click(function() {
            box.remove();
            return false;
        });

        $('.boxsave', box).click(function() {
            if (box.hasClass('box1')) boxsave(box, $(row));
            else if (box.hasClass('box2')) boxsave2(box, row);
            return false;
        });

        $('.boxmenu', box).click(function() {
            $('.boxmenu', box).add($('.boxbody', box)).removeClass('activ');
            $(this).addClass('activ');
            $('.' + $(this).html(), box).addClass('activ');
            return false;
        });

        $('.qfbtnrelated', box).each(function() {
            $(this).click(function() {
                var box2 = getBoxBtns().appendTo($('body'));
                box2.removeClass('box1').addClass('box2');
                addSelectors(box, box2);
                activateBox(box2, box);
                return false;
            });
        });

        $('.customheder a', box).each(function() {
            var aCase = this.innerHTML;
            var area = $('.customfield', box);
            $(this).click(function() {
                if (!['required', 'multiple', 'checked', 'readonly'].includes(aCase)) aCase = aCase + '=""';
                area.val(area.val() + ' ' + aCase);
                return false;
            });
        });

        $('.hidediv', box).each(function() {
            var div = $(this);
            var chen = function() {
                var radio = div.prev().find('input:radio:checked');
                if (radio.val()) div.show();
                else div.hide();
            }
            chen();
            div.prev().find('input').on('change', function() {
                chen();
            });
        });

        if ('draggable' in box) box.draggable();

        box.click(function() {
            var b2 = $('.box2');
            if (b2.length) {
                $('.box1').add(b2).css('z-index', 1);
                $(this).css('z-index', 2);
            }
        });
    }

    function addSelectors(box1, box2) {
        var id = '';
        if ($('input[name=related]', box1).val()) id = $('input[name=related]', box1).val();
        jQuery.ajax({
            url: "index.php?option=com_qf3&task=ajax&mod=selectors&id=" + id + "&projectid=" + document.forms.adminForm.projectid.value,
            success: function(msg) {
                box2.append(msg);
                activateBox2(box2);
            }
        });
    }

    function activateBox2(box1, box2) {
        $('#filter_project', box2).on('change', function() {
            jQuery.ajax({
                url: "index.php?option=com_qf3&task=ajax&mod=getForms&id=" + this.value,
                success: function(msg) {
                    $('#filter_form', box2).html($(msg).html());
                }
            });
        });
    }

    function boxsave(box, row) {
        var dat = {};
        var olddat = row.data('settings');
        dat.teg = olddat.teg;
        dat.fildnum = olddat.fildnum;
        $('input, select, textarea', box).each(function() {
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
        row.data('settings', dat);
        box.remove();
        paintrow(row);
    }

    function paintrow(row) {
        var dat = row.data('settings');

        if (dat.custom && dat.custom.indexOf('required') + 1) row.addClass('req');
        else row.removeClass('req');

        if(row.hasClass('optionRow')) {
            if (dat.related) row.find('input').addClass('related');
            else row.find('input').removeClass('related');

            if (dat.math) row.find('input').addClass('calc');
            else row.find('input').removeClass('calc');

            var label = row.closest('.l_td').find('.qflabelclass');
            if ($('input', row.parent()).hasClass('calc')) label.addClass('calc');
            else label.removeClass('calc');
            if ($('input', row.parent()).hasClass('related')) label.addClass('related');
            else label.removeClass('related');
        }

        var pl;
        if (dat.custom && (pl = _getAttr(dat.custom, 'placeholder'))) {
            $('.qfsmoll', row).html(pl);
        } else {
            $('.qfsmoll', row).html('');
        }
    }

    function _getAttr(str, attr) {
        var reg = new RegExp(attr + " *= *([\"])([^\"]+)\\1").exec(str);
        if (reg) return reg[2];
    }

    function boxsave2(box2, box1) {
        $('input[name=related]', box1).val($('#filter_form', box2).val());
        $('.field_title', box1).html($('#filter_form option:selected', box2).text());
        box2.remove();
    }

    function fieldGroupTitle(id) {
        jQuery.ajax({
            url: "index.php?option=com_qf3&task=ajax&mod=fieldGroupTitle&id=" + id,
            success: function(msg) {
                $('.field_title_' + id).html(msg);
            }
        });
        return id;
    }

    function boxrelated(dat, activ) {
        return '<div class="boxbody related-fields' + (activ ? ' activ' : '') + '"><div class="boxbodyinner"><br>group id: <input name="related" type="text" value="' + (dat.related ? dat.related : '') + '" class="qfrelatedclass" /><button class="qfbtnrelated">select</button><div class = "field_title field_title_' + fieldGroupTitle(dat.related) + '"></div><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCRELATED') + '</div></div></div>';
    }

    function boxcalculator(dat) {
        return '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMAT') + '</div>math: <input name="math" type="text" value="' + (dat.math ? dat.math : '') + '" class="qfcalculatorclass" /></div></div>';
    }

    function boxvisibility(dat) {
        return '<div class="boxbody visibility_in_email"><div class="boxbodyinner"><div><input type="radio" name="hide" value=""' + (!dat.hide ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_BY_DEFAULT') + '<br><input type="radio" name="hide" value="1"' + (dat.hide == 1 ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_HIDE_FIELD_AND_DEPENDENT_STRUCTURE') + '<br><input type="radio" name="hide" value="3"' + (dat.hide == 3 ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_HIDE_FIELD_BUT_SHOW_DEPENDENT_STRUCTURE') + '</div></div></div>';
    }

    function boxchbxvisibility(dat) {
        return '<div class="boxbody visibility_in_email"><div class="boxbodyinner"><div><input type="radio" name="hide" value=""' + (!dat.hide ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_BY_DEFAULT') + '<br><input type="radio" name="hide" value="1"' + (dat.hide == 1 ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_HIDE_FIELD_AND_DEPENDENT_STRUCTURE') + '<br><input type="radio" name="hide" value="2"' + (dat.hide == 2 ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_HIDE_ONLY_IF_NOT_SELECTED') + '<br><input type="radio" name="hide" value="3"' + (dat.hide == 3 ? ' checked' : '') + ' /> ' + QuickForm.JText('QF_HIDE_FIELD_BUT_SHOW_DEPENDENT_STRUCTURE') + '</div></div></div>';
    }

    function boxFileVisibility(dat) {
        return '<div class="boxbody visibility_in_email"><div class="boxbodyinner"><div>' + qfhide(dat) + qffiletoemail(dat) + qffiletoserver(dat) + '</div></div></div>';
    }

    function boxcalendaroptions(dat) {
        return '<div class="boxbody options"><div class="boxbodyinner">' + addtext(dat, 'format', 'd-m-Y') + qfdouble(dat) + '</div></div>';
    }

    function boxcounters(dat) {
        return '<div class="boxbody counters"><div class="boxbodyinner">Ya.Metrika<div><span>TARGET_NAME:</span><input type="text" name="ycounter" value="' + (dat.ycounter ? dat.ycounter : '') + '" /></div></div></div>';
    }

    function boxcustom(dat, arr) {
        var inner = '';
        if (!arr) arr = ['dirname', 'list', 'maxlength', 'pattern', 'readonly', 'size', 'title', 'onclick'];
        arr.forEach(function(el) {
            inner += ' ' + el.link("#") + ',';
        });

        return '<br><div><div class="customheder">' + inner + ' etc.</div><textarea name="custom" class="customfield">' + (dat.custom ? escapeHtml(dat.custom) : '') + '</textarea></div>';
    }

    function getBoxBtns() {
        return $('<div class="boxsetting box1"><a href="#" class="boxdelete">&#10006;</a><a href="#" class="boxsave">&#9745;</a></div>');
    }

    function boxMenu(arr) {
        var h = '<div class="boxmenu activ">params</div>';
        for (var i = 0; i < arr.length; i++) {
            h += '<div class="boxmenu">' + arr[i] + '</div>';
        }
        return h;
    }

    function boxdescription(str) {
        return '<div class="boxbody description">' + QuickForm.JText(str) + '</div>';
    }

    function boxparams(dat, html) {
        var num = formid ? formid + '.' + dat.fildnum : '';
        return '<div class="boxbody params activ"><div class="boxbodyinner"><div><span>fieldid: </span>' + num + '<input type="hidden" name="fildnum" value="' + dat.fildnum + '" /><hr></div>' + html + '</div></div>';
    }


    function getBoxText(dat) {
        if (['input[text]', 'input[hidden]'].includes(dat.teg)) var arr = ['autocomplete', 'class', 'pattern', 'placeholder', 'required', 'value'];
        else var arr = ['class', 'max', 'min', 'required', 'step', 'value'];
        var html = qfhide(dat) + ((dat.teg == 'qf_number') ? qforient(dat) : '') + boxcustom(dat, arr);
        var boxBody = boxparams(dat, html) + boxcalculator(dat);
        return [boxMenu(['calculator']), boxBody];
    }

    function getBoxCheckbox(dat) {
        var html = qfpos(dat) + boxcustom(dat, ['class', 'checked', 'placeholder', 'required']);
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxchbxvisibility(dat) + boxcalculator(dat);
        return [boxMenu(['related-fields', 'visibility_in_email', 'calculator']), boxBody];
    }

    function getBoxRadio(dat) {
        var html = ((dat.teg == 'input[radio]') ? qforient(dat) : '') + boxcustom(dat, ['class', 'placeholder', 'required']);
        var boxBody = boxparams(dat, html) + boxvisibility(dat);
        return [boxMenu(['visibility_in_email']), boxBody];
    }

    function getBoxFile(dat) {
        var html = qfpos(dat) + addtext(dat, 'extens', 'jpg,gif,png') + boxcustom(dat, ['accept', 'class', 'multiple', 'required']);
        var boxBody = boxparams(dat, html) + boxFileVisibility(dat) + boxdescription('QF_QFFILE');
        return [boxMenu(['visibility_in_email', 'description']), boxBody];
    }

    function getBoxCustomHtml(dat) {
        var html = addcheckbox(dat, 'qfshowf', 'QF_SHOWF', true) + addcheckbox(dat, 'qfshowl', 'QF_SHOWL', false);
        var boxBody = boxparams(dat, html) + boxdescription('QF_CUSTOMHTML');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxSubmit(dat) {
        var html = addtext(dat, 'redirect', '') + boxcustom(dat, ['class', 'title', 'value']);
        var boxBody = boxparams(dat, html) + boxcounters(dat);
        return [boxMenu(['counters']), boxBody];
    }

    function getBoxButton(dat) {
        var html = boxcustom(dat, ['class', 'value']);
        var boxBody = boxparams(dat, html);
        return [boxMenu([]), boxBody];
    }

    function getBoxAddToCart(dat) {
        var html = boxcustom(dat, ['class', 'value']);
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCADDTOCART');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxRecaptcha(dat) {
        var html = addtext(dat, 'class', '');
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCRECAPTCHA');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxCalculatorSum(dat) {
        var html = qfpos(dat) + addtext(dat, 'unit', '') + addtext(dat, 'fixed', '0') + qfformat(dat) + addtext(dat, 'class', '');
        var boxBody = boxparams(dat, html) + boxdescription('QF_CALCSUM');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxQfcalendar(dat) {
        var html = qfhide(dat) + boxcustom(dat, ['class', 'placeholder', 'value', 'required']);
        var boxBody = boxparams(dat, html) + boxcalendaroptions(dat) + boxcalculator(dat) + boxdescription('QF_DESCCALENDAR');
        return [boxMenu(['options', 'calculator', 'description']), boxBody];
    }

    function getBoxBackemail(dat) {
        var html = addcheckbox(dat, 'qfshowf', 'QF_SHOWF', true) + qfpos(dat) + qfreg(dat) + boxcustom(dat, ['class', 'checked', 'required']);
        var boxBody = boxparams(dat, html) + boxdescription('QF_BACKEMAIL');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxIncluder(dat) {
        var boxBody = boxparams(dat, '') + boxrelated(dat) + boxdescription('QF_DESCINCLUDER');
        return [boxMenu(['related-fields', 'description']), boxBody];
    }

    function getBoxCloner(dat) {
        var html = qforient(dat) + addcheckbox(dat, 'sum', 'QF_SUMCLONER', false) + addtext(dat, 'max', '') + addtext(dat, 'numbering', '');
        var boxBody = boxparams(dat, html) + boxrelated(dat);
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMATCL') + '</div>' + addtext(dat, 'clonerstart', '') + addtext(dat, 'clonerend', '') + '</div></div>';
        boxBody += boxdescription('QF_DESCCLONER');
        return [boxMenu(['related-fields', 'calculator', 'description']), boxBody];
    }

    function getBoxQftabs(dat) {
        var html = qforient(dat) + qfhide(dat) + addtext(dat, 'class', '');
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCTABS');
        return [boxMenu(['description']), boxBody];
    }

    function getBoxCustomPhp(dat) {
        var boxBody = boxparams(dat, '') + qfcustomphp1(dat) + qfcustomphp2(dat);
        return [boxMenu(['form', 'email']), boxBody];
    }

    function getBoxStepper(dat) {
        var html = addtext(dat, 'class', '');
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxdescription('QF_DESCSTEPPER');
        return [boxMenu(['related-fields', 'description']), boxBody];
    }

    function getBoxStepperbtns(dat) {
        var html = addtext(dat, 'class', '') + addtext(dat, 'prev', '') + addtext(dat, 'next', '');
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxdescription('QF_STEPPERBTNS');
        return [boxMenu(['related-fields', 'description']), boxBody];
    }

    function getBoxDef(dat) {
        var html = qfhide(dat) + boxcustom(dat, ['autocomplete', 'class', 'pattern', 'placeholder', 'value', 'required']);
        var boxBody = boxparams(dat, html);
        return [boxMenu([]), boxBody];
    }


    function addtext(dat, atr, v) {
        return '<div><span>' + atr + ':</span><input type="text" name="' + atr + '" value="' + ((atr in dat) ? escapeHtml(dat[atr]) : v) + '" /></div>';
    }

    function addcheckbox(dat, atr, text, checked) {
        return '<div><input type="checkbox" name="' + atr + '" value="1"' + ((atr in dat) ? (dat[atr] ? ' checked' : '') : (checked ? ' checked' : '')) + ' /><span> ' + QuickForm.JText(text) + '</span></div>';
    }

    function qfhide(dat) {
        return '<div><input type="checkbox" name="hide" value="1"' + (dat.hide ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_HIDELETTER') + '</span></div>';
    }

    function qfreg(dat) {
        return '<div class="qfreg"><input type="radio" name="reg" value=""' + (!dat.reg ? ' checked' : '') + ' /> all <input type="radio" name="reg" value="1"' + (dat.reg ? ' checked' : '') + ' /> registered</div>';
    }

    function qffiletoemail(dat) {
        return '<div><input type="checkbox" name="filetoemail" value="1"' + (('filetoemail' in dat) ? (dat.filetoemail ? ' checked' : '') : ' checked') + ' /><span> ' + QuickForm.JText('QF_FILETOEMAIL') + '</span></div>';
    }

    function qffiletoserver(dat) {
        return '<div><input type="checkbox" name="filetoserver" value="1"' + (dat.filetoserver ? ' checked' : '') + (!qf_filesmod ? ' disabled' : '') + ' /><span> ' + QuickForm.JText('QF_FILETOSERVER') + '</span></div>';
    }

    function qfpos(dat) {
        return '<div><span>label:</span><input type="radio" name="pos" value=""' + (!dat.pos ? ' checked' : '') + ' /> before <input type="radio" name="pos" value="1"' + (dat.pos ? ' checked' : '') + ' /> after</div>';
    }

    function qforient(dat) {
        return '<div><input type="radio" name="orient" value=""' + (!dat.orient ? ' checked' : '') + ' /> vertical <input type="radio" name="orient" value="1"' + (dat.orient ? ' checked' : '') + ' /> horizontal</div>';
    }

    function qfcustomphp1(dat) {
        return '<div class="boxbody form"><div><textarea name="customphp1" class="customphp">' + (('customphp1' in dat) ? escapeHtml(dat.customphp1) : '<div>example 1:</div>\r\n&lt;?php echo "Hello world!"; ?&gt;') + '</textarea></div></div>';
    }

    function qfcustomphp2(dat) {
        return '<div class="boxbody email"><div><textarea name="customphp2" class="customphp">' + (('customphp2' in dat) ? escapeHtml(dat.customphp2) : '<div>example 2:</div>\r\n&lt;?php echo "Hello World"; ?&gt;') + '</textarea></div></div>';
    }

    function qfformat(dat) {
        return '<div><span>format:</span><select name="format"><option value="0"' + (!dat.format ? ' selected' : '') + '>1 250 500,75</option><option value="1"' + (dat.format == 1 ? ' selected' : '') + '>1,250,500.75</option><option value="2"' + (dat.format == 2 ? ' selected' : '') + '>1250500.75</option></select></div>';
    }

    function qfdouble(dat) {
        return '<div><input type="radio" name="double" value=""' + (!dat.double ? ' checked' : '') + ' /> single <input type="radio" name="double" value="1"' + (dat.double ? ' checked' : '') + ' /> double</div><div class="hidediv"><span>label 1:</span><input type="text" name="leb1" value="' + (dat.leb1 ? escapeHtml(dat.leb1) : '') + '" /><span>label 2:</span><input type="text" name="leb2" value="' + (dat.leb2 ? escapeHtml(dat.leb2) : '') + '" /><span>value 1:</span><input type="text" name="val1" value="' + (dat.val1 ? escapeHtml(dat.val1) : '') + '" /><span>value 2:</span><input type="text" name="val2" value="' + (dat.val2 ? escapeHtml(dat.val2) : '') + '" /></div>';
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
                var phrase = 'JText' + str + ' ';
                jQuery(":contains(" + phrase + ")").not(":has(:contains(" + phrase + "))").each(function() {
                    var that = jQuery(this);
                    var html = that.html();
                    html = html.replace(new RegExp(phrase, 'gi'), msg);
                    that.html(html);
                });
            }
        });
        return 'JText' + str + ' ';
    },

    submitform: function(task, form) {
        var bbox = jQuery('.boxsetting');
        bbox.each(function() {
            jQuery(this).hide(100).show(100);
        });
        if (bbox.length) return;

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
