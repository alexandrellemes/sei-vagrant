﻿/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
(function(){function r(a,c){var b=0,d;for(d in c)if(c[d]==a){b=1;break}return b}var l="",w=function(){function a(){b.once("focus",f);b.once("blur",c)}function c(b){b=b.editor;var c=d.getScayt(b),n=b.elementMode==CKEDITOR.ELEMENT_MODE_INLINE;c&&(d.setPaused(b,!c.disabled),d.setControlId(b,c.id),c.destroy(!0),delete d.instances[b.name],n&&a())}var b=this,f=function(a){if("undefined"==typeof d.instances[b.name]&&null==d.instances[b.name]){var c=b.config;a={};"BODY"==b.editable().$.nodeName?a.srcNodeRef=
b.document.getWindow().$.frameElement:a.srcNodeRef=b.editable().$;a.assocApp="CKEDITOR."+CKEDITOR.version+"@"+CKEDITOR.revision;a.customerid=c.scayt_customerid||"1:WvF0D4-UtPqN1-43nkD4-NKvUm2-daQqk3-LmNiI-z7Ysb4-mwry24-T8YrS3-Q2tpq2";a.customDictionaryIds=c.scayt_customDictionaryIds||"";a.userDictionaryName=c.scayt_userDictionaryName||"";a.sLang=c.scayt_sLang||"en_US";a.onLoad=function(){CKEDITOR.env.ie&&8>CKEDITOR.env.version||this.addStyle(this.selectorCss(),"padding-bottom: 2px !important;");b.editable().hasFocus&&
!d.isControlRestored(b)&&this.focus()};c=window.scayt_custom_params;if("object"==typeof c)for(var n in c)a[n]=c[n];d.getControlId(b)&&(a.id=d.getControlId(b));var f=new window.scayt(a);f.afterMarkupRemove.push(function(a){(new CKEDITOR.dom.element(a,f.document)).mergeSiblings()});if(n=d.instances[b.name])f.sLang=n.sLang,f.option(n.option()),f.paused=n.paused;d.instances[b.name]=f;try{f.setDisabled(!1===d.isPaused(b))}catch(e){}b.fire("showScaytState")}};b.elementMode==CKEDITOR.ELEMENT_MODE_INLINE?
a():b.on("contentDom",f);b.on("contentDomUnload",function(){for(var a=CKEDITOR.document.getElementsByTag("script"),b=/^dojoIoScript(\d+)$/i,c=/^https?:\/\/svc\.webspellchecker\.net\/spellcheck\/script\/ssrv\.cgi/i,d=0;d<a.count();d++){var f=a.getItem(d),e=f.getId(),g=f.getAttribute("src");e&&g&&e.match(b)&&g.match(c)&&f.remove()}});b.on("beforeCommandExec",function(a){"source"==a.data.name&&"source"==b.mode&&d.markControlRestore(b)});b.on("afterCommandExec",function(a){!d.isScaytEnabled(b)||"wysiwyg"!=
b.mode||"undo"!=a.data.name&&"redo"!=a.data.name||(d.getScayt(b).setDisabled(!0),d.refresh_timeout&&window.clearTimeout(d.refresh_timeout),d.refresh_timeout=window.setTimeout(function(){d.getScayt(b).setDisabled(!1);d.getScayt(b).focus();d.getScayt(b).refresh()},10))});b.on("destroy",c);b.on("setData",c);b.on("insertElement",function(){var a=d.getScayt(b);d.isScaytEnabled(b)&&(CKEDITOR.env.ie&&b.getSelection().unlock(!0),window.setTimeout(function(){a.focus();a.refresh()},10))},this,null,50);b.on("insertHtml",
function(){var a=d.getScayt(b);d.isScaytEnabled(b)&&(CKEDITOR.env.ie&&b.getSelection().unlock(!0),window.setTimeout(function(){a.focus();a.refresh()},10))},this,null,50);b.on("scaytDialog",function(a){a.data.djConfig=window.djConfig;a.data.scayt_control=d.getScayt(b);a.data.tab=l;a.data.scayt=window.scayt});var e=b.dataProcessor;(e=e&&e.htmlFilter)&&e.addRules({elements:{span:function(a){if(a.attributes["data-scayt_word"]&&a.attributes["data-scaytid"])return delete a.name,a}}});var e=CKEDITOR.plugins.undo.Image.prototype,
g="function"==typeof e.equalsContent?"equalsContent":"equals";e[g]=CKEDITOR.tools.override(e[g],function(a){return function(b){var c=this.contents,f=b.contents,e=d.getScayt(this.editor);e&&d.isScaytReady(this.editor)&&(this.contents=e.reset(c)||"",b.contents=e.reset(f)||"");e=a.apply(this,arguments);this.contents=c;b.contents=f;return e}});e=CKEDITOR.editor.prototype;e.checkDirty=CKEDITOR.tools.override(e.checkDirty,function(a){return function(){var b=null,c=d.getScayt(this);c&&d.isScaytReady(this)?
(b=c.reset(this.getSnapshot()),c=c.reset(this._.previousValue),b=b!==c):b=a.apply(this);return b}});b.document&&(b.elementMode!=CKEDITOR.ELEMENT_MODE_INLINE||b.focusManager.hasFocus)&&f()};CKEDITOR.plugins.scayt={engineLoaded:!1,instances:{},controlInfo:{},setControlInfo:function(a,c){a&&a.name&&"object"!=typeof this.controlInfo[a.name]&&(this.controlInfo[a.name]={});for(var b in c)this.controlInfo[a.name][b]=c[b]},isControlRestored:function(a){return a&&a.name&&this.controlInfo[a.name]?this.controlInfo[a.name].restored:
!1},markControlRestore:function(a){this.setControlInfo(a,{restored:!0})},setControlId:function(a,c){this.setControlInfo(a,{id:c})},getControlId:function(a){return a&&a.name&&this.controlInfo[a.name]&&this.controlInfo[a.name].id?this.controlInfo[a.name].id:null},setPaused:function(a,c){this.setControlInfo(a,{paused:c})},isPaused:function(a){if(a&&a.name&&this.controlInfo[a.name])return this.controlInfo[a.name].paused},getScayt:function(a){return this.instances[a.name]},isScaytReady:function(a){return!0===
this.engineLoaded&&"undefined"!==typeof window.scayt&&this.getScayt(a)},isScaytEnabled:function(a){return(a=this.getScayt(a))?!1===a.disabled:!1},getUiTabs:function(a){var c=[],b=a.config.scayt_uiTabs||"1,1,1",b=b.split(",");b[3]="1";for(var d=0;4>d;d++)c[d]="undefined"!=typeof window.scayt&&"undefined"!=typeof window.scayt.uiTags?parseInt(b[d],10)&&window.scayt.uiTags[d]:parseInt(b[d],10);"object"==typeof a.plugins.wsc?c.push(1):c.push(0);return c},loadEngine:function(a){if(CKEDITOR.env.gecko&&10900>
CKEDITOR.env.version||CKEDITOR.env.opera||CKEDITOR.env.air)return a.fire("showScaytState");if(!0===this.engineLoaded)return w.apply(a);if(-1==this.engineLoaded)return CKEDITOR.on("scaytReady",function(){w.apply(a)});CKEDITOR.on("scaytReady",w,a);CKEDITOR.on("scaytReady",function(){this.engineLoaded=!0},this,null,0);this.engineLoaded=-1;var c=document.location.protocol,c=-1!=c.search(/https?:/)?c:"http:",c=a.config.scayt_srcUrl||c+"//svc.webspellchecker.net/scayt26/loader__base.js",b=d.parseUrl(c).path+
"/";void 0==window.scayt?(CKEDITOR._djScaytConfig={baseUrl:b,addOnLoad:[function(){CKEDITOR.fireOnce("scaytReady")}],isDebug:!1},CKEDITOR.document.getHead().append(CKEDITOR.document.createElement("script",{attributes:{type:"text/javascript",async:"true",src:c}}))):CKEDITOR.fireOnce("scaytReady");return null},parseUrl:function(a){var c;return a.match&&(c=a.match(/(.*)[\/\\](.*?\.\w+)$/))?{path:c[1],file:c[2]}:a}};var d=CKEDITOR.plugins.scayt,u=function(a,c,b,d,e,g,h){a.addCommand(d,e);a.addMenuItem(d,
{label:b,command:d,group:g,order:h})},z={preserveState:!0,editorFocus:!1,canUndo:!1,exec:function(a){if(d.isScaytReady(a)){var c=d.isScaytEnabled(a);this.setState(c?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_ON);a=d.getScayt(a);a.focus();a.setDisabled(c)}else!a.config.scayt_autoStartup&&0<=d.engineLoaded&&(a.focus(),this.setState(CKEDITOR.TRISTATE_DISABLED),d.loadEngine(a))}};CKEDITOR.plugins.add("scayt",{requires:"menubutton,dialog",lang:"af,ar,bg,bn,bs,ca,cs,cy,da,de,el,en-au,en-ca,en-gb,en,eo,es,et,eu,fa,fi,fo,fr-ca,fr,gl,gu,he,hi,hr,hu,is,it,ja,ka,km,ko,lt,lv,mk,mn,ms,nb,nl,no,pl,pt-br,pt,ro,ru,sk,sl,sr-latn,sr,sv,th,tr,ug,uk,vi,zh-cn,zh",
icons:"scayt",hidpi:!0,beforeInit:function(a){var c=a.config.scayt_contextMenuItemsOrder||"suggest|moresuggest|control",b="";if((c=c.split("|"))&&c.length)for(var d=0;d<c.length;d++)b+="scayt_"+c[d]+(c.length!=parseInt(d,10)+1?",":"");a.config.menu_groups=b+","+a.config.menu_groups},checkEnvironment:function(){return CKEDITOR.env.opera||CKEDITOR.env.air?0:1},init:function(a){var c=a.dataProcessor&&a.dataProcessor.dataFilter,b={elements:{span:function(a){var b=a.attributes;b&&b["data-scaytid"]&&delete a.name}}};
c&&c.addRules(b);var f={},e={},g=a.addCommand("scaytcheck",z);CKEDITOR.dialog.add("scaytcheck",CKEDITOR.getUrl(this.path+"dialogs/options.js"));c=d.getUiTabs(a);a.addMenuGroup("scaytButton");a.addMenuGroup("scayt_suggest",-10);a.addMenuGroup("scayt_moresuggest",-9);a.addMenuGroup("scayt_control",-8);var b={},h=a.lang.scayt;b.scaytToggle={label:h.enable,command:"scaytcheck",group:"scaytButton"};1==c[0]&&(b.scaytOptions={label:h.options,group:"scaytButton",onClick:function(){l="options";a.openDialog("scaytcheck")}});
1==c[1]&&(b.scaytLangs={label:h.langs,group:"scaytButton",onClick:function(){l="langs";a.openDialog("scaytcheck")}});1==c[2]&&(b.scaytDict={label:h.dictionariesTab,group:"scaytButton",onClick:function(){l="dictionaries";a.openDialog("scaytcheck")}});b.scaytAbout={label:a.lang.scayt.about,group:"scaytButton",onClick:function(){l="about";a.openDialog("scaytcheck")}};1==c[4]&&(b.scaytWSC={label:a.lang.wsc.toolbar,group:"scaytButton",command:"checkspell"});a.addMenuItems(b);a.ui.add("Scayt",CKEDITOR.UI_MENUBUTTON,
{label:h.title,title:CKEDITOR.env.opera?h.opera_title:h.title,modes:{wysiwyg:this.checkEnvironment()},toolbar:"spellchecker,20",onRender:function(){g.on("state",function(){this.setState(g.state)},this)},onMenu:function(){var b=d.isScaytEnabled(a);a.getMenuItem("scaytToggle").label=h[b?"disable":"enable"];var c=d.getUiTabs(a);return{scaytToggle:CKEDITOR.TRISTATE_OFF,scaytOptions:b&&c[0]?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_DISABLED,scaytLangs:b&&c[1]?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_DISABLED,
scaytDict:b&&c[2]?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_DISABLED,scaytAbout:b&&c[3]?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_DISABLED,scaytWSC:c[4]?CKEDITOR.TRISTATE_OFF:CKEDITOR.TRISTATE_DISABLED}}});a.contextMenu&&a.addMenuItems&&a.contextMenu.addListener(function(b,c){if(!d.isScaytEnabled(a)||c.getRanges()[0].checkReadOnly())return null;var g=d.getScayt(a),t=g.getScaytNode();if(!t)return null;var k=g.getWord(t);if(!k)return null;var l=g.getLang(),p=a.config.scayt_contextCommands||"all",k=window.scayt.getSuggestion(k,
l),p=p.split("|"),q;for(q in f)delete a._.menuItems[q],delete a.commands[q];for(q in e)delete a._.menuItems[q],delete a.commands[q];if(k&&k.length){f={};e={};q=a.config.scayt_moreSuggestions||"on";var l=!1,x=a.config.scayt_maxSuggestions;"number"!=typeof x&&(x=5);!x&&(x=k.length);for(var m=0,w=k.length;m<w;m+=1){var v="scayt_suggestion_"+k[m].replace(" ","_"),y=function(a,b){return{exec:function(){g.replace(a,b)}}}(t,k[m]);m<x?(u(a,"button_"+v,k[m],v,y,"scayt_suggest",m+1),e[v]=CKEDITOR.TRISTATE_OFF):
"on"==q&&(u(a,"button_"+v,k[m],v,y,"scayt_moresuggest",m+1),f[v]=CKEDITOR.TRISTATE_OFF,l=!0)}l&&(a.addMenuItem("scayt_moresuggest",{label:h.moreSuggestions,group:"scayt_moresuggest",order:10,getItems:function(){return f}}),e.scayt_moresuggest=CKEDITOR.TRISTATE_OFF)}else u(a,"no_sugg",h.noSuggestions,"scayt_no_sugg",{exec:function(){}},"scayt_control",1,!0),e.scayt_no_sugg=CKEDITOR.TRISTATE_OFF;if(r("all",p)||r("ignore",p))u(a,"ignore",h.ignore,"scayt_ignore",{exec:function(){g.ignore(t)}},"scayt_control",
2),e.scayt_ignore=CKEDITOR.TRISTATE_OFF;if(r("all",p)||r("ignoreall",p))u(a,"ignore_all",h.ignoreAll,"scayt_ignore_all",{exec:function(){g.ignoreAll(t)}},"scayt_control",3),e.scayt_ignore_all=CKEDITOR.TRISTATE_OFF;if(r("all",p)||r("add",p))u(a,"add_word",h.addWord,"scayt_add_word",{exec:function(){window.scayt.addWordToUserDictionary(t)}},"scayt_control",4),e.scayt_add_word=CKEDITOR.TRISTATE_OFF;g.fireOnContextMenu&&g.fireOnContextMenu(a);return e});c=function(b){b.removeListener();CKEDITOR.env.opera||
CKEDITOR.env.air?g.setState(CKEDITOR.TRISTATE_DISABLED):g.setState(d.isScaytEnabled(a)?CKEDITOR.TRISTATE_ON:CKEDITOR.TRISTATE_OFF)};a.on("showScaytState",c);a.on("instanceReady",c);if(a.config.scayt_autoStartup)a.on("instanceReady",function(){d.loadEngine(a)})},afterInit:function(a){var c,b=function(a){if(a.hasAttribute("data-scaytid"))return!1};a._.elementsPath&&(c=a._.elementsPath.filters)&&c.push(b);a.addRemoveFormatFilter&&a.addRemoveFormatFilter(b)}})})();