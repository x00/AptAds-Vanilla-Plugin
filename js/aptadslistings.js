/*
 Sticky-kit v1.0.4 | WTFPL | Leaf Corcoran 2014 | http://leafo.net
*/
(function(){var b,m;b=this.jQuery;m=b(window);b.fn.stick_in_parent=function(e){var u,n,f,s,B,l,C;null==e&&(e={});s=e.sticky_class;u=e.inner_scrolling;f=e.parent;n=e.offset_top;null==n&&(n=0);null==f&&(f=void 0);null==u&&(u=!0);null==s&&(s="is_stuck");B=function(a,e,l,v,y,p,t){var q,z,k,w,c,d,A,x,g,h;if(!a.data("sticky_kit")){a.data("sticky_kit",!0);d=a.parent();null!=f&&(d=d.closest(f));if(!d.length)throw"failed to find stick parent";q=k=!1;g=b("<div />");g.css("position",a.css("position"));A=function(){var c,
b;c=parseInt(d.css("border-top-width"),10);b=parseInt(d.css("padding-top"),10);e=parseInt(d.css("padding-bottom"),10);l=d.offset().top+c+b;v=d.height();c=k?(k=!1,q=!1,a.insertAfter(g).css({position:"",top:"",width:"",bottom:""}),g.detach(),!0):void 0;y=a.offset().top-parseInt(a.css("margin-top"),10)-n;p=a.outerHeight(!0);t=a.css("float");g.css({width:a.outerWidth(!0),height:p,display:a.css("display"),"vertical-align":a.css("vertical-align"),"float":t});if(c)return h()};A();if(p!==v)return w=void 0,
c=n,h=function(){var b,h,r,f;r=m.scrollTop();null!=w&&(h=r-w);w=r;k?(f=r+p+c>v+l,q&&!f&&(q=!1,a.css({position:"fixed",bottom:"",top:c}).trigger("sticky_kit:unbottom")),r<y&&(k=!1,c=n,"left"!==t&&"right"!==t||a.insertAfter(g),g.detach(),b={position:"",width:"",top:""},a.css(b).removeClass(s).trigger("sticky_kit:unstick")),u&&(b=m.height(),p>b&&!q&&(c-=h,c=Math.max(b-p,c),c=Math.min(n,c),k&&a.css({top:c+"px"})))):r>y&&(k=!0,b={position:"fixed",top:c},b.width="border-box"===a.css("box-sizing")?a.outerWidth()+
"px":a.width()+"px",a.css(b).addClass(s).after(g),"left"!==t&&"right"!==t||g.append(a),a.trigger("sticky_kit:stick"));if(k&&(null==f&&(f=r+p+c>v+l),!q&&f))return q=!0,"static"===d.css("position")&&d.css({position:"relative"}),a.css({position:"absolute",bottom:e,top:"auto"}).trigger("sticky_kit:bottom")},x=function(){A();return h()},z=function(){m.off("scroll",h);b(document.body).off("sticky_kit:recalc",x);a.off("sticky_kit:detach",z);a.removeData("sticky_kit");a.css({position:"",bottom:"",top:""});
d.position("position","");if(k)return a.insertAfter(g).removeClass(s),g.remove()},m.on("touchmove",h),m.on("scroll",h),m.on("resize",x),b(document.body).on("sticky_kit:recalc",x),a.on("sticky_kit:detach",z),setTimeout(h,0)}};l=0;for(C=this.length;l<C;l++)e=this[l],B(b(e));return this}}).call(this);


