/**
 * EDS Motion Effects — Frontend + Preview Engine
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  var instances = [], mouseX = 0, mouseY = 0;
  var winW = window.innerWidth, winH = window.innerHeight;
  var raf = null;

  function getCfg(el) {
    try { return JSON.parse(el.getAttribute('data-eml') || ''); } catch(e) { return null; }
  }
  function adminH() { return document.body.classList.contains('admin-bar') ? (window.innerWidth >= 782 ? 32 : 46) : 0; }

  /* ── Boot one element ──────────────────────────────────────────────────── */
  function boot(el) {
    var cfg = getCfg(el);
    if (!cfg || !Object.keys(cfg).length) {
      // If previously had effects, clean up
      if (el._edsM) { el._edsM.destroy(); instances = instances.filter(function(i){return i.el!==el;}); delete el._edsM; }
      return;
    }
    if (el._edsM) { el._edsM.destroy(); instances = instances.filter(function(i){return i.el!==el;}); }
    var inst = new M(el, cfg);
    el._edsM = inst;
    instances.push(inst);
  }

  function init() {
    document.querySelectorAll('[data-eml]').forEach(boot);
    startListeners();
    tick();
  }

  function startListeners() {
    if (window._edsMList) return;
    window._edsMList = true;
    window.addEventListener('scroll',    tick,     { passive: true });
    window.addEventListener('mousemove', onMouse,  { passive: true });
    window.addEventListener('resize',    onResize, { passive: true });
  }

  function tick() {
    if (!raf) raf = requestAnimationFrame(function(){ instances.forEach(function(i){i.update();}); raf=null; });
  }
  function onMouse(e) { mouseX=e.clientX; mouseY=e.clientY; instances.forEach(function(i){i.mouse();}); }
  function onResize()  { winW=window.innerWidth; winH=window.innerHeight; instances.forEach(function(i){if(i.c.st)i.applySticky();}); tick(); }

  /* ── MutationObserver ──────────────────────────────────────────────────── */
  function observe() {
    if (typeof MutationObserver === 'undefined') return;
    var root = document.body;
    new MutationObserver(function(muts) {
      muts.forEach(function(m) {
        if (m.type==='attributes' && m.attributeName==='data-eml') {
          boot(m.target); startListeners(); tick();
        }
        if (m.type==='childList') {
          m.addedNodes.forEach(function(n) {
            if (n.nodeType!==1) return;
            if (n.hasAttribute('data-eml')) { boot(n); startListeners(); tick(); }
            n.querySelectorAll('[data-eml]').forEach(function(el){ boot(el); startListeners(); tick(); });
          });
        }
      });
    }).observe(root, { attributes:true, attributeFilter:['data-eml'], subtree:true, childList:true });
  }

  /* ── Elementor frontend hook — fires in preview iframe after each render ── */
  function registerHook() {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
      var el = $scope[0];
      if (!el) return;
      if (el.hasAttribute('data-eml')) { boot(el); startListeners(); tick(); }
      el.querySelectorAll('[data-eml]').forEach(function(child){ boot(child); startListeners(); tick(); });
    });
  }

  /* ── Init ──────────────────────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ init(); observe(); });
  } else {
    init(); observe();
  }

  // Elementor fires this via jQuery — must use jQuery(window).on()
  $(window).on('elementor/frontend/init', registerHook);

  /* ── MotionInstance ────────────────────────────────────────────────────── */
  function M(el, cfg) {
    this.el=el; this.c=cfg; this.isStuck=false; this.ph=null;
    if (cfg.st) { this.measureNat(); this.applySticky(); }
  }
  M.prototype.destroy = function() { this.unstick(); this.resetSticky(); this.el.style.transform=this.el.style.filter=this.el.style.opacity=''; };
  M.prototype.update = function() {
    var c=this.c, el=this.el;
    if (c.st && !c.sc) this.updateJsSticky();
    if (!c.sx) return;
    var rect=el.getBoundingClientRect(), p=Math.max(0,Math.min(1,1-((rect.top+rect.height)/(winH+rect.height))));
    var t=[], f=[];
    if (c.sy) { var ym=c.sy.d==='positive'?1:-1; t.push('translateY('+((p-.5)*c.sy.s*ym*100).toFixed(2)+'px)'); }
    if (c.sX) { var xm=c.sX.d==='negative'?-1:1; t.push('translateX('+((p-.5)*c.sX.s*xm*100).toFixed(2)+'px)'); }
    if (c.so) { var op; if(c.so.d==='in') op=1-(1-p)*(c.so.l/10); else if(c.so.d==='out') op=1-p*(c.so.l/10); else op=1-Math.abs(p-.5)*2*(c.so.l/10); el.style.opacity=Math.max(0,Math.min(1,op)).toFixed(3); }
    if (c.sb) { var bv; if(c.sb.d==='in') bv=(1-p)*c.sb.l; else if(c.sb.d==='out') bv=p*c.sb.l; else bv=Math.abs(p-.5)*2*c.sb.l; f.push('blur('+Math.max(0,bv).toFixed(2)+'px)'); }
    if (c.sr) { var rm=c.sr.d==='negative'?-1:1; t.push('rotate('+(p*c.sr.s*rm*360).toFixed(2)+'deg)'); }
    if (c.ss) { var sv=c.ss.d==='out'?1+(1-p)*(c.ss.s/10):1+p*(c.ss.s/10); t.push('scale('+sv.toFixed(4)+')'); }
    if (t.length) el.style.transform=t.join(' ');
    if (f.length) el.style.filter=f.join(' ');
  };
  M.prototype.mouse = function() {
    var c=this.c; if (!c.mx) return;
    var rect=this.el.getBoundingClientRect(), nx=(mouseX-(rect.left+rect.width/2))/winW, ny=(mouseY-(rect.top+rect.height/2))/winH, t=[];
    if (c.mt) { var tm=c.mt.d==='opposite'?-1:1; t.push('translateX('+(nx*c.mt.s*tm*30).toFixed(2)+'px) translateY('+(ny*c.mt.s*tm*30).toFixed(2)+'px)'); }
    if (c.mi) { var tim=c.mi.d==='opposite'?-1:1; t.push('perspective(1000px) rotateX('+(ny*c.mi.s*tim*-10).toFixed(2)+'deg) rotateY('+(nx*c.mi.s*tim*10).toFixed(2)+'deg)'); }
    if (t.length) { this.el.style.transform=t.join(' '); this.el.style.transition='transform 0.1s ease-out'; }
  };
  M.prototype.devOk = function() { var d=this.c.son||['desktop','tablet'],dev=winW<=767?'mobile':winW<=1024?'tablet':'desktop'; return d.indexOf(dev)!==-1; };
  M.prototype.applySticky = function() { var c=this.c,el=this.el; if(!this.devOk()){this.resetSticky();return;} if(c.sc){el.style.position='sticky';el.style[c.st]=(c.so_off||0)+'px';el.style.zIndex='100';el.style[c.st==='top'?'bottom':'top']='';} };
  M.prototype.resetSticky = function() { var el=this.el; el.style.position=el.style.top=el.style.bottom=el.style.zIndex=''; };
  M.prototype.measureNat = function() { var r=this.el.getBoundingClientRect(),sy=window.scrollY||window.pageYOffset; this.nTop=r.top+sy;this.nW=r.width;this.nH=r.height; };
  M.prototype.updateJsSticky = function() { var c=this.c; if(!this.devOk()){this.unstick();return;} var sy=window.scrollY||window.pageYOffset,off=c.so_off||0; if(c.st==='top'){if(sy>=this.nTop-off)this.stick(off+adminH(),null);else this.unstick();}else{if(sy<=this.nTop+this.nH-winH+off)this.stick(null,off);else this.unstick();} };
  M.prototype.stick = function(top,bot) { if(this.isStuck)return; this.isStuck=true; var el=this.el; if(!this.ph){var ph=document.createElement('div');ph.style.cssText='display:block;width:'+this.nW+'px;height:'+this.nH+'px;pointer-events:none;visibility:hidden;';ph.setAttribute('aria-hidden','true');el.parentNode.insertBefore(ph,el);this.ph=ph;} el.style.position='fixed';el.style.width=this.nW+'px';el.style.top=top!==null?top+'px':'';el.style.bottom=bot!==null?bot+'px':'';el.style.left=this.el.getBoundingClientRect().left+'px';el.style.zIndex='9999'; };
  M.prototype.unstick = function() { if(!this.isStuck)return; this.isStuck=false; var el=this.el; el.style.position=el.style.width=el.style.top=el.style.bottom=el.style.left=el.style.zIndex=''; if(this.ph&&this.ph.parentNode){this.ph.parentNode.removeChild(this.ph);this.ph=null;} };

}(jQuery));
