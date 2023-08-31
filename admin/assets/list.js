/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */

(() => {
  const $ = (s, c) => (c ?? document).querySelector(s),
    $$ = (s, c) => (c ?? document).querySelectorAll(s);

  document.addEventListener("DOMContentLoaded", () => {
    $$('.qftabs').forEach((tab) => {
      QFlist.activateTabs(tab);
    });

    if ($('input[name="qffield[p][modal]"]')) {
      QFlist.activateProject();
    }

    $$('input[name="cid[]"]').forEach((el) => {
      el.addEventListener('change', () => {
        QFlist.toolbartoggle();
      });
    });
    QFlist.toolbartoggle();

    $$('a.js_sort_dir').forEach((el) => {
      el.addEventListener('click', () => {
        QFlist.filterdir(el);
      });
    });

    $$('.js_sort_list').forEach((el) => {
      el.addEventListener('change', () => {
        QFlist.filterlist(el);
      });
    });

    if ($('select[name="qffield[addfiles]"')) {
      QFlist.disfiles();
    }

    QFlist.filled();

    // joomla 4 menu
    $$('#sidebarmenu .item-level-3 a').forEach((el) => {
      if (el.href.indexOf('com_qf3') + 1) {
        el.classList.add('qfmenuitem');

        if (el.href.indexOf('projects') + 1) {
          el.classList.add('qfprojects');
        } else if (el.href.indexOf('historys') + 1) {
          el.classList.add('qfhistorys');
        } else if (el.href.indexOf('settings') + 1) {
          el.classList.add('qfsettings');
        } else if (el.href.indexOf('shop') + 1) {
          el.classList.add('qfshop');
        } else if (el.href.indexOf('attachment') + 1) {
          el.classList.add('qfattachment');
        }
      }
    });

  });

  return QFlist = {
    filled: function() {
      let els = $$('.qf3_filter input, .qf3_filter select');
      let fill = function() {
        els.forEach((el) => {
          if (el.value) el.classList.add('filled');
          else el.classList.remove('filled');
        });
      }
      els.forEach((el) => {
        el.addEventListener('change', () => {
          fill();
        });
      });
      fill();
    },

    toolbartoggle: function() {
      var btns = $$('.activate, .deactivate, .delete, .export, .csv', $('.qf3_toolbar'));
      var checked = $('input[name="cid[]"]:checked');
      btns.forEach((btn) => {
        checked ? btn.classList.remove('qfdisabled') : btn.classList.add('qfdisabled');
      })
    },

    filterdir: function(el) {
      var col = el.dataset.order.split('.');
      var form = document.forms.qfadminform;
      this.tag('<input type="hidden" name="task" value="' + col[0] + '.filterdir">').to(form);
      this.tag('<input type="hidden" name="col" value="' + col[1] + '">').to(form);
      form.submit();
    },

    filtersearch: function() {
      var col = $('input.search').dataset.order.split('.');
      var form = document.forms.qfadminform;
      this.tag('<input type="hidden" name="task" value="' + col[0] + '.filterlist">').to(form);
      this.tag('<input type="hidden" name="col" value="' + col[1] + '">').to(form);
      this.tag('<input type="hidden" name="v" value="">').to(form).value = $('input.search').value;
      form.submit();
    },

    filterlist: function(el) {
      var col = el.dataset.order.split('.');
      var val = el.value;
      var form = document.forms.qfadminform;
      this.tag('<input type="hidden" name="task" value="' + col[0] + '.filterlist">').to(form);
      this.tag('<input type="hidden" name="col" value="' + col[1] + '">').to(form);
      this.tag('<input type="hidden" name="v" value="' + val + '">').to(form);
      form.submit();
    },

    qflistedit: function(task) {
      var form = document.forms.qfadminform;
      this.tag('<input type="hidden" name="task" value="' + task + '">').to(form);
      if (task.split('.')[1] == 'close') form.submit();
      else this.tag('<input type="submit">').to(form).click();
    },

    tag: function(str) {
      var wrapper = document.createElement('div');
      wrapper.innerHTML = str;
      return {
        to: function(parent) {
          return parent.appendChild(wrapper.firstChild)
        }
      }
    },

    req: function(data, func) {
      var request = new XMLHttpRequest();
      request.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          return func(this.responseText);
        }
      }
      request.open('POST', window.location.href, true);
      if (typeof(data) === 'string') request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      request.send(data);
    },

    help: function(task) {
      this.req("option=com_qf3&task=" + task, (res) => {
        $('.helpbox')?.remove();
        var div = document.createElement('div');
        div.className = 'helpbox';
        div.innerHTML = '<div class="qfmessboxclose" onclick="this.parentNode.parentNode.removeChild(this.parentNode)">✕</div>' + res;
        document.forms.qfadminform.parentNode.insertBefore(div, document.forms.qfadminform);
      });
    },

    activateTabs: function(tab) {
      var items = [];
      for (const child of tab.children) {
        if (child.classList.contains('qftabsitem')) {
          child.style.display = 'none';
          items.push(child);
        } else if (child.classList.contains('qftabslabelsbox')) labelsbox = child;
      }
      items[0].style.display = 'block';

      var labels = $$('.qftabsitemlabel', labelsbox);
      labels.forEach((label) => {
        label.onclick = () => {
          labels.forEach((one) => {
            one.classList.remove('qftabactiv');
          });
          label.classList.add('qftabactiv');
          items.forEach((one) => {
            one.style.display = 'none';
          });
          for (var i = 0; i < labels.length; i++) {
            if (labels[i] == label) items[i].style.display = 'block';
          }
        }
      });

      var radioyesno = function() {
        $$('.radioyesno').forEach((el) => {
          if (el.checked) el.parentNode.classList.add('checked');
          else el.parentNode.classList.remove('checked');
        });
      }
      radioyesno();
      $$('.radioyesno').forEach((el) => {
        el.addEventListener('change', () => {
          radioyesno();
        });
      });
    },

    opacity: function(el, val, time, func) {
      el.style.transition = 'opacity ' + (time / 1000) + 's';
      setTimeout(() => {
        el.style.opacity = val
      }, 10);
      if (func) setTimeout(() => {
        func(el)
      }, time);
    },

    activateProject: function() {
      var modallink = () => {
        var box = $('input[name="qffield[p][modallink]"]').closest('.qffield');
        if ($('input[name="qffield[p][modal]"]:checked').value == 1) {
          this.opacity(box, 1, 200, () => {
            box.style.display = '';
          });
        } else {
          this.opacity(box, 0, 200, () => {
            box.style.display = 'none';
          });
        }
      }
      modallink();
      $$('input[name="qffield[p][modal]"]').forEach((el) => {
        el.addEventListener('change', () => {
          modallink();
        });
      });

      var csschoose = () => {
        var n = $('input[name="qffield[csschoose]"]:checked').value;
        var box1 = $('.box_cssform');
        var box2 = $('.box_createcssfile');
        var box3 = $('.box_copycssfile');
        if (n == 'y') {
          this.opacity(box1, 1, 200, () => box1.style.display = '');
          this.opacity(box2, 0, 200, () => box2.style.display = 'none');
          this.opacity(box3, 0, 200, () => box3.style.display = 'none');
        } else {
          this.opacity(box1, 0, 200, () => box1.style.display = 'none');
          this.opacity(box2, 1, 200, () => box2.style.display = '');
          this.opacity(box3, 1, 200, () => box3.style.display = '');
        }
      }
      csschoose();
      $$('input[name="qffield[csschoose]"]').forEach((el) => {
        el.addEventListener('change', () => {
          csschoose();
        });

      });


      var calculatordesk = () => {
        var el1 = $$('.cdesk');
        el1.forEach((el) => {
          this.opacity(el, 0, 200, () => el.style.display = 'none');
        })
        var n = $('input[name="qffield[p][calculatortype]"]:checked').value;
        var el2 = $('.cdesk_' + n);
        this.opacity(el2, 1, 200, () => el2.style.display = '');

        var box = $('.box_calcformula');
        if (n != '0' && n != 'default') {
          this.opacity(box, 1, 200, () => box.style.display = '');
        } else {
          this.opacity(box, 0, 200, () => box.style.display = 'none');
        }
      }
      calculatordesk();
      $$('input[name="qffield[p][calculatortype]"]').forEach((el) => {
        el.addEventListener('change', () => {
          calculatordesk();
        });
      });

    },

    checkAll: function(checkbox, stub) {
      if (!checkbox.form) {
        return false;
      }

      var currentStab = stub || 'cb';
      var elements = [].slice.call(checkbox.form.elements);
      elements.forEach(function(element) {
        if (element.type === checkbox.type && element.name === 'cid[]') {
          element.checked = checkbox.checked;
        }
      });

      return true;
    },
    disfiles: function() {
      var sel = $('select[name="qffield[addfiles]"]');
      var accept = $$('input[name="qffield[accept]"]');
      var func = function() {
        var fld2 = $('input[name="qffield[reqfiles]"]').closest('.qffield');
        var fld3 = accept[0].closest('.qffield');
        var fld4 = $('input[name="qffield[whitelist]"]').closest('.qffield');

        [fld2, fld3, fld4].forEach((el) => {
          el.style.display = 'none'
        });
        if (1 * sel.value) {
          [fld2, fld3].forEach((el) => {
            el.style.display = ''
          });
          if (1 * $('input[name="qffield[accept]"]:checked').value) {
            fld4.style.display = '';
          }
        }
      };

      [sel, ...accept].forEach((el) => {
        el.onchange = () => func();
      });
      func();
    },
  }
})();
