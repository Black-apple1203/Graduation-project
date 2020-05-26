/* ============================================================
 * jquery.index.js  首页js集合
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	// 处理圆角
	if (window.PIE) { 
	    $('.pie_about').each(function() {
	        PIE.attach(this);
	    })
	}

  //滚动消息
  $(function(){
    var _wrap=$('#J_msgbox');
    var _interval=2000;
    var _moving;
    _wrap.hover(function(){
      clearInterval(_moving);
    },function(){
      _moving=setInterval(function(){
        var _field=_wrap.find('li:first');
        var _h=_field.outerHeight();
        _field.animate({marginTop:-_h+'px'},600,function(){
          _field.css('marginTop',0).appendTo(_wrap.find("ul"));
        })
      },_interval)
    }).trigger('mouseleave');
  });

	// 快速注册简历
  $('#J_reg').click(function(){
    var qsDialog = $(this).dialog({
      loading: true,
      footer: false,
      header: false,
      border: false,
      backdrop: false
    });
    if($('#JRegHidVal').val() == 1){
      var creatsUrl = qscms.root + '?m=Home&c=AjaxPersonal&a=resume_add_dig';
    }else{
      var creatsUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_regisiter';
    }
    $.getJSON(creatsUrl, {no_apply:1},function(result){
      if(result.status==1){
        qsDialog.hide();
        var qsDialogSon = $(this).dialog({
          content: result.data.html,
          footer: false,
          header: false,
          border: false
        });
        qsDialogSon.setInnerPadding(false);
      } else {
        qsDialog.hide();
        disapperTooltip("remind", result.msg);
      }
    })
  })

  // ajax加载登录口内信息
	$.getJSON(qscms.root + '?m=Home&c=index&a=ajax_user_info',function(result){
		if(result.status == 1){
			$('#J_userWrap').html(result.data.html);
		}
	})

	// 顶部搜索类型切换
  $('.J_sli').click(function() {
    $(this).addClass('select').siblings().removeClass('select');
    var typeValue = $.trim($(this).data('type'));
    $('#top_search_type').val(typeValue);
  })

    // 顶部回车搜索
	$('#top_search_input').bind('keypress', function(event) {
		if (event.keyCode == "13") {
			$("#top_search_btn").click();
		}
	})

    // 顶部搜索跳转
    $('#top_search_btn').click(function() {
        $('#top_search_input').val(htmlspecialchars($('#top_search_input').val()));
        var post_data = $('#ajax_search_location').serialize();
        if(qscms.keyUrlencode==1){
            post_data = encodeURI(post_data);
        }
        $.post(qscms.root + '?m=Home&c=Index&a=search_location',post_data,function(result){
            if(result.status == 1){
                window.location=result.data;
            }
        },'json')
        return !1;
    })

    // 换一批  ajax_loading  
    var isDone = true; // 防止重复点击
    var ajaxpage = {};
    $('.J_change_batch').click(function() {
    	var $thisParent = $(this).closest('.J_change_parent');
    	var $url = $(this).data('url');
    	$thisParent.addClass('open-ajax');
    	if (isDone) {
    		isDone = false;
            if(!ajaxpage[$url]) ajaxpage[$url] = 2;
        	$.getJSON($url, {p: ajaxpage[$url]}, function(result) {
        		if(result.status == 1){
                    $thisParent.find('.J_change_result').html(result.data.html);
                    isDone = true;
                    if(result.data.isfull){
                        ajaxpage[$url] = 1;
                    }else{
                        ajaxpage[$url]++;
                    }
                }
                $thisParent.removeClass('open-ajax');
        	})
    	}
    })

    // 新闻资讯切换
    var isDoneNews = true; // 防止重复点击
    $('.J_news_list_title').click(function() {
        var $cid = $(this).attr('cid');
        $(this).addClass('select').siblings().removeClass('select');
        $('.J_news_content').find('.ajax_loading').show();
        if (isDoneNews) {
            isDoneNews = false;
            $.getJSON(qscms.root + '?m=Home&c=AjaxCommon&a=news_list', {type_id: $cid}, function(data) {
                $('.J_news_content').find('.J_news_txt').html(data.data);
                isDoneNews = true;
                $('.J_news_content').find('.ajax_loading').hide();
            })
        }
    })
}(window.jQuery);