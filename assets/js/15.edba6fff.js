(window.webpackJsonp=window.webpackJsonp||[]).push([[15],{373:function(t,s,a){"use strict";a.r(s);var n=a(44),e=Object(n.a)({},(function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"регистрация-транспортов"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#регистрация-транспортов"}},[t._v("#")]),t._v(" Регистрация транспортов")]),t._v(" "),a("div",{staticClass:"custom-block warning"},[a("p",{staticClass:"custom-block-title"},[t._v("ВАЖНО")]),t._v(" "),a("p",[t._v("Начиная с версии "),a("code",[t._v("5.1")]),t._v(" пакета "),a("code",[t._v("symfony/messenger")]),t._v(" транспорты AMQP, Redis и Doctrine вынесены в отдельные пакеты и в будущем будут удалены из основного пакета.")])]),t._v(" "),a("h2",{attrs:{id:"регистрация-транспорта-на-примере-redis"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#регистрация-транспорта-на-примере-redis"}},[t._v("#")]),t._v(" Регистрация транспорта на примере Redis")]),t._v(" "),a("h3",{attrs:{id:"composer"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#composer"}},[t._v("#")]),t._v(" Composer")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[a("span",{pre:!0,attrs:{class:"token function"}},[t._v("composer")]),t._v(" require symfony/redis-messenger\n")])])]),a("h3",{attrs:{id:"регистрация-фабрики"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#регистрация-фабрики"}},[t._v("#")]),t._v(" Регистрация фабрики")]),t._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token php language-php"}},[a("span",{pre:!0,attrs:{class:"token delimiter important"}},[t._v("<?php")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// local/php_interface/init.php")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("Bitrix"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Main"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Loader")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("Bsi"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Queue"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Queue")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("Symfony"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Component"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Messenger"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Bridge"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Redis"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Transport"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("RedisTransportFactory")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("if")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Loader")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("includeModule")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'bsi.queue'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$queue")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Queue")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("getInstance")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$queue")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("->")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("registerTransportFactory")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'redis'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("RedisTransportFactory")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("class")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$queue")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("->")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("boot")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n")])])])]),a("h3",{attrs:{id:"пример-конфигурации"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#пример-конфигурации"}},[t._v("#")]),t._v(" Пример конфигурации")]),t._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// ...")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'transports'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=>")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n       "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'async'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=>")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n           "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'dsn'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=>")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[t._v("'redis://redis:6379/messages'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n       "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// ...")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])]),a("div",{staticClass:"custom-block warning"},[a("p",{staticClass:"custom-block-title"},[t._v("ВАЖНО")]),t._v(" "),a("p",[t._v("Обработчики должны добавляться до инициализации системы очередей (вызова метода "),a("code",[t._v("boot()")]),t._v(").")])]),t._v(" "),a("p",[a("strong",[t._v("Ссылки по теме:")])]),t._v(" "),a("ul",[a("li",[a("a",{attrs:{href:"https://symfony.com/doc/current/messenger.html",target:"_blank",rel:"noopener noreferrer"}},[t._v("Messenger: Sync & Queued Message Handling"),a("OutboundLink")],1)])])])}),[],!1,null,null,null);s.default=e.exports}}]);