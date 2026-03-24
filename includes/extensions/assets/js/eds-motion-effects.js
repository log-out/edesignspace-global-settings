/**
 * EDS Motion Effects — Frontend + Live Editor Preview
 *
 * Uses elementorModules.frontend.handlers.Base with getElementSettings()
 * which reads directly from the live Elementor backbone model.
 *
 * eDesign Space · https://edesignspace.com/
 */
(function () {
  'use strict';

  var instances  = [];
  var mouseX = 0, mouseY = 0;
  var winW = window.innerWidth, winH = window.innerHeight;
  var raf = null;

  function gs(obj) { return obj && typeof obj === 'object' ? (parseFloat(obj.size) || 0) : (parseFloat(obj) || 0); }

  function adminH() {
    return document.body.classList.contains('admin-bar') ? (window.innerWidth >= 782 ? 32 : 46) : 0;
  }

  /* ─── Global scroll/mouse/resize listeners ──────────────────────────────── */

  function ensureListeners() {
    if (window._edsMotionListeners) return;
    window._edsMotionListeners = true;
    window.addEventListener('scroll',    scheduleUpdate, { passive: true });
    window.addEventListener('mousemove', onMouseMove,    { passive: true });
    window.addEventListener('resize',    onResize,       { passive: true });
  }

  function scheduleUpdate() {
    if (!raf) raf = requestAnimationFrame(function () {
      instances.forEach(function (i) { i.update(); });
      raf = null;
    });
  }

  function onMouseMove(e) { mouseX = e.clientX; mouseY = e.clientY; instances.forEach(function (i) { i.onMouse(); }); }
  function onResize()     { winW = window.innerWidth; winH = window.innerHeight; instances.forEach(function (i) { if (i.s._eds_sticky) i.applySticky(); }); scheduleUpdate(); }

  /* ─── MotionInstance ─────────────────────────────────────────────────────── */

  function MotionInstance(el, s) {
    this.el = el; this.s = s;
    this.isStuck = false; this.placeholder = null;
    if (s._eds_sticky) { this.measureNaturalPos(); this.applySticky(); }
  }

  MotionInstance.prototype.destroy = function () {
    instances = instances.filter(function (i) { return i !== this; }.bind(this));
    this.unstick(); this.resetCssSticky();
    this.el.style.transform = this.el.style.filter = this.el.style.opacity = '';
  };

  MotionInstance.prototype.update = function () {
    var s = this.s, el = this.el;
    if (s._eds_sticky && s._eds_sticky_column !== 'yes') this.updateJsSticky();
    if (s._eds_scroll_fx !== 'yes') return;

    var r = el.getBoundingClientRect();
    var p = Math.max(0, Math.min(1, 1 - ((r.top + r.height) / (winH + r.height))));
    var t = [], f = [];

    if (s._eds_scroll_y_enable === 'yes') { var ys = gs(s._eds_scroll_y_speed)||4, ym = s._eds_scroll_y_dir==='positive'?1:-1; t.push('translateY('+((p-.5)*ys*ym*100).toFixed(2)+'px)'); }
    if (s._eds_scroll_x_enable === 'yes') { var xs = gs(s._eds_scroll_x_speed)||4, xm = s._eds_scroll_x_dir==='negative'?-1:1; t.push('translateX('+((p-.5)*xs*xm*100).toFixed(2)+'px)'); }
    if (s._eds_scroll_opacity_enable === 'yes') { var ol=(gs(s._eds_scroll_opacity_level)||10)/10, op; if(s._eds_scroll_opacity_dir==='in') op=1-(1-p)*ol; else if(s._eds_scroll_opacity_dir==='out') op=1-p*ol; else op=1-Math.abs(p-.5)*2*ol; el.style.opacity=Math.max(0,Math.min(1,op)).toFixed(3); }
    if (s._eds_scroll_blur_enable === 'yes') { var bl=gs(s._eds_scroll_blur_level)||7, bv; if(s._eds_scroll_blur_dir==='in') bv=(1-p)*bl; else if(s._eds_scroll_blur_dir==='out') bv=p*bl; else bv=Math.abs(p-.5)*2*bl; f.push('blur('+Math.max(0,bv).toFixed(2)+'px)'); }
    if (s._eds_scroll_rotate_enable === 'yes') { var rs=gs(s._eds_scroll_rotate_speed)||1, rm=s._eds_scroll_rotate_dir==='negative'?-1:1; t.push('rotate('+(p*rs*rm*360).toFixed(2)+'deg)'); }
    if (s._eds_scroll_scale_enable === 'yes') { var ss=(gs(s._eds_scroll_scale_speed)||4)/10, sv=s._eds_scroll_scale_dir==='out'?1+(1-p)*ss:1+p*ss; t.push('scale('+sv.toFixed(4)+')'); }
    if (t.length) el.style.transform = t.join(' ');
    if (f.length) el.style.filter    = f.join(' ');
  };

  MotionInstance.prototype.onMouse = function () {
    var s = this.s; if (s._eds_mouse_fx !== 'yes') return;
    var r = this.el.getBoundingClientRect(), nx=(mouseX-(r.left+r.width/2))/winW, ny=(mouseY-(r.top+r.height/2))/winH, t=[];
    if (s._eds_mouse_track_enable==='yes') { var ts=gs(s._eds_mouse_track_speed)||1, tm=s._eds_mouse_track_dir==='opposite'?-1:1; t.push('translateX('+(nx*ts*tm*30).toFixed(2)+'px) translateY('+(ny*ts*tm*30).toFixed(2)+'px)'); }
    if (s._eds_mouse_tilt_enable==='yes')  { var ti=gs(s._eds_mouse_tilt_speed)||4, tim=s._eds_mouse_tilt_dir==='opposite'?-1:1; t.push('perspective(1000px) rotateX('+(ny*ti*tim*-10).toFixed(2)+'deg) rotateY('+(nx*ti*tim*10).toFixed(2)+'deg)'); }
    if (t.length) { this.el.style.transform=t.join(' '); this.el.style.transition='transform 0.1s ease-out'; }
  };

  MotionInstance.prototype.deviceEnabled = function () {
    var d=this.s._eds_sticky_on||['desktop','tablet'], dev=winW<=767?'mobile':winW<=1024?'tablet':'desktop';
    return d.indexOf(dev) !== -1;
  };
  MotionInstance.prototype.applySticky = function () {
    var s=this.s, el=this.el; if (!this.deviceEnabled()) { this.resetCssSticky(); return; }
    if (s._eds_sticky_column==='yes') { el.style.position='sticky'; el.style[s._eds_sticky]=gs(s._eds_sticky_offset)+'px'; el.style.zIndex='100'; el.style[s._eds_sticky==='top'?'bottom':'top']=''; }
  };
  MotionInstance.prototype.resetCssSticky = function () { var el=this.el; el.style.position=el.style.top=el.style.bottom=el.style.zIndex=''; };
  MotionInstance.prototype.measureNaturalPos = function () { var r=this.el.getBoundingClientRect(), sy=window.scrollY||window.pageYOffset; this.naturalTop=r.top+sy; this.naturalWidth=r.width; this.naturalHeight=r.height; };
  MotionInstance.prototype.updateJsSticky = function () {
    var s=this.s; if (!this.deviceEnabled()) { this.unstick(); return; }
    var pos=s._eds_sticky, off=gs(s._eds_sticky_offset), sy=window.scrollY||window.pageYOffset;
    if (pos==='top') { if (sy>=this.naturalTop-off) this.stick(off+adminH(),null); else this.unstick(); }
    else { if (sy<=this.naturalTop+this.naturalHeight-winH+off) this.stick(null,off); else this.unstick(); }
  };
  MotionInstance.prototype.stick = function (top, bottom) {
    if (this.isStuck) return; this.isStuck=true; var el=this.el;
    if (!this.placeholder) { var ph=document.createElement('div'); ph.style.cssText='display:block;width:'+this.naturalWidth+'px;height:'+this.naturalHeight+'px;pointer-events:none;visibility:hidden;'; ph.setAttribute('aria-hidden','true'); el.parentNode.insertBefore(ph,el); this.placeholder=ph; }
    el.style.position='fixed'; el.style.width=this.naturalWidth+'px'; el.style.top=top!==null?top+'px':''; el.style.bottom=bottom!==null?bottom+'px':''; el.style.left=this.el.getBoundingClientRect().left+'px'; el.style.zIndex='9999';
  };
  MotionInstance.prototype.unstick = function () {
    if (!this.isStuck) return; this.isStuck=false; var el=this.el;
    el.style.position=el.style.width=el.style.top=el.style.bottom=el.style.left=el.style.zIndex='';
    if (this.placeholder&&this.placeholder.parentNode) { this.placeholder.parentNode.removeChild(this.placeholder); this.placeholder=null; }
  };

  /* ─── Elementor Handler (official API) ──────────────────────────────────── */

  function registerHandler() {
    if (typeof elementorModules === 'undefined' || !elementorModules.frontend) return;

    var EdsMotionHandler = elementorModules.frontend.handlers.Base.extend({

      onInit: function () {
        elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
        this.bootMotion();
      },

      // Fires in the EDITOR whenever any setting on this element changes.
      onElementChange: function (changedProp) {
        var motionProps = [
          '_eds_scroll_fx','_eds_scroll_y_enable','_eds_scroll_y_dir','_eds_scroll_y_speed','_eds_scroll_y_viewport_enter','_eds_scroll_y_viewport_leave',
          '_eds_scroll_x_enable','_eds_scroll_x_dir','_eds_scroll_x_speed',
          '_eds_scroll_opacity_enable','_eds_scroll_opacity_dir','_eds_scroll_opacity_level',
          '_eds_scroll_blur_enable','_eds_scroll_blur_dir','_eds_scroll_blur_level',
          '_eds_scroll_rotate_enable','_eds_scroll_rotate_dir','_eds_scroll_rotate_speed',
          '_eds_scroll_scale_enable','_eds_scroll_scale_dir','_eds_scroll_scale_speed',
          '_eds_mouse_fx','_eds_mouse_track_enable','_eds_mouse_track_dir','_eds_mouse_track_speed',
          '_eds_mouse_tilt_enable','_eds_mouse_tilt_dir','_eds_mouse_tilt_speed',
          '_eds_sticky','_eds_sticky_offset','_eds_sticky_column','_eds_sticky_on'
        ];
        if (motionProps.indexOf(changedProp) !== -1) {
          this.bootMotion();
        }
      },

      bootMotion: function () {
        var el  = this.$element[0];
        var s   = this.getElementSettings(); // Live settings from backbone model

        // Destroy existing instance
        if (el._edsMotion) { el._edsMotion.destroy(); delete el._edsMotion; }

        var hasMotion = s._eds_scroll_fx === 'yes' || s._eds_mouse_fx === 'yes' || (s._eds_sticky && s._eds_sticky !== '');
        if (!hasMotion) return;

        var inst = new MotionInstance(el, s);
        el._edsMotion = inst;
        instances.push(inst);
        ensureListeners();
        scheduleUpdate();
      }
    });

    // Register for ALL element types (global)
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($element) {
      elementorFrontend.elementsHandler.addHandler(EdsMotionHandler, { $element: $element });
    });
  }

  // Register immediately if elementorFrontend is ready, else wait for init
  if (typeof elementorFrontend !== 'undefined' && elementorFrontend.isInit) {
    registerHandler();
  } else {
    jQuery(window).on('elementor/frontend/init', registerHandler);
  }

}());
