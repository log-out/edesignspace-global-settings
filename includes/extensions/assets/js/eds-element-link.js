/**
 * EDS Element Link — Frontend + Preview
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  function bind(el) {
    var url     = el.getAttribute('data-eds-link') || '';
    var isExt   = el.getAttribute('data-eds-link-ext') === '1';
    var noFollow = el.getAttribute('data-eds-link-nofollow') === '1';
    if (el._edsLinkHandler) { el.removeEventListener('click', el._edsLinkHandler); delete el._edsLinkHandler; }
    if (!url) return;
    var handler = function(e) {
      var t=e.target;
      while(t&&t!==el){var tag=(t.tagName||'').toLowerCase();if(tag==='a'||tag==='button'||tag==='input'||tag==='select'||tag==='textarea')return;t=t.parentElement;}
      if (isExt){var a=document.createElement('a');a.href=url;a.target='_blank';a.rel=noFollow?'nofollow noopener noreferrer':'noopener noreferrer';a.click();}
      else{window.location.href=url;}
    };
    el._edsLinkHandler=handler;
    el.addEventListener('click',handler);
    if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex','0');
    if (!el._edsLinkKey){el._edsLinkKey=function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();el.click();}};el.addEventListener('keydown',el._edsLinkKey);}
    el.setAttribute('role','link');
  }

  function init() { document.querySelectorAll('[data-eds-link]').forEach(bind); }

  function observe() {
    if (typeof MutationObserver==='undefined') return;
    new MutationObserver(function(muts){
      muts.forEach(function(m){
        if (m.type==='attributes'&&m.attributeName==='data-eds-link') bind(m.target);
        if (m.type==='childList') m.addedNodes.forEach(function(n){
          if (n.nodeType!==1) return;
          if (n.hasAttribute('data-eds-link')) bind(n);
          n.querySelectorAll('[data-eds-link]').forEach(bind);
        });
      });
    }).observe(document.body, {attributes:true, attributeFilter:['data-eds-link'], subtree:true, childList:true});
  }

  function registerHook() {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
      var el=$scope[0];
      if (!el) return;
      if (el.hasAttribute('data-eds-link')) bind(el);
      el.querySelectorAll('[data-eds-link]').forEach(bind);
    });
  }

  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',function(){init();observe();});
  else { init(); observe(); }

  $(window).on('elementor/frontend/init', registerHook);

}(jQuery));
