/* BEKUKU transactions menu scripts */

/* SOURCE: adminlte.min.js */

/*!
 * AdminLTE v4.9.1 (https://adminlte.io)
 * Copyright 2014-2026 Colorlib <https://colorlib.com>
 * Licensed under MIT (https://github.com/ColorlibHQ/AdminLTE/blob/master/LICENSE)
 */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?t(exports):"function"==typeof define&&define.amd?define(["exports"],t):t((e="undefined"!=typeof globalThis?globalThis:e||self).adminlte={})}(this,function(e){"use strict";const t=[],n={controller:new AbortController,hasInitialized:!1,isReplaying:!1},s=()=>n.controller.signal,i=()=>{if(!n.hasInitialized){n.hasInitialized=!0,n.isReplaying=!0;try{for(const e of t)e()}finally{n.isReplaying=!1}}},a=e=>{t.push(e),n.hasInitialized&&e()},o=()=>{n.controller.abort(),n.controller=new AbortController,n.hasInitialized=!1},r=()=>{n.isReplaying||(o(),i())};"loading"===document.readyState?document.addEventListener("DOMContentLoaded",r,{once:!0}):i(),document.addEventListener("turbo:before-render",o),document.addEventListener("turbo:load",i);const c=new WeakMap,l=e=>{const t=c.get(e)??[];for(const e of t)globalThis.clearTimeout(e);c.delete(e)},d=e=>{for(const t of["height","padding-top","padding-bottom","margin-top","margin-bottom","overflow","transition-duration","transition-property"])e.style.removeProperty(t)},u=(e,t=500)=>{if(l(e),t<=1)return e.style.display="none",void d(e);e.style.transitionProperty="height, margin, padding",e.style.transitionDuration=`${t}ms`,e.style.boxSizing="border-box",e.style.height=`${e.offsetHeight}px`,e.style.overflow="hidden";const n=globalThis.setTimeout(()=>{e.style.height="0",e.style.paddingTop="0",e.style.paddingBottom="0",e.style.marginTop="0",e.style.marginBottom="0"},1),s=globalThis.setTimeout(()=>{e.style.display="none",d(e),c.delete(e)},t);c.set(e,[n,s])},h=(e,t=500)=>{l(e),d(e),e.style.removeProperty("display");let{display:n}=globalThis.getComputedStyle(e);if("none"===n&&(n="block"),e.style.display=n,t<=1)return;const s=e.offsetHeight;e.style.overflow="hidden",e.style.height="0",e.style.paddingTop="0",e.style.paddingBottom="0",e.style.marginTop="0",e.style.marginBottom="0";const i=globalThis.setTimeout(()=>{e.style.boxSizing="border-box",e.style.transitionProperty="height, margin, padding",e.style.transitionDuration=`${t}ms`,e.style.height=`${s}px`,e.style.removeProperty("padding-top"),e.style.removeProperty("padding-bottom"),e.style.removeProperty("margin-top"),e.style.removeProperty("margin-bottom")},1),a=globalThis.setTimeout(()=>{d(e),c.delete(e)},t);c.set(e,[i,a])},m="hold-transition";class p{_element;_holdTransitionTimer;constructor(e){this._element=e,this._holdTransitionTimer=void 0}holdTransition(e=100){this._holdTransitionTimer&&clearTimeout(this._holdTransitionTimer),document.body.classList.add(m),this._holdTransitionTimer=setTimeout(()=>{document.body.classList.remove(m)},e)}}a(()=>{const e=new p(document.body);window.addEventListener("resize",()=>e.holdTransition(200),{signal:s()}),setTimeout(()=>{document.body.classList.add("app-loaded")},400)});const g=new WeakMap;class f{static get NAME(){throw new Error("Component subclasses must override the static NAME getter.")}static get DATA_KEY(){return`lte.${this.NAME}`}static _getInstance(e){return e?g.get(e)?.get(this.DATA_KEY)??null:null}_element;constructor(e){this._element=e;const t=g.get(e)??new Map;g.set(e,t),t.set(this.constructor.DATA_KEY,this)}dispose(){const e=g.get(this._element);e?.delete(this.constructor.DATA_KEY),0===e?.size&&g.delete(this._element)}}const b=(e,t,n={})=>{const s=new CustomEvent(t,{bubbles:!0,cancelable:n.cancelable??!1,detail:n.detail});return e.dispatchEvent(s),s},y="card-widget",v=`.lte.${y}`,_=`collapse${v}`,S=`expand${v}`,E=`remove${v}`,L=`collapsed${v}`,A=`expanded${v}`,T=`removed${v}`,k=`maximized${v}`,w=`minimized${v}`,x="card",q="collapsed-card",M="collapsing-card",$="expanding-card",I="was-collapsed",C="maximized-card",N='[data-lte-toggle="card-remove"]',P='[data-lte-toggle="card-collapse"]',R='[data-lte-toggle="card-maximize"]',z=`.${x}`,B=".card-body",D=".card-footer",O={animationSpeed:500,collapseTrigger:P,removeTrigger:N,maximizeTrigger:R};class F extends f{static get NAME(){return y}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e,t={}){return this.getInstance(e)??new this(e,t)}_parent;_config;constructor(e,t={}){super(e),this._parent=e.closest(z),e.classList.contains(x)&&(this._parent=e),this._config={...O,...t}}collapse(){this._parent&&(b(this._parent,_,{cancelable:!0}).defaultPrevented||(this._parent.classList.add(M),this._parent.classList.remove($),this._parent.querySelectorAll(`:scope > ${B}, :scope > ${D}`).forEach(e=>{e instanceof HTMLElement&&u(e,this._config.animationSpeed)}),setTimeout(()=>{this._parent?.classList.contains(M)&&(this._parent.classList.add(q),this._parent.classList.remove(M),b(this._parent,L))},this._config.animationSpeed)))}expand(){this._parent&&(b(this._parent,S,{cancelable:!0}).defaultPrevented||(this._parent.classList.add($),this._parent.classList.remove(M,q),this._parent.querySelectorAll(`:scope > ${B}, :scope > ${D}`).forEach(e=>{e instanceof HTMLElement&&h(e,this._config.animationSpeed)}),setTimeout(()=>{this._parent?.classList.contains($)&&(this._parent.classList.remove($),b(this._parent,A))},this._config.animationSpeed)))}remove(){if(!this._parent)return;if(b(this._parent,E,{cancelable:!0}).defaultPrevented)return;const e=this._parent;u(e,this._config.animationSpeed),setTimeout(()=>{b(e,T),e.remove(),this.dispose()},this._config.animationSpeed)}toggle(){this._parent?.classList.contains(q)||this._parent?.classList.contains(M)?this.expand():this.collapse()}maximize(){this._parent&&(this._parent.style.height=`${this._parent.offsetHeight}px`,this._parent.style.width=`${this._parent.offsetWidth}px`,this._parent.style.transition="all .15s",setTimeout(()=>{const e=document.querySelector("html");e&&e.classList.add(C),this._parent&&(this._parent.classList.add(C),this._parent.classList.contains(q)&&this._parent.classList.add(I),b(this._parent,k))},150))}minimize(){this._parent&&(this._parent.style.height="auto",this._parent.style.width="auto",this._parent.style.transition="all .15s",setTimeout(()=>{const e=document.querySelector("html");e&&e.classList.remove(C),this._parent&&(this._parent.classList.remove(C),this._parent?.classList.contains(I)&&this._parent.classList.remove(I),b(this._parent,w),setTimeout(()=>{this._parent?.style.removeProperty("height"),this._parent?.style.removeProperty("width"),this._parent?.style.removeProperty("transition")},150))},10))}toggleMaximize(){this._parent?.classList.contains(C)?this.minimize():this.maximize()}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element))return;const n=t.closest(P),s=t.closest(N),i=t.closest(R),a=n??s??i;if(!a)return;e.preventDefault();const o=a.closest(z);if(!o)return;const r=F.getOrCreateInstance(o);n?r.toggle():s?r.remove():r.toggleMaximize()});const H="treeview",K=`.lte.${H}`,W=`expand${K}`,Y=`collapse${K}`,j=`expanded${K}`,U=`collapsed${K}`,V=`load${K}`,G="menu-open",J=".nav-item",Q=".nav-link",X=".nav-treeview",Z='[data-lte-toggle="treeview"]',ee={animationSpeed:300,accordion:!0},te=(e,t)=>{const n=e.querySelector(`:scope > ${Q}`);n?.setAttribute("aria-expanded",String(t))};class ne extends f{static get NAME(){return H}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e,t={}){return this.getInstance(e)??new this(e,t)}_config;constructor(e,t={}){super(e),this._config={...ee,...t}}open(){if(b(this._element,W,{cancelable:!0}).defaultPrevented)return;if(this._config.accordion){const e=this._element.parentElement?.querySelectorAll(`${J}.${G}`);e?.forEach(e=>{if(!this._element.contains(e)){e.classList.remove(G),te(e,!1);const t=e?.querySelector(X);t&&u(t,this._config.animationSpeed)}})}this._element.classList.add(G),te(this._element,!0);const e=this._element.querySelector(X);e&&h(e,this._config.animationSpeed),setTimeout(()=>{this._element.classList.contains(G)&&b(this._element,j)},this._config.animationSpeed)}close(){if(b(this._element,Y,{cancelable:!0}).defaultPrevented)return;this._element.classList.remove(G),te(this._element,!1);const e=this._element.querySelector(X);e&&u(e,this._config.animationSpeed),setTimeout(()=>{this._element.classList.contains(G)||b(this._element,U)},this._config.animationSpeed)}toggle(){this._element.classList.contains(G)?this.close():this.open()}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element))return;const n=t.closest(Z);if(!n)return;const s=t.closest(J),i=t.closest(Q);if(!s?.querySelector(X))return;"#"!==t.getAttribute("href")&&"#"!==i?.getAttribute("href")||e.preventDefault();const a=n.dataset.accordion,o=n.dataset.animationSpeed,r={accordion:void 0===a?ee.accordion:"true"===a,animationSpeed:void 0===o?ee.animationSpeed:Number(o)};ne.getOrCreateInstance(s,r).toggle()}),a(()=>{document.querySelectorAll(`${J}.${G}`).forEach(e=>{const t=e.querySelector(X);if(t){h(t,0);const n=new Event(V);e.dispatchEvent(n)}}),document.querySelectorAll(Z).forEach(e=>{e.querySelectorAll(J).forEach(e=>{e.querySelector(`:scope > ${X}`)&&te(e,e.classList.contains(G))})})});const se="direct-chat",ie=`.lte.${se}`,ae=`expanded${ie}`,oe=`collapsed${ie}`,re="direct-chat-contacts-open";class ce extends f{static get NAME(){return se}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e){return this.getInstance(e)??new this(e)}toggle(){this._element.classList.contains(re)?(this._element.classList.remove(re),b(this._element,oe)):(this._element.classList.add(re),b(this._element,ae))}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element))return;const n=t.closest('[data-lte-toggle="chat-pane"]');if(!n)return;e.preventDefault();const s=n.closest(".direct-chat");s&&ce.getOrCreateInstance(s).toggle()});const le="fullscreen",de=`.lte.${le}`,ue=`maximized${de}`,he=`minimized${de}`,me='[data-lte-toggle="fullscreen"]';function pe(){const e=document.querySelector('[data-lte-icon="maximize"]'),t=document.querySelector('[data-lte-icon="minimize"]'),n=Boolean(document.fullscreenElement);e?.classList.toggle("d-none",n),t?.classList.toggle("d-none",!n);const s=n?ue:he;document.querySelectorAll(me).forEach(e=>{b(e,s)})}class ge extends f{static get NAME(){return le}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e){return this.getInstance(e)??new this(e)}inFullScreen(){document.documentElement.requestFullscreen().catch(()=>{})}outFullscreen(){document.exitFullscreen().catch(()=>{})}toggleFullScreen(){document.fullscreenEnabled&&(document.fullscreenElement?this.outFullscreen():this.inFullScreen())}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element))return;const n=t.closest(me);n&&(e.preventDefault(),ge.getOrCreateInstance(n).toggleFullScreen())}),a(()=>{document.addEventListener("fullscreenchange",pe,{signal:s()})});const fe="push-menu",be=`.lte.${fe}`,ye=`open${be}`,ve=`collapse${be}`,_e=`opened${be}`,Se=`collapsed${be}`,Ee="sidebar-overlay",Le="sidebar-collapse",Ae="sidebar-open",Te=".app-sidebar",ke="lte.sidebar.state",we={sidebarBreakpoint:991.98,enablePersistence:!1};class xe extends f{static get NAME(){return fe}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e,t={}){return this.getInstance(e)??new this(e,t)}_config;constructor(e,t={}){super(e),this._config={...we,...t}}isCollapsed(){return document.body.classList.contains(Le)}isExplicitlyOpen(){return document.body.classList.contains(Ae)}isMiniMode(){return document.body.classList.contains("sidebar-mini")}isMobileSize(){return globalThis.innerWidth<=this._config.sidebarBreakpoint}expand(){b(this._element,ye,{cancelable:!0}).defaultPrevented||(document.body.classList.remove(Le),this.isMobileSize()&&document.body.classList.add(Ae),b(this._element,_e))}collapse(){b(this._element,ve,{cancelable:!0}).defaultPrevented||(document.body.classList.remove(Ae),document.body.classList.add(Le),b(this._element,Se))}toggle(){const e=this.isCollapsed();e?this.expand():this.collapse(),this._config.enablePersistence&&this.saveSidebarState(e?Ae:Le)}setupSidebarBreakPoint(){const e=document.querySelector('[class*="sidebar-expand"]');if(!e)return;const t=globalThis.getComputedStyle(e,"::before").getPropertyValue("content");if(!t||"none"===t)return;const n=Number(t.replace(/[^\d.-]/g,""));Number.isNaN(n)||(this._config={...this._config,sidebarBreakpoint:n})}updateStateByResponsiveLogic(){this.isMobileSize()?this.isExplicitlyOpen()||this.collapse():this.isMiniMode()&&this.isCollapsed()||this.expand()}saveSidebarState(e){if(void 0!==globalThis.localStorage)try{localStorage.setItem(ke,e)}catch{}}loadSidebarState(){if(void 0!==globalThis.localStorage)try{const e=localStorage.getItem(ke);e===Le?this.collapse():e===Ae?this.expand():this.updateStateByResponsiveLogic()}catch{this.updateStateByResponsiveLogic()}}clearSidebarState(){if(void 0!==globalThis.localStorage)try{localStorage.removeItem(ke)}catch{}}init(){this.setupSidebarBreakPoint(),this._config.enablePersistence||this.clearSidebarState(),this._config.enablePersistence&&!this.isMobileSize()?this.loadSidebarState():this.isCollapsed()||this.updateStateByResponsiveLogic()}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element))return;if(!t.closest('[data-lte-toggle="sidebar"]'))return;e.preventDefault();const n=document.querySelector(Te);n&&xe.getOrCreateInstance(n).toggle()}),a(()=>{const e=document.querySelector(Te);if(!e)return;const t=e.dataset.sidebarBreakpoint,n=e.dataset.enablePersistence,i={sidebarBreakpoint:void 0===t?we.sidebarBreakpoint:Number(t),enablePersistence:void 0===n?we.enablePersistence:"true"===n},a=xe.getOrCreateInstance(e,i);a.init(),globalThis.matchMedia(`(max-width: ${a._config.sidebarBreakpoint}px)`).addEventListener("change",()=>{a.updateStateByResponsiveLogic()},{signal:s()});const o=document.querySelector(".app-wrapper");let r=o?.querySelector(`:scope > .${Ee}`);r||(r=document.createElement("div"),r.className=Ee,o?.append(r));const c=s();let l=!1;r.addEventListener("touchstart",()=>{l=!1},{passive:!0,signal:c}),r.addEventListener("touchmove",()=>{l=!0},{passive:!0,signal:c}),r.addEventListener("touchend",e=>{l||(e.preventDefault(),a.collapse()),l=!1},{passive:!1,signal:c}),r.addEventListener("click",e=>{e.preventDefault(),a.collapse()},{signal:c})});const qe="lte-theme",Me="data-bs-theme",$e="data-bs-theme-value",Ie=`[${$e}]`,Ce=new Set(["light","dark","auto"]),Ne=e=>Ce.has(e),Pe=()=>"off"===document.documentElement.getAttribute("data-lte-color-mode"),Re=(()=>{const{documentElement:e}=document;if(e.hasAttribute("data-lte-theme-resolved"))return null;const t=e.getAttribute(Me);return t&&Ne(t)?t:null})();class ze{getStoredTheme(){try{const e=localStorage.getItem(qe);return e&&Ne(e)?e:null}catch{return null}}getMarkupTheme(){return Re}getPreferredTheme(){return(this.getStoredTheme()??this.getMarkupTheme())||(this._prefersDark()?"dark":"light")}resolveTheme(e){return"auto"===e?this._prefersDark()?"dark":"light":e}setTheme(e){try{localStorage.setItem(qe,e)}catch{}this._applyTheme(e),this._showActiveTheme(e),document.dispatchEvent(new CustomEvent("changed.lte.color-mode",{detail:{theme:e,resolved:this.resolveTheme(e)}}))}_applyTheme(e){const t=this.resolveTheme(e);document.documentElement.setAttribute(Me,t),document.documentElement.style.colorScheme=t}_prefersDark(){return globalThis.matchMedia("(prefers-color-scheme: dark)").matches}_showActiveTheme(e){document.querySelectorAll(Ie).forEach(t=>{const n=t.getAttribute($e)===e;t.classList.toggle("active",n),t.setAttribute("aria-pressed",String(n)),t.querySelector(".bi-check-lg")?.classList.toggle("d-none",!n)}),document.querySelectorAll("[data-lte-theme-icon]").forEach(t=>{t.classList.toggle("d-none",t.dataset.lteThemeIcon!==e)})}init(){if(Pe())return;const e=this.getPreferredTheme();this._applyTheme(e),this._showActiveTheme(e)}}document.addEventListener("click",e=>{const t=e.target;if(!(t instanceof Element)||Pe())return;const n=t.closest(Ie),s=n?.getAttribute($e);s&&Ne(s)&&(new ze).setTheme(s)}),a(()=>{const e=new ze;e.init(),globalThis.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",()=>{if(Pe())return;const t=e.getStoredTheme()??e.getMarkupTheme();t&&"auto"!==t||(e._applyTheme("auto"),e._showActiveTheme(t??"auto"))},{signal:s()})});const Be="sidebar-search",De=`filtered.lte.${Be}`,Oe="menu-open",Fe='[data-lte-toggle="sidebar-search"]',He=".nav-item",Ke=".nav-header",We=":scope > .nav-link",Ye=e=>e.querySelector(":scope > .nav-treeview"),je=(e,t,n,s)=>{e.classList.toggle(Oe,n),e.querySelector(We)?.setAttribute("aria-expanded",String(n)),t.style.display=s};class Ue extends f{static get NAME(){return Be}static getInstance(e){return this._getInstance(e)}static getOrCreateInstance(e){return this.getInstance(e)??new this(e)}_menu;_emptyState;_snapshot=null;constructor(e){super(e);const t=e.dataset.lteTarget,n=e.closest(".app-sidebar")??document;this._menu=t?document.querySelector(t):n.querySelector(".sidebar-menu"),this._emptyState=n.querySelector("[data-lte-search-empty]")}search(e){const t=this._menu;if(!t)return;const n=e.trim().toLowerCase();if(!n)return void this.clear();this._snapshot??=this._takeSnapshot(t);const s=[...t.querySelectorAll(He)],i=new Set;for(const e of s){const t=e.querySelector(We)?.textContent?.replace(/\s+/g," ").trim().toLowerCase();t?.includes(n)&&i.add(e)}for(let e=s.length-1;e>=0;e--){const t=s[e];t.hidden=!(i.has(t)||t.querySelector(`${He}:not([hidden])`))}for(const e of i)for(const t of e.querySelectorAll(He))t.hidden=!1;let a=0;for(const e of s){e.hidden||a++;const t=Ye(e);if(t){const n=!e.hidden&&Boolean(e.querySelector(`${He}:not([hidden])`));je(e,t,n,n?"block":"none")}}for(const e of t.querySelectorAll(Ke))e.hidden=!0;this._emptyState&&(this._emptyState.hidden=a>0),b(this._element,De,{detail:{query:n,matches:a}})}clear(){const e=this._menu;if(e){for(const t of e.querySelectorAll(`${He}, ${Ke}`))t.hidden=!1;if(this._snapshot){for(const[e,t]of this._snapshot){const n=Ye(e);n&&je(e,n,t.open,t.display)}this._snapshot=null}this._emptyState&&(this._emptyState.hidden=!0),b(this._element,De,{detail:{query:"",matches:-1}})}}dispose(){this.clear(),super.dispose()}_takeSnapshot(e){const t=new Map;for(const n of e.querySelectorAll(He)){const e=Ye(n);e&&t.set(n,{open:n.classList.contains(Oe),display:e.style.display})}return t}}document.addEventListener("input",e=>{const t=e.target;t instanceof HTMLInputElement&&t.matches(Fe)&&Ue.getOrCreateInstance(t).search(t.value)}),document.addEventListener("keydown",e=>{const t=e.target;"Escape"===e.key&&t instanceof HTMLInputElement&&t.matches(Fe)&&(t.value="",Ue.getOrCreateInstance(t).clear())}),a(()=>{document.querySelectorAll(Fe).forEach(e=>{e.value&&Ue.getOrCreateInstance(e).search(e.value)})});class Ve{config;liveRegion=null;focusHistory=[];signal=s();constructor(e={}){this.config={announcements:!0,skipLinks:!0,focusManagement:!0,keyboardNavigation:!0,reducedMotion:!0,...e},this.init()}init(){this.config.announcements&&this.createLiveRegion(),this.config.skipLinks&&this.addSkipLinks(),this.config.focusManagement&&this.initFocusManagement(),this.config.keyboardNavigation&&this.initKeyboardNavigation(),this.config.reducedMotion&&this.respectReducedMotion(),this.initErrorAnnouncements(),this.initTableAccessibility(),this.initFormAccessibility()}createLiveRegion(){if(this.liveRegion)return;const e=document.getElementById("live-region");e?this.liveRegion=e:(this.liveRegion=document.createElement("div"),this.liveRegion.id="live-region",this.liveRegion.className="live-region",this.liveRegion.setAttribute("aria-live","polite"),this.liveRegion.setAttribute("aria-atomic","true"),this.liveRegion.setAttribute("role","status"),document.body.append(this.liveRegion))}addSkipLinks(){if(document.querySelector(".skip-links"))return void this.ensureSkipTargets();const e=document.createElement("div");e.className="skip-links";const t=document.createElement("a");t.href="#main",t.className="skip-link",t.textContent="Skip to main content";const n=document.createElement("a");n.href="#navigation",n.className="skip-link",n.textContent="Skip to navigation",e.append(t),e.append(n),document.body.insertBefore(e,document.body.firstChild),this.ensureSkipTargets()}ensureSkipTargets(){const e=[["main",'main, [role="main"]'],["navigation",'nav, [role="navigation"]']];for(const[t,n]of e){const e=document.getElementById(t)??document.querySelector(n);e&&(e.id||(e.id=t),e.hasAttribute("tabindex")||e.setAttribute("tabindex","-1"))}}initFocusManagement(){document.addEventListener("keydown",e=>{"Escape"===e.key&&this.handleEscapeKey(e)},{signal:this.signal}),this.initModalFocusManagement(),this.initDropdownFocusManagement()}handleEscapeKey(e){if(!document.querySelector(".modal.show")&&document.querySelector(".dropdown-menu.show")){const t=document.querySelector('[data-bs-toggle="dropdown"][aria-expanded="true"]');t?.click(),e.preventDefault()}}initKeyboardNavigation(){document.addEventListener("keydown",e=>{const t=e.target;t.matches("input, textarea, select, [contenteditable], [contenteditable] *")||(t.closest(".nav, .navbar-nav, .dropdown-menu")&&this.handleMenuNavigation(e),"Enter"!==e.key&&" "!==e.key||!t.hasAttribute("role")||"button"!==t.getAttribute("role")||t.matches('button, input[type="button"], input[type="submit"]')||(e.preventDefault(),t.click()))},{signal:this.signal})}handleMenuNavigation(e){if(!["ArrowUp","ArrowDown","ArrowLeft","ArrowRight","Home","End"].includes(e.key))return;const t=e.target,n=Array.from(t.closest(".nav, .navbar-nav, .dropdown-menu")?.querySelectorAll("a, button")||[]).filter(e=>null!==e.offsetParent),s=n.indexOf(t);if(-1===s)return;let i;switch(e.key){case"ArrowDown":case"ArrowRight":i=s<n.length-1?s+1:0;break;case"ArrowUp":case"ArrowLeft":i=s>0?s-1:n.length-1;break;case"Home":i=0;break;case"End":i=n.length-1;break;default:return}e.preventDefault(),n[i]?.focus()}respectReducedMotion(){if(globalThis.matchMedia("(prefers-reduced-motion: reduce)").matches&&(document.body.classList.add("reduce-motion"),document.documentElement.style.scrollBehavior="auto",!document.getElementById("adminlte-reduce-motion"))){const e=document.createElement("style");e.id="adminlte-reduce-motion",e.textContent="\n          *, *::before, *::after {\n            animation-duration: 0.01ms !important;\n            animation-iteration-count: 1 !important;\n            transition-duration: 0.01ms !important;\n          }\n        ",document.head.append(e)}}initErrorAnnouncements(){const e=new MutationObserver(e=>{e.forEach(e=>{e.addedNodes.forEach(e=>{if(e.nodeType===Node.ELEMENT_NODE){const t=e;t.matches(".alert-danger, .invalid-feedback, .error")&&this.announce(t.textContent||"Error occurred","assertive"),t.matches(".alert-success, .success")&&this.announce(t.textContent||"Success","polite")}})})});e.observe(document.body,{childList:!0,subtree:!0}),this.signal.addEventListener("abort",()=>{e.disconnect()},{once:!0})}initTableAccessibility(){document.querySelectorAll("table").forEach(e=>{if(e.hasAttribute("role")||e.setAttribute("role","table"),e.querySelectorAll("th").forEach(e=>{if(!e.hasAttribute("scope")){const t=e.closest("thead"),n=0===e.cellIndex;t?e.setAttribute("scope","col"):n&&e.setAttribute("scope","row")}}),!e.querySelector("caption")&&e.hasAttribute("title")){const t=document.createElement("caption");t.textContent=e.getAttribute("title")||"",e.insertBefore(t,e.firstChild)}})}initFormAccessibility(){document.querySelectorAll("input, select, textarea").forEach(e=>{const t=e;if(!t.labels?.length&&!t.hasAttribute("aria-label")&&!t.hasAttribute("aria-labelledby")){const e=t.getAttribute("placeholder");e&&t.setAttribute("aria-label",e)}if(t.hasAttribute("required")){const e=t.labels?.[0];if(e&&!e.querySelector(".required-indicator")){const t=document.createElement("span");t.className="required-indicator sr-only",t.textContent=" (required)",e.append(t)}}t.classList.contains("disable-adminlte-validations")||t.addEventListener("invalid",()=>{this.handleFormError(t)},{signal:this.signal})})}handleFormError(e){e.id||e.name||(e.id=Qe.generateId("field"));const t=`${e.id||e.name}-error`;let n=document.getElementById(t);n||(n=document.createElement("div"),n.id=t,n.className="invalid-feedback",n.setAttribute("role","alert"),e.parentNode?.append(n)),n.textContent=e.validationMessage;const s=(e.getAttribute("aria-describedby")||"").split(/\s+/).filter(Boolean);s.includes(t)||s.push(t),e.setAttribute("aria-describedby",s.join(" ")),e.classList.add("is-invalid"),this.announce(`Error in ${e.labels?.[0]?.textContent||e.name}: ${e.validationMessage}`,"assertive")}initModalFocusManagement(){document.addEventListener("show.bs.modal",()=>{this.focusHistory.push(document.activeElement)},{signal:this.signal}),document.addEventListener("shown.bs.modal",e=>{const t=e.target,n=t.querySelector("[autofocus]")||t.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');n?.focus()},{signal:this.signal}),document.addEventListener("hidden.bs.modal",()=>{const e=this.focusHistory.pop();e?.isConnected&&e.focus()},{signal:this.signal})}initDropdownFocusManagement(){document.addEventListener("shown.bs.dropdown",e=>{const t=e.target.querySelector(".dropdown-menu"),n=t?.querySelector("a, button");n&&n.focus()},{signal:this.signal})}announce(e,t="polite"){this.liveRegion||this.createLiveRegion(),this.liveRegion&&(this.liveRegion.setAttribute("aria-live",t),this.liveRegion.textContent=e,setTimeout(()=>{this.liveRegion&&(this.liveRegion.textContent="")},1e3))}focusElement(e){const t=document.querySelector(e);t&&(t.focus(),t.scrollIntoView({behavior:"smooth",block:"center"}))}trapFocus(e){const t=e.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'),n=Array.from(t),s=n[0],i=n.at(-1);e.addEventListener("keydown",e=>{"Tab"===e.key&&(e.shiftKey?document.activeElement===s&&(i?.focus(),e.preventDefault()):document.activeElement===i&&(s.focus(),e.preventDefault()))},{signal:this.signal})}addLandmarks(){if(!document.querySelector("main")){const e=document.querySelector(".app-main");e&&(e.setAttribute("role","main"),e.id||(e.id="main"))}document.querySelectorAll(".navbar-nav, .nav").forEach((e,t)=>{"UL"!==e.tagName&&"OL"!==e.tagName&&(e.hasAttribute("role")||e.setAttribute("role","navigation"),e.hasAttribute("aria-label")||e.setAttribute("aria-label",`Navigation ${t+1}`))});const e=document.querySelector('form[role="search"], .navbar-search');e&&!e.hasAttribute("role")&&e.setAttribute("role","search")}}const Ge=e=>new Ve(e),Je=e=>{const[t,n,s]=(e=>{const t=/^#([\da-f]{3}|[\da-f]{6})$/i.exec(e.trim());if(t){let e=t[1];return 3===e.length&&(e=[...e].map(e=>e+e).join("")),[Number.parseInt(e.slice(0,2),16),Number.parseInt(e.slice(2,4),16),Number.parseInt(e.slice(4,6),16)]}return e.match(/\d+/g)?.map(Number)||[0,0,0]})(e).map(e=>(e/=255)<=.03928?e/12.92:(e+.055)**2.4/1.055**2.4);return.2126*t+.7152*n+.0722*s},Qe={checkColorContrast:(e,t)=>{const n=Je(e),s=Je(t),i=(Math.max(n,s)+.05)/(Math.min(n,s)+.05);return{ratio:Math.round(100*i)/100,passes:i>=4.5}},generateId:(e="a11y")=>`${e}-${Math.random().toString(36).slice(2,11)}`,isFocusable:e=>["a[href]","button:not([disabled])","input:not([disabled])","select:not([disabled])","textarea:not([disabled])",'[tabindex]:not([tabindex="-1"])','[contenteditable="true"]'].some(t=>e.matches(t))};a(()=>{Ge({announcements:!0,skipLinks:!0,focusManagement:!0,keyboardNavigation:!0,reducedMotion:!0}).addLandmarks()}),e.CardWidget=F,e.ColorMode=ze,e.DirectChat=ce,e.FullScreen=ge,e.Layout=p,e.PushMenu=xe,e.SidebarSearch=Ue,e.Treeview=ne,e.initAccessibility=Ge,e.initialize=r,e.teardown=o});
//# sourceMappingURL=adminlte.min.js.map

/* SOURCE: ui.js */

"use strict";

(function () {

    let modal = null;

    let backdrop = null;

    let iframe = null;


    /* =========================================================
       CREATE MODAL
       ========================================================= */

    function createModal() {

        if (modal) {
            return;
        }


        modal =
            document.createElement("div");

        modal.className =
            "bekuku-modal";

        modal.setAttribute(
            "aria-hidden",
            "true"
        );


        modal.innerHTML = `

            <div class="bekuku-modal-dialog">

                <div class="bekuku-modal-header">

                    <h5 class="bekuku-modal-title">
                        Tambah Produk
                    </h5>

                    <button
                        type="button"
                        class="bekuku-modal-close"
                        aria-label="Tutup"
                    >
                        Ã—
                    </button>

                </div>


                <div class="bekuku-modal-body">

                </div>

            </div>

        `;


        document.body.appendChild(
            modal
        );


        modal
            .querySelector(
                ".bekuku-modal-close"
            )
            .addEventListener(
                "click",
                closeModal
            );


        modal.addEventListener(
            "click",
            function (event) {

                if (
                    event.target === modal
                ) {

                    closeModal();

                }

            }
        );

    }


    /* =========================================================
       OPEN MODAL
       ========================================================= */

    function openModal(
        title,
        url
    ) {

        createModal();


        const titleElement =
            modal.querySelector(
                ".bekuku-modal-title"
            );


        const body =
            modal.querySelector(
                ".bekuku-modal-body"
            );


        titleElement.textContent =
            title;


        body.innerHTML = "";


        iframe =
            document.createElement(
                "iframe"
            );


        iframe.src =
            url;


        iframe.title =
            title;


        iframe.setAttribute(
            "scrolling",
            "yes"
        );


        iframe.style.setProperty(
            "display",
            "block",
            "important"
        );


        iframe.style.setProperty(
            "width",
            "100%",
            "important"
        );


        iframe.style.setProperty(
            "height",
            "280px",
            "important"
        );


        iframe.style.setProperty(
            "min-height",
            "0",
            "important"
        );


        iframe.style.setProperty(
            "border",
            "0",
            "important"
        );


        iframe.style.setProperty(
            "overflow",
            "auto",
            "important"
        );


        body.appendChild(
            iframe
        );


        /*
        ---------------------------------------------------------
        BACKDROP
        ---------------------------------------------------------
        */

        backdrop =
            document.createElement(
                "div"
            );

        backdrop.className =
            "bekuku-modal-backdrop";


        document.body.appendChild(
            backdrop
        );


        /*
        ---------------------------------------------------------
        FORM MODAL
        ---------------------------------------------------------
        */

        modal.classList.add(
            "bekuku-form-modal"
        );


        modal.classList.add(
            "is-open"
        );


        modal.setAttribute(
            "aria-hidden",
            "false"
        );


        document.body.classList.add(
            "modal-open"
        );


        /*
        ---------------------------------------------------------
        SETTING IFRAME SETELAH LOAD
        ---------------------------------------------------------
        */

        iframe.addEventListener(
            "load",
            function () {

                try {

                    const doc =
                        iframe.contentDocument;


                    const html =
                        doc.documentElement;


                    const body =
                        doc.body;


                    if (!body) {
                        return;
                    }


                    /*
                    JANGAN beri height tetap
                    */

                    html.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );


                    body.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );


                    body.style.setProperty(
                        "min-height",
                        "100%",
                        "important"
                    );


                    /*
                    JANGAN lock overflow
                    */

                    html.style.setProperty(
                        "overflow-y",
                        "visible",
                        "important"
                    );


                    html.style.setProperty(
                        "overflow-x",
                        "hidden",
                        "important"
                    );


                    body.style.setProperty(
                        "overflow-y",
                        "visible",
                        "important"
                    );


                    body.style.setProperty(
                        "overflow-x",
                        "hidden",
                        "important"
                    );


                    body.classList.add(
                        "bekuku-product-popup-page"
                    );

                    const dialog =
                        modal.querySelector(
                            ".bekuku-modal-dialog"
                        );

                    const modalBody =
                        modal.querySelector(
                            ".bekuku-modal-body"
                        );

                    const isEditPopup =
                        /\/edit\.php(?:[?#]|$)/i.test(url);

                    if (dialog && modalBody) {
                        const isCompactPopup =
                            isEditPopup ||
                            body.classList.contains("popup-form-page") ||
                            body.classList.contains("popup-customer-page") ||
                            body.classList.contains("popup-supplier-page") ||
                            body.classList.contains("popup-kategori-page");

                        if (isCompactPopup) {
                            body.style.setProperty(
                                "min-height",
                                "0",
                                "important"
                            );

                            dialog.style.setProperty(
                                "width",
                                "min(690px, calc(100vw - 40px))",
                                "important"
                            );

                            dialog.style.setProperty(
                                "max-width",
                                "690px",
                                "important"
                            );

                            dialog.style.setProperty(
                                "height",
                                "auto",
                                "important"
                            );

                            dialog.style.setProperty(
                                "max-height",
                                "calc(100vh - 40px)",
                                "important"
                            );

                            modalBody.style.setProperty(
                                "flex",
                                "0 1 auto",
                                "important"
                            );

                            iframe.style.setProperty(
                                "height",
                                "280px",
                                "important"
                            );

                            const modalHeader =
                                modal.querySelector(
                                    ".bekuku-modal-header"
                                );

                            const modalTitle =
                                modal.querySelector(
                                    ".bekuku-modal-title"
                                );

                            const modalClose =
                                modal.querySelector(
                                    ".bekuku-modal-close"
                                );

                            if (modalHeader) {
                                modalHeader.style.setProperty(
                                    "height",
                                    "65px",
                                    "important"
                                );
                            }

                            if (modalTitle) {
                                modalTitle.style.setProperty(
                                    "font-size",
                                    "23px",
                                    "important"
                                );
                            }

                            if (modalClose) {
                                modalClose.style.setProperty(
                                    "width",
                                    "48px",
                                    "important"
                                );

                                modalClose.style.setProperty(
                                    "height",
                                    "48px",
                                    "important"
                                );

                                modalClose.style.setProperty(
                                    "border-radius",
                                    "12px",
                                    "important"
                                );
                            }
                        }

                        const resizePopup = function () {
                            if (!isCompactPopup) {
                                return;
                            }

                            iframe.style.setProperty(
                                "height",
                            "280px",
                                "important"
                            );
                        };

                        resizePopup();
                        window.setTimeout(resizePopup, 100);
                    }

                    if (isEditPopup) {
                        const popupStyle =
                            doc.createElement("style");

                        popupStyle.textContent = `
                            html,
                            body {
                                margin: 0 !important;
                                padding: 0 !important;
                                min-height: 0 !important;
                                background: #0d1b4f !important;
                                overflow-x: hidden !important;
                            }

                            body.bekuku-product-popup-page .app-wrapper,
                            body.bekuku-product-popup-page .app-main,
                            body.bekuku-product-popup-page .app-content,
                            body.bekuku-product-popup-page .container-fluid {
                                min-height: 0 !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }

                            body.bekuku-product-popup-page .app-wrapper {
                                display: block !important;
                            }

                            body.bekuku-product-popup-page .app-content-header,
                            body.bekuku-product-popup-page .breadcrumb,
                            body.bekuku-product-popup-page .card-header,
                            body.bekuku-product-popup-page .app-header,
                            body.bekuku-product-popup-page .app-sidebar,
                            body.bekuku-product-popup-page .app-footer,
                            body.bekuku-product-popup-page footer,
                            body.bekuku-product-popup-page .main-footer {
                                display: none !important;
                            }

                            body.bekuku-product-popup-page .card {
                                margin: 0 !important;
                                border: 0 !important;
                                border-radius: 0 !important;
                                background: #0d1b4f !important;
                                box-shadow: none !important;
                            }

                            body.bekuku-product-popup-page .card-body {
                                padding: 22px !important;
                                background: #0d1b4f !important;
                            }

                            body.bekuku-product-popup-page .card-footer {
                                display: flex !important;
                                justify-content: flex-end !important;
                                gap: 10px !important;
                                margin: 0 !important;
                                padding: 10px 22px !important;
                                border-top: 1px solid rgba(126, 157, 231, .28) !important;
                                background: #12245c !important;
                            }

                            body.bekuku-product-popup-page.popup-form-page {
                                padding: 0 !important;
                                background: #0d1b4f !important;
                            }

                            body.bekuku-product-popup-page .popup-form-card {
                                width: 100% !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                border: 0 !important;
                                border-radius: 0 !important;
                                background: #0d1b4f !important;
                                box-shadow: none !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body {
                                margin: 0 !important;
                                padding: 22px !important;
                                background: #0d1b4f !important;
                                color: #f5f8ff !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body label {
                                display: block !important;
                                margin: 0 0 8px !important;
                                color: #f5f8ff !important;
                                font: 600 14px/1.3 Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body input,
                            body.bekuku-product-popup-page .popup-form-body select {
                                display: block !important;
                                width: 100% !important;
                                height: 44px !important;
                                padding: 9px 12px !important;
                                border: 1px solid #3b5798 !important;
                                border-radius: 9px !important;
                                background: #132760 !important;
                                color: #fff !important;
                                font: 14px Arial, Helvetica, sans-serif !important;
                                box-sizing: border-box !important;
                            }

                            body.bekuku-product-popup-page .popup-form-actions {
                                display: flex !important;
                                justify-content: flex-end !important;
                                align-items: center !important;
                                gap: 10px !important;
                                margin: 0 !important;
                                padding: 10px 22px !important;
                                border-top: 1px solid rgba(126, 157, 231, .28) !important;
                                background: #12245c !important;
                            }

                            body.bekuku-product-popup-page .popup-form-actions button {
                                min-height: 42px !important;
                                padding: 9px 18px !important;
                                border-radius: 9px !important;
                                font: 600 14px Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page form {
                                margin: 0 !important;
                            }

                            body.bekuku-product-popup-page .form-label,
                            body.bekuku-product-popup-page label {
                                color: #f5f8ff !important;
                                font: 600 14px/1.3 Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page .form-control,
                            body.bekuku-product-popup-page .form-select,
                            body.bekuku-product-popup-page input,
                            body.bekuku-product-popup-page select {
                                min-height: 44px !important;
                                height: 44px !important;
                                border: 1px solid #3b5798 !important;
                                border-radius: 9px !important;
                                background: #132760 !important;
                                color: #fff !important;
                            }

                            body.bekuku-product-popup-page .btn-primary,
                            body.bekuku-product-popup-page .popup-kategori-button-save {
                                min-height: 42px !important;
                                border: 1px solid #527dff !important;
                                border-radius: 9px !important;
                                background: #315cff !important;
                                color: #fff !important;
                            }

                            body.bekuku-product-popup-page .btn-secondary,
                            body.bekuku-product-popup-page .popup-kategori-button-cancel {
                                min-height: 42px !important;
                                border: 1px solid #526796 !important;
                                border-radius: 9px !important;
                                background: #162b68 !important;
                                color: #dce6ff !important;
                            }
                        `;

                        doc.head.appendChild(
                            popupStyle
                        );
                    }


                    /*
                    Tombol Batal
                    */

                    const cancel =
                        doc.querySelector(
                            "[data-popup-close], .popup-close"
                        );


                    if (cancel) {

                        cancel.addEventListener(
                            "click",
                            closeModal
                        );

                    }

                } catch (error) {

                    console.warn(
                        "Popup iframe:",
                        error
                    );

                }

            }
        );

    }


    /* =========================================================
       CLOSE
       ========================================================= */

    function closeModal() {

        if (!modal) {
            return;
        }


        modal.classList.remove(
            "is-open"
        );


        modal.setAttribute(
            "aria-hidden",
            "true"
        );


        document.body.classList.remove(
            "modal-open"
        );


        if (backdrop) {

            backdrop.remove();

            backdrop = null;
        }


        iframe = null;

    }


    /* =========================================================
       OPEN PRODUCT
       ========================================================= */

    function openProductPopup(
        element
    ) {

        let url =
            element.dataset.modalUrl ||
            element.getAttribute("href");


        if (!url) {
            return;
        }


        const popupUrl =
            new URL(
                url,
                window.location.href
            );


        /*
        PASTIKAN PHP MASUK MODE POPUP
        */

        popupUrl.searchParams.set(
            "popup",
            "1"
        );


        let title =
            element.dataset.modalTitle;


        if (!title) {

            const path =
                new URL(
                    url,
                    window.location.href
                ).pathname.toLowerCase();

            if (path.includes("/categories/")) {

                title =
                    "Edit Kategori";

            } else if (path.includes("/customers/")) {

                title =
                    "Edit Customer";

            } else if (path.includes("/suppliers/")) {

                title =
                    "Edit Supplier";

            } else if (
                /create\.php/i.test(
                    url
                )
            ) {

                title =
                    "Tambah Produk";

            } else if (
                /edit\.php/i.test(
                    url
                )
            ) {

                title =
                    "Edit Produk";

            } else {

                title =
                    "Detail";

            }

        }


        openModal(
            title,
            popupUrl.href
        );

    }


    /* =========================================================
       CLICK
       ========================================================= */

    document.addEventListener(
        "click",
        function (event) {

            if (event.defaultPrevented) {
                return;
            }

            const element =
                event.target.closest(
                    "a[href], [data-modal-url]"
                );


            if (!element) {
                return;
            }


            if (
                element.dataset.noModal !== undefined
            ) {

                return;
            }


            const url =
                element.dataset.modalUrl ||
                element.getAttribute("href") ||
                "";


            /*
            Hanya popup create/edit/detail
            */

            if (
                /(?:create|edit|detail)\.php/i
                    .test(url)
            ) {

                event.preventDefault();

                openProductPopup(
                    element
                );

            }

        }
    );


    /* =========================================================
       ESC
       ========================================================= */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape"
            ) {

                closeModal();

            }

        }
    );

})();

