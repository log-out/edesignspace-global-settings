/**
 * EDS Loop Animations — Editor Panel JS
 *
 * Runs in the EDITOR PANEL (not the preview iframe).
 * Listens to elementor.channels.editor for control changes and directly
 * applies/removes loop animation classes on the preview iframe element.
 *
 * This is the reliable approach for all widget types — it doesn't depend on
 * data-settings mutation timing which varies between widget types.
 *
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  var CONTROLS = {
    animation : '_eds_loop_animation',
    intensity  : '_eds_loop_intensity',
    hover      : '_eds_loop_hover_pause',
  };

  var PREFIX_ANIM = 'eds-loop--';
  var PREFIX_INT  = 'eds-loop-i--';

  /**
   * Get the preview iframe's document.
   */
  function getPreviewDoc() {
    var frame = elementor.$preview && elementor.$preview[0];
    if (!frame) return null;
    return frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
  }

  /**
   * Find the DOM element in the preview iframe for a given Elementor element model.
   */
  function getPreviewEl(elementModel) {
    var doc = getPreviewDoc();
    if (!doc || !elementModel) return null;
    var id = elementModel.get('id');
    return doc.querySelector('.elementor-element.elementor-element-' + id);
  }

  /**
   * Apply (or clear) loop animation classes on a preview DOM element.
   */
  function applyClasses(domEl, anim, intensity, hover) {
    if (!domEl) return;

    // Remove all previous loop classes
    var toRemove = [];
    domEl.classList.forEach(function (c) {
      if (c === 'eds-loop-el' || c === 'eds-loop-hover-pause'
          || c.indexOf(PREFIX_ANIM) === 0 || c.indexOf(PREFIX_INT) === 0) {
        toRemove.push(c);
      }
    });
    toRemove.forEach(function (c) { domEl.classList.remove(c); });
    domEl.style.transform = '';

    // Stop any random animation
    if (domEl._edsLoopRandom) domEl._edsLoopRandom = false;

    if (!anim) return;

    intensity = intensity || 'medium';
    domEl.classList.add('eds-loop-el', PREFIX_ANIM + anim, PREFIX_INT + intensity);
    if (hover === 'yes') domEl.classList.add('eds-loop-hover-pause');

    // Random Move
    if (anim === 'random') {
      var doc = getPreviewDoc();
      if (doc && doc.defaultView && doc.defaultView.edsLoopRandom) {
        doc.defaultView.edsLoopRandom(domEl, intensity, hover === 'yes');
      }
    }
  }

  /**
   * Read current loop settings from an element model.
   */
  function getLoopSettings(model) {
    return {
      anim      : model.getSetting(CONTROLS.animation)  || '',
      intensity : model.getSetting(CONTROLS.intensity)  || 'medium',
      hover     : model.getSetting(CONTROLS.hover)      || '',
    };
  }

  function init() {
    if (typeof elementor === 'undefined' || !elementor.channels) return;

    // Fire when any control value changes in the editor panel.
    elementor.channels.editor.on('change', function (view) {
      try {
        var controlName = view.options && view.options.elementSettingKey;
        // Only care about our three loop controls
        var ourControls = [CONTROLS.animation, CONTROLS.intensity, CONTROLS.hover];
        if (ourControls.indexOf(controlName) === -1) return;

        // Get the element model currently being edited
        var elementModel = elementor.selection.getElements && elementor.selection.getElements()[0];
        if (!elementModel) return;

        var s     = getLoopSettings(elementModel);
        var domEl = getPreviewEl(elementModel);
        applyClasses(domEl, s.anim, s.intensity, s.hover);
      } catch (e) { /* silently fail — never break editor */ }
    });

    // Also re-apply when the panel opens/switches to a different element,
    // so existing animations show immediately when you click an element.
    elementor.channels.editor.on('editor:opened', function () {
      try {
        var elementModel = elementor.selection.getElements && elementor.selection.getElements()[0];
        if (!elementModel) return;
        var s = getLoopSettings(elementModel);
        if (!s.anim) return;
        var domEl = getPreviewEl(elementModel);
        applyClasses(domEl, s.anim, s.intensity, s.hover);
      } catch (e) {}
    });
  }

  $(window).on('elementor:init', init);
  if (window.elementor) init();

}(jQuery));
