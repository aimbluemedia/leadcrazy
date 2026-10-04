<?php
/**
 * The script a Premium member pastes on their site. Plain ES5, no globals left
 * behind, safe to include more than once on a page.
 *
 *   <script src="https://leadcrazy.com/widget/storm-landscaping.js" data-view="page" async></script>
 *
 * @var string $slug @var string $origin
 */
?>
(function () {
  var ORIGIN = <?= json_encode($origin, JSON_UNESCAPED_SLASHES) ?>;
  var SLUG = <?= json_encode($slug) ?>;
  var scripts = document.querySelectorAll('script[src*="/widget/' + SLUG + '.js"]:not([data-lc-done])');
  for (var i = 0; i < scripts.length; i++) {
    var s = scripts[i];
    s.setAttribute('data-lc-done', '1');
    var view = s.getAttribute('data-view') === 'page' ? 'page' : 'form';
    var frame = document.createElement('iframe');
    frame.src = ORIGIN + '/embed/' + encodeURIComponent(SLUG) + '?view=' + view;
    frame.title = 'Request a free quote';
    frame.loading = 'lazy';
    frame.setAttribute('scrolling', 'no');
    frame.style.cssText = 'display:block;width:100%;max-width:' + (view === 'page' ? '1200px' : '560px')
      + ';min-height:600px;border:0;margin:0 auto;overflow:hidden;background:transparent;';
    s.parentNode.insertBefore(frame, s.nextSibling);
    (function (f) {
      window.addEventListener('message', function (ev) {
        if (ev.origin !== ORIGIN || ev.source !== f.contentWindow) return;
        var d = ev.data || {};
        if (d.lc === 'height' && typeof d.h === 'number') f.style.height = Math.ceil(d.h) + 'px';
        if (d.lc === 'top') {
          var r = f.getBoundingClientRect();
          window.scrollTo({ top: window.pageYOffset + r.top - 20, behavior: 'smooth' });
        }
      });
    })(frame);
  }
})();