/* SOURCE: transactions-create.js */

/*
|--------------------------------------------------------------------------
| BEKUKU POS
| JavaScript Transaksi
|--------------------------------------------------------------------------
| Digunakan oleh:
| - transactions/index.php
| - transactions/create.php
| - transactions/edit.php
| - transactions/detail.php
| - transactions/delete.php
|--------------------------------------------------------------------------
*/

"use strict";

function showTransactionNotice(message) {
    const modal = document.getElementById("transactionNoticeModal");

    if (!modal) {
        return;
    }

    modal.querySelector(".transaction-notice-text").textContent = message;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
}

function closeTransactionNotice() {
    const modal = document.getElementById("transactionNoticeModal");

    if (!modal) {
        return;
    }

    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
}


/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function formatRupiah(angka) {

    const nilai = Number(angka) || 0;

    return "Rp " + nilai.toLocaleString("id-ID");
}


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL TRANSAKSI
|--------------------------------------------------------------------------
*/

function hitungTotal() {

    const rows = document.querySelectorAll(".product-row");

    let total = 0;
    let selectedCount = 0;

    rows.forEach(function (row) {

        const checkbox =
            row.querySelector(".product-checkbox");

        const quantity =
            row.querySelector(".quantity");

        const subtotal =
            row.querySelector(".subtotal");


        if (!checkbox || !quantity || !subtotal) {
            return;
        }


        if (checkbox.checked) {

            selectedCount++;

            const price =
                parseFloat(
                    checkbox.dataset.price
                ) || 0;


            let qty =
                parseInt(
                    quantity.value
                ) || 0;


            const maxStock =
                parseInt(
                    quantity.max
                ) || 0;


            /*
            | Quantity minimal 1
            */

            if (qty < 1) {
                qty = 1;
                quantity.value = 1;
            }


            /*
            | Quantity tidak boleh melebihi stok
            */

            if (
                maxStock > 0 &&
                qty > maxStock
            ) {

                qty = maxStock;

                quantity.value = maxStock;
            }


            const hasil =
                price * qty;


            subtotal.textContent =
                formatRupiah(hasil);


            total += hasil;

        } else {

            subtotal.textContent =
                "Rp 0";
        }
    });


    /*
    | Update total
    */

    const totalElement =
        document.getElementById("total");


    if (totalElement) {
        totalElement.textContent =
            formatRupiah(total);
    }

    updateQrisBarcode(total);

    const selectedCountElement =
        document.getElementById("selectedCount");

    if (selectedCountElement) {
        selectedCountElement.innerHTML =
            '<i class="bi bi-check2"></i> ' +
            selectedCount +
            " produk";
    }


    /*
    | Update kembalian
    */

    hitungKembalian(total);


    return total;
}


