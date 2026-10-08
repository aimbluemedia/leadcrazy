/* LeadCrazy free lead form builder (/how-it-works). No dependencies.
   All state lives in one object; every change re-renders the preview.
   The server validates the posted JSON again, so nothing here is trusted. */
(function () {
  'use strict';
  var root = document.querySelector('[data-builder]');
  if (!root) return;

  var cfg = JSON.parse(root.getAttribute('data-config'));
  var form = root.querySelector('[data-builder-form]');
  var $ = function (sel, el) { return (el || root).querySelector(sel); };
  var $$ = function (sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); };

  function inDays(n) { var d = new Date(); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); }

  var state = Object.assign({
    trade: '', business: '', phone: '', city: '', state: '',
    services: [], allServices: [], cities: [], zips: [],
    offer: { on: true, title: '', body: '', expires: inDays(30) }
  }, cfg.state || {});
  state.offer = Object.assign({ on: true, title: '', body: '', expires: inDays(30) }, state.offer || {});
  if (!state.allServices || !state.allServices.length) state.allServices = state.services.slice();
  var step = cfg.step || 1;

  /* ---------- helpers ---------- */
  function slugify(s) {
    return (s || '').toLowerCase().replace(/&/g, ' and ').normalize('NFKD').replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 60) || 'your-business';
  }
  function trade() { return cfg.trades[state.trade] || cfg.trades.other; }
  function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }
  function fmtDate(iso) {
    if (!iso) return '';
    var d = new Date(iso + 'T12:00:00');
    return isNaN(d) ? '' : d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  }

  /* ---------- trade ---------- */
  $$('[data-trade]').forEach(function (b) {
    b.addEventListener('click', function () {
      var key = b.getAttribute('data-trade');
      var t = cfg.trades[key];
      var changed = state.trade !== key;
      state.trade = key;
      if (changed) {
        state.allServices = t.services.slice();
        state.services = t.services.slice();
        state.offer.title = t.offer.title;
        state.offer.body = t.offer.body;
      }
      render();
      go(2);
    });
  });

  /* ---------- simple fields ---------- */
  $$('[data-field]').forEach(function (input) {
    var path = input.getAttribute('data-field').split('.');
    var get = function () { return path.length === 2 ? state[path[0]][path[1]] : state[path[0]]; };
    var set = function (v) { if (path.length === 2) state[path[0]][path[1]] = v; else state[path[0]] = v; };
    if (input.type === 'checkbox') input.checked = !!get(); else input.value = get() || '';
    input.addEventListener('input', function () {
      set(input.type === 'checkbox' ? input.checked : input.value);
      if (path[0] === 'city' && state.city.trim() && state.cities.length === 0) { /* added on leaving step 2 */ }
      render();
    });
    input.addEventListener('change', function () { set(input.type === 'checkbox' ? input.checked : input.value); render(); });
  });

  /* ---------- lists: services, cities, zips ---------- */
  function addTo(kind) {
    var input = $('[data-add-input="' + kind + '"]');
    var v = input.value.trim();
    if (!v) return;
    if (kind === 'zips' && !/^\d{5}$/.test(v)) { flash('Zip codes are 5 digits.'); return; }
    if (kind === 'services') {
      if (state.allServices.length >= cfg.limits.services) { flash('That is plenty of services for one form.'); return; }
      if (state.allServices.indexOf(v) === -1) state.allServices.push(v);
      if (state.services.indexOf(v) === -1) state.services.push(v);
    } else {
      var list = state[kind];
      if (list.length >= cfg.limits[kind]) { flash('Free pages list up to ' + cfg.limits[kind] + ' ' + (kind === 'zips' ? 'zip codes' : 'cities') + '. Upgrade for unlimited.'); return; }
      if (list.indexOf(v) === -1) list.push(v);
    }
    input.value = '';
    flash('');
    render();
  }
  $$('[data-add]').forEach(function (b) { b.addEventListener('click', function () { addTo(b.getAttribute('data-add')); }); });
  $$('[data-add-input]').forEach(function (i) {
    i.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addTo(i.getAttribute('data-add-input')); } });
  });

  function drawServices() {
    var box = $('[data-services]');
    box.innerHTML = '';
    state.allServices.forEach(function (s) {
      var on = state.services.indexOf(s) !== -1;
      var b = el('button', 'toggle' + (on ? ' is-on' : ''), s);
      b.type = 'button';
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      b.addEventListener('click', function () {
        var i = state.services.indexOf(s);
        if (i === -1) state.services.push(s); else state.services.splice(i, 1);
        render();
      });
      box.appendChild(b);
    });
  }
  function drawList(kind) {
    var box = $('[data-list="' + kind + '"]');
    box.innerHTML = '';
    state[kind].forEach(function (v, i) {
      var b = el('button', 'toggle is-on', v + ' ');
      b.type = 'button';
      b.setAttribute('aria-label', 'Remove ' + v);
      b.appendChild(el('span', 'toggle__x', '×'));
      b.addEventListener('click', function () { state[kind].splice(i, 1); render(); });
      box.appendChild(b);
    });
    $('[data-count="' + kind + '"]').textContent = state[kind].length + ' of ' + cfg.limits[kind];
  }

  /* ---------- preview ---------- */
  function pv(name) { return $('[data-pv="' + name + '"]'); }
  function render() {
    $$('[data-trade]').forEach(function (b) {
      var on = b.getAttribute('data-trade') === state.trade;
      b.classList.toggle('is-on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    drawServices(); drawList('cities'); drawList('zips');
    $('[data-offer-fields]').hidden = !state.offer.on;
    $$('[data-field]').forEach(function (input) {
      if (document.activeElement === input) return;
      var path = input.getAttribute('data-field').split('.');
      var v = path.length === 2 ? state[path[0]][path[1]] : state[path[0]];
      if (input.type === 'checkbox') input.checked = !!v; else if (input.value !== (v || '')) input.value = v || '';
    });

    var t = trade();
    var name = state.business.trim() || 'Your Business';
    var place = [state.city.trim(), state.state.trim()].filter(Boolean).join(', ');
    var url = cfg.base + slugify(state.business);
    pv('url').textContent = url;
    $('[data-slug-preview]').textContent = url;
    pv('badge').textContent = 'Free ' + t.noun + ' Quote';
    pv('headline').textContent = name + (place ? ' – ' + t.label + ' in ' + place : '');
    pv('serving').textContent = 'Get your free quote in 24 hours' + (state.phone.trim() ? ' · ' + state.phone.trim() : '');
    pv('form-title').textContent = name;

    var offer = pv('offer');
    offer.hidden = !(state.offer.on && state.offer.title.trim());
    pv('offer-title').textContent = state.offer.title;
    pv('offer-body').textContent = state.offer.body;
    pv('offer-exp').textContent = state.offer.expires ? 'Expires: ' + fmtDate(state.offer.expires) : '';

    var checks = pv('services');
    checks.innerHTML = '';
    (state.services.length ? state.services : ['Your services']).slice(0, 8).forEach(function (s) { checks.appendChild(el('i', '', s)); });
    pv('budget').textContent = (t.budgets && t.budgets[0] ? 'e.g. ' + t.budgets[2 % t.budgets.length] : 'Select a range');

    var area = pv('area');
    area.innerHTML = '';
    var cities = state.cities.length ? state.cities : (state.city.trim() ? [state.city.trim()] : []);
    if (cities.length || state.zips.length) {
      area.appendChild(el('p', 'pv__label', 'Service area'));
      var wrap = el('div', 'pv__pills');
      cities.forEach(function (c) { wrap.appendChild(el('span', 'pv__pill', c)); });
      state.zips.forEach(function (z) { wrap.appendChild(el('span', 'pv__pill pv__pill--zip', z)); });
      area.appendChild(wrap);
    }
  }

  /* ---------- steps ---------- */
  var errorBox = $('[data-step-error]');
  function flash(msg) { errorBox.textContent = msg; errorBox.hidden = !msg; }

  function problem(n) {
    if (n === 1 && !state.trade) return 'Pick your trade to continue.';
    if (n === 2) {
      if (!state.business.trim()) return 'Enter your business name.';
      if (state.phone.replace(/\D/g, '').length < 10) return 'Enter a phone number customers can call.';
      if (!state.city.trim()) return 'Enter the city you are based in.';
    }
    if (n === 3 && !state.services.length) return 'Turn on at least one service.';
    if (n === 4 && !state.cities.length && !state.zips.length) return 'Add at least one city or zip code.';
    if (n === 5 && state.offer.on && !state.offer.title.trim()) return 'Give your offer a title, or switch it off.';
    return '';
  }

  function go(n) {
    // Can only jump ahead past steps that are complete.
    for (var i = 1; i < n; i++) { var p = problem(i); if (p) { n = i; flash(p); break; } }
    if (n > 1 && step === 2 && state.city.trim() && !state.cities.length) state.cities.push(state.city.trim());
    step = n;
    $$('[data-step]').forEach(function (fs) { fs.hidden = +fs.getAttribute('data-step') !== step; });
    $$('[data-goto]').forEach(function (b) {
      var k = +b.getAttribute('data-goto');
      b.parentNode.className = k === step ? 'is-current' : (k < step ? 'is-done' : '');
    });
    $('[data-back]').hidden = step === 1;
    $('[data-next]').hidden = step === 6;
    render();
  }

  $('[data-next]').addEventListener('click', function () {
    var p = problem(step);
    if (p) { flash(p); return; }
    flash('');
    go(step + 1);
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  $('[data-back]').addEventListener('click', function () { flash(''); go(step - 1); });
  $$('[data-goto]').forEach(function (b) { b.addEventListener('click', function () { flash(''); go(+b.getAttribute('data-goto')); }); });

  form.addEventListener('submit', function (e) {
    for (var i = 1; i <= 5; i++) { var p = problem(i); if (p) { e.preventDefault(); go(i); flash(p); return; } }
    $('[data-builder-json]').value = JSON.stringify({
      trade: state.trade, business: state.business, phone: state.phone, city: state.city, state: state.state,
      services: state.services, cities: state.cities, zips: state.zips, offer: state.offer
    });
    var btn = $('[data-publish]');
    setTimeout(function () { btn.disabled = true; btn.textContent = 'Publishing…'; }, 0);
  });

  go(step);
})();
