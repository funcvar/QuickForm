(function($) {

  $(document).ready(function() {
    var cartbox = $('.qf_cart_box');
      cartbox.on('click', function() {
        $.QFcart.getCartBox();
      });
  });
  return $.QFcart = {
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
          $.QFcart.verticallycentr(box, true);
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
        if(cont.length && cont.css('display') != 'none') {
          cont.stop().animate({
            'opacity': 0
          }, 300, function() {
            this.style.display = 'none';
          });
        }
        else{
          over.add(box).stop().animate({
            'opacity': 0
          }, 300, function() {
            $(this).remove();
          });
        }
      }

      $('.qfcartclose').click(function() {
        boxclose();
      })

      function boxActivate(box) {
        $('.qf_td_1 span', box).each(function(i, v){
          this.onclick = function(){
            removeRow(i, this);
          }
        })

        $('.qf_td_5 input', box).each(function(i, v){
          this.onchange = function(){
            changeRow(i, this);
          }
        })

        var qfforms = $('.qf2form form', box);

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
      }


      function activateCartForm(form) {
        form.cartsubmit = function(){
          var dat, n=0;
          var forms = $('.qf_cart_foot_l .qf2form form');

          var valid = true;
          forms.each(function(){
            if(!this.checkValidity()) {
              this.submit();
              valid = false;
            }
          });
          if(!valid) return;

          $('.qfcartform').animate({
            'opacity': 0
          }, 900);

          var send = function(f){
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
              // url: '/index.php?task=ajax&mod=confirmCart',
              data: dat,
              processData: pr,
              contentType: pr,
              success: function(res) {
                if(res == 'yes') {
                  n++;
                  if( n < forms.length) {
                    send(forms[n]);
                  }
                  else{
                    form.submit();
                  }
                }
              }
            });
          }

          if(forms.length) {
            send(forms[0]);
          }
          else {
            form.submit();
          }
        }
      }



      function sum() {
        var arr= [], html='';
        var els = $('input[name="qfprice[]"]', $('#qf_resultprice'));
        els.each(function(){
          var u = $(this).data('unit');
          if(!arr[u]) arr[u] = 1*this.value;
          else arr[u] += 1*this.value;
        });

        for( var u in arr ){
          if (!arr.hasOwnProperty(u)) continue;
          html += '<div><label class="qf2label">'+QF_TEXT_2+'</label><span class="qfpriceinner">'+$.QuickForm3.strPrice(arr[u])+'</span><span class="qfunitinner">'+u+'</span></div>';
        }

        var div = document.createElement('div');
        div.className = "finalpricerow total";
        div.innerHTML = html;
        div = $(div).appendTo('#qf_resultprice');
      }

      function checkforfinalprice(form){

        $('.qfprice', form).each(function(){
          var div = document.createElement('div');
          div.className = "finalpricerow";
          div.innerHTML = this.innerHTML;
          div = $(div).appendTo('#qf_resultprice');
        })
      }



      function changeRow(i, el){
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
            },300);
            //verticallycentr(box, false);
            boxActivate(box);
            updateMiniCart();
          }
        });
      }


      function removeRow(i, el){
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
            }, 300, function(){
              box2.html(res).css('opacity', 0).animate({
                'opacity': 1
              },600);
              //verticallycentr(box, false);
              boxActivate(box);
              updateMiniCart();
            });
          }
        });
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

    cartnext: function(){
      var fs = $('#qf_delivery .qf2form form').add($('#qf_payment .qf2form form'));
      var valid = true;
      fs.each(function(){
        if(!this.checkValidity()) {
          this.submit();
          valid = false;
        }
      });

      if(!valid) return;


      var box = $('.qfcartform');
      box.animate({
        'opacity': 0
      }, 60, function(){
        $('#qf_contacts').css({
          'display': 'block',
          'opacity': 0
        }).animate({
          'opacity': 1
        }, 100, function(){
          $.QFcart.verticallycentr(box, true);
        });

      });


    },

    addFormInCart: function(form){
      $.QuickForm3.prepareFormForSend(form);
      if (!form.checkValidity()) {
        return form.submit();
      }
      $.ajax({
        type: 'POST',
        url: form.root.value,
        data: $(form).serialize() + '&task=ajax&mod=ajaxminicart',
        success: function(res) {
          $.QFcart.updateMiniCart(res);
        }
      });
      return false;
    },

    updateMiniCart: function(res){
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
            $.QFcart.getCartBox();
          });
        } else {
          shuttle.html(res);
          shuttleAnimate(shuttle);
        }
      }
    },

    verticallycentr: function(box, e){
      var winh = $(window).height();
      var scr = $(document).scrollTop();
      if (box.height() < winh) {
        scr = (winh - box.height()) / 2 + scr;
      }
      box.css('top', scr).animate({
        'opacity': 1
      }, 500);

      if(e){
        window.addEventListener('scroll', function(e){
         if(box.height() < $(window).height()){
            if((box.offset().top-getBodyScrollTop()) != ($(window).height() - box.height()) / 2) {
              box.stop().animate({'top':($(window).height() - box.height()) / 2+getBodyScrollTop()},300);
            }
         }
         else{
           if((box.offset().top-getBodyScrollTop()) > 0)
             box.stop().animate({'top':0+getBodyScrollTop()},300);
           if((box.offset().top-getBodyScrollTop()+box.height()-$(window).height()) < 0)
             box.stop().animate({'top':getBodyScrollTop()-box.height()+$(window).height()},300);
         }
        }, false);
      }
      function getBodyScrollTop(){
      	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
      }

    }

  };
})(jQuery);