/*
|--------------------------------------------------------------------------
| HITUNG KEMBALIAN
|--------------------------------------------------------------------------
*/

function hitungKembalian(total) {

    const paymentElement =
        document.getElementById(
            "payment_amount"
        );


    const changeElement =
        document.getElementById(
            "change"
        );


    if (!paymentElement || !changeElement) {
        return;
    }


    const payment =
        parseFloat(
            paymentElement.value
        ) || 0;


    const change =
        payment - total;


    if (change >= 0) {

        changeElement.textContent =
            formatRupiah(change);


        changeElement.classList.remove(
            "text-danger"
        );


        changeElement.classList.add(
            "text-success"
        );

    } else {

        changeElement.textContent =
            "Uang kurang";


        changeElement.classList.remove(
            "text-success"
        );


        changeElement.classList.add(
            "text-danger"
        );
    }
}


/*
|--------------------------------------------------------------------------
| INISIALISASI CHECKBOX PRODUK
|--------------------------------------------------------------------------
*/

function initProductCheckbox() {

    const checkboxes =
        document.querySelectorAll(
            ".product-checkbox"
        );


    checkboxes.forEach(function (checkbox) {

        checkbox.addEventListener(
            "change",
            function () {

                const row =
                    this.closest(".product-row");


                if (!row) {
                    return;
                }


                const quantity =
                    row.querySelector(
                        ".quantity"
                    );


                if (!quantity) {
                    return;
                }


                const maxStock =
                    parseInt(
                        quantity.max
                    ) || 0;


                /*
                | Produk stok 0 tidak boleh dipilih
                */

                if (
                    this.checked &&
                    maxStock <= 0
                ) {

                    this.checked = false;


                    quantity.disabled =
                        true;


                    showTransactionNotice(
                        "Produk ini sedang habis."
                    );


                    hitungTotal();

                    return;
                }


                /*
                | Produk dipilih
                */

                if (this.checked) {

                    quantity.disabled =
                        false;


                    if (
                        parseInt(
                            quantity.value
                        ) < 1
                    ) {

                        quantity.value = 1;
                    }


                    quantity.focus();

                } else {

                    /*
                    | Produk tidak dipilih
                    */

                    quantity.disabled =
                        true;


                    quantity.value = 1;
                }


                hitungTotal();
            }
        );
    });
}


