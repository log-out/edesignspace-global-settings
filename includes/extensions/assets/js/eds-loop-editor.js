/**
 * EDS Loop Animations — Editor Panel JS
 * Listens to Elementor's editor channel and directly updates
 * data-elp + CSS classes on the preview iframe element.
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  var CONTROLS = { animation:'_eds_loop_animation', intensity:'_eds_loop_intensity', hover:'_eds_loop_hover_pause' };
  var OUR_CONTROLS = [CONTROLS.animation, CONTROLS.intensity, CONTROLS.hover];

  function getPreviewEl(model) {
    var frame = elementor.$preview && elementor.$preview[0];
    if (!frame) return null;
    var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
    if (!doc || !model) return null;
    return doc.querySelector('.elementor-element.elementor-element-' + model.get('id'));
  }

  function applyToPreview(model) {
    var el = getPreviewEl(model);
    if (!el) return;

    var anim = model.getSetting(CONTROLS.animation) || '';
    var intensity = model.getSetting(CONTROLS.intensity) || 'medium';
    var hover = model.getSetting(CONTROLS.hover) === 'yes' ? 1 : 0;

    // Remove old loop classes
    var toRemove = [];
    el.classList.forEach(function(c) {
      if (c==='eds-loop-el'||c==='eds-loop-hover-pause'||c.indexOf('eds-loop--')===0||c.indexOf('eds-loop-i--')===0) toRemove.push(c);
    });
    toRemove.forEach(function(c){el.classList.remove(c);});
    el.style.transform = '';
    if (el._edsLoopRandom) el._edsLoopRandom = false;

    if (!anim) {
      el.removeAttribute('data-elp');
      return;
    }

    var cfg = { a: anim, i: intensity, h: hover };
    el.setAttribute('data-elp', JSON.stringify(cfg));
    el.classList.add('eds-loop-el', 'eds-loop--' + anim, 'eds-loop-i--' + intensity);
    if (hover) el.classList.add('eds-loop-hover-pause');

    if (anim === 'random') {
      var frame = elementor.$preview && elementor.$preview[0];
      var doc = frame && (frame.contentDocument || (frame.contentWindow && frame.contentWindow.document));
      if (doc && doc.defaultView && doc.defaultView.edsLoopRandom) {
        doc.defaultView.edsLoopRandom(el, intensity, !!hover);
      }
    }
  }

  function init() {
    if (typeof elementor === 'undefined' || !elementor.channels) return;

    elementor.channels.editor.on('change', function (view) {
      try {
        var key = view.options && view.options.elementSettingKey;
        if (OUR_CONTROLS.indexOf(key) === -1) return;
        var model = elementor.selection.getElements && elementor.selection.getElements()[0];
        if (model) applyToPreview(model);
      } catch(e) {}
    });

    elementor.channels.editor.on('editor:opened', function () {
      try {
        var model = elementor.selection.getElements && elementor.selection.getElements()[0];
        if (model && model.getSetting(CONTROLS.animation)) applyToPreview(model);
      } catch(e) {}
    });
  }

  $(window).on('elementor:init', init);
  if (window.elementor) init();

}(jQuery));
