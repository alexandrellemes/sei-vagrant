﻿CKEDITOR.plugins.add("zoom",{requires:"richcombo",init:function(e){var f=e.config,d=CKEDITOR.document.getHead().append("style");d.setAttribute("type","text/css");d.$.styleSheet?d.$.styleSheet.cssText=".cke_combopanel__zoom { height: 200px; width: 100px; }.cke_combo__zoom .cke_combo_text { width: 40px;}":d.$.innerHTML=".cke_combopanel__zoom { height: 200px; width: 100px; }.cke_combo__zoom .cke_combo_text { width: 40px;}";e.ui.addRichCombo("Zoom",{label:"Zoom",title:"Zoom",multiSelect:!1,className:"zoom",
modes:{wysiwyg:1,source:1},panel:{css:[CKEDITOR.skin.getPath("editor")].concat(f.contentsCss)},init:function(){var b=[50,75,100,125,150,200,400],a;this.startGroup("Zoom level");for(i=0;i<b.length;i++)a=b[i],this.add(a,a+" %",a+" %");a=CKEDITOR.config.zoom?CKEDITOR.config.zoom:100;this.setValue(a,a+" %");CKEDITOR.config.zoom=a},onRender:function(){e.on("mode",function(b){if(this.lastValue)this.onClick(this.lastValue)},this);zoom=CKEDITOR.config.zoom?CKEDITOR.config.zoom:100;CKEDITOR.config.zoom=zoom;
CKEDITOR.on("currentInstance",function(b){this.setValue(CKEDITOR.config.zoom,CKEDITOR.config.zoom+" %")},this);CKEDITOR.on("instanceReady",function(b){this.apply(b.editor)},this)},apply:function(b){var a=b.editable().$,c=CKEDITOR.config.zoom||100;a.style.width=100==c||CKEDITOR.env.ie&&7==CKEDITOR.env.version?"auto":Math.floor(1E4/c-1)+"%";CKEDITOR.env.gecko?(a.style.MozTransformOrigin="top left",a.style.MozTransform="scale("+c/100+")"):CKEDITOR.env.webkit?(a.style.WebkitTransformOrigin="top left",
a.style.WebkitTransform="scale("+c/100+")"):CKEDITOR.env.ie?(a.style.zoom=c/100,7<CKEDITOR.env.version&&(b.document.getDocumentElement().$.style.overflowX="hidden")):(a.style.OTransformOrigin="top left",a.style.OTransform="scale("+c/100+")",a.style.TransformOrigin="top left",a.style.Transform="scale("+c/100+")");this.setValue(CKEDITOR.config.zoom,CKEDITOR.config.zoom+" %");this.lastValue=c;b.fire("afterZoom",null,b)},onClick:function(b){if(b!=CKEDITOR.config.zoom){CKEDITOR.config.zoom=b;var a=document.getElementById("hdnInfraPrefixoCookie").value;
infraCriarCookie(a+"_zoom_editor",b,365);for(inst in CKEDITOR.instances)this.apply(CKEDITOR.instances[inst])}}})}});