/*
|--------------------------------------------------------------------------
| INISIALISASI QUANTITY
|--------------------------------------------------------------------------
*/

function initQuantity() {

    const quantities =
        document.querySelectorAll(
            ".quantity"
        );


    quantities.forEach(function (quantity) {

        function selectDefaultQuantity() {
            if (quantity.value === "1") {
                quantity.select();
            }
        }

        quantity.addEventListener(
            "focus",
            selectDefaultQuantity
        );

        quantity.addEventListener(
            "click",
            selectDefaultQuantity
        );

        quantity.addEventListener(
            "input",
            function () {

                const max =
                    parseInt(
                        this.max
                    ) || 0;


                let value =
                    parseInt(
                        this.value
                    ) || 0;


                /*
                | Minimal 1
                */

                if (value < 1) {

                    value = 1;

                    this.value = 1;
                }


                /*
                | Maksimal sesuai stok
                */

                if (
                    max > 0 &&
                    value > max
                ) {

                    value = max;

                    this.value = max;
                }


                hitungTotal();
            }
        );


        /*
        | Validasi ketika input kehilangan fokus
        */

        quantity.addEventListener(
            "blur",
            function () {

                const max =
                    parseInt(
                        this.max
                    ) || 0;


                let value =
                    parseInt(
                        this.value
                    ) || 1;


                if (value < 1) {
                    value = 1;
                }


                if (
                    max > 0 &&
                    value > max
                ) {

                    value = max;
                }


                this.value = value;


                hitungTotal();
            }
        );
    });
}


