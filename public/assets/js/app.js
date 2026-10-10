/* LeadCrazy site, members and superadmin. No dependencies. */
(function () {
  'use strict';
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.querySelector('[data-nav]');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Copy buttons for embed code.
  Array.prototype.forEach.call(document.querySelectorAll('[data-copy]'), function (btn) {
    btn.addEventListener('click', function () {
      var src = document.getElementById(btn.getAttribute('data-copy'));
      if (!src) return;
      var text = src.textContent;
      var done = function () { var t = btn.textContent; btn.textContent = 'Copied!'; setTimeout(function () { btn.textContent = t; }, 1600); };
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(done);
      else { var r = document.createRange(); r.selectNodeContents(src); var s = getSelection(); s.removeAllRanges(); s.addRange(r); document.execCommand('copy'); done(); }
    });
  });

  // Confirm destructive actions.
  Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });

  // Lead calculator on the homepage. Same formulas as partials/lead-calculator.php.
  var calc = document.querySelector('[data-lcalc]');
  if (calc) {
    var money = function (n) { return '$' + Math.round(n).toLocaleString('en-US'); };
    var num = function (n) { return (Math.round(n * 10) / 10).toLocaleString('en-US'); };
    var val = function (k) { return parseFloat(calc.querySelector('[name="' + k + '"]').value); };
    var set = function (k, t) { var el = calc.querySelector('[data-out="' + k + '"]'); if (el) el.textContent = t; };
    var paint = function (input) {
      var pct = (input.value - input.min) / (input.max - input.min) * 100;
      input.style.setProperty('--fill', pct + '%');
    };
    var run = function () {
      var v = { visitors: val('visitors'), rate: val('rate'), lift: val('lift'), value: val('value'), close: val('close'), margin: val('margin') };
      var at = function (lift) {
        var leads = v.visitors * v.rate / 100 * lift, jobs = leads * v.close / 100, rev = jobs * v.value;
        return { leads: leads, jobs: jobs, rev: rev, profit: rev * v.margin / 100 };
      };
      var a = at(1), b = at(v.lift);
      set('leadsNow', num(a.leads)); set('leadsLc', num(b.leads));
      set('jobsNow', num(a.jobs)); set('jobsLc', num(b.jobs));
      set('revNow', money(a.rev)); set('revLc', money(b.rev));
      set('profitNow', money(a.profit)); set('profitLc', money(b.profit));
      set('extraLeads', '+' + num(b.leads - a.leads)); set('extraJobs', '+' + num(b.jobs - a.jobs));
      set('yearRev', money((b.rev - a.rev) * 12)); set('yearProfit', money((b.profit - a.profit) * 12));
      set('monthGap', money(b.profit - a.profit));
      set('perLead', money(v.value * v.close / 100 * v.margin / 100) + ' profit');
      Array.prototype.forEach.call(calc.querySelectorAll('input[type=range]'), function (i) {
        var out = document.getElementById(i.id + '-out');
        var n = parseFloat(i.value);
        if (out) out.innerHTML = out.getAttribute('data-pre') + (n >= 1000 ? n.toLocaleString('en-US') : n) + out.getAttribute('data-post');
        paint(i);
      });
    };
    calc.addEventListener('input', run);
    run();
  }

  // Show / hide on every password field.
  var EYE = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYE_OFF = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6C3.8 8.4 2 12 2 12s3.6 7 10 7c1.9 0 3.5-.6 4.9-1.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
  Array.prototype.forEach.call(document.querySelectorAll('input[type=password]'), function (input) {
    if (input.closest('.pw-wrap')) return;
    var wrap = document.createElement('span');
    wrap.className = 'pw-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-eye';
    btn.setAttribute('aria-label', 'Show password');
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML = EYE;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? EYE_OFF : EYE;
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      input.focus();
    });
    wrap.appendChild(btn);
    // Never submit with the password left visible on screen behind a redirect.
    if (input.form) input.form.addEventListener('submit', function () { input.type = 'password'; });
  });
})();
