(()=>{var e,t,r={5540:(e,t,r)=>{var n={"./ru.js":6038};function a(e){var t=o(e);return r(t)}function o(e){if(!r.o(n,e)){var t=new Error("Cannot find module '"+e+"'");throw t.code="MODULE_NOT_FOUND",t}return n[e]}a.keys=function(){return Object.keys(n)},a.resolve=o,e.exports=a,a.id=5540},767:(e,t,r)=>{"use strict";r(1038),r(8783),r(9554),r(1539),r(4747),r(7941),r(8309);var n=r(144),a=r(5614),o=r.n(a),s=r(7186),i=r.n(s),u=r(7626),l=r.n(u),d=r(7665),c=r.n(d),f=r(7698),v=r.n(f),p=r(7787),m=r.n(p),h=r(2173),b=r.n(h),y=r(3229),g=r.n(y),P=r(7099),O=r.n(P),Z=r(6426),k=r.n(Z),w=r(1530),j=r.n(w),E=r(905),_=r.n(E),T=r(9840);r(1802).default.use(T.Z),n.ZP.prototype.$ELEMENT={size:"small"},n.ZP.use(_()),n.ZP.use(j()),n.ZP.use(k()),n.ZP.use(O()),n.ZP.use(g()),n.ZP.use(b()),n.ZP.use(m()),n.ZP.use(v()),n.ZP.use(c()),n.ZP.use(l()),n.ZP.use(i()),n.ZP.use(o());r(6992),r(3948),r(4916),r(4723);var C=r(7152);n.ZP.use(C.Z);const x=new C.Z({locale:"ru",fallbackLocale:"ru",messages:(M=r(5540),L={},M.keys().forEach((function(e){var t=e.match(/([A-Za-z0-9-_]+)\./i);if(t&&t.length>1){var r=t[1];L[r]=M(e).default}})),L)});var M,L;r(8674);const N={Dashboard:function(){return Promise.all([r.e(484),r.e(447)]).then(r.bind(r,6099))}};n.ZP.config.productionTip=!1,document.addEventListener("DOMContentLoaded",(function(){Array.from(document.querySelectorAll(".vue-shell")).forEach((function(e){var t=e.dataset.initial;if(void 0!==t)try{t=JSON.parse(t)}catch(e){console.warn(e)}void 0!==N[e.dataset.name]&&new n.ZP({el:e,i18n:x,render:function(r){return r(N[e.dataset.name],{props:{initial:t}})}})}))}))},6038:(e,t,r)=>{"use strict";r.r(t),r.d(t,{default:()=>n});const n={label:{uuid:"UUID",message:"Сообщение",status:"Статус",sent_at:"Дата отправки",received_at:"Дата получения",handled_at:"Дата обработки",failed_at:"Дата ошибки",transport_name:"Получатель",buses:"Шины",problem:"Проблема",ok:"ОК",findMessages:"Поиск сообщений"},title:{status:"Статус",consumers:"Подписчики",stats:"Статистика",messages:"Сообщения",summary:"Сводка",data:"Данные",errors:"Ошибки"},tooltip:{autoUpdate:"Автоматическое обновление",notFoundConsumers:"Не обнаружено активных подписчиков"},enums:{status:{sent:"Отправлено",received:"Получено",handled:"Обработано",failed:"С ошибками"},datePreset:{last5m:"Последние 5 минут",last15m:"Последние 15 минут",last30m:"Последние 30 минут",last1h:"Последний 1 час",last3h:"Последние 3 часа",last6h:"Последние 6 часов",last12h:"Последние 12 часов",last24h:"Последние 24 часа",last2d:"Последние 2 дня",last7d:"Последние 7 дней",last30d:"Последние 30 дней",last60d:"Последние 60 дней",last90d:"Последние 90 дней",last6M:"Последние 6 месяцев",last1y:"Последний 1 год"}}}},2640:e=>{"use strict";e.exports=BX}},n={};function a(e){var t=n[e];if(void 0!==t)return t.exports;var o=n[e]={exports:{}};return r[e].call(o.exports,o,o.exports,a),o.exports}a.m=r,e=[],a.O=(t,r,n,o)=>{if(!r){var s=1/0;for(d=0;d<e.length;d++){for(var[r,n,o]=e[d],i=!0,u=0;u<r.length;u++)(!1&o||s>=o)&&Object.keys(a.O).every((e=>a.O[e](r[u])))?r.splice(u--,1):(i=!1,o<s&&(s=o));if(i){e.splice(d--,1);var l=n();void 0!==l&&(t=l)}}return t}o=o||0;for(var d=e.length;d>0&&e[d-1][2]>o;d--)e[d]=e[d-1];e[d]=[r,n,o]},a.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return a.d(t,{a:t}),t},a.d=(e,t)=>{for(var r in t)a.o(t,r)&&!a.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},a.f={},a.e=e=>Promise.all(Object.keys(a.f).reduce(((t,r)=>(a.f[r](e,t),t)),[])),a.u=e=>e+"."+{447:"70e017ad",484:"ce118c7d"}[e]+".js",a.miniCssF=e=>{},a.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),a.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),t={},a.l=(e,r,n,o)=>{if(t[e])t[e].push(r);else{var s,i;if(void 0!==n)for(var u=document.getElementsByTagName("script"),l=0;l<u.length;l++){var d=u[l];if(d.getAttribute("src")==e){s=d;break}}s||(i=!0,(s=document.createElement("script")).charset="utf-8",s.timeout=120,a.nc&&s.setAttribute("nonce",a.nc),s.src=e),t[e]=[r];var c=(r,n)=>{s.onerror=s.onload=null,clearTimeout(f);var a=t[e];if(delete t[e],s.parentNode&&s.parentNode.removeChild(s),a&&a.forEach((e=>e(n))),r)return r(n)},f=setTimeout(c.bind(null,void 0,{type:"timeout",target:s}),12e4);s.onerror=c.bind(null,s.onerror),s.onload=c.bind(null,s.onload),i&&document.head.appendChild(s)}},a.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.p="/bitrix/js/bsi.queue/",(()=>{var e={143:0};a.f.j=(t,r)=>{var n=a.o(e,t)?e[t]:void 0;if(0!==n)if(n)r.push(n[2]);else{var o=new Promise(((r,a)=>n=e[t]=[r,a]));r.push(n[2]=o);var s=a.p+a.u(t),i=new Error;a.l(s,(r=>{if(a.o(e,t)&&(0!==(n=e[t])&&(e[t]=void 0),n)){var o=r&&("load"===r.type?"missing":r.type),s=r&&r.target&&r.target.src;i.message="Loading chunk "+t+" failed.\n("+o+": "+s+")",i.name="ChunkLoadError",i.type=o,i.request=s,n[1](i)}}),"chunk-"+t,t)}},a.O.j=t=>0===e[t];var t=(t,r)=>{var n,o,[s,i,u]=r,l=0;if(s.some((t=>0!==e[t]))){for(n in i)a.o(i,n)&&(a.m[n]=i[n]);if(u)var d=u(a)}for(t&&t(r);l<s.length;l++)o=s[l],a.o(e,o)&&e[o]&&e[o][0](),e[o]=0;return a.O(d)},r=self.webpackChunk=self.webpackChunk||[];r.forEach(t.bind(null,0)),r.push=t.bind(null,r.push.bind(r))})();var o=a.O(void 0,[57],(()=>a(767)));o=a.O(o)})();