/*
|--------------------------------------------------------------------------
| PEMBAYARAN
|--------------------------------------------------------------------------
*/

function initPayment() {

    const paymentElement =
        document.getElementById(
            "payment_amount"
        );

    const paymentMethod =
        document.getElementById(
            "payment_method"
        );


    if (!paymentElement) {
        return;
    }


    paymentElement.addEventListener(
        "input",
        function () {

            hitungTotal();
        }
    );

    if (paymentMethod) {
        paymentMethod.addEventListener(
            "change",
            hitungTotal
        );
    }
}


function updateQrisBarcode(totalValue) {

    const paymentMethod =
        document.getElementById(
            "payment_method"
        );

    const panel =
        document.getElementById(
            "qrisBarcodePanel"
        );

    const barcode =
        document.getElementById(
            "qrisBarcode"
        );

    const amount =
        document.getElementById(
            "qrisBarcodeAmount"
        );

    const note =
        document.getElementById(
            "qrisBarcodeNote"
        );

    if (!paymentMethod || !panel || !barcode || !amount) {
        return;
    }

    const paymentType =
        paymentMethod.value.toLowerCase();

    const isMidtransQris =
        paymentType === "qris";

    const isImageQris =
        paymentType === "qris_image";

    const isQris =
        isMidtransQris || isImageQris;

    panel.classList.toggle("is-visible", isQris);
    panel.setAttribute("aria-hidden", isQris ? "false" : "true");

    if (!isQris) {
        return;
    }

    const totalText =
        document.getElementById("total")?.textContent || "Rp 0";

    const total =
        Math.round(Number(totalValue) || 0);

    amount.textContent =
        "Total: " + totalText.trim();

    barcode.replaceChildren();

    if (isImageQris) {
        const imageUrl =
            paymentMethod.dataset.qrisImageUrl || "";

        if (imageUrl === "") {
            if (note) {
                note.textContent =
                    "Atur BEKUKU_QRIS_IMAGE_URL untuk menampilkan gambar QRIS.";
            }
            return;
        }

        const image = document.createElement("img");
        image.src = imageUrl;
        image.alt = "QRIS merchant";
        image.width = 240;
        image.height = 240;
        image.addEventListener("error", function () {
            barcode.textContent = "Gambar QRIS tidak dapat dimuat.";
            if (note) {
                note.textContent =
                    "Periksa path atau URL BEKUKU_QRIS_IMAGE_URL.";
            }
        });
        barcode.appendChild(image);
        if (note) {
            note.textContent =
                "Scan QRIS merchant menggunakan aplikasi pembayaran.";
        }
        return;
    }

    if (total <= 0) {
        if (note) {
            note.textContent = "Pilih produk terlebih dahulu untuk membuat QRIS.";
        }
        return;
    }

    const csrfToken =
        document.querySelector('input[name="csrf_token"]')?.value || "";
    const body = new URLSearchParams({
        amount: String(total),
        csrf_token: csrfToken
    });

    if (note) {
        note.textContent = "Menghubungkan ke Midtrans...";
    }

    fetch("midtrans-qris.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "Accept": "application/json"
        },
        body: body.toString()
    })
        .then(function (response) {
            return response.json().then(function (result) {
                if (!response.ok) {
                    throw new Error(result.error || "QRIS Midtrans gagal dibuat.");
                }
                return result;
            });
        })
        .then(function (result) {
            const image = document.createElement("img");
            image.src = result.qr_code_url;
            image.alt = "QRIS Midtrans";
            image.width = 180;
            image.height = 180;
            barcode.replaceChildren(image);
            if (note) {
                note.textContent = "Scan QRIS ini menggunakan aplikasi pembayaran.";
            }
        })
        .catch(function (error) {
            barcode.textContent = "QRIS belum tersedia.";
            if (note) {
                note.textContent = error.message;
            }
        });
}