jQuery(document).ready(function($){
    var help = $('<a class="SmallButton Popup AptAdsHelpBtn" href="'+gdn.url('/settings/aptads/info')+'">'+gdn.definition('AptAdsHelpBtn')+'</a>');
    $('.AptAdTabs').append(help);
    
    $('#Form_Page').change(function(){
        if($(this).val()==''){
            $('#Form_Page1').removeAttr('disabled');
            
        }else{
            $('#Form_Page1').attr('disabled','disabled');
            $('#Form_Page1').val($(this).val());
        }
        
    });
    
    $('#Form_Page').trigger('change');
    
    $('#Form_Embed').change(function(){
    });
    
    var templateHelp = $.parseJSON(gdn.definition('AptAdsTemplateHelp'));
    
    $('#Form_Template').change(function(){
        var templateHelpTxt = '';
        if($(this).val() in templateHelp){
            templateHelpTxt = templateHelp[$(this).val()]
        }
        $('.AptAdsTemplateHelp').html($('<div class="AptAdsTemplateHelpContainer" />').html(templateHelpTxt));
        $('.AptAdsTemplateHelp').stick_in_parent();
        
    });
    
    $('#Form_Template').trigger('change');
    
    $('#Form_Fit').change(function(){
        if($(this).is(':checked')){
            $('#Form_Width').attr('disabled','disabled');
            $('#Form_Height').attr('disabled','disabled');
        }else{
            $('#Form_Width').removeAttr('disabled');
            $('#Form_Height').removeAttr('disabled');
        }

    });

    $('#Form_Fit').trigger('change');
    
    $('.EditPlacement').mousedown(function(){
        var id = $(this).attr('id').split('_')[1];
            $.ajax({
                url: gdn.definition('PlacementURL')+'/'+id,
                data: 'DeliveryType=DATA&DeliveryMethod=JSON',
                dataType: 'json',  
                success: function(data) {
                    
                    var Placement = data.AdPlacement;
                    
                    $.each(Placement,function(i,v){
                        $('.AddEditPlacement').text(gdn.definition('EditAdPlacement'));
                        $('.AddEditButton').show();
                        if($('#Form_'+i).is(':checkbox')){
                            $('#Form_'+i).val(1);
                            if(v!='0'){
                                $('#Form_'+i).attr('checked','checked');
                            }else{
                                $('#Form_'+i).removeAttr('checked');
                            }
                            
                        }else{
                            $('#Form_'+i).val(v);
                            if($('#Form_'+i).is('select')){
                                opts = $('#Form_'+i).find('option').map(function(){return $(this).val();}).get();
                                if($.inArray(v, opts) == -1){
                                    $('#Form_'+i).val('');
                                    $('#Form_'+i+'1').val(v);
                                }
                            }
                        }
                    });
                    
                    $('#EmbedCode').text(Placement.AdPlacementID);
                    $('#EmbedLabel').text('#'+Placement.Label);
                    $('#EmbedCodeID').show()
                    $('#EmbedCodeDescription').hide();
                    $('#Form_Page').trigger('change');
                    $('#Form_Fit').trigger('change');
                    $('#Form_Template').trigger('change');
                }
            });

        //return false;
    });
    
    $('.EditAd').mousedown(function(){
        var id = $(this).attr('id').split('_')[1];
            $.ajax({
                url: gdn.definition('AdURL')+'/'+id,
                data: 'DeliveryType=DATA&DeliveryMethod=JSON',
                dataType: 'json',  
                success: function(data) {
                    
                    var ad = data.Ad;
                    
                    $.each(ad,function(i,v){
                        $('.AddEdit').text(gdn.definition('EditAd'));
                        $('.AddEditButton').show();
                        if($('#Form_'+i).is(':checkbox')){
                            $('#Form_'+i).val(1);
                            if(v!='0'){
                                $('#Form_'+i).attr('checked','checked');
                            }else{
                                $('#Form_'+i).removeAttr('checked');
                            }
                            
                        }else{
                            $('#Form_'+i).val(v);
                        }
                        
                        if(i=='ExpireReminder' && v){
                            
                            var d = v.match(/[0-9]{4}-\d{2}-\d{2}/)[0].split('-');
                            $('#Form_'+i+'_Year').val(d[0]);
                            $('#Form_'+i+'_Month').val(parseInt(d[1].replace(/^0+/, '')));
                            $('#Form_'+i+'_Day').val(parseInt(d[2].replace(/^0+/, '')));
                        }else{
                            $('#Form_'+i+'_Year').val(0);
                            $('#Form_'+i+'_Month').val(0);
                            $('#Form_'+i+'_Day').val(0);
                        }
                    });
                }
            });

        //return false;
    });
    
    $('.AddEditButton').click(function(){
        if($('#Form_AdID').length){
            $('#Form_AdID').val(0);
        }else{
            $('#Form_AdPlacementID').val(0);
        }
        $('#EmbedCodeDescription').show();
        $('#EmbedCodeID').hide();
        $('.AddEditPlacement').text(gdn.definition('AddAdPlacement'));
        $('.AddEdit').text(gdn.definition('AddAd'));
        $(this).hide();
    });
    
    if(gdn.definition('AdPlacementOne')==1){
        $('a.EditPlacement:first').trigger('mousedown');
    }
    
    if(gdn.definition('AdOne')==1){
        $('a.EditAd:first').trigger('mousedown');
    }
    
    if(gdn.definition('LastID',0)>0){
        $('#Edit_'+gdn.definition('LastID')).trigger('mousedown');
    }
    
    $('.DeletePlacement, .DeleteAd').popup({'confirm' : true,  'followConfirm' : true});
    
});
