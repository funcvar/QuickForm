(function($) {
    $(document).ready(function() {
        var cartbox = $('.qf_cart_box');
        cartbox.on('click', function() {
            QFcart.getCartBox();
        });
    });

    var actions = {
        start: function() {
            var $preloader = $('<div id="jpreloader" class="preloader-overlay"><div class="loader" style="position:absolute;left:50%;top:50%;margin-left:-32px;margin-top:-32px;"><svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="64px" height="64px" viewBox="0 0 128 128" xml:space="preserve"><rect x="0" y="0" width="100%" height="100%" fill="#f3ffff" /><path fill="#000000" fill-opacity="1" d="M64.4 16a49 49 0 0 0-50 48 51 51 0 0 0 50 52.2 53 53 0 0 0 54-52c-.7-48-45-55.7-45-55.7s45.3 3.8 49 55.6c.8 32-24.8 59.5-58 60.2-33 .8-61.4-25.7-62-60C1.3 29.8 28.8.6 64.3 0c0 0 8.5 0 8.7 8.4 0 8-8.6 7.6-8.6 7.6z"><animateTransform attributeName="transform" type="rotate" from="0 64 64" to="360 64 64" dur="1800ms" repeatCount="indefinite"></animateTransform></path></svg></div></div>');
            $preloader.css({
                'background-color': '#f3ffff',
                'width': '100%',
                'height': '100%',
                'left': '0',
                'top': '0',
                'opacity': '0.6',
                'position': 'absolute'
            });
            this.append($preloader);
        },

        stop: function() {
            this.find('.preloader-overlay').remove();
        }
    };

    $.fn.preloader = function(action) {
        actions[action].apply(this);
        return this;
    };

    return QFcart = {
        getCartBox: function() {
            if ($('.qfcartoverlay').length) {
                $('.qfcartoverlay').remove();
            }
            if ($('.qfcartform').length) {
                $('.qfcartform').remove();
            }

            var over = $('<div class="qfcartoverlay"></div>').appendTo(document.body);
            var qfoverop = over.css('opacity');
            var box = $('<div class="qfcartform"><div class="qfcartclose">✕</div></div>').appendTo(document.body);
            var box2 = $('<div class="qfcartforminner"></div>').appendTo(box);

            jQuery.ajax({
                data: {
                    option: "com_qf3",
                    task: "ajax",
                    mod: "qfcart"
                },
                success: function(res) {
                    box2.append(res);
                    QFcart.verticallycentr(box, true);
                    boxActivate(box);
                },
            });

            over.css({
                'display': 'block',
                'opacity': 0,
                'height': $(document).height()
            }).animate({
                'opacity': qfoverop
            }, 600).click(function() {
                boxclose();
            });

            box.css({
                'display': 'block',
                'opacity': 0
            });

            var boxclose = function() {
                var cont = $('#qf_contacts');
                if (cont.length && cont.css('display') != 'none') {
                    cont.stop().animate({
                        'opacity': 0
                    }, 300, function() {
                        this.style.display = 'none';
                    });
                } else if ($('.atch_box').length) {
                    $('.atch_box').animate({
                        'opacity': 0
                    }, 600, function() {
                        $(this).remove();
                    });
                    $('.qfcartform').show().animate({
                        'opacity': 1
                    }, 800);
                    QFcart.verticallycentr(box, false);
                } else {
                    over.add(box).stop().animate({
                        'opacity': 0
                    }, 300, function() {
                        $(this).remove();
                    });
                }
            }

            $('.qfcartclose').click(function() {
                boxclose();
            });

            function boxActivate(box) {
                $('.qf_td_1 span', box).each(function(i, v) {
                    this.onclick = function() {
                        removeRow(i, this);
                    }
                });

                $('.qf_td_5 input', box).each(function(i, v) {
                    this.onchange = function() {
                        changeRow(i, this);
                    }
                })

                $('.atch', box).each(function(i, v) {
                    this.onclick = function() {
                        upload_layouts(i, this);
                    }
                });

                var qfforms = $('.qf3form form', box);

                qfforms.each(function() {
                    $.QuickForm3.initiate(this);
                    checkforfinalprice(this);
                });
                sum();

                qfforms.on('qfsetprice', function() {
                    $('.finalpricerow').remove();
                    qfforms.each(function() {
                        checkforfinalprice(this);
                    });
                    sum();
                });

                $('form.cart_form', box).each(function() {
                    activateCartForm(this);
                });

                var discount_box = $('.qf_cart_discount');
                if (discount_box.length) {
                    activateDiscaunt(discount_box);
                }
            }


            function activateCartForm(form) {
                form.cartsubmit = function() {
                    var dat, n = 0;
                    var forms = $('.qf_cart_foot_l .qf3form form');

                    forms.each(function() {
                        if (!this.checkValidity()) {
                            return this.submit();
                        }
                    });

                    if (!QFcart.checkreqfiles()) return false;

                    $('.qfcartform').animate({
                        'opacity': 0
                    }, 900);

                    var send = function(f) {
                        $.QuickForm3.prepareFormForSend(f);
                        var pr = true;
                        if (!window.FormData)
                            dat = $(f).serialize() + '&task=ajax&mod=confirmCart';
                        else {
                            dat = new FormData(f);
                            dat.set('task', 'ajax');
                            dat.set('mod', 'confirmCart');
                            pr = false;
                        }

                        $.ajax({
                            type: 'POST',
                            url: form.root.value,
                            data: dat,
                            processData: pr,
                            contentType: pr,
                            success: function(res) {
                                if (res == 'yes') {
                                    n++;
                                    if (n < forms.length) {
                                        send(forms[n]);
                                    } else {
                                        form.submit();
                                    }
                                }
                            }
                        });
                    }

                    if (forms.length) {
                        send(forms[0]);
                    } else {
                        form.submit();
                    }
                }
            }


            function activateDiscaunt(box) {
                doDiscounts();
                var btn = $('input[name=disbut]', box);
                btn.on('click', function() {
                    if (this.className != 'enabled' && !this.form.checkValidity()) {
                        return $('<input type="submit">').hide().appendTo(this.form).click().remove();
                    } else if (this.className == 'enabled') {
                        $('input[name=disinp]', box).val('');
                    }
                    getPromocod(this);
                });
                if ($('input[name=disinp]', box).val()) {
                    getPromocod(btn[0]);
                }
            }

            function getPromocod(btn) {
                $.ajax({
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "qfcartpromocod",
                        cod: $('input[name=disinp]', box).val()
                    },
                    success: function(res) {
                        if (res) {
                            var el = $('input[name="qfprice[]"]', $('#qf_resultprice'))[0];
                            var price = el.value;
                            var unit = $(el).data('unit');
                            var discount = price * res / 100;

                            $('.finalpricerow').remove();
                            $('.qf_cart_foot_l .qf3form form').each(function() {
                                checkforfinalprice(this);
                            });

                            var html = '<label>'+ qf_txt_discount + ' '+ res + '%</label><span class="qfpriceinner">' + discount + '</span><span class="qfunitinner">' + unit + '</span><input name="qfprice[]" type="hidden" value="-' + discount + '" data-unit="' + unit + '">';
                            var div = document.createElement('div');
                            div.className = "finalpricerow";
                            div.innerHTML = html;
                            div = $(div).appendTo('#qf_resultprice');
                            sum();
                            btn.className = 'enabled';
                            btn.value = qf_txt_dis;
                        } else {
                            $('.finalpricerow').remove();
                            $('.qf_cart_foot_l .qf3form form').each(function() {
                                checkforfinalprice(this);
                            });
                            sum();
                            btn.className = '';
                            btn.value = qf_txt_act;
                            doDiscounts();
                        }
                    }
                });

            }

            function doDiscounts() {
                if (!qf_cart_discount) return;
                var el = $('input[name="qfprice[]"]', $('#qf_resultprice'))[0];
                var price = el.value;
                var unit = $(el).data('unit');
                var ar, disc, arr = qf_cart_discount.split('%');
                for (var i = 0; i < arr.length; i++) {
                    ar = arr[i].split('-');
                    if (+ar[0] < +price && +price <= +ar[1]) {
                        disc = ar[2];
                        break;
                    }
                }
                if (disc) {
                    var discount = price * disc / 100;

                    $('.finalpricerow').remove();
                    $('.qf_cart_foot_l .qf3form form').each(function() {
                        checkforfinalprice(this);
                    });

                    var html = '<label>'+ qf_txt_discount + ' '+ disc + '%</label><span class="qfpriceinner">' + discount + '</span><span class="qfunitinner">' + unit + '</span><input name="qfprice[]" type="hidden" value="-' + discount + '" data-unit="' + unit + '">';
                    var div = document.createElement('div');
                    div.className = "finalpricerow";
                    div.innerHTML = html;
                    div = $(div).appendTo('#qf_resultprice');
                    sum();
                }
            }



            function sum() {
                var arr = [],
                    html = '';
                var pricebox = $('#qf_resultprice');
                var els = $('input[name="qfprice[]"]', pricebox);

                els.each(function() {
                    var u = $(this).data('unit');
                    if (!arr[u]) arr[u] = 1 * this.value;
                    else arr[u] += 1 * this.value;
                });

                for (var u in arr) {
                    if (!arr.hasOwnProperty(u)) continue;
                    html += '<div><label class="qf3label">' + pricebox.data('text_price_total') + '</label><span class="qfpriceinner">' + $.QuickForm3.strPrice(arr[u], 0) + '</span><span class="qfunitinner">' + u + '</span></div>';
                }

                var div = document.createElement('div');
                div.className = "finalpricerow total";
                div.innerHTML = html;
                div = $(div).appendTo('#qf_resultprice');
            }

            function checkforfinalprice(form) {

                $('.qfcalculatorsum', form).each(function() {
                    var div = document.createElement('div');
                    div.className = "finalpricerow";
                    div.innerHTML = this.innerHTML;
                    div = $(div).appendTo('#qf_resultprice');
                })
            }



            function changeRow(i, el) {
                box2.animate({
                    'opacity': 0
                }, 300);
                jQuery.ajax({
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "qfcartchangerow",
                        num: i,
                        val: el.value
                    },
                    success: function(res) {
                        box2.html(res).css('opacity', 0).animate({
                            'opacity': 1
                        }, 300);
                        QFcart.verticallycentr(box, false);
                        boxActivate(box);
                        updateMiniCart();
                    }
                });
            }


            function removeRow(i, el) {
                $(el).closest('tr').animate({
                    'opacity': 0
                }, 900);

                jQuery.ajax({
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "qfcartremrow",
                        num: i
                    },
                    success: function(res) {
                        $(el).closest('tr').stop().animate({
                            'opacity': 0
                        }, 300, function() {
                            box2.html(res).css('opacity', 0).animate({
                                'opacity': 1
                            }, 600);
                            QFcart.verticallycentr(box, false);
                            boxActivate(box);
                            updateMiniCart();
                        });
                    }
                });
            }


            function upload_layouts(i, el) {
                var box = $('<div class="atch_box"><div class="qfcartclose">✕</div><div>').appendTo(document.body);
                box.css({
                    'display': 'block',
                    'opacity': 0
                });
                $('.qfcartform').animate({
                    'opacity': 0
                }, 800, function() {
                    $(this).hide();
                    QFcart.verticallycentr($('.qfcartform'), false);
                });
                jQuery.ajax({
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "showAttachmentBox",
                        num: i
                    },
                    success: function(res) {
                        box.append(res);
                        QFcart.verticallycentr(box, true);
                        $('.qfcartclose', box).click(function() {
                            boxclose();
                        });
                        activateUploadBox(box, i);
                    }
                });
            }

            function activateUploadBox(box, iii) {
                var PR_formData = [],
                    iimg = 0;
                $(".atch_area").click(function() {
                    $("#file_field").click();
                });
                $("#file_field").bind({
                    change: function() {
                        displayFiles(this.files);
                    }
                });

                box.bind({
                    dragover: function() {
                        return false;
                    },
                    drop: function(e) {
                        var files = e.originalEvent.dataTransfer.files;
                        if (files.length) {
                            event.preventDefault();
                            event.stopPropagation();
                            return displayFiles(files);
                        }
                    },
                    dragenter: function(e) {
                        $(this).css('border-color', 'green');
                    },
                    dragleave: function(e) {
                        $(this).css('border-color', '#ccc');
                    }
                });

                $('.atch_link_more a').on('click', function() {
                    $('.atch_link:last').after('<div class="atch_link"><input type="text" placeholder="'+ $('.atch_link input').attr('placeholder') +'"></div>');
                    checkLinks();
                    QFcart.verticallycentr(box, false);
                    return false;
                });
                checkLinks();

                $('.atch_reset').click(function() {
                    boxclose();
                });

                $('.atch_send', box).click(function() {
                    box_layouts_send();
                });

                $('.del_old_img', box).click(function() {
                    del_old_img(this);
                });

                function del_old_img(el) {
                    var imgbox = $(el).parent('.imgtbox_old');
                    var spl = imgbox.find('.del_old').data('href');
                    if (spl) {
                        box.preloader('start');
                        jQuery.ajax({
                            data: {
                                option: "com_qf3",
                                task: "ajax",
                                mod: "attachment_del_img",
                                name: spl,
                                num: iii
                            },
                            success: function(res) {
                                box.preloader('stop');
                                if (res == 'yes') {
                                    imgbox.remove();
                                    QFcart.verticallycentr(box, false);
                                } else {
                                    $('.atch_message').fadeIn().html(res).delay(2500).fadeOut(1500);
                                }
                            }
                        });
                    }
                }

                function box_layouts_send() {
                    var links = [],
                        files = [];
                    $('.atch_link input', box).each(function() {
                        if (this.value) links.push(this.value);
                    });
                    for (var i = 0; i < PR_formData.length; i++) {
                        if (PR_formData[i]) files.push(PR_formData[i]);
                    }
                    var txtarea = $('.atch_coment textarea', box).val();

                    box.preloader('start');

                    var fD = new FormData();
                    for (var i = 0; i < files.length; i++) {
                        fD.append('imagefile[' + i + ']', files[i]);
                    }
                    for (var i = 0; i < links.length; i++) {
                        fD.append('imagelinks[' + i + ']', links[i]);
                    }
                    fD.append('imagecoment', txtarea);
                    fD.append('option', 'com_qf3');
                    fD.append('task', 'ajax');
                    fD.append('mod', 'sessionLoading');
                    fD.append('num', iii);

                    $.ajax({
                        data: fD,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: function(data) {
                            box.preloader('stop');
                            if (data) {
                                if (data.indexOf("label:") == 0) {
                                    $('.atch')[iii].innerHTML = data.slice(7);
                                    boxclose();
                                } else {
                                    $('.atch_message').fadeIn().html(data).delay(3500).fadeOut(1500);
                                }
                            }
                        }
                    });


                }

                function checkLinks() {
                    var links = $('.atch_link');
                    $('a', links).remove();
                    if (links.length > 1) {
                        $('✕'.link('#')).appendTo(links).click(function() {
                            $(this).parent().remove();
                            checkLinks();
                            QFcart.verticallycentr(box, false);
                            return false;
                        });
                    }
                }

                function displayFiles(files) {
                    var err = '',
                        accept = $('#file_field').data('accept');
                    if (accept) accept = accept.split(",");
                    $.each(files, function(i, file) {
                        var k = iimg;
                        var ext = file.name.split(".").pop().toLowerCase();
                        if (!accept || $.inArray(ext, accept) != -1) {
                            var ibox = $('<div class="imgtbox_new"></div>').appendTo('.filelisting');
                            var ateg = $('<div class="file_name">' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + 'M)</div>').appendTo(ibox);
                            var del = $('<span class="imgtdel">✕</span>').appendTo(ibox).on('click', function() {
                                ibox.remove();
                                delete PR_formData[k];
                            });
                            PR_formData[iimg] = file;
                            iimg++;
                            QFcart.verticallycentr(box, false);
                        } else {
                            err += '<br>error: File type not supported: ' + file.name;
                        }
                    });
                    err ? $('.atch_message').fadeIn().html(err).delay(5000).fadeOut(1500) : 0;
                }


            }


            function updateMiniCart() {
                jQuery.ajax({
                    data: {
                        option: "com_qf3",
                        task: "ajax",
                        mod: "updateminicart"
                    },
                    success: function(res) {
                        $('.qf_cart_box').html(res);
                        $('#qfshuttle').html(res);
                    }
                });

            }

        },

        checkreqfiles: function() {
            var fl;
            $('.atch a').each(function() {
                var el = $(this);
                if (el.data('req')) {
                    var col = el.css('color');
                    el.animate({
                        'opacity': 0
                    }, 300, function() {
                        el.css('color', '#ff0000').animate({
                            'opacity': 1
                        }, 600, function() {
                            el.animate({
                                'opacity': 0
                            }, 600, function() {
                                el.css('color', col).animate({
                                    'opacity': 1
                                }, 600);
                            });
                        });
                    });
                    fl = true;
                }
            });
            if (!fl) return true;
        },

        cartnext: function() {
            var fs = $('#qf_delivery .qf3form form').add($('#qf_payment .qf3form form'));
            fs.each(function() {
                if (!this.checkValidity()) {
                    return this.submit();
                }
            });

            if (!QFcart.checkreqfiles()) return false;

            var box = $('.qfcartform');
            box.animate({
                'opacity': 0
            }, 60, function() {
                $('#qf_contacts').css({
                    'display': 'block',
                    'opacity': 0
                }).animate({
                    'opacity': 1
                }, 100, function() {
                    QFcart.verticallycentr(box, true);
                });

            });


        },

        addFormInCart: function(form) {
            $.QuickForm3.prepareFormForSend(form);
            if (!form.checkValidity()) {
                return form.submit();
            }
            $.ajax({
                type: 'POST',
                url: form.root.value,
                data: $(form).serialize() + '&task=ajax&mod=ajaxminicart',
                success: function(res) {
                    QFcart.updateMiniCart(res);
                }
            });
            return false;
        },

        updateMiniCart: function(res) {
            var cartbox = $('.qf_cart_box');
            var over = $('<div class="qfcartoverlay"></div>').appendTo(document.body);
            over.css({
                'display': 'block',
                'height': $(document).height()
            }).animate({
                'opacity': 0
            }, 400, function() {
                $(this).remove();
            });

            if (!cartbox.length) return updateShuttle();
            updateShuttle();

            cartbox.animate({
                'opacity': 0
            }, 300, function() {
                cartbox.html(res);
                cartbox.animate({
                    'opacity': 1
                }, 600);
            });

            function inWindow(currentEls) {
                var scrollTop = $(window).scrollTop();
                var windowHeight = $(window).height();
                var result = [];
                currentEls.each(function() {
                    var el = $(this);
                    var offset = el.offset();
                    if (scrollTop <= offset.top && (el.height() + offset.top) < (scrollTop + windowHeight)) result.push(this);
                });
                return result.length;
            }

            function updateShuttle() {
                var shuttleAnimate = function(shuttle) {
                    var cartbox = $('.qf_cart_box');
                    if (!inWindow(cartbox)) {
                        shuttle.animate({
                            'opacity': 1
                        }, 500, function() {
                            shuttle.animate({
                                'opacity': 0
                            }, 3000);
                        })
                    }
                }
                var shuttle = $('#qfshuttle');
                if (!shuttle.length) {
                    shuttle = $('<div id="qfshuttle">' + res + '</div>');
                    shuttleAnimate(shuttle.appendTo('body'));
                    shuttle.on('mouseover', function() {
                        $(this).stop().animate({
                            'opacity': 1
                        }, 600);
                    }).on('mouseleave', function() {
                        $(this).stop().animate({
                            'opacity': 0
                        }, 600);
                    }).on('click', function() {
                        QFcart.getCartBox();
                    });
                } else {
                    shuttle.html(res);
                    shuttleAnimate(shuttle);
                }
            }
        },

        verticallycentr: function(box, flag) {
            if (!box.length) return;

            var getBodyScrollTop = function() {
                return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop)
            };

            var scrll = function() {
                var bh = box.outerHeight(),
                    wh = $(window).height(),
                    gbs = getBodyScrollTop(),
                    bot = box.offset().top,
                    t;
                setTimeout(function() {
                    box.stop(false, true);
                    if (bh < wh) {
                            t = (wh - bh) / 2 + gbs;
                    } else {
                        if ((bot - gbs + bh - wh) < 0)
                            t = gbs - bh + wh;
                        else if ((bot - gbs) > 0)
                            t = gbs;
                    }
                    box.animate({
                        'top': t,
                        'opacity': 1
                    }, 500, "linear");
                }, 100);
            }

            $('.qfoverlay').css('height', $(document).height());

            if (flag) {
                window.addEventListener('scroll', function() {
                    scrll();
                }, false);
            }
            scrll();
        },

    };

})(jQuery);