/*
|--------------------------------------------------------------------------
| VALIDASI FORM TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionForm() {

    const form =
        document.querySelector(
            'form[method="POST"]'
        );


    if (!form) {
        return;
    }


    /*
    | Hanya aktif jika halaman memiliki
    | elemen transaksi create.
    */

    const productCheckboxes =
        form.querySelectorAll(
            ".product-checkbox"
        );


    if (
        productCheckboxes.length === 0
    ) {
        return;
    }


    form.addEventListener(
        "submit",
        function (event) {

            let selectedCount = 0;

            let total = 0;


            productCheckboxes.forEach(
                function (checkbox) {

                    if (!checkbox.checked) {
                        return;
                    }


                    selectedCount++;


                    const row =
                        checkbox.closest(
                            ".product-row"
                        );


                    if (!row) {
                        return;
                    }


                    const quantity =
                        row.querySelector(
                            ".quantity"
                        );


                    const qty =
                        parseInt(
                            quantity?.value
                        ) || 0;

                    const maxStock =
                        parseInt(
                            quantity?.max
                        ) || 0;

                    if (
                        maxStock > 0 &&
                        qty > maxStock
                    ) {
                        event.preventDefault();

                        showTransactionNotice(
                            "Transaksi melebihi stok produk. " +
                            "Stok tersedia: " +
                            maxStock +
                            ", jumlah diminta: " +
                            qty +
                            "."
                        );

                        quantity.focus();

                        return;
                    }


                    const price =
                        parseFloat(
                            checkbox.dataset.price
                        ) || 0;


                    total +=
                        price * qty;
                }
            );


            /*
            | Tidak ada produk
            */

            if (selectedCount === 0) {

                event.preventDefault();


                alert(
                    "Silakan pilih minimal satu produk."
                );


                return;
            }


            /*
            | Pembayaran
            */

            const paymentElement =
                document.getElementById(
                    "payment_amount"
                );


            if (paymentElement) {

                const payment =
                    parseFloat(
                        paymentElement.value
                    ) || 0;


                if (payment < total) {

                    event.preventDefault();


                    alert(
                        "Jumlah pembayaran tidak mencukupi."
                    );


                    paymentElement.focus();


                    return;
                }
            }
        }
    );
}


