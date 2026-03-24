/**
 * EDS Gallery Widget — Frontend JavaScript
 * eDesign Space · https://edesignspace.com/
 *
 * Handles: filter bar, masonry reflow, lightbox.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.eds-gallery-wrap').forEach(initGallery);
  });

  function initGallery(wrap) {
    var config = {};
    try { config = JSON.parse(wrap.getAttribute('data-config') || '{}'); } catch(e) {}

    var gallery  = wrap.querySelector('.eds-gallery');
    var items    = Array.prototype.slice.call(wrap.querySelectorAll('.eds-gallery__item'));
    var layout   = config.layout || 'grid';

    // Responsive columns via CSS custom properties.
    updateColumns(wrap, gallery, config);
    window.addEventListener('resize', debounce(function () {
      updateColumns(wrap, gallery, config);
      if (layout === 'masonry') { reflowMasonry(gallery, config); }
    }, 150));

    // Filter bar.
    var filterBtns = Array.prototype.slice.call(wrap.querySelectorAll('.eds-gallery__filter-btn'));
    if (filterBtns.length) {
      initFilter(filterBtns, items, config);
    }

    // Masonry.
    if (layout === 'masonry') {
      imagesLoaded(gallery, function () { reflowMasonry(gallery, config); });
    }

    // Lightbox.
    var lbId = wrap.id ? wrap.id + '-lb' : null;
    var lb   = lbId ? document.getElementById(lbId) : null;
    if (config.lightbox && lb) {
      initLightbox(wrap, items, lb, config);
    }
  }

  // ── Responsive columns ─────────────────────────────────────────────────────

  function updateColumns(wrap, gallery, config) {
    var w = wrap.offsetWidth;
    var cols;
    if (w <= 767) {
      cols = config.cols_mobile || 1;
    } else if (w <= 1024) {
      cols = config.cols_tablet || 2;
    } else {
      cols = config.columns || 3;
    }
    gallery.style.setProperty('--eds-gallery-cols', cols);
    // CSS column-count for masonry
    if (config.layout === 'masonry') {
      gallery.style.columnCount = cols;
    }
  }

  // ── Masonry reflow ─────────────────────────────────────────────────────────

  function reflowMasonry(gallery) {
    // CSS column masonry — no JS reflow needed. Browser handles it.
    // We just set column-count above.
  }

  // ── Filter ─────────────────────────────────────────────────────────────────

  function initFilter(btns, items, config) {
    var animClass = config.filter_anim || 'fade';
    var duration  = 300;

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var filter = btn.getAttribute('data-filter');

        btns.forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');

        items.forEach(function (item) {
          var tags  = (item.getAttribute('data-tags') || '').split(' ');
          var match = (filter === '*') || tags.indexOf(filter) !== -1;

          if (match) {
            item.classList.remove('is-hiding', 'is-hidden');
            void item.offsetWidth; // reflow
            item.style.opacity   = '';
            item.style.transform = '';
          } else {
            item.classList.add('is-hiding');
            setTimeout(function () {
              item.classList.add('is-hidden');
              item.classList.remove('is-hiding');
            }, duration);
          }
        });
      });
    });
  }

  // ── Lightbox ────────────────────────────────────────────────────────────────

  function initLightbox(wrap, items, lb, config) {
    var lbItems   = []; // visible/ordered items
    var current   = 0;
    var img       = lb.querySelector('.eds-lightbox__img');
    var backdrop  = lb.querySelector('.eds-lightbox__backdrop');
    var closeBtn  = lb.querySelector('.eds-lightbox__close');
    var prevBtn   = lb.querySelector('.eds-lightbox__prev');
    var nextBtn   = lb.querySelector('.eds-lightbox__next');
    var counter   = lb.querySelector('.eds-lightbox__counter');
    var capTitle  = lb.querySelector('.eds-lightbox__caption-title');
    var capDesc   = lb.querySelector('.eds-lightbox__caption-desc');
    var loop      = config.lb_loop !== false;
    var keyboard  = config.lb_keyboard !== false;
    var showCap   = config.lb_caption !== false;

    // Open triggers.
    wrap.querySelectorAll('.eds-gallery__lb-trigger').forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var itemEl = trigger.closest('.eds-gallery__item');
        lbItems    = getVisibleItems(items);
        var idx    = lbItems.indexOf(itemEl);
        openLightbox(idx < 0 ? 0 : idx);
      });
    });

    function getVisibleItems(all) {
      return all.filter(function (el) {
        return !el.classList.contains('is-hidden') && !el.classList.contains('is-hiding');
      });
    }

    function openLightbox(idx) {
      current = idx;
      lb.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      showSlide(current);
    }

    function closeLightbox() {
      lb.setAttribute('hidden', '');
      document.body.style.overflow = '';
    }

    function showSlide(idx) {
      var item  = lbItems[idx];
      if (!item) return;
      var src   = item.getAttribute('data-src') || '';
      var title = item.getAttribute('data-title') || '';
      var desc  = item.getAttribute('data-desc') || '';

      img.classList.add('is-loading');
      var tmp = new Image();
      tmp.onload = function () {
        img.src = src;
        img.alt = title;
        img.classList.remove('is-loading');
      };
      tmp.src = src;

      if (showCap) {
        if (capTitle) capTitle.textContent = title;
        if (capDesc)  capDesc.textContent  = desc;
      }

      if (counter) {
        counter.textContent = (idx + 1) + ' / ' + lbItems.length;
      }

      if (prevBtn) {
        prevBtn.classList.toggle('is-disabled', !loop && idx === 0);
      }
      if (nextBtn) {
        nextBtn.classList.toggle('is-disabled', !loop && idx === lbItems.length - 1);
      }
    }

    function navigate(dir) {
      var total = lbItems.length;
      var next  = current + dir;
      if (loop) {
        next = ((next % total) + total) % total;
      } else {
        if (next < 0 || next >= total) return;
      }
      current = next;
      showSlide(current);
    }

    if (closeBtn)  { closeBtn.addEventListener('click',  closeLightbox); }
    if (backdrop)  { backdrop.addEventListener('click',  closeLightbox); }
    if (prevBtn)   { prevBtn.addEventListener('click',   function () { navigate(-1); }); }
    if (nextBtn)   { nextBtn.addEventListener('click',   function () { navigate(1);  }); }

    if (keyboard) {
      document.addEventListener('keydown', function (e) {
        if (lb.hasAttribute('hidden')) return;
        if (e.key === 'ArrowLeft')  { navigate(-1); }
        if (e.key === 'ArrowRight') { navigate(1);  }
        if (e.key === 'Escape')     { closeLightbox(); }
      });
    }

    // Touch swipe.
    var touchStartX = 0;
    lb.addEventListener('touchstart', function (e) { touchStartX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 50) { navigate(dx > 0 ? -1 : 1); }
    });
  }

  // ── Utilities ───────────────────────────────────────────────────────────────

  function debounce(fn, delay) {
    var t;
    return function () { clearTimeout(t); t = setTimeout(fn, delay); };
  }

  // Simple imagesLoaded polyfill.
  function imagesLoaded(container, cb) {
    var imgs = container.querySelectorAll('img');
    if (!imgs.length) { cb(); return; }
    var loaded = 0;
    function done() { if (++loaded === imgs.length) cb(); }
    imgs.forEach(function (img) {
      if (img.complete) { done(); }
      else { img.addEventListener('load', done); img.addEventListener('error', done); }
    });
  }

}());
