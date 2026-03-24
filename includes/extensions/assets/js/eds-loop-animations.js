/**
 * EDS Loop Animations — Frontend + Live Editor Preview
 *
 * Uses elementorModules.frontend.handlers.Base with getElementSettings()
 * for live editor updates.
 *
 * eDesign Space · https://edesignspace.com/
 */
(function () {
  'use strict';

  function applyLoop(el, s) {
    var anim      = s._eds_loop_animation || '';
    var intensity = s._eds_loop_intensity  || 'medium';
    var hover     = s._eds_loop_hover_pause === 'yes';

    // Remove all previous loop classes and stop random animation
    var toRemove = [];
    el.classList.forEach(function (c) {
      if (c === 'eds-loop-el' || c === 'eds-loop-hover-pause' || c.indexOf('eds-loop--') === 0 || c.indexOf('eds-loop-i--') === 0) toRemove.push(c);
    });
    toRemove.forEach(function (c) { el.classList.remove(c); });
    el.style.transform = '';
    if (el._edsLoopRandom) el._edsLoopRandom = false;

    if (!anim) return;

    el.classList.add('eds-loop-el', 'eds-loop--' + anim, 'eds-loop-i--' + intensity);
    if (hover) el.classList.add('eds-loop-hover-pause');
    if (anim === 'random') runRandom(el, intensity, hover);
  }

  function runRandom(el, intensity, pauseOnHover) {
    var range = intensity === 'low' ? 12 : intensity === 'high' ? 40 : 24;
    el._edsLoopRandom = true;
    var fromX=0, fromY=0, tX=0, tY=0, start=null, paused=false;
    function next() { tX=(Math.random()-.5)*2*range; tY=(Math.random()-.5)*2*range; }
    next();
    if (pauseOnHover) {
      el.addEventListener('mouseenter', function(){paused=true;});
      el.addEventListener('mouseleave', function(){paused=false;});
    }
    (function step(ts) {
      if (!el._edsLoopRandom) return;
      if (!start) start=ts;
      if (!paused) {
        var p=Math.min((ts-start)/2000,1), t=p<.5?4*p*p*p:1-Math.pow(-2*p+2,3)/2;
        el.style.transform='translate('+(fromX+(tX-fromX)*t).toFixed(2)+'px,'+(fromY+(tY-fromY)*t).toFixed(2)+'px)';
        if (p>=1){fromX=tX;fromY=tY;start=null;next();}
      } else { start=null; }
      requestAnimationFrame(step);
    })(performance.now());
  }

  // Expose for editor panel JS to trigger random move cross-iframe
  window.edsLoopRandom = runRandom;

  function registerHandler() {
    if (typeof elementorModules === 'undefined' || !elementorModules.frontend) return;

    var EdsLoopHandler = elementorModules.frontend.handlers.Base.extend({

      onInit: function () {
        elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
        this.boot();
      },

      // Fires in the editor when any control on this element changes
      onElementChange: function (changedProp) {
        var loopProps = ['_eds_loop_animation','_eds_loop_intensity','_eds_loop_hover_pause','_eds_loop_duration','_eds_loop_delay','_eds_loop_easing'];
        if (loopProps.indexOf(changedProp) !== -1) {
          this.boot();
        }
      },

      boot: function () {
        var el = this.$element[0];
        var s  = this.getElementSettings(); // Always live from backbone model
        applyLoop(el, s);
      }
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($element) {
      elementorFrontend.elementsHandler.addHandler(EdsLoopHandler, { $element: $element });
    });
  }

  if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isInit) {
    registerHandler();
  } else {
    jQuery(window).on('elementor/frontend/init', registerHandler);
  }

}());