/*
|--------------------------------------------------------------------------
| PENCARIAN PRODUK
|--------------------------------------------------------------------------
*/

function initProductFilter() {

    const searchProduct =
        document.getElementById(
            "searchProduct"
        );


    const filterCategory =
        document.getElementById(
            "filterCategory"
        );


    const productRows =
        document.querySelectorAll(
            ".product-row"
        );


    const noProductFound =
        document.getElementById(
            "noProductFound"
        );

    const productPagination =
        document.getElementById(
            "productPagination"
        );

    const productsPerPage = 5;
    let currentPage = 1;

    /*
    | Tidak berada di halaman create
    */

    initSearchableFilterSelect();

    if (
        !searchProduct ||
        !filterCategory ||
        productRows.length === 0
    ) {
        return;
    }

    function initSearchableFilterSelect() {
        const select = document.querySelector(".searchable-filter-select");

        if (!select || select.parentElement.querySelector(".product-dropdown")) {
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.className = "product-dropdown";
        wrapper.innerHTML = `
            <button type="button" class="form-control product-dropdown-toggle">
                Semua Kategori
            </button>
            <div class="product-dropdown-menu">
                <input type="search" class="form-control product-dropdown-search"
                    placeholder="Cari kategori..." autocomplete="off">
                <div class="product-dropdown-options"></div>
            </div>
        `;

        select.parentNode.insertBefore(wrapper, select);
        select.style.display = "none";

        const toggle = wrapper.querySelector(".product-dropdown-toggle");
        const search = wrapper.querySelector(".product-dropdown-search");
        const options = wrapper.querySelector(".product-dropdown-options");

        function renderOptions() {
            const keyword = search.value.toLowerCase().trim();
            options.innerHTML = "";

            Array.from(select.options).forEach(function (option) {
                if (option.value && !option.textContent.toLowerCase().includes(keyword)) {
                    return;
                }

                const button = document.createElement("button");
                button.type = "button";
                button.className = "product-dropdown-option";
                button.textContent = option.textContent.trim();
                button.dataset.value = option.value;
                options.appendChild(button);
            });

            if (!options.children.length) {
                options.innerHTML = '<span class="product-dropdown-empty">Kategori tidak ditemukan</span>';
            }
        }

        toggle.addEventListener("click", function () {
            document.querySelectorAll(".product-dropdown.is-open").forEach(function (item) {
                if (item !== wrapper) item.classList.remove("is-open");
            });
            wrapper.classList.toggle("is-open");
            renderOptions();
            if (wrapper.classList.contains("is-open")) {
                const rect = toggle.getBoundingClientRect();
                const menu = wrapper.querySelector(".product-dropdown-menu");
                menu.style.left = `${rect.left}px`;
                menu.style.width = `${rect.width}px`;
                menu.style.top = `${rect.bottom + 3}px`;
                search.focus();
            }
        });

        search.addEventListener("input", renderOptions);
        options.addEventListener("click", function (event) {
            const option = event.target.closest(".product-dropdown-option");
            if (!option) return;

            select.value = option.dataset.value;
            toggle.textContent = option.textContent;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            wrapper.classList.remove("is-open");
        });

        document.addEventListener("click", function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove("is-open");
            }
        });
    }


    function filterProducts() {

        const keyword =
            searchProduct.value
                .toLowerCase()
                .trim();


        const category =
            filterCategory.value;


        const matchingRows = [];


        productRows.forEach(
            function (row) {

                const name =
                    row.dataset.name || "";


                const sku =
                    row.dataset.sku || "";


                const barcode =
                    row.dataset.barcode || "";


                const rowCategory =
                    row.dataset.category || "";


                const cocokKeyword =
                    name.includes(keyword) ||
                    sku.includes(keyword) ||
                    barcode.includes(keyword);


                const cocokCategory =
                    category === "" ||
                    rowCategory === category;


                if (
                    cocokKeyword &&
                    cocokCategory
                ) {
                    matchingRows.push(row);

                } else {
                    row.style.display =
                        "none";
                }
            }
        );

        const totalPages = Math.max(
            1,
            Math.ceil(matchingRows.length / productsPerPage)
        );
        currentPage = Math.min(currentPage, totalPages);
        const firstIndex = (currentPage - 1) * productsPerPage;
        const pageRows = matchingRows.slice(
            firstIndex,
            firstIndex + productsPerPage
        );

        matchingRows.forEach(function (row) {
            row.style.display = "none";
        });
        pageRows.forEach(function (row) {
            row.style.display = "";
        });

        if (noProductFound) {
            noProductFound.style.display =
                matchingRows.length === 0
                    ? ""
                    : "none";
        }

        if (productPagination) {
            if (matchingRows.length <= productsPerPage) {
                productPagination.innerHTML = "";
            } else {
                let pagination = '<small class="text-muted">Menampilkan ' +
                    (firstIndex + 1) + "-" +
                    Math.min(firstIndex + productsPerPage, matchingRows.length) +
                    " dari " + matchingRows.length + " produk</small>";
                pagination += '<nav aria-label="Navigasi produk transaksi"><ul class="pagination pagination-sm mb-0">';
                pagination += '<li class="page-item ' +
                    (currentPage === 1 ? "disabled" : "") +
                    '"><button type="button" class="page-link" data-product-page="' +
                    (currentPage - 1) + '">&laquo;</button></li>';
                const pageStart =
                    Math.floor((currentPage - 1) / 10) * 10 + 1;
                const pageEnd = Math.min(totalPages, pageStart + 9);
                for (let page = pageStart; page <= pageEnd; page++) {
                    pagination += '<li class="page-item ' +
                        (page === currentPage ? "active" : "") +
                        '"><button type="button" class="page-link" data-product-page="' +
                        page + '">' + page + "</button></li>";
                }
                pagination += '<li class="page-item ' +
                    (currentPage === totalPages ? "disabled" : "") +
                    '"><button type="button" class="page-link" data-product-page="' +
                    (currentPage + 1) + '">&raquo;</button></li></ul></nav>';
                productPagination.innerHTML = pagination;
            }
        }
    }


    filterCategory.addEventListener(
        "change",
        function () {
            currentPage = 1;
            filterProducts();
        }
    );

    searchProduct.addEventListener("input", function () {
        currentPage = 1;
        filterProducts();
    });

    if (productPagination) {
        productPagination.addEventListener("click", function (event) {
            const button = event.target.closest("[data-product-page]");
            if (!button || button.parentElement.classList.contains("disabled")) {
                return;
            }
            currentPage = Number(button.dataset.productPage) || 1;
            filterProducts();
        });
    }

    filterProducts();
}


