/**
 * EDS Advanced Heading Widget — Frontend JavaScript
 * eDesign Space · https://edesignspace.com/
 */
(function () {
  'use strict';

  function initHeading(wrap) {
    var config = {};
    try { config = JSON.parse(wrap.getAttribute('data-config') || '{}'); } catch(e) {}

    var type = config.type || 'typing';

    switch (type) {
      case 'typing':    initTyping(wrap, config);    break;
      case 'rotating':  initRotating(wrap, config, 'is-rotating'); break;
      case 'slide':     initRotating(wrap, config, 'is-slide');    break;
      case 'zoom':      initRotating(wrap, config, 'is-zoom');     break;
      case 'flip':      initRotating(wrap, config, 'is-flip');     break;
      case 'fade':      initRotating(wrap, config, 'is-fade');     break;
      // 'wave', 'highlighted', 'static' are CSS-only — no JS needed
    }
  }

  // ── Typing effect ─────────────────────────────────────────────────────────

  function initTyping(wrap, config) {
    var wordsWrap  = wrap.querySelector('.eah-words-wrap');
    if (!wordsWrap) return;

    var words       = config.words || [];
    var speed       = config.speed       || 3000;
    var charSpeed   = config.typing_speed || 80;
    var loop        = config.loop !== false;
    var current     = 0;
    var charIndex   = 0;
    var deleting    = false;
    var pausing     = false;
    var displayEl   = wordsWrap.querySelector('.eah-word.is-active') || wordsWrap.querySelector('.eah-word');

    if (!displayEl || !words.length) return;

    // Create a live text span
    var liveEl = document.createElement('span');
    liveEl.className = 'eah-word is-active';
    wordsWrap.innerHTML = '';
    wordsWrap.appendChild(liveEl);

    // Re-attach cursor if present
    var cursor = document.createElement('span');
    cursor.className = 'eah-cursor';
    cursor.setAttribute('aria-hidden', 'true');
    if (config.cursor !== false) wordsWrap.appendChild(cursor);

    function tick() {
      var word = words[current] || '';

      if (!deleting) {
        liveEl.textContent = word.substring(0, charIndex + 1);
        charIndex++;
        if (charIndex >= word.length) {
          if (!loop && current === words.length - 1) return;
          pausing = true;
          setTimeout(function () {
            pausing = false;
            deleting = true;
            tick();
          }, speed);
          return;
        }
        setTimeout(tick, charSpeed);
      } else {
        liveEl.textContent = word.substring(0, charIndex - 1);
        charIndex--;
        if (charIndex <= 0) {
          deleting  = false;
          current   = (current + 1) % words.length;
          charIndex = 0;
          setTimeout(tick, 300);
          return;
        }
        setTimeout(tick, Math.max(20, charSpeed / 2));
      }
    }

    setTimeout(tick, 600);
  }

  // ── Rotating / Slide / Zoom / Flip / Fade ─────────────────────────────────

  function initRotating(wrap, config, cssClass) {
    var wordsWrap = wrap.querySelector('.eah-words-wrap');
    if (!wordsWrap) return;

    wordsWrap.classList.add(cssClass);

    var wordEls = Array.prototype.slice.call(wordsWrap.querySelectorAll('.eah-word'));
    if (!wordEls.length) return;

    var current = 0;
    var speed   = config.speed || 3000;
    var loop    = config.loop !== false;
    var total   = wordEls.length;

    // Show first word
    wordEls[0].classList.add('is-active');

    if (total < 2) return;

    function next() {
      var cur  = current;
      var nxt  = (current + 1) % total;

      if (!loop && nxt === 0 && cur === total - 1) return;

      wordEls[cur].classList.remove('is-active');
      wordEls[cur].classList.add('is-leaving');

      setTimeout(function () {
        wordEls[cur].classList.remove('is-leaving');
      }, 500);

      wordEls[nxt].classList.add('is-active');
      current = nxt;

      setTimeout(next, speed);
    }

    setTimeout(next, speed);
  }

  // ── Boot ──────────────────────────────────────────────────────────────────

  function init() {
    document.querySelectorAll('.eah-wrap[data-config]').forEach(function(wrap) {
      // Only init once
      if (!wrap.dataset.eahInit) {
        wrap.dataset.eahInit = '1';
        initHeading(wrap);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Elementor editor: re-init on every element render/re-render in the preview iframe.
  // Must be registered outside DOMContentLoaded so it fires during Elementor's init cycle.
  function registerElementorHook() {
    if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) return;
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/eds-advanced-heading.default',
      function ($scope) {
        var wrap = $scope[0] && $scope[0].querySelector('.eah-wrap');
        if (wrap) {
          delete wrap.dataset.eahInit;
          initHeading(wrap);
        }
      }
    );
  }

  // Try immediately (preview iframe already loaded), then on elementor:init as fallback.
  registerElementorHook();
  window.addEventListener('elementor/frontend/init', registerElementorHook);

}());
