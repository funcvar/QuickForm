/* @Copyright ((c) plasma-web.ru
v 4.0.2
 */

qfDatePicker = function(box) {
  var cache = {};
  var widgets = box.querySelectorAll('.widgetCalendar');
  var setinp = box.querySelector('.calendar_inp');
  var form = box.closest('form');
  var settings = JSON.parse(setinp.dataset.settings || 0);
  var modyinp = function() {
    if (widgets.length == 2 && settings.math) {
      var dt0 = qfdatePickerObj.getDate(widgets[0], 0);
      var dt1 = qfdatePickerObj.getDate(widgets[1], 0);
      var days = Math.ceil((Date.parse(dt1) - Date.parse(dt0)) / 86400000);
      setinp.value = (days > 0 ? days : 0);
    }
  }
  widgets.forEach((one) => {
    var qf_date = one.closest('.qf_date');
    var inp = qf_date.querySelector('input');
    const link = qf_date.querySelector('.qf_date_a');
    qfdatePickerObj.init(one, {
      format: settings.format,
      date: new Date(),
      onRender: function(date) {
        let dis = false;
        if (settings.past) {
          var now= new Date(new Date().setHours(0, 0, 0, 0));
          dis = (date.valueOf() < now.valueOf());
        }
        if (! dis && settings.sa) {
          dis = (date.getDay() == 6);
        }
        if (! dis && settings.su) {
          dis = (date.getDay() == 0);
        }
      	return {
      		disabled: dis
      	}
      },
      onChange: function(formated, dates) {
        inp.value = formated;
        modyinp();
        QuickForm.sumForm(form);
      }
    });
    qfdatePickerObj.setDate(one, inp.value, true, true);

    inp.onchange = () => {
      qfdatePickerObj.setDate(one, inp.value, true, true);
      modyinp();
      QuickForm.sumForm(form);
    }

    one.onmouseleave = () => {
        qfdatePickerObj.animate(one, 600);
      }
      [link, inp].forEach((el) => {
        el.onclick = () => {
          qfdatePickerObj.animate(one, 300);
          return false;
        }
      });
  });
  modyinp();
}