/*
|--------------------------------------------------------------------------
| RIWAYAT TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionHistory() {

    const search =
        document.getElementById(
            "transactionSearch"
        );


    const statusFilter =
        document.getElementById(
            "transactionStatusFilter"
        );


    const paymentFilter =
        document.getElementById(
            "transactionPaymentFilter"
        );


    const table =
        document.getElementById(
            "transactionTable"
        );


    const noResult =
        document.getElementById(
            "transactionNoResult"
        );


    const visibleCount =
        document.getElementById(
            "transactionVisibleCount"
        );


    if (
        !search ||
        !statusFilter ||
        !paymentFilter ||
        !table
    ) {
        return;
    }


    const rows =
        table.querySelectorAll(
            "tbody tr.transaction-row"
        );


    function filterTransactions() {

        const keyword =
            search.value
                .toLowerCase()
                .trim();


        const selectedStatus =
            statusFilter.value
                .toLowerCase();


        const selectedPayment =
            paymentFilter.value
                .toLowerCase();


        let count = 0;


        rows.forEach(
            function (row) {

                const id =
                    (
                        row.dataset.id ||
                        ""
                    ).toLowerCase();


                const customer =
                    (
                        row.dataset.customer ||
                        ""
                    ).toLowerCase();


                const date =
                    (
                        row.dataset.date ||
                        ""
                    ).toLowerCase();


                const rowStatus =
                    (
                        row.dataset.status ||
                        ""
                    ).toLowerCase();


                const rowPayment =
                    (
                        row.dataset.payment ||
                        ""
                    ).toLowerCase();


                const cocokKeyword =
                    keyword === "" ||
                    id.includes(keyword) ||
                    customer.includes(keyword) ||
                    date.includes(keyword);


                const cocokStatus =
                    selectedStatus === "all" ||
                    rowStatus === selectedStatus;


                const cocokPayment =
                    selectedPayment === "all" ||
                    rowPayment === selectedPayment;


                if (
                    cocokKeyword &&
                    cocokStatus &&
                    cocokPayment
                ) {

                    row.style.display =
                        "";


                    count++;

                } else {

                    row.style.display =
                        "none";
                }
            }
        );


        /*
        | Update jumlah transaksi tampil
        */

        if (visibleCount) {

            visibleCount.textContent =
                count.toLocaleString(
                    "id-ID"
                );
        }


        /*
        | Tampilkan pesan jika tidak ada
        */

        if (noResult) {

            noResult.style.display =
                count === 0
                    ? ""
                    : "none";
        }
    }


    search.addEventListener(
        "input",
        filterTransactions
    );


    statusFilter.addEventListener(
        "change",
        filterTransactions
    );


    paymentFilter.addEventListener(
        "change",
        filterTransactions
    );
}


/*
|--------------------------------------------------------------------------
| TOMBOL PRINT DETAIL TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionPrint() {

    const printButtons =
        document.querySelectorAll(
            "[data-transaction-print], [data-print]"
        );


    const receiptSize = document.getElementById("receiptSize");
    const receipt = document.getElementById("receiptPrint");

    function printReceipt() {
        if (!receipt) {
            return;
        }

        document.body.classList.add("receipt-print-mode");
        window.setTimeout(function () {
            window.print();
        }, 100);
    }

    window.addEventListener("afterprint", function () {
        document.body.classList.remove("receipt-print-mode");
    });

    if (receiptSize) {
        receiptSize.addEventListener("change", function () {
            document.body.dataset.receiptSize = this.value;
        });
        document.body.dataset.receiptSize = receiptSize.value;
    }

    printButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            printReceipt();
        });
    });
}


/*
|--------------------------------------------------------------------------
| KONFIRMASI DELETE / BATAL TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionDelete() {

    const deleteForms =
        document.querySelectorAll(
            ".transaction-delete-form"
        );


    deleteForms.forEach(
        function (form) {

            form.addEventListener(
                "submit",
                function (event) {

                    const confirmed =
                        window.confirm(
                            "Apakah Anda yakin ingin membatalkan transaksi ini?"
                        );


                    if (!confirmed) {

                        event.preventDefault();
                    }
                }
            );
        }
    );
}


/*
|--------------------------------------------------------------------------
| DISABLE DOUBLE SUBMIT
|--------------------------------------------------------------------------
*/

function initPreventDoubleSubmit() {

    const transactionForms =
        document.querySelectorAll(
            'form[data-transaction-form]'
        );


    transactionForms.forEach(
        function (form) {

            form.addEventListener(
                "submit",
                function () {

                    const submitButton =
                        form.querySelector(
                            'button[type="submit"]'
                        );


                    if (!submitButton) {
                        return;
                    }


                    /*
                    | Jangan langsung disable
                    | jika browser menemukan error
                    | validasi HTML.
                    */

                    setTimeout(
                        function () {

                            submitButton.disabled =
                                true;


                            submitButton.dataset
                                .originalText =
                                submitButton.innerHTML;


                            submitButton.innerHTML =
                                '<i class="bi bi-hourglass-split"></i> Memproses...';

                        },
                        50
                    );
                }
            );
        }
    );
}


/*
|--------------------------------------------------------------------------
| INISIALISASI SEMUA
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {

        /*
        | CREATE
        */

        initProductCheckbox();

        initQuantity();

        initPayment();

        initProductFilter();

        initTransactionForm();


        /*
        | INDEX / RIWAYAT
        */

        initTransactionHistory();


        /*
        | DETAIL
        */

        initTransactionPrint();


        /*
        | DELETE
        */

        initTransactionDelete();


        /*
        | Proteksi double submit
        */

        initPreventDoubleSubmit();


        /*
        | Hitung total awal
        | jika halaman create.
        */

        if (
            document.querySelector(
                ".product-row"
            )
        ) {

            hitungTotal();
        }
    }
);

/* SOURCE: stock-movements.js */

(function () {
    "use strict";

    function initializeProductFilter() {
        var select = document.querySelector("[data-searchable-products]");
        var search = document.querySelector(".product-filter-search");

        if (!select || !search) {
            return;
        }

        var wrapper = document.createElement("div");
        wrapper.className = "movement-product-dropdown";
        wrapper.innerHTML =
            '<button type="button" class="form-control movement-product-toggle">Semua Produk</button>' +
            '<div class="movement-product-menu">' +
                '<input type="search" class="form-control movement-product-search" placeholder="Cari nama produk..." autocomplete="off">' +
                '<div class="movement-product-options"></div>' +
            '</div>';

        search.parentNode.insertBefore(wrapper, search);
        search.remove();
        wrapper.appendChild(select);
        select.style.display = "none";

        var toggle = wrapper.querySelector(".movement-product-toggle");
        var menu = wrapper.querySelector(".movement-product-menu");
        var menuSearch = wrapper.querySelector(".movement-product-search");
        var options = wrapper.querySelector(".movement-product-options");

        Array.prototype.forEach.call(select.options, function (option) {
            if (option.selected) {
                toggle.textContent = option.textContent.trim();
            }
        });

        function renderOptions() {
            var keyword = menuSearch.value.toLowerCase().trim();
            options.innerHTML = "";

            Array.prototype.forEach.call(select.options, function (option) {
                if (option.textContent.toLowerCase().indexOf(keyword) === -1) {
                    return;
                }

                var button = document.createElement("button");
                button.type = "button";
                button.className = "movement-product-option";
                button.textContent = option.textContent.trim();
                button.dataset.value = option.value;
                options.appendChild(button);
            });

            if (!options.children.length) {
                options.innerHTML = '<span class="movement-product-empty">Produk tidak ditemukan</span>';
            }
        }

        toggle.addEventListener("click", function () {
            document.querySelectorAll(".movement-product-dropdown.is-open").forEach(function (item) {
                if (item !== wrapper) {
                    item.classList.remove("is-open");
                }
            });

            var isOpen = wrapper.classList.toggle("is-open");

            if (isOpen) {
                renderOptions();
                menuSearch.value = "";
                var toggleRect = toggle.getBoundingClientRect();
                var menuHeight = Math.min(300, window.innerHeight - 24);
                var spaceBelow = window.innerHeight - toggleRect.bottom;
                var menuTop = spaceBelow >= menuHeight + 4
                    ? toggleRect.bottom + 4
                    : Math.max(12, toggleRect.top - menuHeight - 4);

                menu.style.top = menuTop + "px";
                menu.style.left = toggleRect.left + "px";
                menu.style.width = toggleRect.width + "px";
                menuSearch.focus();
            }
        });

        menuSearch.addEventListener("input", renderOptions);
        window.addEventListener("resize", function () {
            if (!wrapper.classList.contains("is-open")) {
                return;
            }

            var rect = toggle.getBoundingClientRect();
            menu.style.left = rect.left + "px";
            menu.style.width = rect.width + "px";
            menu.style.top = Math.max(12, rect.top - Math.min(300, window.innerHeight - 24) - 4) + "px";
        });
        options.addEventListener("click", function (event) {
            var option = event.target.closest(".movement-product-option");

            if (!option) {
                return;
            }

            select.value = option.dataset.value;
            toggle.textContent = option.textContent;
            wrapper.classList.remove("is-open");
        });

        document.addEventListener("click", function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove("is-open");
            }
        });
    }

    initializeProductFilter();

    var printButtons = document.querySelectorAll("[data-print]");

    if (!printButtons.length) {
        return;
    }

    function printMovements() {
        window.setTimeout(function () {
            window.print();
        }, 50);
    }

    printButtons.forEach(function (button) {
        button.addEventListener("click", printMovements);
    });

}());

