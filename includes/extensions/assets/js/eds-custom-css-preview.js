/**
 * EDS Custom CSS — Live Editor Preview
 *
 * Uses elementorModules.frontend.handlers.Base with getElementSettings()
 * to inject/update <style> tags in real-time as you type in the editor.
 *
 * Loaded only in the preview iframe via elementor/preview/enqueue_scripts.
 *
 * eDesign Space · https://edesignspace.com/
 */
(function () {
  'use strict';

  var CSS_KEY = '_eds_custom_css';

  function injectStyle(el, rawCSS) {
    var id       = 'eds-css-' + (el.getAttribute('data-id') || '');
    var existing = document.getElementById(id);

    if (!rawCSS || !rawCSS.trim()) {
      if (existing) existing.remove();
      return;
    }

    var selector = '.elementor-element.elementor-element-' + (el.getAttribute('data-id') || '');
    var css      = rawCSS.replace(/selector/g, selector);

    if (!existing) {
      existing    = document.createElement('style');
      existing.id = id;
      document.head.appendChild(existing);
    }
    existing.textContent = css;
  }

  function registerHandler() {
    if (typeof elementorModules === 'undefined' || !elementorModules.frontend) return;

    var EdsCSSHandler = elementorModules.frontend.handlers.Base.extend({

      onInit: function () {
        elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
        this.applyCSS();
      },

      onElementChange: function (changedProp) {
        if (changedProp === CSS_KEY) {
          this.applyCSS();
        }
      },

      applyCSS: function () {
        var el     = this.$element[0];
        var s      = this.getElementSettings();
        var rawCSS = s[CSS_KEY] || '';
        injectStyle(el, rawCSS);
      }
    });

    elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($element) {
      elementorFrontend.elementsHandler.addHandler(EdsCSSHandler, { $element: $element });
    });
  }

  if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isInit) {
    registerHandler();
  } else {
    jQuery(window).on('elementor/frontend/init', registerHandler);
  }

}());