qfdatePickerObj = {

  init: function(one, options) {
    options = Object.assign({}, qfdatePickerObj.defaults, options);
    qfdatePickerObj.extendDate(options.locale);
    options.calendars = Math.max(1, parseInt(options.calendars, 10) || 1);
    if (options.date.constructor == String) {
      options.date = parseDate(options.date, options.format);
      options.date.setHours(0, 0, 0, 0);
    }
    options.date = options.date.valueOf();
    if (!options.current) {
      options.current = new Date();
    } else {
      options.current = parseDate(options.current, options.format);
    }
    options.current.setDate(1);
    options.current.setHours(0, 0, 0, 0);

    var cal = document.createElement('div');
    cal.innerHTML = qfdatePickerObj.tpl.wrapper;
    cal.className = 'datepicker';
    cal.options = options;
    cal.onclick = (e) => {
      return qfdatePickerObj.click(e, cal);
    }

    var cnt, html = '',wd = options.locale.daysMin;
    for (var i = 0; i < options.calendars; i++) {
      cnt = options.starts;
      if (i > 0) {
        html += qfdatePickerObj.tpl.space;
      }
      html += qfdatePickerObj.tmpl2(qfdatePickerObj.tpl.head.join(''), {
        week: options.locale.weekMin,
        prev: options.prev,
        next: options.next,
        day1: wd[(cnt++) % 7],
        day2: wd[(cnt++) % 7],
        day3: wd[(cnt++) % 7],
        day4: wd[(cnt++) % 7],
        day5: wd[(cnt++) % 7],
        day6: wd[(cnt++) % 7],
        day7: wd[(cnt++) % 7]
      });
    }
    cal.querySelector('tr').innerHTML = html;
    cal.querySelector('td table').classList.add(qfdatePickerObj.views[options.view]);
    qfdatePickerObj.fill(cal);
    one.appendChild(cal);
  },

  animate: function(node, time) {
    const cal = node.querySelector('.datepicker');
    let state = node.state || false;
    node.style.transition = (time / 1000) + 's ease height';
    node.style.height = state ? 0 : cal.offsetHeight + 'px';
    node.state = !state;
  },

  setDate: function(widget, date, shiftTo, minutes) {
    const cal = widget.querySelector('.datepicker');
    var options = cal.options;
    options.date = date;
    if (options.date.constructor == String) {
      options.date = this.parseDate(options.date, options.format);
      if (!minutes) options.date.setHours(0, 0, 0, 0);
    }
    options.date = options.date.valueOf();
    if (shiftTo) {
      options.current = new Date(options.date);
    }
    this.fill(cal);
  },

  getDate: function(widget, formated) {
    const cal = widget.querySelector('.datepicker');
    return this.prepareDate(cal.options)[formated ? 0 : 1];
  },
  prepareDate: function(options) {
    var tmp = new Date(options.date);
    return [this.formatDate(tmp, options.format), tmp];
  },
  formatDate: function(date, format) {
    var m = date.getMonth();
    var d = date.getDate();
    var y = date.getFullYear();
    var s = {};
    var hr = date.getHours();
    var pm = (hr >= 12);
    var dy = date.getDayOfYear();
    var min = date.getMinutes();
    var parts = format.split('');
    for (var i = 0; i < parts.length; i++) {
      switch (parts[i]) {
        case 'D':
          parts[i] = date.getDayName();
          break;
        case 'l':
          parts[i] = date.getDayName(true);
          break;
        case 'M':
          parts[i] = date.getMonthName();
          break;
        case 'F':
          parts[i] = date.getMonthName(true);
          break;
        case 'd':
          parts[i] = (d < 10) ? ("0" + d) : d;
          break;
        case 'j':
          parts[i] = d;
          break;
        case 'H':
          parts[i] = (hr < 10) ? ("0" + hr) : hr;
          break;
        case 'z':
          parts[i] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy;
          break;
        case 'G':
          parts[i] = hr;
          break;
        case 'm':
          parts[i] = (m < 9) ? ("0" + (1 + m)) : (1 + m);
          break;
        case 'n':
          parts[i] = 1 + m;
          break;
        case 'i':
          parts[i] = (min < 10) ? ("0" + min) : min;
          break;
        case 'A':
          parts[i] = pm ? "PM" : "AM";
          break;
        case 'a':
          parts[i] = pm ? "pm" : "am";
          break;
        case 's':
          parts[i] = (sec < 10) ? ("0" + sec) : sec;
          break;
        case 'y':
          parts[i] = ('' + y).substr(2, 2);
          break;
        case 'Y':
          parts[i] = y;
          break;
      }
    }
    return parts.join('');
  },
  parseDate: function(date, format) {
    if (date.constructor == Date) {
      return new Date(date);
    }
    var parts = date.split(/\W+/);
    var against = format.split(/\W+/),
      d, m, y, h, min, now = new Date();
    for (var i = 0; i < parts.length; i++) {
      switch (against[i]) {
        case 'd':
        case 'e':
          d = parseInt(parts[i], 10);
          break;
        case 'm':
          m = parseInt(parts[i], 10) - 1;
          break;
        case 'Y':
        case 'y':
          y = parseInt(parts[i], 10);
          y += y > 100 ? 0 : (y < 29 ? 2000 : 1900);
          break;
        case 'H':
        case 'I':
        case 'k':
        case 'l':
          h = parseInt(parts[i], 10);
          break;
        case 'P':
        case 'p':
          if (/pm/i.test(parts[i]) && h < 12) {
            h += 12;
          } else if (/am/i.test(parts[i]) && h >= 12) {
            h -= 12;
          }
          break;
        case 'i':
          min = parseInt(parts[i], 10);
          break;
      }
    }
    return new Date(
      y === undefined ? now.getFullYear() : y,
      m === undefined ? now.getMonth() : m,
      d === undefined ? now.getDate() : d,
      h === undefined ? now.getHours() : h,
      min === undefined ? now.getMinutes() : min,
      0
    );
  },
  fill: function(el) {
    var options = el.options;
    var currentCal = Math.floor(options.calendars / 2),
      date, data, dow, month, cnt = 0,
      week, days, indic, indic2, html, tblCal;
    el.querySelectorAll('td>table tbody').forEach((one) => {
      one.remove()
    });
    for (var i = 0; i < options.calendars; i++) {
      date = new Date(options.current);
      date.addMonths(-currentCal + i);
      tblCal = el.querySelectorAll('table')[(i + 1)];
      switch (tblCal.className) {
        case 'datepickerViewDays':
          dow = this.formatDate(date, 'M, Y');
          break;
        case 'datepickerViewMonths':
          dow = date.getFullYear();
          break;
        case 'datepickerViewYears':
          dow = (date.getFullYear() - 6) + ' - ' + (date.getFullYear() + 5);
          break;
      }
      tblCal.querySelectorAll('thead>tr th span')[1].innerText = dow;
      dow = date.getFullYear() - 6;
      data = {
        data: [],
        className: 'datepickerYears'
      }
      for (var j = 0; j < 12; j++) {
        data.data.push(dow + j);
      }
      html = this.tmpl2(this.tplmonths().join(''), data);
      date.setDate(1);
      data = {
        weeks: [],
        test: 10
      };
      month = date.getMonth();
      var dow = (date.getDay() - options.starts) % 7;
      date.addDays(-(dow + (dow < 0 ? 7 : 0)));
      week = -1;
      cnt = 0;
      while (cnt < 42) {
        indic = parseInt(cnt / 7, 10);
        indic2 = cnt % 7;
        if (!data.weeks[indic]) {
          week = date.getWeekNumber();
          data.weeks[indic] = {
            week: week,
            days: []
          };
        }
        data.weeks[indic].days[indic2] = {
          text: date.getDate(),
          classname: []
        };
        if (month != date.getMonth()) {
          data.weeks[indic].days[indic2].classname.push('datepickerNotInMonth');
        }
        if (date.getDay() == 0) {
          data.weeks[indic].days[indic2].classname.push('datepickerSunday');
        } else if (date.getDay() == 6) {
          data.weeks[indic].days[indic2].classname.push('datepickerSaturday');
        } else {
          data.weeks[indic].days[indic2].classname.push('datepickerDay');
        }
        var fromUser = options.onRender(date);
        var val = date.valueOf();
        if (fromUser.selected || options.date == val) {
          data.weeks[indic].days[indic2].classname.push('datepickerSelected');
        }
        if (fromUser.disabled) {
          data.weeks[indic].days[indic2].classname.push('datepickerDisabled');
        }
        if (fromUser.className) {
          data.weeks[indic].days[indic2].classname.push(fromUser.className);
        }
        data.weeks[indic].days[indic2].classname = data.weeks[indic].days[indic2].classname.join(' ');
        cnt++;
        date.addDays(1);
      }
      html = this.tmpl2(this.tpldays().join(''), data) + html;
      data = {
        data: options.locale.monthsShort,
        className: 'datepickerMonths'
      };
      html = this.tmpl2(this.tplmonths().join(''), data) + html;
      tblCal.insertAdjacentHTML('beforeend', html);
    }
  },

  tmpl2: function(str, data) {
    var fn = !/\W/.test(str) ?
      cache[str] = cache[str] : new Function("obj",
        "var p=[],print=function(){p.push.apply(p,arguments);};" +
        "with(obj){p.push('" +
        str
        .replace(/[\r\t\n]/g, " ")
        .split("<%").join("\t")
        .replace(/((^|%>)[^\t]*)'/g, "$1\r")
        .replace(/\t=(.*?)%>/g, "',$1,'")
        .split("\t").join("');")
        .split("%>").join("p.push('")
        .split("\r").join("\\'") +
        "');}return p.join('');");

    return data ? fn(data) : fn;
  },

  tpldays: function() {
    var d = [];
    d.push('<tbody class="datepickerDays">');
    for (var i = 0; i < 6; i++) {
      d.push('<tr>');
      d.push('<th class="datepickerWeek"><a href="#"><span><%=weeks[' + i + '].week%></span></a></th>');
      for (var ii = 0; ii < 7; ii++) {
        d.push('<td class="<%=weeks[' + i + '].days[' + ii + '].classname%>"><a href="#"><span><%=weeks[' + i + '].days[' + ii + '].text%></span></a></td>');
      }
      d.push('</tr>');
    }
    d.push('</tbody>');
    return d;
  },

  tplmonths: function() {
    var m = [],
      n = 0;
    m.push('<tbody class="<%=className%>">');
    for (var i = 0; i < 3; i++) {
      m.push('<tr>');
      for (var ii = 0; ii < 4; ii++) {
        m.push('<td colspan="2"><a href="#"><span><%=data[' + n + ']%></span></a></td>');
        n++;
      }
      m.push('</tr>');
    }
    m.push('</tbody>');
    return m;
  },

  tpl: {
    wrapper: '<div class="datepickerContainer"><table cellspacing="0" cellpadding="0"><tbody><tr></tr></tbody></table></div>',
    head: [
      '<td>',
      '<table cellspacing="0" cellpadding="0">',
      '<thead>',
      '<tr>',
      '<th class="datepickerGoPrev"><a href="#"><span><%=prev%></span></a></th>',
      '<th colspan="6" class="datepickerMonth"><a href="#"><span></span></a></th>',
      '<th class="datepickerGoNext"><a href="#"><span><%=next%></span></a></th>',
      '</tr>',
      '<tr class="datepickerDoW">',
      '<th><span><%=week%></span></th>',
      '<th><span><%=day1%></span></th>',
      '<th><span><%=day2%></span></th>',
      '<th><span><%=day3%></span></th>',
      '<th><span><%=day4%></span></th>',
      '<th><span><%=day5%></span></th>',
      '<th><span><%=day6%></span></th>',
      '<th><span><%=day7%></span></th>',
      '</tr>',
      '</thead>',
      '</table></td>'
    ],
    space: '<td class="datepickerSpace"><div></div></td>',
  },

  defaults: {
    starts: 1,
    prev: '&#10148;',
    next: '&#10148;',
    view: 'days',
    calendars: 1,
    format: 'Y-m-d',
    onRender: function() {
      return {};
    },
    onChange: function() {
      return true;
    },
    locale: {
      days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
      daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
      months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
      monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      weekMin: 'wk'
    }
  },

  extendDate: function(options) {
    if (Date.prototype.tempDate) {
      return;
    }
    Date.prototype.tempDate = null;
    Date.prototype.months = options.months;
    Date.prototype.monthsShort = options.monthsShort;
    Date.prototype.days = options.days;
    Date.prototype.daysShort = options.daysShort;
    Date.prototype.getMonthName = function(fullName) {
      return this[fullName ? 'months' : 'monthsShort'][this.getMonth()];
    };
    Date.prototype.getDayName = function(fullName) {
      return this[fullName ? 'days' : 'daysShort'][this.getDay()];
    };
    Date.prototype.addDays = function(n) {
      this.setDate(this.getDate() + n);
      this.tempDate = this.getDate();
    };
    Date.prototype.addMonths = function(n) {
      if (this.tempDate == null) {
        this.tempDate = this.getDate();
      }
      this.setDate(1);
      this.setMonth(this.getMonth() + n);
      this.setDate(Math.min(this.tempDate, this.getMaxDays()));
    };
    Date.prototype.addYears = function(n) {
      if (this.tempDate == null) {
        this.tempDate = this.getDate();
      }
      this.setDate(1);
      this.setFullYear(this.getFullYear() + n);
      this.setDate(Math.min(this.tempDate, this.getMaxDays()));
    };
    Date.prototype.getMaxDays = function() {
      var tmpDate = new Date(Date.parse(this)),
        d = 28,
        m = tmpDate.getMonth();
      while (tmpDate.getMonth() == m) {
        d++;
        tmpDate.setDate(d);
      }
      return d - 1;
    };
    Date.prototype.getFirstDay = function() {
      var tmpDate = new Date(Date.parse(this));
      tmpDate.setDate(1);
      return tmpDate.getDay();
    };
    Date.prototype.getWeekNumber = function() {
      var tempDate = new Date(this);
      tempDate.setDate(tempDate.getDate() - (tempDate.getDay() + 6) % 7 + 3);
      var dms = tempDate.valueOf();
      tempDate.setMonth(0);
      tempDate.setDate(4);
      return Math.round((dms - tempDate.valueOf()) / (604800000)) + 1;
    };
    Date.prototype.getDayOfYear = function() {
      var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
      var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
      var time = now - then;
      return Math.floor(time / 24 * 60 * 60 * 1000);
    };
  },

  views: {
    years: 'datepickerViewYears',
    moths: 'datepickerViewMonths',
    days: 'datepickerViewDays'
  },

  click: function(ev, cal) {
    var el = ev.target;
    if (el.tagName == 'SPAN') {
      el = el.parentNode;
    }

    if (el.tagName == 'A') {
      el.blur();
      if (el.classList.contains('datepickerDisabled')) {
        return false;
      }

      var options = cal.options;
      var parentEl = el.parentNode;
      var tblEl = parentEl.parentNode.parentNode.parentNode;
      var tblIndex = [].indexOf.call(cal.querySelectorAll('table'), tblEl) - 1;
      var tmp = new Date(options.current);
      var changed = false;
      var fillIt = false;
      if (parentEl.tagName == 'TH') {
        if (parentEl.classList.contains('datepickerMonth')) {
          tmp.addMonths(tblIndex - Math.floor(options.calendars / 2));
          switch (tblEl.className) {
            case 'datepickerViewDays':
              tblEl.className = 'datepickerViewMonths';
              el.querySelector('span').innerText = tmp.getFullYear();
              break;
            case 'datepickerViewMonths':
              tblEl.className = 'datepickerViewYears';
              el.querySelector('span').innerText = (tmp.getFullYear() - 6) + ' - ' + (tmp.getFullYear() + 5);
              break;
            case 'datepickerViewYears':
              tblEl.className = 'datepickerViewDays';
              el.querySelector('span').innerText = qfdatePickerObj.formatDate(tmp, 'M, Y');
              break;
          }
        } else if (parentEl.parentNode.parentNode.tagName == 'THEAD') {
          switch (tblEl.className) {
            case 'datepickerViewDays':
              options.current.addMonths(parentEl.classList.contains('datepickerGoPrev') ? -1 : 1);
              break;
            case 'datepickerViewMonths':
              options.current.addYears(parentEl.classList.contains('datepickerGoPrev') ? -1 : 1);
              break;
            case 'datepickerViewYears':
              options.current.addYears(parentEl.classList.contains('datepickerGoPrev') ? -12 : 12);
              break;
          }
          fillIt = true;
        }
      } else if (parentEl.tagName == 'TD' && !parentEl.classList.contains('datepickerDisabled')) {
        switch (tblEl.className) {
          case 'datepickerViewMonths':
            options.current.setMonth([].indexOf.call(tblEl.querySelectorAll('tbody.datepickerMonths td'), parentEl));
            options.current.setFullYear(parseInt(tblEl.querySelector('thead th.datepickerMonth span').innerText, 10));
            options.current.addMonths(Math.floor(options.calendars / 2) - tblIndex);
            tblEl.className = 'datepickerViewDays';
            break;
          case 'datepickerViewYears':
            options.current.setFullYear(parseInt(el.innerText, 10));
            tblEl.className = 'datepickerViewMonths';
            break;
          default:
            var val = parseInt(el.innerText, 10);
            tmp.addMonths(tblIndex - Math.floor(options.calendars / 2));
            if (parentEl.classList.contains('datepickerNotInMonth')) {
              tmp.addMonths(val > 15 ? -1 : 1);
            }
            tmp.setDate(val);
            options.date = tmp.valueOf();
            changed = true;
            break;
        }
        fillIt = true;
      }
      if (fillIt) {
        qfdatePickerObj.fill(cal);
      }
      if (changed) {
        options.onChange.apply(cal, qfdatePickerObj.prepareDate(options));
      }
    }
    return false;
  }

}
