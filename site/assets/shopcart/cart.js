(() => {
  const $ = (s, c) => (c ?? document).querySelector(s),
    $$ = (s, c) => (c ?? document).querySelectorAll(s),
    opacity = (el, val, time, func) => QuickForm.opacity(el, val, time, func);
  let bodystyle;

  function tag(str) {
    var wrapper = document.createElement("div");
    wrapper.innerHTML = str;
    return {
      to: function (parent) {
        return parent.appendChild(wrapper.firstChild);
      },
    };
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("qf_cart_sum")) QFcart.CartBox();
    });
    QFcart.updateMiniCart();
  });

  return (QFcart = {
    CartBox: function () {
      bodystyle = document.body.style.cssText;
      $$(".qfcartoverlay, .qfcartform").forEach((el) => el.remove());
      const cl = !QuickForm.is_touch_device() ? "desk" : "touch";

      var over = tag('<div class="qfcartoverlay ' + cl + '"></div>').to(
        document.body
      );
      var overop = window.getComputedStyle(over).opacity || "0";
      var box = tag(
        '<div class="qfcartform ' +
          cl +
          '"><div class="qfcartclose">✕</div></div>'
      ).to(document.body);
      var box2 = tag('<div class="qfcartforminner"></div>').to(box);

      QuickForm.req("option=com_qf3&task=ajax.qfcart", (res) => {
        const html = document.createRange().createContextualFragment(res);
        box2.append(html);
        boxActivate(box);
        QuickForm.Ycentr(box, true);
      });

      over.style.opacity = "0";
      over.style.height = document.body.clientHeight + "px";
      opacity(over, overop, 900);
      over.onclick = () => QFcart.boxclose();

      box.style.opacity = "0";
      $(".qfcartclose", box).onclick = () => QFcart.boxclose();

      function boxActivate(box) {
        $$(".qf_td_1 span", box).forEach((el, i) => {
          el.onclick = () => removeRow(i, el);
        });

        $$(".qf_td_5 input", box).forEach((el, i) => {
          el.onchange = () => changeRow(i, el);
        });

        $$(".atch", box).forEach((el, i) => {
          el.onclick = () => QFcart.attachmentbox(i);
        });

        var qfforms = $$(".qf3form form", box);
        var dbox = $(".qf_cart_discount");

        qfforms.forEach((form) => {
          QuickForm.initiate(form);
          QFcart.checkforfinalprice(form);
          form.addEventListener("qfsetprice", () => {
            $$(".finalpricerow", box).forEach((el) => el.remove());
            qfforms.forEach((fm) => {
              QFcart.checkforfinalprice(fm);
            });
            QFcart.sum();
            if (dbox) {
              QFcart.activateDiscaunt(dbox);
            }
          });
        });
        QFcart.sum();

        if (dbox) {
          QFcart.activateDiscaunt(dbox);
        }
      }

      function changeRow(i, el) {
        opacity(box2, 0, 900, 0);
        QuickForm.req(
          "option=com_qf3&task=ajax.qfcartchangerow&num=" +
            i +
            "&val=" +
            el.value,
          (res) => {
            box2.style.opacity = "0";
            box2.innerHTML = res;
            opacity(box2, 1, 300, 0);
            boxActivate(box);
            QFcart.updateMiniCart();
          }
        );
      }

      function removeRow(i, el) {
        opacity(el.closest("tr"), 0, 600, 0);
        QuickForm.req(
          "option=com_qf3&task=ajax.qfcartremrow&num=" + i,
          (res) => {
            box2.style.opacity = "0";
            box2.innerHTML = res;
            opacity(box2, 1, 600, 0);
            boxActivate(box);
            QFcart.updateMiniCart();
          }
        );
      }
    },

    sum: function () {
      var arr = [];
      var pricebox = $("#qf_resultprice");
      if (!pricebox) return;
      var els = $$('input[name="qfprice[]"]', pricebox);

      els.forEach((el) => {
        var u = el.dataset.unit;
        if (!arr[u]) arr[u] = 1 * el.value;
        else arr[u] += 1 * el.value;
      });

      var html = "";
      for (var u in arr) {
        if (!arr.hasOwnProperty(u)) continue;
        html +=
          '<div><label class="qf3label">' +
          pricebox.dataset.text_price_total +
          '</label><span class="qfpriceinner">' +
          QFcart.round(arr[u]) +
          '</span><span class="qfunitinner">' +
          u +
          "</span></div>";
      }

      tag('<div class="finalpricerow total">' + html + "</div>").to(pricebox);
    },

    checkforfinalprice: function (form) {
      var box = $("#qf_resultprice");
      if (!box) return;
      $$(".qfcalculatorsum", form).forEach((el) => {
        tag('<div class="finalpricerow">' + el.innerHTML + "</div>").to(box);
      });
    },

    round: function (value) {
      return QuickForm.strPrice(
        Number(
          Math.round(value + "e" + qf_cart_fixed) + "e-" + qf_cart_fixed
        ).toFixed(qf_cart_fixed),
        qf_cart_format
      );
    },

    activateDiscaunt: function (box) {
      var resbox = $("#qf_resultprice");
      if (!resbox) return;
      var priceinput = $('input[name="qfprice[]"]', resbox);
      doDiscounts();

      var promocodbtn = $("input[name=disbut]", box);
      if (promocodbtn) {
        var promocodinp = $("input[name=disinp]", box);
        promocodbtn.onclick = function () {
          if (this.className != "enabled" && !this.form.checkValidity()) {
            var sub = tag('<input type="submit" style="display:none">').to(
              this.form
            );
            sub.click();
            sub.remove();
          } else if (this.className == "enabled") {
            promocodinp.value = "";
          }
          getPromocod(this);
        };
        if (promocodinp.value) {
          getPromocod(promocodbtn);
        }
      }

      function setdiscount(disc, abs) {
        var price = priceinput.value;
        var unit = priceinput.dataset.unit;
        var discount, label;
        if (abs) {
          discount = disc;
          label = qf_txt_discount;
        } else {
          discount = (price * disc) / 100;
          label = qf_txt_discount + " " + disc + "%";
        }
        $$(".finalpricerow").forEach((el) => el.remove());
        tag(
          '<div class="finalpricerow"><label>' +
            label +
            '</label><span class="qfpriceinner">-' +
            QFcart.round(discount) +
            '</span><span class="qfunitinner">' +
            unit +
            '</span><input name="qfprice[]" type="hidden" value="-' +
            discount +
            '" data-unit="' +
            unit +
            '"></div>'
        ).to(resbox);

        $$(".qf_cart_foot_l .qf3form form").forEach((el) => {
          QFcart.checkforfinalprice(el);
        });
        QFcart.sum();
      }

      function doDiscounts() {
        if (!qf_cart_discount) return;
        var price = priceinput.value;
        var ar,
          disc,
          abs,
          arr = qf_cart_discount.match(/([^&%]+[&%])/g) || [];
        for (var i = 0; i < arr.length; i++) {
          if (arr[i].endsWith("&")) abs = "abs";
          else abs = "";
          ar = arr[i].slice(0, -1).split("-");
          if (+ar[0] < +price && +price <= +ar[1]) {
            disc = ar[2];
            break;
          }
        }
        if (disc) {
          setdiscount(disc, abs);
        }
      }

      function getPromocod(btn) {
        QuickForm.req(
          "option=com_qf3&task=ajax.qfcartpromocod&cod=" + promocodinp.value,
          (res) => {
            if (res.length > 1) {
              if (res.endsWith("&")) setdiscount(res.slice(0, -1), "abs");
              else setdiscount(res.slice(0, -1), "");

              btn.className = "enabled";
              btn.value = qf_txt_dis;
            } else {
              $$(".finalpricerow").forEach((el) => el.remove());
              $$(".qf_cart_foot_l .qf3form form").forEach((el) => {
                QFcart.checkforfinalprice(el);
              });
              QFcart.sum();
              btn.className = "";
              btn.value = qf_txt_act;
              doDiscounts();
            }
          }
        );
      }
    },

    attachmentbox: function (indx) {
      const box = tag(
        '<div class="atch_box"><div class="qfcartclose">✕</div><div>'
      ).to(document.body);
      box.style.display = "block";
      box.style.opacity = "0";
      const cart = $(".qfcartform");
      opacity(cart, 0, 600, (cart) => {
        cart.style.display = "none";
      });
      QuickForm.req(
        "option=com_qf3&task=ajax.showAttachmentBox&num=" + indx,
        (res) => {
          box.insertAdjacentHTML("beforeend", res);
          QuickForm.Ycentr(box, true);
          $(".qfcartclose", box).onclick = () => this.boxclose();
          QFcart.activateAttachmentbox(box, indx);
        }
      );
    },

    activateAttachmentbox: function (box, iii) {
      var PR_formData = [],
        iimg = 0,
        counter = 0,
        inputfile = $("#file_field", box);
      $(".atch_area", box).onclick = () => {
        inputfile.click();
      };
      inputfile.onchange = function () {
        displayFiles(this.files);
      };
      box.ondragover = () => {
        return false;
      };
      box.ondrop = (e) => {
        var files = e.dataTransfer.files;
        if (files.length) {
          event.preventDefault();
          event.stopPropagation();
          return displayFiles(files);
        }
      };
      box.ondragenter = () => {
        counter++;
        box.style.borderColor = "green";
      };
      box.ondragleave = () => {
        counter--;
        if (!counter) box.style.borderColor = "#ccc";
      };

      var checkLinks = () => {
        var links = $$(".atch_link", box);
        links.forEach((one) => {
          $("a", one)?.remove();
          if (links.length > 1) {
            tag("✕".link("#")).to(one).onclick = function () {
              one.remove();
              checkLinks();
              return false;
            };
          }
        });
      };

      var animaterr = (err) => {
        const el = $(".atch_message");
        if (!el) return;
        el.innerHTML = err;
        setTimeout(() => {
          opacity(el, 0, 900, (el) => el.remove());
        }, 5000);
      };

      $(".atch_link_more a", box).onclick = function () {
        this.insertAdjacentHTML(
          "beforebegin",
          '<div class="atch_link"><input type="text" placeholder="' +
            $(".atch_link input", box).placeholder +
            '"></div>'
        );
        checkLinks();
        return false;
      };
      checkLinks();

      $(".atch_reset", box).onclick = () => QFcart.boxclose();
      $(".atch_send", box).onclick = () => filessend();
      $$(".del_old_img", box).forEach((el) => {
        el.onclick = () => del_old_img(el);
      });

      function del_old_img(el) {
        var imgbox = el.closest(".imgtbox_old");
        var spl = $(".del_old", imgbox)?.dataset.href;
        if (spl) {
          let prel = box.appendChild(QuickForm.preloader());
          QuickForm.req(
            "option=com_qf3&task=ajax.attachment_del_img&name=" +
              spl +
              "&num=" +
              iii,
            (res) => {
              prel.remove();
              if (res == "yes") {
                imgbox.remove();
              } else {
                animaterr(res);
              }
            }
          );
        }
      }

      function filessend() {
        var links = [],
          files = [];
        $$(".atch_link input", box).forEach((el) => {
          if (el.value) links.push(el.value);
        });
        for (var i = 0; i < PR_formData.length; i++) {
          if (PR_formData[i]) files.push(PR_formData[i]);
        }
        var txtarea = $(".atch_coment textarea", box).value;

        let prel = box.appendChild(QuickForm.preloader());

        var fD = new FormData();
        for (var i = 0; i < files.length; i++) {
          fD.append("imagefile[" + i + "]", files[i]);
        }
        for (var i = 0; i < links.length; i++) {
          fD.append("imagelinks[" + i + "]", links[i]);
        }
        fD.set("imagecoment", txtarea);
        fD.set("option", "com_qf3");
        fD.set("task", "ajax.sessionLoading");
        fD.set("num", iii);

        QuickForm.req(fD, (res) => {
          prel.remove();
          if (res) {
            if (res.indexOf("label:") == 0) {
              $$(".atch")[iii].innerHTML = res.slice(7);
              QFcart.boxclose();
            } else {
              animaterr(res);
            }
          }
        });
      }

      function displayFiles(files) {
        var err = "",
          accept = inputfile.dataset.accept;
        if (accept) accept = accept.split(",");

        Object.keys(files).forEach((k) => {
          var k = iimg;
          var ext = files[k].name.split(".").pop().toLowerCase();
          if (!accept || accept.includes(ext)) {
            var ibox = tag('<div class="imgtbox_new"></div>').to(
              $(".filelisting", box)
            );
            var ateg = tag(
              '<div class="file_name">' +
                files[k].name +
                " (" +
                (files[k].size / 1024 / 1024).toFixed(2) +
                "M)</div>"
            ).to(ibox);
            var del = (tag('<span class="imgtdel">✕</span>').to(ibox).onclick =
              function () {
                ibox.remove();
                delete PR_formData[k];
              });
            PR_formData[iimg] = files[k];
            iimg++;
          } else {
            err += "<br>error: File type not supported: " + files[k].name;
          }
        });
        err ? animaterr(err) : 0;
      }
    },

    boxclose: function () {
      const cart = $(".qfcartform"),
        atch_box = $(".atch_box"),
        cont = $("#qf_contacts"),
        close = (el) => {
          opacity(el, 0, 600, (el) => {
            el.remove();
          });
          cart.style.display = "";
          opacity(cart, 1, 600, 0);
        };

      if (atch_box) close(atch_box);
      else if (cont) close(cont);
      else {
        const over = $(".qfcartoverlay");
        [cart, over].forEach((el) => {
          opacity(el, 0, 600, (el) => {
            el.remove();
          });
        });
        const scroll = document.body.style.top;
        document.body.style.cssText = bodystyle;
        window.scrollTo(0, parseInt(scroll || "0") * -1);
      }
    },

    updateMiniCart: function () {
      QuickForm.req("option=com_qf3&task=ajax.updateminicart", (res) => {
        QFcart.animateMiniCart(res);
      });
    },

    checkreqfiles: function () {
      var fl;
      $$(".atch a").forEach((el) => {
        if (el.dataset.req) {
          fl = true;
          var col = window.getComputedStyle(el).color;
          opacity(el, 0, 300, (el) => {
            el.style.color = "#ff0000";
            opacity(el, 1, 600, (el) => {
              opacity(el, 0, 600, (el) => {
                el.style.color = col;
                opacity(el, 1, 600, 0);
              });
            });
          });
        }
      });
      if (!fl) return true;
    },

    cartnext: function () {
      var fl,
        forms = $$("#qf_delivery form, #qf_payment form");
      forms.forEach((el) => {
        if (!el.checkValidity()) {
          fl = 1;
          return el.submit();
        }
      });
      if (fl || !QFcart.checkreqfiles()) return false;

      var cart = $(".qfcartform");
      opacity(cart, 0, 600, (cart) => {
        cart.style.display = "none";
      });
      QuickForm.req("option=com_qf3&task=ajax.cartcontacts", (res) => {
        var box = tag(res).to(document.body);
        $(".qfcartclose", box).onclick = () => {
          var cont = $("#qf_contacts");
          opacity(cont, 0, 600, (cont) => {
            cont.remove();
          });
          cart.style.display = "";
          opacity(cart, 1, 800, 0);
        };
        QuickForm.Ycentr(box, true);
        var form = $("#qf_contacts form");
        QuickForm.initiate(form);
      });
    },

    cartsubmit: function (form) {
      var fl,
        dat,
        n = 0;
      var forms = $$(
        ".qf_cart_foot_l .qf3form form, #qf_contacts .qf3form form"
      );
      form.sumbit = form.root.value.replace(/[w.|-]/g, "").split(/\/+/)[1];
      QuickForm.prepareFormForSend(form);

      forms.forEach((el) => {
        if (!el.checkValidity()) {
          fl = 1;
          return el.submit();
        }
      });

      if (fl || !QFcart.checkreqfiles()) return false;

      var cart = $(".qfcartform");
      opacity(cart, 0, 900, 0);
      cart.parentNode.appendChild(QuickForm.preloader("cartpreloaderoverlay"));

      var send = function (one) {
        QuickForm.prepareFormForSend(one);
        var formData = new FormData(one);
        formData.set("task", "ajax.confirmCart");
        QuickForm.req(formData, (res) => {
          if (res == "yes") {
            n++;
            if (n < forms.length) {
              send(forms[n]);
            } else {
              form.submit();
            }
          }
        });
      };

      if (forms.length) {
        send(forms[0]);
      } else {
        form.submit();
      }
    },

    hashForm: function (form) {
      const hashCode = (s) =>
        s.split("").reduce((a, b) => ((a << 5) - a + b.charCodeAt(0)) | 0, 0);
      QuickForm.prepareFormForSend(form);
      const formData = new FormData(form);
      formData.delete("root");
      formData.delete("task");
      var els = $$('input[type="hidden"]', form);
      els.forEach((el) => {
        if (el.value == "1" && el.name.length == 32) {
          formData.delete(el.name);
        }
      });
      const queryString = new URLSearchParams(formData).toString();
      return hashCode(queryString).toString();
    },

    addFormInCart: function (form) {
      QuickForm.prepareFormForSend(form);
      if (!form.checkValidity()) {
        return form.submit();
      }
      var formData = new FormData(form);
      formData.set("formhash", this.hashForm(form));
      formData.set("task", "ajax.ajaxminicart");
      QuickForm.req(formData, (res) => {
        var over = tag('<div class="qfcartoverlay"></div>').to(document.body);
        over.style.display = "block";
        over.style.height = document.body.clientHeight + "px";
        opacity(over, 0, 600, (over) => {
          over.remove();
        });
        QFcart.animateMiniCart(res);
      });
      return false;
    },

    animateShuttle: function (res) {
      var shuttleAnimate = function (shut, time) {
        opacity(shut, 1, time, (shut) => {
          opacity(shut, 0.2, time);
          $$(".qf_h", shut).forEach((el) => {
            el.style.display = "none";
          });
        });
      };
      var shut = $("#qfshuttle");
      if (!shut) {
        shut = tag('<div id="qfshuttle">' + res + "</div>").to(document.body);
        shuttleAnimate(shut, 900);
        shut.onmouseover = () => {
          opacity(shut, 1, 600, 0);
          $$(".qf_h", shut).forEach((el) => {
            el.style.display = "";
          });
        };
        shut.onmouseleave = () => {
          opacity(shut, 0.2, 600, (shut) => {
            $$(".qf_h", shut).forEach((el) => {
              el.style.display = "none";
            });
          });
        };
        shut.onclick = () => {
          QFcart.CartBox();
        };
      } else {
        shut.innerHTML = res;
        shuttleAnimate(shut, 900);
      }
      this.animateBtns();
    },

    animateBtns: function () {
      $$(".qfaddtocart input").forEach((btn) => {
        var incart = $(".qf_cart_incart");
        var qf_cart_incart = incart?.dataset.incart || [];

        if (qf_cart_incart.includes(this.hashForm(btn.form))) {
          btn.parentNode.classList.add("incart");
          btn.value = btn.dataset.incart;
          btn.disabled = true;
        } else {
          btn.parentNode.classList.remove("incart");
          btn.value = btn.dataset.tocart;
          btn.disabled = false;
        }
      });
    },

    animateMiniCart: function (res) {
      var module = $(".qf_cart_box");
      if (!module) return this.animateShuttle(res);
      opacity(module, 0, 300, (module) => {
        module.innerHTML = res;
        opacity(module, 1, 600, 0);
        this.animateShuttle(
          res.replace(/<span class="qf_cart_incart".*<\/span>/g, "")
        );
        var mess = $$(".qfhidemescart");
        if (mess.length) {
          var box = QuickForm.startoverlay("qf3form shopmess");
          box.appendChild(mess[0].firstChild);
          QuickForm.Ycentr(box, true);
          mess.forEach((mes) => {
            mes.remove();
          });
        }
      });
    },
  });
})();
