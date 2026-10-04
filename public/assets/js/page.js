/* LeadCrazy business page. No dependencies. */
(function () {
  'use strict';

  function reveal(btnAttr, boxAttr) {
    var btn = document.querySelector('[' + btnAttr + ']');
    var box = document.querySelector('[' + boxAttr + ']');
    if (!btn || !box) return;
    btn.addEventListener('click', function () {
      box.classList.add('is-open');
      btn.parentNode.removeChild(btn);
    });
  }
  reveal('data-gallery-more', 'data-gallery');
  reveal('data-reviews-more', 'data-reviews');
  reveal('data-offers-more', 'data-offers');

  // Stop a double click sending the lead twice.
  var form = document.querySelector('form.lf');
  if (form) {
    form.addEventListener('submit', function () {
      var b = form.querySelector('.lf-submit');
      if (b) { setTimeout(function () { b.disabled = true; b.textContent = 'Sending...'; }, 0); }
    });
  }

  // Inside the $49 embed: tell the host page how tall we are, so its iframe
  // never needs a scrollbar, and ask it to scroll to us when we change page.
  if (window.parent === window || !document.body.classList.contains('is-embed')) return;

  function post(msg) { try { window.parent.postMessage(msg, '*'); } catch (e) {} }
  var last = 0;
  function size() {
    var h = document.documentElement.scrollHeight;
    if (Math.abs(h - last) > 2) { last = h; post({ lc: 'height', h: h }); }
  }
  size();
  window.addEventListener('load', size);
  window.addEventListener('resize', size);
  if (window.ResizeObserver) new ResizeObserver(size).observe(document.body);
  else setInterval(size, 500);

  if (/[?&]thanks=1/.test(location.search) || document.querySelector('.lf-alert')) post({ lc: 'top' });
  Array.prototype.forEach.call(document.querySelectorAll('a[href="#quote"]'), function (a) {
    a.addEventListener('click', function () { post({ lc: 'top' }); });
  });
})();
