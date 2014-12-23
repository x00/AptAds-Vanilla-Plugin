jQuery(document).ready(function($){
    var embedIds = [];
    $('input.AptAds').replaceWith(function(){ return '<!--|aptads embed='+$(this).val()+'|-->'});
    var embeds = $("body *:not(iframe)").contents().filter(function(){ return this.nodeType == 8;});
    var url = $($('script').filter(function(){return $(this).attr('src') && $(this).attr('src').match(/embedcode\.js(\?.*)?$/);})[0]).attr('src').replace(/plugins\/AptAds\/js\/embedcode\.js(\?.*)?$/i,'plugin/AptAds/embed/');
    embeds.each(function(){
        var m = this.nodeValue.match(/\|aptads\s+embed\s*=\s*(\d+|[a-z]+)\s*\|/);
        if(m){
            embedIds.push(m[1]);
        }
    });
    $.ajax(url,{
            dataType:'jsonp',
            type:'post',
            'data':{'EmbedIDs':embedIds},
            traditional:false,
            success: function(result){
                
                if(result['AdPlacements'] || result['EmbedSpaces'] && embedIds.length){
                    
                    if(result['Style'] && !$('style#ApAdsStyle').length){
                        $('head').append('<style id="ApAdsStyle" type="text/css">'+result['Style']+'</style>');
                    }
                    if(result['AdPlacements'])
                        $.each(result['AdPlacements'],function(i,a){
                            embeds.each(function(){
                                var m = this.nodeValue.match(/\|aptads\s+embed\s*=\s*(\d+)\s*\|/);
                                if(m && m[1]==a.AdPlacementID){
                                    $(this).replaceWith(a.AdCode);
                                }
                            });                             
                        });
                        
                    
                    if(result['EmbedSpaces'])
                        $.each(result['EmbedSpaces'],function(es, ads){
                            
                            embeds.each(function(){
                                var m = this.nodeValue.match(/\|aptads\s+embed\s*=\s*([a-z]+)\s*\|/);
                                if(m && m[1]==es){
                                    $(this).replaceWith(ads.AdCode);
                                }
                            });     
                        });
                }
            }
        }
    );

});
