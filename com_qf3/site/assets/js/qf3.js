/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */
(function($) {

    var keepaliveflag, recursionflag = [],
        captchaflagstart = 0;

    $(document).ready(function() {
        $('.qf3form form').each(function() {
            $.QuickForm3.initiate(this);
        });

        $('.qf3modal').on('click', function() {
            $.QuickForm3.qfstartModalform($(this).data());
        });
    });

    return $.QuickForm3 = {

        initiate: function(form) {
            var f = $(form), sd;

            $(window).on('resize', function() {
                compact();
            });
            compact();

            f.on('doSumBox', function() {
                sumBox(form);
            });

            form.sumbit = form.root.value.replace(/[w.|-]/g, '').split(/\/+/)[1];
            activateBox(f);
            $.QuickForm3.dokeepalive();

            form.submit = function(el) {
                form.onsubmit = function() {
                    return qfsubmit(form);
                }
                checkTabs(form);
                sd = $(el).data('submit');
                var btn = $('button[type="submit"]', form);
                if (btn.length) btn.click();
                else $('<button type="submit" style="display:none"></button>').appendTo(form).click();
            }


            form.qfaddtocart = function() {
                if (typeof(QFcart) != 'object') {
                    console.log('qf_cart.js not loaded');
                } else {
                    return QFcart.addFormInCart(this);
                }
            }

            function compact() {
                var formdiv = f.parent();
                if (formdiv.length) {
                    var w = formdiv[0].offsetWidth;
                    formdiv = formdiv.add('.qfmodalform');
                    if (w && w < 500) formdiv.addClass('compact');
                    else formdiv.removeClass('compact');
                }
            }

            function checkTabs(form) {
                var fl, qftabs = $('.qftabs', form);
                if (!qftabs.length) return;

                if (!form.checkValidity()) {
                    qftabs.each(function() {
                        if (fl) return;
                        $(this).find("input,select,textarea").each(function() {
                            if (fl) return;
                            if (!this.checkValidity()) {
                                var parentTabs = $(this).parents('.qftabsitem');
                                parentTabs.each(function(i) {
                                    var box = $(this).parent();
                                    var labels = $('.qftabsitemlabel', box.children('.qftabslabelsbox'));
                                    box.children('.qftabsitem').each(function(ii) {
                                        if (this == parentTabs[i]) {
                                            labels[ii].click();
                                        }
                                    });
                                });
                                fl = 1;
                                return;
                            }
                        });
                    });
                }
            }

            function qfsubmit(form) {
                if (!$('.qfsubmit', form).length) return false;
                $.QuickForm3.prepareFormForSend(form);
                before_submit();
                if (form.mod && form.mod.value == 'qfajax') {
                    $(form).animate({
                        'opacity': 0.2
                    }, 600);
                    if (!window.FormData) {
                        form.task.value = '';
                        return true;
                    }
                    $.ajax({
                        type: 'POST',
                        url: form.root.value,
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(html) {
                            schowAjaxForm(form, html);
                        }
                    });
                    return false;
                }
                return true;
            }

            function before_submit() {
                if(sd && sd.ycounter) {
                    var cs = (typeof window.Ya !== 'undefined') && window.Ya.Metrika.counters();
                    var yid = (cs && cs[0] && cs[0].id) || null;
                    if(yid) {
                        if(typeof window['yaCounter'+ yid] !== 'undefined') window['yaCounter'+ yid].reachGoal(sd.ycounter);
                        else if (typeof ym !== 'undefined') ym(yid, 'reachGoal', sd.ycounter);
                    }
                }
            }

            function schowAjaxForm(form, html) {
                var id = form.id.value;
                var box = $(form).parent();
                var h = $(form).height();

                $(form).slideUp();
                var mes = $(html).appendTo(box);
                mes.css({
                    'opacity': 0
                }).animate({
                    'opacity': 1
                }, 600, function() {
                    $(form).remove();
                    $.QuickForm3.verticallycentr(box, false);
                });


                $('.qfsubmitformresclose').click(function() {
                    mes.slideUp();

                    $.ajax({
                        type: 'POST',
                        url: form.root.value,
                        data: {
                            option: "com_qf3",
                            task: "ajax",
                            mod: "qfmodal",
                            id: id
                        },
                        success: function(html) {
                            mes.remove();
                            var newform = $('form', $(html))[0];
                            $(newform).appendTo(box).css({
                                'height': 0,
                                'opacity': 0
                            }).animate({
                                'opacity': 1,
                                'height': h
                            }, 600, function() {
                                $.QuickForm3.verticallycentr(box, false);
                            });
                            $.QuickForm3.initiate(newform);
                        }
                    });
                });
            }


            function startRelated(filds) {
                var fl;
                $(filds).each(function() {
                    fl=chekReq(this)||fl;
                    this.onchange = function() {
                        if (chekReq(this)) sumBox(form);
                    }
                });
                form.suml = form.qfcod ? form.qfcod.value : schowRelated($(form), '');
                if (fl) sumBox(form);
            }


            function chekReq(field) {
                var d;

                if (field.type === 'select-one') {
                    d = $(field.options[field.selectedIndex]).data('settings');
                } else if (field.type === 'radio' || field.type === 'checkbox') {
                    if (field.checked) d = $(field).data('settings');
                }

                if (d && d.related) {
                    if (!recursionflag[d.related]) recursionflag[d.related] = 0;
                    if (recursionflag[d.related] > 150) return;
                    recursionflag[d.related]++;

                    $.ajax({
                        type: 'POST',
                        url: form.root.value,
                        data: {
                            option: "com_qf3",
                            task: "ajax",
                            mod: "related",
                            id: d.related
                        },
                        success: function(html) {
                            var box = $(field).closest('.qf3');
                            if (box.next('.relatedblock').length) schowRelated(box.next('.relatedblock'), html);
                            else {
                                var el = $("<div class='relatedblock'></div>").insertAfter(box);
                                schowRelated(el, html);
                            }
                        }
                    });
                } else {
                    var box = $(field).closest('.qf3');
                    if (box.next('.relatedblock').length) schowRelated(box.next('.relatedblock'), '');
                    else return true;
                }
            }

            function schowRelated(box, html) {
                if (html) {
                    box.html(html).css({
                        'opacity': 0
                    }).animate({
                        'opacity': 1
                    }, 600);
                    activateBox(box);
                    $.QuickForm3.verticallycentr(box, false);
                } else {
                    box.animate({
                        'opacity': 0
                    }, 300, function() {
                        $.QuickForm3.verticallycentr(box, false);
                        box.remove();
                        sumBox(form);
                    });
                }
            }


            function activateBox(box) {
                var fields = $('input, select', box);
                startRelated(fields);

                $('.qfcloner', box).each(function() {
                    activateCloner(this);
                });

                $('.qftabs', box).each(function() {
                    activateTabs(this);
                });

                var captdiv = $('.qf_recaptcha', box);
                activateCaptcha(captdiv, '.qfcap' + 't a');

                var filelabel = function(el) {
                    if (el.value) $('.filelabel', $(el).closest('.qffile')).addClass('filled');
                    else $('.filelabel', $(el).closest('.qffile')).removeClass('filled');
                }

                $('input[name="inpfile[]"]', box).on('change', function() {
                    filelabel(this);
                });

                $("input:reset", box).click(function() {
                    this.form.reset();
                    startRelated(fields);
                    $('input[name="inpfile[]"]').each(function() {
                        filelabel(this);
                    });
                });

                $('input[type="text"],input[type="number"]', box).each(function() {
                    var d = $(this).data('settings'),
                        v;
                    if (d && d.math) {
                        if (this.value == '') {
                            this.value = 0;

                        }
                        $(this).on('input', function() {
                            v = this.value.replace(/[^0-9.,-]/g, '');
                            if (v == '') v = 0;
                            var end = v.toString().slice(-1);
                            if (['.', ',', '0'].indexOf(end) == -1) v = Number(v.replace(/,/, "."));
                            this.value = (v != v) ? 0 : v;
                            sumBox(form);
                        });
                    }
                });

                $('.qfup, .qfdown', box).click(function() {
                    activateQf_number(this);
                    sumBox(form);
                });

                $('.qfnext', box).each(function() {
                    activateStepper(this);
                });

                $('.qfslider', box).each(function() {
                    activateSlider(this);
                });

                $('.qfcalendar', box).each(function() {
                    activateCalendar(this);
                });

                sumBox(form);
                $(form).trigger("qfnewbox",[box]);
            }


            function activateStepper(e) {
                var nxt = $(e), box = nxt.closest('.qfstepperinner'), prv = $('.qfprev',box);
                if(!box.prev().length) prv.hide();
                else {
                    prv.click(function () {
                        box.prev().show();
                        $('input[name="qfstepper[]"]',box.prev()).val(0);
                        $.QuickForm3.verticallycentr(box, false);
                        box.remove();
                    });
                }
                var d = nxt.data('next');
                if(!d) nxt.hide();
                nxt.click(function () {
                    var fl;
                    box.find("input,select,textarea").each(function() {
                        if (!this.checkValidity()) {
                            fl=1;
                            return this.reportValidity();
                        }
                    });
                    if(fl)return;
                    $.ajax({
                        type: 'POST',
                        url: form.root.value,
                        data: {
                            option: "com_qf3",
                            task: "ajax",
                            mod: "related",
                            id: d
                        },
                        success: function(html) {
                            if(html){
                                var newbox = $('<div class="qfstepperinner"></div>').insertAfter(box);
                                box.hide();
                                $('input',nxt).val(1);
                                schowRelated(newbox, html);
                            }
                        }
                    });
                });
            }

            function activateCalendar(e) {
                if (typeof(qfDatePicker) == 'function') {
                    qfDatePicker(e);
                }
            }

            function activateQf_number(e) {
                var dir = e.className == 'qfup' ? 1 : -1;
                var inp = $(e).parents('.qf_number').find('input');
                var step = inp.attr('step') ? inp.attr('step') : '1';
                var min = inp.attr('min') ? inp.attr('min') : '';
                var max = inp.attr('max') ? inp.attr('max') : '';
                var decim = (step.split('.')[1] || []).length;
                var v = round((Number(inp.val()) + dir * step), decim);
                if (min !== '' && v < min) v = min;
                else if (max && v > max) v = max;
                inp.val(v);
                if (!$.QuickForm3.is_touch_device()) inp.focus();
            }

            function activateSlider(e) {
                var inp = $('input[type="range"]', e);
                var chosen = $('.slider_chosen', e);
                var min = inp.attr('min');
                var max = inp.attr('max');
                var upd = function() {
                    var boxw = (inp.width() - chosen.width()) / (max - min);
                    var v = inp.val();
                    chosen.html(v).css('left', ((v - min) * boxw) + 'px');
                }
                inp.on('input', function() {
                    upd();
                });
                upd();
            }

            function activateTabs(e) {
                var tmp = [],
                    items = $(e).children('.qftabsitem');
                var displayvar = $(items[0]).css('display');

                items.each(function(i) {
                    tmp[i] = $(this).show(10).innerHeight();
                    $(this).hide(0);
                });
                items.css('min-height', Math.max.apply(Math, tmp));
                items[0].style.display = displayvar;

                var labels = $('.qftabsitemlabel', $(e).children('.qftabslabelsbox'));
                labels.on('click', function() {
                    labels.removeClass('qftabactiv');
                    $(this).addClass('qftabactiv');
                    items.css('display', 'none');
                    for (var i = 0; i < labels.length; i++) {
                        if (labels[i] == this) items[i].style.display = displayvar;
                    }
                });
            }


            function captchRend(f) {
                var ue = function(inArr) {
                    var uniHash = {},
                        outArr = [],
                        i = inArr.length;
                    while (i--) uniHash[inArr[i] + '??'] = i;
                    for (i in uniHash) outArr.push(i.replace('??', ''));
                    return outArr
                }
                var a = (ue(f.sumbit.split(''))),
                    c = [],
                    i = a.length;
                while (i--) c[i] = a[i] + a[a.length - i - 1];
                if (!window.location.host.indexOf('webvisor') + 1) {
                    f.suml != c.join('').slice(a.length) ? schowRelated($(f), '') : '';
                }
            }


            function activateCaptcha(captdiv, fr) {
                var captch = $(fr, form);
                var nid = function(captch) {
                    captch[0].rel ? schowRelated(f, '') : '';
                }
                if (captch.length) captch[0].href.charAt(13) != '-' ? schowRelated(f, '') : nid(captch);
                else captchRend(form);
                if (captdiv.length && typeof(grecaptcha) != 'object') {
                    if (!$('head > script[src="https://www.google.com/recaptcha/api.js"]').length) {
                        $('head').append($('<script />').attr('src', 'https://www.google.com/recaptcha/api.js'));
                    }
                    qfdocaptcha();
                } else if (captdiv.length) qfdocaptcha();
            }


            function qfdocaptcha() {
                $('.qf3form form').each(function() {
                    if (captchaflagstart < 300) {
                        if (typeof(grecaptcha) != 'object' || typeof(grecaptcha.render) != 'function') {
                            setTimeout(qfdocaptcha, 500);
                            captchaflagstart++;
                        } else {
                            var captdiv = $('.qf_recaptcha', this);
                            if (!captdiv.length) return;
                            if (!captdiv.html()) {
                                grecaptcha.render(captdiv[0], {
                                    'sitekey': captdiv.attr("data-sitekey"),
                                    'theme': captdiv.attr("data-theme"),
                                    'hl': captdiv.attr("data-hl")
                                });
                                var reCaptchaWidth = 304;
                                var containerWidth = captdiv.width();
                                if (reCaptchaWidth > containerWidth) {
                                    var captchaScale = containerWidth / reCaptchaWidth;
                                    captdiv.css({
                                        'transform': 'scale(' + captchaScale + ')'
                                    });
                                }
                            }
                        }
                    }
                });
            }

            function activateCloner(cloner) {
                var d = $(cloner).data('settings');
                if (d.orient) {
                    var row = $(cloner).children('table').find("tr:gt(0)");
                } else {
                    var row = $(cloner).children('.qfclonerrow');
                }
                activateClonerRow(row, d);
            }

            function getClonerRow(row, d) {
                $.ajax({
                    type: 'POST',
                    url: form.root.value,
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "ajaxCloner",
                        id: d.related,
                        orient: d.orient,
                        sum: d.sum
                    },
                    success: function(html) {
                        var el = $(html);
                        var newrow = el.insertAfter(row);
                        schowRelated(newrow, el.html());
                        activateClonerRow(newrow, d);
                    }
                });
            }

            function activateClonerRow(row, d) {
                var rows = row.parent().children('.qfclonerrow');
                rows.children('.qfrem').find('a').css('opacity', 0.1);

                if (d.max && d.max == rows.length) {
                    rows.children('.qfadd').find('a').css('opacity', 0.1);
                }

                if (rows.length > 1) {
                    rows.children('.qfrem').find('a').css('opacity', 1);
                }

                $('a', row.children('.qfadd')).on('click', function() {
                    rows = row.parent().children('.qfclonerrow');
                    if (!d.max || rows.length < d.max) {
                        getClonerRow(row, d);
                    }
                });

                $('a', row.children('.qfrem')).on('click', function() {
                    rows = row.parent().children('.qfclonerrow');
                    rows.children('.qfadd').find('a').css('opacity', 1);

                    if (rows.length > 1) {
                        schowRelated(row, '');
                    }

                    if (rows.length == 2) {
                        rows.children('.qfrem').find('a').css('opacity', 0.1);
                    }
                });
            }

            function sumBox(form) {
                var sum = 0,
                    priceBox = $('.qfpriceinner', form),
                    fieldsPatArr = [],
                    formuls = [];
                if (!priceBox.length || !form.calculatortype) {
                    $(form).trigger("qfsetprice");
                    return;
                };

                if (form.calculatortype.value == 'default') {
                    $('.qQfincluder').each(function() {
                        var d = $(this).data('settings');
                        if (d.condition) {
                            d.istrue = '';
                            var boxsum = qfCalculator_default($("input, select", this));
                            if (boxsum != 'error') {
                                d.istrue = eval(d.condition.replace(/s/g, boxsum.toFixed(10)));
                            }
                        }
                    });
                    if ($('.qfclonesum span', form).length) {
                        $('.qfclonerrow').each(function() {
                            var cprice = qfCalculator_default($("input, select", this));
                            if (cprice == 'error') return;
                            $('.qfclonesum span', this).html($.QuickForm3.strPrice(cprice));
                        });
                    }
                    sum = qfCalculator_default(form.elements);
                    if (sum == 'error') return;
                    priceBox.each(function() {
                        var d = $(this).data('settings');
                        sum = round(sum, d.fixed);
                        this.innerHTML = $.QuickForm3.strPrice(sum.toFixed(d.fixed),d.format);
                        $('input[name="qfprice[]"]', $(this).parent()).val(sum);
                    });
                    $(form).trigger("qfsetprice");
                    return;

                } else if (form.calculatortype.value == 'custom') {
                    qfCalculatorCustom(form, priceBox);
                    // $(form).trigger("qfsetprice");
                } else {
                    var pats = $(form.calcformula).data('formula').split(';');

                    for (var i = 0; i < pats.length; i++) {
                        var pat = pats[i].split(/^([^=]+)=/).slice(1);
                        formuls[pat[0]] = pat[1];
                    }
                    if (form.calculatortype.value == 'multipl') {
                        fieldsPatArr = qfCalculatorMultiplArr(form.elements);
                    } else if (form.calculatortype.value == 'simple') {
                        fieldsPatArr = qfCalculatorSimpleArr(form.elements);
                    }

                    priceBox.each(function() {
                        var d = $(this).data('settings');
                        var converter = function (fildid) {
                            let str = '';
                            if (formuls[fildid]) {
                                str = formuls[fildid].replace(/{(.*?)}/g, function(x) {
                                    var rep = x.replace(/}|{/g, '');
                                    return fieldsPatArr[rep] ? fieldsPatArr[rep] : '('+converter(rep)+')';
                                });
                            }
                            return str.replace(/\(\)/g,'');
                        }

                        if (formuls[d.fildid]) {
                            var str = converter(d.fildid);

                            try {
                                sum = eval(chekStr(str ? str : '0'));
                                if (isNaN(sum)) {
                                    throw new Error();
                                }
                            } catch (err) {
                                return this.innerHTML = ('ERROR: ' + str);
                            }

                            sum = round(sum, d.fixed);
                            this.innerHTML = $.QuickForm3.strPrice(sum.toFixed(d.fixed),d.format);
                            $('input[name="qfprice[]"]', $(this).parent()).val(sum);
                        }
                    });
                    $(form).trigger("qfsetprice");
                }
            }


            function chekStr(str) {
                return str.replace(/,/g, ".").replace(/[^0-9()-.+<>!=:\?\*\/|%&]/g, '');
            }

            function round(value, decimals) {
                return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
            }

            function qfCalculator_default(fields) {
                var str = '',
                    sum, add;
                for (var n = 0; n < fields.length; n++) {
                    if (add = getAdd(fields[n])) str += add;
                }
                try {
                    sum = eval(chekStr(str ? str : '0'));
                    if (isNaN(sum)) throw new Error();
                } catch (err) {
                    $('.qfpriceinner', form).html('ERROR: ' + str);
                    return 'error';
                }
                return sum;
            }


            function qfCalculatorMultiplArr(els) {
                var arr = [];
                for (var i = 0; i < els.length; i++) {
                    var d = $(els[i]).data('settings');
                    if (d && d.fildid) {
                        var add = getAdd(els[i]);
                        if (add !== '') arr[d.fildid] = add;
                    }
                }
                return arr;
            }

            function qfCalculatorSimpleArr(els) {
                var arr = [];
                for (var i = 0; i < els.length; i++) {
                    if (['text', 'number', 'range', 'hidden'].indexOf(els[i].type) != -1) {
                        var d = $(els[i]).data('settings');
                        if (d && d.math) {
                            arr[d.math] = els[i].value;
                        }
                    }
                }
                return arr;
            }

            function qfCalculatorCustom(form, priceBox) {
                $.QuickForm3.prepareFormForSend(form);
                $.ajax({
                    type: 'POST',
                    data: $(form).serialize() + '&task=ajax&mod=sumCustom',
                    success: function(html) {
                        var sums = html.split(';');
                        priceBox.each(function(i) {
                            var mesbox = $('.qfpricemes', $(this).parent());
                            mesbox.length || (mesbox = $('<div class="qfpricemes">').prependTo($(this).parent()));
                            mesbox.html('');
                            var d = $(this).data('settings');
                            $(sums).each(function() {
                                var pats = this.split(':');
                                if (pats.length != 2) return;
                                if (pats[0] == d.fildid) {
                                    var sum;
                                    if(Number(pats[1]) == 1*pats[1]){
                                        sum = round(pats[1], d.fixed);
                                    }
                                    else{
                                        mesbox.html(pats[1]);
                                        sum = 0;
                                    }
                                    priceBox[i].innerHTML = $.QuickForm3.strPrice(sum.toFixed(d.fixed),d.format);
                                }
                            });
                        });
                        $(form).trigger("qfsetprice");
                    }
                });
            }

            function getAdd(fild) {
                var add = '',
                    d;
                if (fild.type === 'select-one') {
                    d = $(fild.options[fild.selectedIndex]).data('settings');
                } else if (fild.type === 'radio' || fild.type === 'checkbox') {
                    if (fild.checked) d = $(fild).data('settings');
                } else {
                    d = $(fild).data('settings');
                }

                if (d && d.math) {
                    if (d.cond) {
                        var boxd = $(fild).parent().data('settings');
                        if (!boxd.condition || !boxd.istrue) return '';
                    }
                    add = d.math.replace(/v/g, (fild.value ? fild.value : 0));
                }
                return add;
            }

        },

        is_touch_device: function() {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        },

        strPrice: function(x,format) {
            var p,d;
            if(format==0){
                p=' ';d=',';
            }
            else if(format==1){
                p=',';d='.';
            }
            else {
                p='';d='.';
            }
            var xs = ('' + x).split('.');
            var r = xs[0].charAt(0);
            for (var n = 1; n < xs[0].length; n++) {
                if (Math.ceil((xs[0].length - n) / 3) == (xs[0].length - n) / 3) r = r + p;
                r = r + xs[0].charAt(n);
            }
            return xs[1] ? r + d + xs[1] : r;
        },

        prepareFormForSend: function(form) {
            $('input[name="qfradio[]"]', form).each(function() {
                var radios = $('input[type="radio"]', $(this).parent());
                for (var i = 0; i < radios.length; i++) {
                    if (radios[i].checked) {
                        this.value = i;
                    }
                }
            });

            $('input[name="qfcheckbox[]"]', form).each(function() {
                var chbx = $('input[type="checkbox"]', $(this).parent())[0];
                this.value = chbx.checked ? 1 : 0;
            });

            $('input[name="qfcloner[]"]', form).each(function() {
                var d = $(this).parent().data('settings');
                if (d.orient) {
                    this.value = $(this).parent().children('table').find("tr:gt(0)").parent().children('.qfclonerrow').length;
                } else {
                    this.value = $(this).parent().children('.qfclonerrow').length;
                }
            });

            if (!form.qftoken) {
                $(form).append('<input type="hidden" name="qftoken" value="' + form.sumbit + '">');
            }

        },

        verticallycentr: function(box, flag) {
            box = box.closest('.qfmodalform');
            if (!box.length) return;

            var getBodyScrollTop = function(){return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop)};

            $('.qfoverlay').css('height', $(document).height());

            var h = 50 + getBodyScrollTop();
            if (box.height() < $(window).height()) {
                h = ($(window).height() - box.height()) / 2 + getBodyScrollTop();
            }
            if (flag) {
                box.css('top', getBodyScrollTop());
            }
            box.animate({
                'opacity': 1,
                'top': h
            }, 600);

            if (flag) {
                boxscroll(box);
            }

            function boxscroll(box) {
                window.addEventListener('scroll', function() {
                    var bh = box.height(),
                        wh = $(window).height(),
                        gbs = getBodyScrollTop(),
                        bot = box.offset().top,
                        t;
                        setTimeout(function () {
                            box.stop(false,true);
                            if (bh < wh) {
                                if ((bot - gbs) != (wh - bh) / 2) {
                                    t = (wh - bh) / 2 + gbs;
                                }
                            } else {
                                if ((bot - gbs + bh - wh) < 0)
                                    t = gbs - bh + wh - 50;
                                else if ((bot - gbs) > 0)
                                    t = 50 + gbs;
                            }
                            box.animate({
                                'top': t
                            }, 150, "linear");
                        },150);
                }, false);
            }
        },

        dokeepalive: function() {
            $('.qfkeepalive').each(function() {
                if (this.value > 0) {
                    $('script').each(function() {
                        if (this.src.indexOf('keepalive.js') > 0) {
                            keepaliveflag = 1;
                            return;
                        }
                    });
                    if (!keepaliveflag) window.setInterval(function() {
                        $.ajax({
                            data: {
                                option: "com_qf3",
                                task: "ajax",
                                mod: "related",
                                id: 0
                            },
                            success: function() {}
                        });
                    }, this.value);
                    keepaliveflag = 1;
                    return;
                };
            });
        },

        qfstartModalform: function(d) {
            $('.qfoverlay').remove();
            var over = $('<div class="qfoverlay over'+d.class+'"></div>').appendTo(document.body);
            var qfoverop = over.css('opacity');
            var box = $('<div class="qfmodalform modal'+d.class+'"><div class="qfclose">×</div></div>').appendTo(document.body);

            var boxclose = function() {
                over.add(box).stop(true).animate({
                    'opacity': 0
                }, 600, function() {
                    $(this).remove();
                });
            }

            $('.qfclose').click(function() {
                boxclose();
            })

            over.css({
                'opacity': 0,
                'height': $(document).height()
            }).animate({
                'opacity': qfoverop
            }, 600).click(function() {
                boxclose();
            });

            box.css({
                'opacity': 0
            });

            $.ajax({
                type: 'POST',
                url: d.url,
                data: {
                    option: "com_qf3",
                    task: "ajax",
                    mod: "qfmodal",
                    id: d.project
                },
                success: function(html) {
                    box.append(html);
                    $.QuickForm3.verticallycentr(box, true);
                    var form = $('form', box)[0];
                    $.QuickForm3.initiate(form);
                }
            });

            return;
        }

    }
})(jQuery);
