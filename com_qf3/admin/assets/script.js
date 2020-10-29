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
        var menu = [{"one-off":["userName", "userEmail", "userPhone", "backemail"], "input":["text", "checkbox", "radio", "hidden", "file", "reset", "button"], "HTML5":["number", "tel", "email", "color", "date", "range", "url"], "QuickForm":["customHtml", "customPhp", "calculatorSum", "recaptcha", "cloner", "qfincluder", "addToCart", "qftabs", "qfcalendar", {"stepper":["stepperbox", "stepperbtns"]}], "customized":["qf_number", "qf_range", "qf_checkbox"]}, "select", "textarea", "submit"];

        function parsemenu(data) {
            var n,v,html='';
            for(n in data){
                var cl='';
                if(n=='input'|| n=='HTML5') cl=' class="inp"';
                v = data[n];
                if (typeof(v) == "string") html += '<li><a href="#">' + v + '</a></li>';
                else if(v.constructor == Array) html += '<li>'+n+'<ul'+cl+'>' + parsemenu(v) + '</ul></li>';
                else if (typeof(v) == "object") html += parsemenu(v);
            }
            return html;
        }

        $('<ul id="qfmenu">' + parsemenu(menu) + '</ul>').appendTo('#toolbar-iconaddfild').mouseleave(function() {
            $(this).remove();
        }).find('a').click(function() {
            addfield(this);
            $('#qfmenu').remove();
            return false;
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

        newRow.innerHTML = '<td class="l_td">' + leb + '</td><td class="r_td">' + rteg + '</td><td class="atr_td"><a href="#">&#128736;</a></td><td class="drag_td"><a href="#">&#8661;</a></td><td class="del_td"><a href="#">&#10006;</a></td>';
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
        return $('<div class="optionRow" data-settings="{}"><input name="qfoption" type="text" value="" /><a href="#" class="setting">&#128736;</a><a href="#" class="plus">+</a><a href="#" class="delete">&#10006;</a></div>');
    }

    function boxOptionSetting(optionRow, calc) {
        var dat = $(optionRow).data('settings');
        var box = getBoxBtns().appendTo($('body'));
        if(calc){
          box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div><div class="boxmenu">calculator</div></div><div class="boxinnerright">' + boxrelated(dat,1) + boxcalculator(dat) + '</div></div>');
        }
        else{
          box.append('<div class="boxtitle">' + $('input', optionRow).val() + '</div><div class="boxinner"><div class="boxinnerleft"><div class="boxmenu activ">related-fields</div></div><div class="boxinnerright">' + boxrelated(dat,1) + '</div></div>');
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
        else if (dat.teg == 'select') return getBoxSelect(dat);
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
        else if (dat.teg == 'qf_number') return getBoxQfnumber(dat);
        else if (dat.teg == 'qf_range') return getBoxQfslider(dat);
        else if (dat.teg == 'input[file]') return getBoxFile(dat);
        else if (dat.teg == 'qfcalendar') return getBoxQfcalendar(dat);
        else if (dat.teg == 'stepperbox') return getBoxStepper(dat);
        else if (dat.teg == 'stepperbtns') return getBoxStepperbtns(dat);

        else return getBoxDef(dat);
    }



    function activateBox(box, row) {
        $('.boxdelete', box).click(function() {
            box.remove();
            return false;
        });

        $('.boxsave', box).click(function() {
            if(box.hasClass('box1')) boxsave(box, row);
            else if(box.hasClass('box2')) boxsave2(box, row);
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
                area.val(area.val() + ' '+aCase+'=""');
                return false;
            });
        });

        $('.hidediv', box).each(function() {
            var div = $(this);
            var chen = function(){
              var radio = div.prev().find('input:radio:checked');
              if(radio.val()) div.show();
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
            if(b2.length) {
                $('.box1').add(b2).css('z-index', 1);
                $(this).css('z-index', 2);
            }
        });
    }

    function addSelectors(box1,box2) {
        var id = '';
        if($('input[name=related]', box1).val()) id = $('input[name=related]', box1).val();
        jQuery.ajax({
            url: "index.php?option=com_qf3&task=ajax&mod=selectors&id=" + id + "&projectid=" + document.forms.adminForm.projectid.value,
            success: function(msg) {
                box2.append(msg);
                activateBox2(box2);
            }
        });
    }

    function activateBox2(box1,box2) {
        $('#filter_project', box2).on('change', function () {
            jQuery.ajax({
                url: "index.php?option=com_qf3&task=ajax&mod=getForms&id=" + this.value,
                success: function(msg) {
                    $('#filter_form', box2).html($(msg).html());
                }
            });
        });
    }

    function boxsave(box, row) {
        var dat = $(row).data('settings');
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
        box.remove();
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
                $('.field_title_'+id).html(msg);
            }
        });
        return id;
    }

    function boxrelated(dat, activ) {
        return '<div class="boxbody related-fields'+(activ?' activ':'')+'"><div class="boxbodyinner"><br>group id: <input name="related" type="text" value="' + (dat.related ? dat.related : '') + '" class="qfrelatedclass" /><button class="qfbtnrelated">select</button><div class = "field_title field_title_'+fieldGroupTitle(dat.related)+'"></div><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCRELATED') + '</div></div></div>';
    }

    function boxcalculator(dat) {
        return '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMAT') + '</div>math: <input name="math" type="text" value="' + (dat.math ? dat.math : '') + '" class="qfcalculatorclass" /></div></div>';
    }
    function boxcalendaroptions(dat) {
        return '<div class="boxbody options"><div class="boxbodyinner">'+qfaddDat(dat, 'format', 'Y-m-d') + qfdouble(dat) + '</div></div>';
    }
    function boxcounters(dat) {
        return '<div class="boxbody counters"><div class="boxbodyinner">Ya.Metrika<div><span>TARGET_NAME:</span><input type="text" name="ycounter" value="' + (dat.ycounter ? dat.ycounter : '') + '" /></div></div></div>';
    }

    function boxcustom(dat) {
        var inner = '';
        var arr = ['autocomplete', 'dirname', 'list', 'maxlength', 'pattern', 'max', 'min', 'readonly', 'step', 'size', 'title', 'value', 'onclick'];
        arr.forEach(function(el) {
            inner += ' '+el.link("#")+',';
        });

        return '<div class="boxbody custom"><div class="boxbodyinner"><div class="customheder">' +inner+ ' etc.</div><textarea name="custom" class="customfield">' + (dat.custom ? escapeHtml(dat.custom) : '') + '</textarea></div></div>';
    }

    function getBoxBtns() {
        return $('<div class="boxsetting box1"><a href="#" class="boxdelete">&#10006;</a><a href="#" class="boxsave">&#9745;</a></div>');
    }

    function boxMenu(arr) {
        var h = '<div class="boxmenu activ">params</div>';
        for (var i = 0; i < arr.length; i++) {
            h += '<div class="boxmenu">' +arr[i]+ '</div>';
        }
        return h;
    }

    function boxdescription(str) {
        return '<div class="boxbody description">' + QuickForm.JText(str) + '</div>';
    }

    function getBoxBackemail(dat) {
        var html = qfrequired(dat) + qfshowf(dat) + qfpos(dat) + qfreg(dat) + qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxdescription('QF_BACKEMAIL') + boxcustom(dat);
        return [boxMenu(['custom','description']), boxBody];
    }
    function getBoxDef(dat) {
        var html = qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcustom(dat);
        return [boxMenu(['custom']), boxBody];
    }
    function getBoxText(dat) {
        var html = qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcalculator(dat) + boxcustom(dat);
        return [boxMenu(['calculator','custom']), boxBody];
    }
    function getBoxCheckbox(dat) {
        var html = qfrequired(dat) + qfchecked(dat) + cbxhide(dat) + qfpos(dat) + qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxcalculator(dat) + boxcustom(dat);
        return [boxMenu(['related-fields','calculator','custom']), boxBody];
    }
    function getBoxRadio(dat) {
        var html = qfhide(dat) + hideone(dat) + qforient(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcustom(dat);
        return [boxMenu(['custom']), boxBody];
    }
    function getBoxSelect(dat) {
        var html = qfrequired(dat) + qfhide(dat) + hideone(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcustom(dat);
        return [boxMenu(['custom']), boxBody];
    }
    function getBoxFile(dat) {
        var html = qfrequired(dat) + qfhide(dat) + qfpos(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcustom(dat);
        return [boxMenu(['custom']), boxBody];
    }
    function getBoxButton(dat) {
        var html = qfbtnclass(dat) + qfaddDat(dat, 'value');
        var boxBody = boxparams(dat, html) + boxcustom(dat);
        return [boxMenu(['custom']), boxBody];
    }
    function getBoxCustomHtml(dat) {
        var html = qfshowf(dat) + qfshowl(dat);
        var boxBody = boxparams(dat, html) + boxdescription('QF_CUSTOMHTML');
        return [boxMenu(['description']), boxBody];
    }
    function getBoxCustomPhp(dat) {
        var html = '';
        var boxBody = boxparams(dat, html) + qfcustomphp1(dat) + qfcustomphp2(dat);
        return [boxMenu(['form','email']), boxBody];
    }
    function getBoxCalculatorSum(dat) {
        var html = qfpos(dat) + qfaddDat(dat, 'unit') + qffixed(dat) + qfformat(dat) + qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxdescription('QF_CALCSUM');
        return [boxMenu(['description']), boxBody];
    }
    function getBoxRecaptcha(dat) {
        var html = qfcaptcha(dat) + qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCRECAPTCHA');
        return [boxMenu(['description']), boxBody];
    }
    function getBoxCloner(dat) {
        var html = qforient(dat) + qfsum(dat) + qfaddDat(dat, 'max') + qfaddDat(dat, 'numbering');
        var boxBody = boxparams(dat, html) + boxrelated(dat);
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMATCL') + '</div>' + qfaddDat(dat, 'clonerstart')+ qfaddDat(dat, 'clonerend')+'</div></div>';
        boxBody += boxdescription('QF_DESCCLONER');
        return [boxMenu(['related-fields','calculator','description']), boxBody];
    }
    function getBoxIncluder(dat) {
        var html = '';
        var boxBody = boxparams(dat, html) + boxrelated(dat);
        boxBody += '<div class="boxbody calculator"><div class="boxbodyinner"><div class="boxbodyinnerhelp">' + QuickForm.JText('QF_DESCMATINCLUDER') + '</div>'+ qfaddDat(dat, 'condition')+ qfaddDat(dat, 'start')+ qfaddDat(dat, 'end')+'</div></div>';
        boxBody += boxdescription('QF_DESCINCLUDER');
        return [boxMenu(['related-fields','calculator','description']), boxBody];
    }
    function getBoxStepper(dat) {
        var html = qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxdescription('QF_DESCSTEPPER');
        return [boxMenu(['related-fields','description']), boxBody];
    }
    function getBoxStepperbtns(dat) {
        var html = qfaddDat(dat, 'class') + qfaddDat(dat, 'prev') + qfaddDat(dat, 'next');
        var boxBody = boxparams(dat, html) + boxrelated(dat) + boxdescription('QF_STEPPERBTNS');
        return [boxMenu(['related-fields','description']), boxBody];
    }
    function getBoxAddToCart(dat) {
        var html = qfaddDat(dat, 'class') + qfcartvalue(dat);
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCADDTOCART') + boxcustom(dat);
        return [boxMenu(['custom','description']), boxBody];
    }
    function getBoxQftabs(dat) {
        var html = qforient(dat) + qfhide(dat) + qfaddDat(dat, 'class');
        var boxBody = boxparams(dat, html) + boxdescription('QF_DESCTABS');
        return [boxMenu(['description']), boxBody];
    }
    function getBoxQfcalendar(dat) {
        var html = qfrequired(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder') + qfaddDat(dat, 'value') + qfhide(dat);
        var boxBody = boxparams(dat, html) + boxcalendaroptions(dat) + boxcalculator(dat) + boxcustom(dat) + boxdescription('QF_DESCCALENDAR');
        return [boxMenu(['options','calculator','custom','description']), boxBody];
    }
    function getBoxQfnumber(dat) {
        var html = qfrequired(dat) + qforient(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcalculator(dat) + boxcustom(dat);
        return [boxMenu(['calculator','custom']), boxBody];
    }
    function getBoxQfslider(dat) {
        var html = qfrequired(dat) + qfhide(dat) + qfaddDat(dat, 'class') + qfaddDat(dat, 'placeholder');
        var boxBody = boxparams(dat, html) + boxcalculator(dat) + boxcustom(dat);
        return [boxMenu(['calculator','custom']), boxBody];
    }
    function getBoxSubmit(dat) {
        var html = qfbtnclass(dat) + qfsubmitvalue(dat) + qfaddDat(dat, 'redirect');
        var boxBody = boxparams(dat, html) + boxcounters(dat) + boxcustom(dat);
        return [boxMenu(['counters','custom']), boxBody];
    }

    function boxparams(dat, html) {
        var num = formid?formid+'.'+dat.fildnum:'';
        return '<div class="boxbody params activ"><div class="boxbodyinner"><div><span>fieldid: </span>'+num+'<input type="hidden" name="fildnum" value="' + dat.fildnum + '" /><hr></div>' + html + '</div></div>';
    }

    function qfrequired(dat) {
        return '<div><input type="checkbox" name="required" value="1"' + (dat.required ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_REQUIRED') + '</span></div>';
    }
    function qfshowf(dat) {
        return '<div><input type="checkbox" name="qfshowf" value="1"' + (('qfshowf' in dat) ? (dat.qfshowf ? ' checked' : '') : ' checked') + ' /><span> ' + QuickForm.JText('QF_SHOWF') + '</span></div>';
    }
    function qfshowl(dat) {
        return '<div><input type="checkbox" name="qfshowl" value="1"' + (dat.qfshowl ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_SHOWL') + '</span></div>';
    }
    function qfreg(dat) {
        return '<div><input type="radio" name="reg" value=""' + (!dat.reg ? ' checked' : '') + ' /> show all <input type="radio" name="reg" value="1"' + (dat.reg ? ' checked' : '') + ' /> show logged</div>';
    }
    function qfaddDat(dat, atr, v) {
        var val='';
        if(v) val = v;
        return '<div><span>'+atr+':</span><input type="text" name="'+atr+'" value="' + (dat[atr] ? escapeHtml(dat[atr]) : val) + '" /></div>';
    }
    function qfhide(dat) {
        return '<div><input type="checkbox" name="hide" value="1"' + (dat.hide ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_HIDELETTER') + '</span></div>';
    }
    function hideone(dat) {
        return '<div><input type="checkbox" name="hideone" value="1"' + (dat.hideone ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_HIDELETTERONE') + '</span></div>';
    }
    function cbxhide(dat) {
        return '<div><span>' + QuickForm.JText('QF_HIDELETTER') + ':</span><br><input type="radio" name="cbxhide" value=""' + (!dat.cbxhide ? ' checked' : '') + ' /> no <input type="radio" name="cbxhide" value="1"' + (dat.cbxhide==1 ? ' checked' : '') + ' /> always <input type="radio" name="cbxhide" value="2"' + (dat.cbxhide==2 ? ' checked' : '') + ' /> empty <input type="radio" name="cbxhide" value="3"' + (dat.cbxhide==3 ? ' checked' : '') + ' /> field only</div>';
    }
    function qfchecked(dat) {
        return '<div><input type="checkbox" name="checked" value="1"' + (dat.checked ? ' checked' : '') + ' /><span> checked</span></div>';
    }
    function qfpos(dat) {
        return '<div><span>text:</span><input type="radio" name="pos" value=""' + (!dat.pos ? ' checked' : '') + ' /> before <input type="radio" name="pos" value="1"' + (dat.pos ? ' checked' : '') + ' /> after</div>';
    }
    function qforient(dat) {
        return '<div><input type="radio" name="orient" value=""' + (!dat.orient ? ' checked' : '') + ' /> vertical <input type="radio" name="orient" value="1"' + (dat.orient ? ' checked' : '') + ' /> horizontal</div>';
    }
    function qfbtnclass(dat) {
        var cl = '';
        if (typeof(dat.class) == 'undefined') cl = 'btn btn-primary';
        return '<div><span>class:</span><input type="text" name="class" value="' + (dat.class ? dat.class : cl) + '" /></div>';
    }
    function qfcustomphp1(dat) {
        return '<div class="boxbody form"><div><textarea name="customphp1" class="customphp">' + (('customphp1' in dat) ? escapeHtml(dat.customphp1) : '<div>example 1:</div>\r\n&lt;?php echo "Hello world!"; ?&gt;') + '</textarea></div></div>';
    }
    function qfcustomphp2(dat) {
        return '<div class="boxbody email"><div><textarea name="customphp2" class="customphp">' + (('customphp2' in dat) ? escapeHtml(dat.customphp2) : '<div>example 2:</div>\r\n&lt;?php echo "Hello World"; ?&gt;') + '</textarea></div></div>';
    }
    function qffixed(dat) {
        return '<div><span>fixed:</span><input type="text" name="fixed" value="' + (dat.fixed ? dat.fixed : 0) + '" /></div>';
    }
    function qfformat(dat) {
        return '<div><span>format:</span><select name="format"><option value="0"' + (!dat.format ? ' selected' : '') + '>1 250 500,75</option><option value="1"' + (dat.format==1 ? ' selected' : '') + '>1,250,500.75</option><option value="2"' + (dat.format==2 ? ' selected' : '') + '>1250500.75</option></select></div>';
    }
    function qfcaptcha(dat) {
        return '<div><input type="radio" name="show" value=""' + (!dat.show ? ' checked' : '') + ' /> show all <input type="radio" name="show" value="1"' + (dat.show ? ' checked' : '') + ' /> only guest</div>';
    }
    function qfdouble(dat) {
        return '<div><input type="radio" name="double" value=""' + (!dat.double ? ' checked' : '') + ' /> single <input type="radio" name="double" value="1"' + (dat.double ? ' checked' : '') + ' /> double</div><div class="hidediv"><span>label 1:</span><input type="text" name="leb1" value="' + (dat.leb1 ? escapeHtml(dat.leb1) : '') + '" /><span><br>label 2:</span><input type="text" name="leb2" value="' + (dat.leb2 ? escapeHtml(dat.leb2) : '') + '" /><span><br>value 1:</span><input type="text" name="val1" value="' + (dat.val1 ? escapeHtml(dat.val1) : '') + '" /><span><br>value 2:</span><input type="text" name="val2" value="' + (dat.val2 ? escapeHtml(dat.val2) : '') + '" /></div>';
    }
    function qfsum(dat) {
        return '<div><input type="checkbox" name="sum" value="1"' + (dat.sum ? ' checked' : '') + ' /><span> ' + QuickForm.JText('QF_SUMCLONER') + '</span></div>';
    }
    function qfcartvalue(dat) {
        return '<div><span>value:</span><input type="text" name="value" value="' + (('value' in dat) ? escapeHtml(dat.value) : 'QF_ADDTOCART') + '" /></div>';
    }
    function qfsubmitvalue(dat) {
        return '<div><span>value:</span><input type="text" name="value" value="' + (('value' in dat) ? escapeHtml(dat.value) : 'QF_SUBMIT') + '" /></div>';
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
        bbox.each(function () {
            jQuery(this).hide(100).show(100);
        });
        if(bbox.length) return;

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
