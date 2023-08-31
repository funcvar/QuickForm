/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */
(() => {
  const $ = (s, c) => (c ?? document).querySelector(s),
    $$ = (s, c) => (c ?? document).querySelectorAll(s);

  var keepaliveflag,
    recursionflag = [],
    qfrecords = [],
    captchaflag = 0;

  document.addEventListener("DOMContentLoaded", () => {
    QuickForm.checkMessages();

    $$(".qf3form form").forEach((form) => {
      QuickForm.initiate(form);
    });

    document.addEventListener("click", function (event) {
      var modal = event.target.classList.contains("qf3modal");
      if (modal) QuickForm.startModalform(event.target);
    });

    window.addEventListener("resize", function () {
      QuickForm.compact();
    });
    QuickForm.compact();
  });

  return (QuickForm = {
    initiate: function (form) {
      form.ondoSumBox = () => this.sumForm(form);
      form.submit = (target) => {
        form.onsubmit = function () {
          return QuickForm.qfsubmit(form);
        };
        this.setinvalidclass(form);
        this.checkTabs(form);
        form.sd = JSON.parse(target?.dataset.submit || 0);
        var btn = $('button[type="submit"]', form);
        if (btn) btn.click();
        else {
          btn = document.createElement("button");
          btn.type = "submit";
          btn.style.display = "none";
          form.appendChild(btn).click();
        }
      };

      form.qfaddtocart = function () {
        if (typeof QFcart != "object") {
          console.log("cart.js not loaded");
        } else {
          return QFcart.addFormInCart(this);
        }
      };

      form.addEventListener("keydown", function (e) {
        if (e.keyCode == 13 && e.target.type != "textarea") {
          e.preventDefault();
        }
      });

      const cl = !this.is_touch_device() ? "desk" : "touch";
      form.parentNode.classList.add(cl);
      form.sumbit = form.root.value.replace(/[w.|-]/g, "").split(/\/+/)[1];
      this.activateBox(form);
      this.keepalive();
    },

    setinvalidclass: function (form) {
      $$("input, select, textarea", form).forEach((field) => {
        if (field.hasAttribute("required") && !field.checkValidity()) {
          field.classList.add("invalid");
          field.addEventListener("input", () => {
            field.checkValidity()
              ? field.classList.remove("invalid")
              : field.classList.add("invalid");
          });
        }
      });
    },

    activateBox: function (box) {
      const fields = $$("input, select", box),
        inpid = $("input[name=id]", box),
        form = box.closest("form");

      var fromrecords = inpid && qfrecords[inpid.value];
      if (!fromrecords) this.process(fields, form);

      $$(".qfcloner", box).forEach((el) => {
        this.activateCloner(el);
      });

      $$(".qftabs", box).forEach((el) => {
        this.activateTabs(el);
      });

      $$(".qfup, .qfdown", box).forEach((el) => {
        el.onclick = () => {
          this.activateQfnumber(el);
        };
      });

      $$('input[type="reset"]', box).forEach((el) => {
        el.onclick = () => {
          el.form.reset();
          this.process(fields, form);
          this.sumForm(form);
        };
      });

      $$(".qfnext", box).forEach((el) => {
        this.activateStepper(el);
      });

      $$(".qfslider", box).forEach((el) => {
        this.activateRange(el);
      });

      $$(".qfcalendar", box).forEach((el) => {
        this.activateCalendar(el);
      });

      $$(".qfaddtocart", box).forEach((el) => {
        this.activateAddtocart(el);
      });

      $$(".customfile", box).forEach((el) => {
        this.activateCustomfile(el);
      });

      $$(".qfspoiler", box).forEach((el) => {
        this.activateSpoiler(el);
      });

      $$(".boxadder", box).forEach((el) => {
        this.checkaddersblok(el);
      });

      $$('input[type="text"],input[type="number"]', box).forEach((el) => {
        this.activateMathinputs(el);
      });

      this.activateCaptcha(box, ".qfcap" + "t a");
      this.sumForm(form);
      form.dispatchEvent(new Event("qfnewbox"));

      setTimeout(() => {
        if (!$(".qf3form form") && $(".qf3form"))
          $(".qf3form").innerHTML = "QuickForm: Invalid activation key.";
      }, 1600);
    },

    activateMathinputs: function (inp) {
      const d = JSON.parse(inp.dataset.settings || 0);
      if (d && d.math) {
        if (inp.value == "") inp.value = 0;
        if (inp.type == "text")
          inp.addEventListener("input", () => {
            let v = inp.value.replace(/[^0-9.,-]/g, "");
            if (v == "") v = 0;
            var end = v.toString().slice(-1);
            if ([".", ",", "0"].indexOf(end) == -1)
              v = Number(v.replace(/,/, "."));
            inp.value = v != v ? 0 : v;
            this.sumForm(inp.form);
          });
        else if (inp.type == "number")
          inp.addEventListener("input", () => {
            this.sumForm(inp.form);
          });
      }
    },

    activateCustomfile: function (box) {
      const inp = $('input[type="file"]', box),
        extfortmb = [
          "image/png",
          "image/gif",
          "image/jpeg",
          "image/pjpeg",
          "image/svg+xml",
        ],
        updateFileList = function (fileField, i) {
          let fileBuffer = Array.from(fileField.files);
          fileBuffer.splice(i, 1);
          const dT = new ClipboardEvent("").clipboardData || new DataTransfer();
          for (let file of fileBuffer) {
            dT.items.add(file);
          }
          fileField.files = dT.files;
          tmbs();
        },
        tmbs = () => {
          const cmb = $(".customfilebox", box);
          cmb.innerHTML = "";
          if (inp.files && inp.files[0]) {
            for (var i = 0; i < inp.files.length; i++) {
              if (
                window.FileReader &&
                extfortmb.indexOf(inp.files[i].type) != -1
              ) {
                var reader = new FileReader();
                reader.onload = (function (i, file) {
                  return function (e) {
                    let dbox = cmb.appendChild(document.createElement("div"));
                    let img = dbox.appendChild(document.createElement("img"));
                    img.src = e.target.result;
                    let a = dbox.appendChild(document.createElement("a"));
                    a.href = "#";
                    a.innerHTML = "✗";
                    a.onclick = () => {
                      updateFileList(inp, i);
                      return false;
                    };
                  };
                })(i, inp.files[i]);
                reader.readAsDataURL(inp.files[i]);
              } else {
                let dbox = cmb.appendChild(document.createElement("div"));
                let div = dbox.appendChild(document.createElement("div"));
                div.innerHTML =
                  '<div title="' +
                  inp.files[i].name +
                  '">' +
                  inp.files[i].name.split(".").pop() +
                  "</div>";

                (function (i) {
                  let a = dbox.appendChild(document.createElement("a"));
                  a.href = "#";
                  a.innerHTML = "✗";
                  a.onclick = () => {
                    updateFileList(inp, i);
                    return false;
                  };
                })(i);
              }
            }
          }
        };
      $(".customfilebtn", box).onclick = () => inp.click();
      inp.onchange = () => tmbs();
    },

    activateSpoiler: function (box) {
      var inner = $(".spoilerinner", box),
        label = $(".qf3label", box),
        cl,
        toglbtn = (v) => {
          if (v) {
            cl = ["opened", "closed"];
            this.opacity(inner, 0, 600, () => {
              inner.style.display = "none";
            });
          } else {
            inner.style.display = "";
            cl = ["closed", "opened"];
            this.opacity(inner, 1, 600, 0);
          }
          label.classList.remove(cl[0]);
          label.classList.add(cl[1]);
        };

      if (!inner.classList.contains("vis")) {
        inner.style.display = "none";
        inner.style.opacity = 0;
      }

      if (inner.classList.contains("hid") || inner.classList.contains("vis")) {
        label.onclick = () => {
          inner.style.display == "none" ? toglbtn(0) : toglbtn(1);
        };
      } else {
        label.onclick = () => {
          if (inner.style.display != "none") return;
          toglbtn(0);
          const bodystyle = document.body.style.cssText;
          this.Ycentr(inner, true, "fixed");
          $(".qfclose", box).onclick = () => {
            toglbtn(1);
            const scroll = document.body.style.top;
            document.body.style.cssText = bodystyle;
            window.scrollTo(0, parseInt(scroll || "0") * -1);
          };
        };
      }
    },

    activateStepper: function (nextbtn) {
      const box = nextbtn.closest(".qfstepperinner"),
        nextid = parseInt(nextbtn.dataset.next),
        prev = $(".qfprev", box),
        prevnode = box.previousElementSibling;

      if (!prevnode) prev.style.display = "none";
      else {
        prev.onclick = () => {
          prevnode.style.opacity = "0";
          this.opacity(prevnode, 1, 400, 0);
          prevnode.style.display = "";
          $('input[name="qfstepper[]"]', prevnode).value = 0;
          box.remove();
        };
      }

      if (!nextid) nextbtn.style.display = "none";

      let dis;
      nextbtn.onclick = () => {
        let fl;
        $$("input,select,textarea", box).forEach((field) => {
          if (!field.checkValidity()) {
            fl = 1;
            return field.reportValidity();
          }
        });
        if (fl || dis) return;
        dis = 1;

        let prel = box.appendChild(this.preloader());
        this.req("option=com_qf3&task=ajax.related&id=" + nextid, (res) => {
          prel.remove();
          box.insertAdjacentHTML(
            "afterend",
            '<div class="qfstepperinner" style="position:relative">'
          );
          this.opacity(box, 0, 300, () => (box.style.display = "none"));
          $("input", nextbtn).value = 1;
          this.animatefields(box.nextSibling, res);
          dis = 0;
        });
      };
    },

    activateCalendar: function (box) {
      if (typeof qfDatePicker == "function") {
        qfDatePicker(box);
      } else {
        const css = document.createElement("link");
        css.rel = "stylesheet";
        css.type = "text/css";
        css.href = this.getAssetsURL() + "/datepicker/css/datepicker.css";
        document.head.appendChild(css);
        const scr = document.createElement("script");
        scr.src = this.getAssetsURL() + "/datepicker/js/datepicker.js";
        document.head.appendChild(scr);
        scr.onload = () => qfDatePicker(box);
      }
    },

    getAssetsURL: function () {
      var script = document.currentScript || $('script[src*="qf3.js"]');
      return script.src.split("/js/qf3")[0];
    },

    activateAddtocart: function (box) {
      if (typeof QFcart != "object") {
        const css = document.createElement("link");
        css.rel = "stylesheet";
        css.type = "text/css";
        css.href = this.getAssetsURL() + "/shopcart/cart_default.css";
        document.head.appendChild(css);
        const scr = document.createElement("script");
        scr.src = this.getAssetsURL() + "/shopcart/cart.js";
        document.head.appendChild(scr);
        scr.onload = () => QFcart.updateMiniCart();
      }

      var form = box.closest("form");
      if (!form.animbtns) {
        ["change", "qfnewbox"].forEach((ev) => {
          form.addEventListener(ev, function () {
            if (typeof QFcart == "object") QFcart.animateBtns();
            form.animbtns = true;
          });
        });
      }
    },

    activateQfnumber: function (btn) {
      const dir = btn.className == "qfup" ? 1 : -1,
        inp = $("input", btn.closest(".qf_number")),
        step = inp.getAttribute("step") || "1",
        min = inp.getAttribute("min") ?? "",
        max = inp.getAttribute("max") ?? "",
        decim = (step.split(".")[1] || []).length;
      let v = this.round(Number(inp.value) + dir * step, decim);
      if (min !== "" && v < min) v = min;
      else if (max !== "" && v > max) v = max;
      inp.value = v;
      this.sumForm(inp.form);
      inp.form.dispatchEvent(new Event("change"));
    },

    activateRange: function (box) {
      const inp = $('input[type="range"]', box),
        chosen = $(".slider_chosen", box),
        min = inp.getAttribute("min") || 0,
        max = inp.getAttribute("max") || 100,
        upd = function () {
          var boxw = (inp.offsetWidth - chosen.offsetWidth) / (max - min);
          chosen.innerHTML = inp.value;
          chosen.style.left = (inp.value - min) * boxw + "px";
        };
      inp.addEventListener("input", () => upd());
      upd();
    },

    activateTabs: function (box) {
      const tabs = this.children("qftabsitem", box),
        own = window.getComputedStyle(tabs[0]).display || "flex",
        labelsbox = $(".qftabslabelsbox", box);
      $$(".qftabsitemlabel", labelsbox).forEach((label, i) => {
        label.onclick = () => {
          $(".qftabactiv", labelsbox).classList.remove("qftabactiv");
          label.classList.add("qftabactiv");
          tabs.forEach((tab) => {
            tab.style.display = "none";
          });
          tabs[i].style.display = own;
        };
      });
    },

    activateCaptcha: function (box, fr) {
      const captdiv = $(".qf_recaptcha", box),
        form = box.closest("form"),
        captch = $(fr, form),
        nid = (captch) => {
          captch.rel ? this.animatefields(form, "", 1500) : "";
        },
        captcha = () => {
          if (captchaflag < 300) {
            if (
              typeof grecaptcha != "object" ||
              typeof grecaptcha.render != "function"
            ) {
              setTimeout(captcha, 600);
              captchaflag++;
            } else {
              if (!captdiv.innerHTML) {
                grecaptcha.render(captdiv, {
                  sitekey: captdiv.dataset.sitekey,
                  theme: captdiv.dataset.theme,
                  hl: captdiv.dataset.hl,
                });
              }
            }
          }
        };
      if (captch)
        captch.href.charAt(13) != "-"
          ? this.animatefields(form, "", 1500)
          : nid(captch);
      else {
        var ue = function (inArr) {
          var uniHash = {},
            outArr = [],
            i = inArr.length;
          while (i--) uniHash[inArr[i] + "??"] = i;
          for (i in uniHash) outArr.push(i.replace("??", ""));
          return outArr;
        };
        var a = ue(form.sumbit.split("")),
          c = [],
          i = a.length;
        while (i--) c[i] = a[i] + a[a.length - i - 1];
        if (form.root.value.indexOf(window.location.host) + 1) {
          form.suml != c.join("").slice(a.length)
            ? this.animatefields(form, "", 1500)
            : "";
        }
      }
      if (captdiv && typeof grecaptcha != "object") {
        if (!$('head > script[src*="recaptcha/api.js"]')) {
          const scr = document.createElement("script");
          scr.src = "https://www.google.com/recaptcha/api.js";
          document.head.appendChild(scr);
        }
        captcha();
      } else if (captdiv) captcha();
    },

    activateCloner: function (cloner) {
      const d = JSON.parse(cloner.dataset.settings || 0);
      const activateRows = () => {
        const row = $(".qfclonerrow", cloner);
        const rows = this.children("qfclonerrow", row.parentNode),
          len = rows.length;

        rows.forEach((one) => {
          const rem = $(".qfrem a", one),
            add = $(".qfadd a", one);
          rem.style.opacity = len > 1 ? "1" : "0.2";
          add.style.opacity = d.max && d.max == len ? "0.2" : "1";
          add.onclick = () => {
            if (!d.max || len < d.max) {
              this.req(
                "option=com_qf3&task=ajax.ajaxCloner&id=" +
                  d.related +
                  "&orient=" +
                  d.orient +
                  "&sum=" +
                  d.sum,
                (res) => {
                  one.insertAdjacentHTML("afterend", res);
                  const newrow = one.nextSibling;
                  newrow.style.opacity = "0";
                  this.opacity(newrow, 1, 600, 0);
                  activateRows();
                  const fields = $$("input, select", newrow),
                    form = cloner.closest("form");
                  this.process(fields, form);
                }
              );
            }
          };
          rem.onclick = () => {
            if (len > 1) {
              this.opacity(one, 0, 200, () => {
                one.remove();
                activateRows();
                this.sumForm(cloner.closest("form"));
              });
            }
          };
        });
      };
      activateRows();
    },

    checkaddersblok: function (el) {
      var arr = [];
      $$(".addercartblok > div").forEach((row) => {
        arr += row.dataset.id;
      });
      const rows = $$(".adderblok > div", el);
      rows.forEach((row) => {
        if (arr.includes(row.dataset.id)) {
          $(".add", row).style.display = "none";
          $(".added", row).style.display = "";
        } else {
          $(".add", row).style.display = "";
          $(".added", row).style.display = "none";
        }
      });
    },

    adderadd: function (el, i) {
      this.req(
        "option=com_qf3&task=ajax.adderadd&opt=" + el.closest("div").dataset.id,
        (res) => {
          $(".addercartinner").innerHTML = res;
          this.checkaddersblok(el.closest(".boxadder"));
        }
      );
    },

    adderdel: function (el) {
      this.req(
        "option=com_qf3&task=ajax.adderdel&opt=" + el.closest("div").dataset.id,
        (res) => {
          $(".addercartinner").innerHTML = res;
          $$(".boxadder").forEach((one) => {
            this.checkaddersblok(one);
          });
        }
      );
    },

    adderplus: function (el, act) {
      if (act == "change") act = "change&v=" + el.value;
      this.req(
        "option=com_qf3&task=ajax.adderplus&act=" +
          act +
          "&opt=" +
          el.closest("div").dataset.id,
        (res) => {
          $(".addercartinner").innerHTML = res;
        }
      );
    },

    sumForm: function (form) {
      let sum = 0,
        priceBoxs = $$(".qfpriceinner", form),
        fieldsPatArr = [],
        formuls = [];

      if (!priceBoxs.length || !form.calculatortype) {
        form.dispatchEvent(new Event("qfsetprice"));
        return;
      }

      if (form.calculatortype.value == "default") {
        if ($(".qfclonesum span", form)) {
          $$(".qfclonerrow", form).forEach((row) => {
            var cprice = this.calculatorDefault($$("input, select", row));
            if (cprice == "error") return;
            $(".qfclonesum span", row).innerHTML = this.strPrice(cprice);
          });
        }
        sum = this.calculatorDefault(form.elements);
        if (sum == "error") return;
        priceBoxs.forEach((priceBox) => {
          var d = JSON.parse(priceBox.dataset.settings || 0);
          sum = this.round(sum, d.fixed);
          priceBox.innerHTML = this.strPrice(sum.toFixed(d.fixed), d.format);
          $('input[name="qfprice[]"]', priceBox.parentNode).value = sum;
        });
        form.dispatchEvent(new Event("qfsetprice"));
        return;
      } else if (form.calculatortype.value == "custom") {
        this.calculatorCustom(form);
      } else {
        var pats = form.calcformula.dataset.formula.split(";");

        for (var i = 0; i < pats.length; i++) {
          var pat = pats[i].split(/^([^=]+)=/).slice(1);
          formuls[pat[0]] = pat[1];
        }
        if (form.calculatortype.value == "multipl") {
          fieldsPatArr = this.calculatorMultipl(form.elements);
        } else if (form.calculatortype.value == "simple") {
          fieldsPatArr = this.calculatorSimple(form.elements);
        }

        priceBoxs.forEach((priceBox) => {
          var d = JSON.parse(priceBox.dataset.settings || 0);
          var converter = function (fieldid) {
            let str = "";
            if (formuls[fieldid]) {
              str = formuls[fieldid].replace(/{(.*?)}/g, function (x) {
                var rep = x.replace(/}|{/g, "");
                return fieldsPatArr[rep]
                  ? fieldsPatArr[rep]
                  : "(" + converter(rep) + ")";
              });
            }
            return str.replace(/\(\)/g, "");
          };

          if (formuls[d.fieldid]) {
            var str = converter(d.fieldid);

            try {
              sum = eval(this.chekStr(str ? str : "0"));
              if (isNaN(sum)) {
                throw new Error();
              }
            } catch (err) {
              return (priceBox.innerHTML = "ERROR: " + str);
            }

            sum = this.round(sum, d.fixed);
            priceBox.innerHTML = this.strPrice(sum.toFixed(d.fixed), d.format);
            $('input[name="qfprice[]"]', priceBox.parentNode).value = sum;
          }
        });
        form.dispatchEvent(new Event("qfsetprice"));
      }
    },

    calculatorDefault: function (fields) {
      var str = "",
        sum,
        add;
      for (var n = 0; n < fields.length; n++) {
        if ((add = this.addmath(fields[n]))) str += add;
      }
      try {
        sum = eval(this.chekStr(str ? str : "0"));
        if (isNaN(sum)) throw new Error();
      } catch (err) {
        $$(".qfpriceinner", fields[0].form).innerHTML = "ERROR: " + str;
        return "error";
      }
      return sum;
    },

    calculatorMultipl: function (fields) {
      var arr = [];
      for (var i = 0; i < fields.length; i++) {
        var d = JSON.parse(fields[i].dataset.settings || 0);
        if (d && d.fieldid) {
          var add = this.addmath(fields[i]);
          if (add !== "") arr[d.fieldid] = add;
        }
      }
      return arr;
    },

    calculatorSimple: function (fields) {
      var arr = [];
      for (var i = 0; i < fields.length; i++) {
        if (
          ["text", "number", "range", "hidden"].indexOf(fields[i].type) != -1
        ) {
          var d = JSON.parse(fields[i].dataset.settings || 0);
          if (d && d.math) {
            arr[d.math] = fields[i].value;
          }
        }
      }
      return arr;
    },

    chekStr: function (str) {
      return str.replace(/,/g, ".").replace(/[^0-9()-.+<>!=:\?\*\/|%&]/g, "");
    },

    round: function (value, decimals) {
      return Number(Math.round(value + "e" + decimals) + "e-" + decimals);
    },

    calculatorCustom: function (form) {
      const priceBoxs = $$(".qfpriceinner", form);
      this.prepareFormForSend(form);
      priceBoxs.forEach((el) => (el.innerHTML = this.prelsvg()));
      var formData = new FormData(form);
      formData.set("task", "ajax.sumCustom");
      $$('input[type="file"]', form).forEach((el) => formData.delete(el.name));
      this.req(formData, (res) => {
        const sums = res.split(";");
        priceBoxs.forEach((priceBox) => {
          let mesbox = $(".qfpricemes", priceBox.parentNode);
          if (!mesbox) {
            priceBox.parentNode.insertAdjacentHTML(
              "afterbegin",
              '<div class="qfpricemes"></div>'
            );
            mesbox = $(".qfpricemes", priceBox.parentNode);
          }
          mesbox.innerHTML = "";
          var d = JSON.parse(priceBox.dataset.settings);
          sums.forEach((ssum) => {
            const pats = ssum.split(":");
            if (pats.length != 2) return;
            if (pats[0] == d.fieldid) {
              let sum;
              if (Number(pats[1]) == 1 * pats[1]) {
                sum = this.round(pats[1], d.fixed);
              } else {
                mesbox.innerHTML = pats[1];
                sum = 0;
              }
              priceBox.innerHTML = this.strPrice(
                sum.toFixed(d.fixed),
                d.format
              );
              $('input[name="qfprice[]"]', priceBox.parentNode).value = sum;
            }
          });
        });
        form.dispatchEvent(new Event("qfsetprice"));
      });
    },

    addmath: function (field) {
      let d,
        add = "";
      if (field.type === "select-one") {
        d = field.options[field.selectedIndex].dataset.settings;
      } else if (field.type === "radio" || field.type === "checkbox") {
        if (field.checked) d = field.dataset.settings;
      } else {
        d = field.dataset.settings;
      }
      if (d) {
        d = JSON.parse(d);
        if (d.math) add = d.math.replace(/v/g, field.value ? field.value : "");
      }
      return add;
    },

    animatefields: function (node, html, time = 600) {
      if (html) {
        this.opacity(node, 0, time / 2, () => {
          if (html.indexOf("<script") != -1) {
            node.innerHTML = "";
            node.append(document.createRange().createContextualFragment(html));
          } else {
            node.innerHTML = html;
          }
          this.activateBox(node);
          this.opacity(node, 1, time / 2, 0);
        });
      } else {
        this.opacity(node, 0, time, (node) => {
          const form = node.closest("form");
          node.remove();
          if (form) this.sumForm(form);
        });
      }
    },

    process: function (fields, form) {
      var fl;
      fields.forEach((field) => {
        fl = this.insertfields(field) || fl;
        field.onchange = () => {
          if (this.insertfields(field)) this.sumForm(form);
        };
      });
      form.suml = form.qfcod
        ? form.qfcod.value
        : this.animatefields(form, "", 3600);
      if (fl) this.sumForm(form);
    },

    insertfields: function (target) {
      const box = target.closest(".qf3");
      let d,
        nextblock = box?.nextSibling?.classList?.contains("relatedblock");

      if (target.type === "select-one") {
        d = target.options[target.selectedIndex].dataset.settings;
      } else if (target.type === "radio" || target.type === "checkbox") {
        if (target.checked) d = target.dataset.settings;
      }
      if (d) d = JSON.parse(d);
      if (d && d.related) {
        if (!recursionflag[d.related]) recursionflag[d.related] = 0;
        else if (recursionflag[d.related] > 150) return;
        recursionflag[d.related]++;

        let prel = box.appendChild(this.preloader());
        prel.style.position = "relative";

        this.req("option=com_qf3&task=ajax.related&id=" + d.related, (res) => {
          prel.remove();
          if (!nextblock)
            box.insertAdjacentHTML(
              "afterend",
              '<div class="relatedblock"></div>'
            );
          this.animatefields(box.nextSibling, res, 600);
        });
      } else {
        if (nextblock) this.animatefields(box.nextSibling, "", 300);
        else return true;
      }
    },

    children: function (clas, node) {
      let res = [];
      Array.from(node.children).forEach((child) => {
        if (child.classList.contains(clas)) res.push(child);
      });
      return res;
    },

    before_submit: function (form) {
      // Yandex Metrika & (Google Analytics not completed yet) support
      if (form.sd && form.sd.ycounter && typeof window.Ya !== "undefined") {
        var metr = window.Ya.Metrika || window.Ya.Metrika2;
        var cs = metr.counters();
        var yid = (cs && cs[0] && cs[0].id) || null;
        if (yid) {
          if (typeof window["yaCounter" + yid] !== "undefined")
            window["yaCounter" + yid].reachGoal(form.sd.ycounter);
          else if (typeof ym !== "undefined")
            ym(yid, "reachGoal", form.sd.ycounter);
        }
      }
    },

    checkTabs: function (form) {
      const tabs = $$(".qftabs", form);
      tabs.forEach((el) => {
        let fl,
          itabs = $$(".qftabsitem", el),
          labs = $$(".qftabsitemlabel", el);
        itabs.forEach((one, i) => {
          if (fl) return;
          let fields = $$("input,select,textarea", one);
          fields.forEach((field) => {
            if (!field.checkValidity()) {
              fl = true;
              return labs[i].click();
            }
          });
        });
      });
    },

    qfsubmit: function (form) {
      if (!$(".qfsubmit", form)) return false;
      form.style.transition = "opacity 0.6s";
      form.style.opacity = "0.1";
      this.prepareFormForSend(form);
      this.before_submit(form);
      if (form.task.value != "ajax.qfajax") return true;
      this.req(new FormData(form), (res) => {
        this.AjaxForm(form, res);
      });
      return false;
    },

    AjaxForm: function (form, html) {
      const id = form.id.value,
        box = form.parentNode,
        close = $(".qfclose", box),
        newform = () => {
          this.req("option=com_qf3&task=ajax.qfmodal&id=" + id, (res) => {
            const doc = new DOMParser().parseFromString(res, "text/html");
            if (close) box.appendChild(close);
            const newf = box.appendChild($("form", doc));
            newf.style.opacity = "0";
            newf.style.transition = "opacity 0.6s";
            setTimeout(() => {
              newf.style.opacity = "1";
            }, 100);
            this.initiate(newf);
          });
        };
      box.innerHTML = html;
      const mes = $("div", box);
      mes.style.opacity = "0";
      mes.style.transition = "opacity 0.6s";
      setTimeout(() => {
        mes.style.opacity = "1";
      }, 100);
      $(".qfclose", box).onclick = () => {
        mes.style.opacity = "0";
        setTimeout(() => {
          box.innerHTML = "";
          newform();
        }, 600);
      };
    },

    compact: function () {
      $$(".qf3form").forEach((el) => {
        el.classList.toggle("compact", el.parentNode.offsetWidth < 500);
      });
    },

    is_touch_device: function () {
      try {
        document.createEvent("TouchEvent");
        return true;
      } catch (e) {
        return false;
      }
    },

    checkMessages: function () {
      const mes = $(".qfhidemes");
      if (mes && mes.innerHTML) {
        const cl = mes.className.replace("qfhidemes", "qf3form qfmess");
        const box = this.startoverlay(cl);
        box.insertAdjacentHTML("beforeend", mes.innerHTML);
        this.Ycentr(box, true);
      }
    },

    strPrice: function (x, format) {
      let pd = ["", "."];
      if (!format) pd = [" ", ","];
      else if (format == 1) pd = [",", "."];
      let xs = ("" + x).split(".");
      let r = xs[0].charAt(0);
      for (let n = 1; n < xs[0].length; n++) {
        if (Math.ceil((xs[0].length - n) / 3) == (xs[0].length - n) / 3)
          r = r + pd[0];
        r = r + xs[0].charAt(n);
      }
      return xs[1] ? r + pd[1] + xs[1] : r;
    },

    prepareFormForSend: function (form) {
      $$('input[name="qfradio[]"]', form).forEach((el, k) => {
        $$('input[type="radio"]', el.parentNode).forEach((one, i) => {
          one.checked ? (el.value = i) : 0;
          one.name = "r" + k; // cart
        });
      });

      $$('input[name="qfcheckbox[]"]', form).forEach((el) => {
        el.value = $('input[type="checkbox"]', el.parentNode).checked ? 1 : 0;
      });

      $$('input[name="inpfile[][]"]', form).forEach((el, i) => {
        el.name = "inpfile[" + i + "][]";
      });

      $$('input[name="qfcloner[]"]', form).forEach((el) => {
        if (JSON.parse(el.parentNode.dataset.settings).orient) {
          el.value = $("tr", el.parentNode).parentNode.children.length - 1;
        } else {
          el.value = this.children("qfclonerrow", el.parentNode).length;
        }
      });

      if (!form.qftoken && form.sumbit) {
        form.insertAdjacentHTML(
          "beforeend",
          '<input type="hidden" name="qftoken" value="' + form.sumbit + '">'
        );
      }
    },

    Ycentr: function (box, flag, pos = "absolute") {
      if (!box) return;
      this.compact();

      let winY =
          window.scrollY || Math.abs(parseInt(document.body.style.top || "0")),
        boxH = box.offsetHeight,
        winH = window.innerHeight,
        ofy = document.body.offsetHeight > winH ? "scroll" : "";

      if (flag) {
        document.body.style.overflowY = ofy;
        document.body.style.position = "fixed";
        document.body.style.width = "100%";
        document.body.style.top = `-${winY}px`;
        box.style.position = pos;
        box.style.zIndex = "99999";
        box.style.overflow = "auto";
        box.style.maxHeight = "100vh";
        this.opacity(box, 1, 600, 0);
        new ResizeObserver(() => this.Ycentr(box, false, pos)).observe(box);
      }
      if (pos == "fixed") winY = 0;
      box.style.top = (winH - boxH) / 2 + winY + "px";
    },

    keepalive: function () {
      if ($(".qfkeepalive") && !keepaliveflag) {
        window.setInterval(() => {
          this.req("option=com_qf3&task=ajax.related&id=0", (res) => {});
        }, 600000);
        keepaliveflag = 1;
      }
    },

    opacity: function (el, val, time, func) {
      el.style.transition = "all " + time / 1000 + "s";
      setTimeout(() => {
        el.style.opacity = val;
      }, 10);
      if (func)
        setTimeout(() => {
          func(el);
        }, time);
    },

    startoverlay: function (cl) {
      $(".qfoverlay")?.remove();
      $(".qfmodalform")?.remove();
      const bodystyle = document.body.style.cssText;
      const ovr = document.createElement("div");
      ovr.className = cl + " qfoverlay";
      document.body.appendChild(ovr);

      const box = document.createElement("div");
      box.className = cl + " qfmodalform";
      box.innerHTML = '<div class="qfclose">×</div>';
      document.body.appendChild(box);

      const boxclose = () => {
        const form = $("form", box);
        if (form) qfrecords[form.id.value] = form;

        [box, ovr].forEach((el) => {
          this.opacity(el, 0, 600, () => {
            el.remove();
          });
        });

        const scroll = document.body.style.top;
        document.body.style.cssText = bodystyle;
        window.scrollTo(0, parseInt(scroll || "0") * -1);
      };

      const opc = window.getComputedStyle(ovr).opacity || "0";
      ovr.style.backgroundColor =
        window.getComputedStyle(ovr).backgroundColor || "#000";
      ovr.style.position = "absolute";
      ovr.style.zIndex = "99998";
      ovr.style.top = "0";
      ovr.style.left = "0";
      ovr.style.margin = "0";
      ovr.style.width = "100%";
      ovr.style.opacity = "0";
      ovr.style.height = document.body.clientHeight + "px";
      this.opacity(ovr, opc, 1000, 0);

      ovr.onclick = () => boxclose();
      $(".qfclose", box).onclick = () => boxclose();
      box.style.opacity = "0";
      return box;
    },

    startModalform: function (el) {
      const id = el.dataset.project,
        cl = el.className.replace("qf3modal", "qf3form"),
        box = this.startoverlay(cl);

      if (qfrecords[id]) {
        box.appendChild(qfrecords[id]);
        let cl2 = !this.is_touch_device() ? "desk" : "touch";
        qfrecords[id].parentNode.classList.add(cl2);
        this.Ycentr(box, true);
        return;
      }

      this.req("option=com_qf3&task=ajax.qfmodal&id=" + id, (res) => {
        const html = document.createRange().createContextualFragment(res);
        box.append($("form", html));
        this.Ycentr(box, true);
        this.initiate($("form", box));
      });
    },

    req: function (data, func) {
      var request = new XMLHttpRequest();
      request.onreadystatechange = function () {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          return func(this.responseText);
        }
      };
      request.open("POST", window.location.href, true);
      if (typeof data === "string")
        request.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded"
        );
      request.send(data);
    },

    prelsvg: function () {
      const a = ",4.8Zm12.8,0A3.2,3.2,0,1,1,",
        b = ",8,3.2,3.2,0,0,1,",
        c = ",4.8A3.2,3.2,0,1,1,",
        d = "4.8,4.8,0,0,1-",
        e = "5.44,5.44,0,0,1-",
        s = "4.16,4.16,0,0,1-";
      return (
        '<svg width="80px" height="10px" viewBox="0 0 128 16" xml:space="preserve" style=""><path fill="#949494" d="M6.4' +
        c +
        "3.2" +
        b +
        "6.4" +
        a +
        "16" +
        b +
        "19.2,4.8ZM32" +
        c +
        "28.8" +
        b +
        "32" +
        a +
        "41.6" +
        b +
        "44.8" +
        a +
        "54.4" +
        b +
        "57.6" +
        a +
        "67.2" +
        b +
        "70.4" +
        a +
        "80" +
        b +
        "83.2,4.8ZM96" +
        c +
        "92.8" +
        b +
        "96" +
        a +
        "105.6" +
        b +
        "108.8" +
        a +
        "118.4" +
        b +
        '121.6,4.8Z"/><g><path fill="#000000" d="M-42.7,3.84A' +
        s +
        "38.54,8a" +
        s +
        "4.16,4.16A" +
        s +
        "46.86,8," +
        s +
        "42.7,3.84Zm12.8-.64A" +
        d +
        "25.1,8a" +
        d +
        "4.8,4.8A" +
        d +
        "34.7,8," +
        d +
        "29.9,3.2Zm12.8-.64A" +
        e +
        "11.66,8a" +
        e +
        "5.44,5.44A" +
        e +
        "22.54,8," +
        e +
        '17.1,2.56Z"/><animateTransform attributeName="transform" type="translate" values="23 0;36 0;49 0;62 0;74.5 0;87.5 0;100 0;113 0;125.5 0;138.5 0;151.5 0;164.5 0;178 0" calcMode="discrete" dur="1170ms" repeatCount="indefinite"/></g></svg>'
      );
    },

    preloader: function (cl = "preloaderoverlay") {
      const el = document.createElement("div");
      el.className = cl;
      el.style.display = "none";
      el.innerHTML = this.prelsvg().replace(
        'style=""',
        'style="position:absolute;top:50%;left:50%;margin-left:-40px;margin-top:-5px"'
      );
      setTimeout(() => {
        el.style.display = "";
      }, 1000);
      return el;
    },
  });
})();
