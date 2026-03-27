/**
 * EDS Loop Animations — Frontend + Preview
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  function getCfg(el) {
    try { return JSON.parse(el.getAttribute('data-elp') || ''); } catch(e) { return null; }
  }

  function applyLoop(el) {
    var cfg = getCfg(el);
    if (el._edsLoopRandom) el._edsLoopRandom = false;
    el.style.transform = '';
    // Remove all previous loop classes
    var rem = [];
    el.classList.forEach(function(c){ if(c==='eds-loop-el'||c==='eds-loop-hover-pause'||c.indexOf('eds-loop--')===0||c.indexOf('eds-loop-i--')===0) rem.push(c); });
    rem.forEach(function(c){el.classList.remove(c);});
    if (!cfg || !cfg.a) return;
    el.classList.add('eds-loop-el', 'eds-loop--'+cfg.a, 'eds-loop-i--'+(cfg.i||'medium'));
    if (cfg.h) el.classList.add('eds-loop-hover-pause');
    if (cfg.a === 'random') runRandom(el, cfg.i||'medium', !!cfg.h);
  }

  function runRandom(el, intensity, pauseOnHover) {
    var range = intensity==='low'?12:intensity==='high'?40:24;
    el._edsLoopRandom = true;
    var fromX=0,fromY=0,tX=0,tY=0,start=null,paused=false;
    function next(){tX=(Math.random()-.5)*2*range;tY=(Math.random()-.5)*2*range;}
    next();
    if (pauseOnHover) {
      el.addEventListener('mouseenter',function(){paused=true;});
      el.addEventListener('mouseleave',function(){paused=false;});
    }
    (function step(ts){
      if (!el._edsLoopRandom) return;
      if (!start) start=ts;
      if (!paused){var p=Math.min((ts-start)/2000,1),t=p<.5?4*p*p*p:1-Math.pow(-2*p+2,3)/2;el.style.transform='translate('+(fromX+(tX-fromX)*t).toFixed(2)+'px,'+(fromY+(tY-fromY)*t).toFixed(2)+'px)';if(p>=1){fromX=tX;fromY=tY;start=null;next();}}else{start=null;}
      requestAnimationFrame(step);
    })(performance.now());
  }

  window.edsLoopRandom = runRandom;

  function init() {
    document.querySelectorAll('[data-elp]').forEach(applyLoop);
  }

  /* ── MutationObserver ──────────────────────────────────────────────────── */
  function observe() {
    if (typeof MutationObserver==='undefined') return;
    new MutationObserver(function(muts){
      muts.forEach(function(m){
        if (m.type==='attributes'&&m.attributeName==='data-elp') applyLoop(m.target);
        if (m.type==='childList') m.addedNodes.forEach(function(n){
          if (n.nodeType!==1) return;
          if (n.hasAttribute('data-elp')) applyLoop(n);
          n.querySelectorAll('[data-elp]').forEach(applyLoop);
        });
      });
    }).observe(document.body, {attributes:true, attributeFilter:['data-elp'], subtree:true, childList:true});
  }

  /* ── Elementor hook — jQuery style ────────────────────────────────────── */
  function registerHook() {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
      var el = $scope[0];
      if (!el) return;
      if (el.hasAttribute('data-elp')) applyLoop(el);
      el.querySelectorAll('[data-elp]').forEach(applyLoop);
    });
  }

  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',function(){init();observe();});
  else { init(); observe(); }

  $(window).on('elementor/frontend/init', registerHook);

}(jQuery));
