var thickDims, tbWidth, tbHeight;
;(function (G) {

	var ag4on=G("#ag4_recent_posts").val();
	if (ag4on != "1")G("#ag4_recent_num").hide();
	else G("#ag4_recent_num").fadeIn("2000");
	
	G("#ag4_recent_posts").change(function () { G("#ag4_recent_num").toggle(); });
	G("#ag4-tabs").tabs();
	
//G(".inside p.c4r > label").css("border", "3px double red");
	//G(".inside p.c4r:has(input[type=checkbox])").css("border", "3px solid red");
	//G(".inside p.c4r:not(:has(input[type=checkbox]))").css("border", "1px double blue");

/*
	var ag4on='0';
	ag4on=G("#ag4_recent_posts").val();
	if (ag4on != "0") {
        G("#ag4_recent_posts").attr("checked", "checked");
        G("#ag4_recent_num").fadeIn("2000");
		G("#ag4_recent_posts").change(function () {
			var ag4on=G("#ag4_recent_posts").val();
		
    }
	
	G("#ag4_recent_posts").click(function () {
        G("#ag4_form #ag4_404_choose_file").toggle()
    });
	
	G("#ag4_recent_posts").change(function () {
        G("#ag4_recent_num").fadeIn("2000");
        g("#formprelim").zbform()
    }).click(function () {
        g(".fsubmit").fadeIn("2000");
        g("#formprelim").zbform()
    })

*/
    /*
	var ag4e = "Custom File Location";
    ag4e = G("#ag4_form #ag4_other_file").val();
	
    if (ag4e != "Custom File Location") {
        G("#ag4_form #ag4_other_file").attr("checked", "checked");
        G("#ag4_form #ag4_404_choose_file").show()
    }
	
    G("#ag4_form #ag4_other_file").removeAttr("checked").click(function () {
        G("#ag4_form #ag4_404_choose_file").toggle()
    });
	*/





	
	G('a.ag4submit').click(function() { G('form#ag4_form').submit();return false; });
	G('a.ag4reset').click(function() { G('form#ag4_reset').submit();return false; });




	// help tab
	G('a.contextualhl').click(function () {
		if ( ! G('#contextual-help-wrap').hasClass('contextual-help-open') )
			G('#screen-options-link-wrap').css('visibility', 'hidden');

		G('#contextual-help-wrap').slideToggle('fast', function() {
			if ( G(this).hasClass('contextual-help-open') ) {
				G('#contextual-help-link').css({'backgroundImage':'url("images/screen-options-right.gif")'});
				G('#screen-options-link-wrap').css('visibility', '');
				G(this).removeClass('contextual-help-open');
			} else {
				G('#contextual-help-link').css({'backgroundImage':'url("images/screen-options-right-up.gif")'});
				G(this).addClass('contextual-help-open');
			}
		});
		return false;
	});
	
	
	
	
	thickDims = function() {
		var tbWindow = G('#TB_window'), H = G(window).height(), W = G(window).width(), w, h;

		w = (tbWidth && tbWidth < W - 90) ? tbWidth : W - 90;
		h = (tbHeight && tbHeight < H - 60) ? tbHeight : H - 60;

		if ( tbWindow.size() ) {
			tbWindow.width(w).height(h);
			G('#TB_iframeContent').width(w).height(h - 27);
			tbWindow.css({'margin-left': '-' + parseInt((w / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				tbWindow.css({'top':'30px','margin-top':'0'});
		}
	};

	thickDims();
	G(window).resize( function() { thickDims() } );

	G('a.thickbox-askapache').click( function() {
		/*var alink = G(this).parents('.available-theme').find('.activatelink'), link = '', href = G(this).attr('href'), url, text;

		if ( tbWidth = href.match(/&tbWidth=[0-9]+/) )
			tbWidth = parseInt(tbWidth[0].replace(/[^0-9]+/g, ''), 10);
		else
			tbWidth = G(window).width() - 90;

		if ( tbHeight = href.match(/&tbHeight=[0-9]+/) )
			tbHeight = parseInt(tbHeight[0].replace(/[^0-9]+/g, ''), 10);
		else
			tbHeight = G(window).height() - 60;

		if ( alink.length ) {
			url = alink.attr('href') || '';
			text = alink.attr('title') || '';
			link = '&nbsp; <a href="' + url + '" target="_top" class="tb-theme-preview-link">' + text + '</a>';
		} else {
			text = G(this).attr('title') || '';
			link = '&nbsp; <span class="tb-theme-preview-link">' + text + '</span>';
		}*/

		G('#TB_title').css({'background-color':'#222','color':'#dfdfdf'});
		G('#TB_closeAjaxWindow').css({'float':'left'});

		G('#TB_iframeContent').width('100%');
		thickDims();
		return false;
	} );

	// Theme details
	/*G('.theme-detail').click(function () {
		G(this).siblings('.themedetaildiv').toggle();
		return false;
	});*/

	G("#codepress-on").hide();
	G("#codepress-off").show();
	
	
	
	postboxes.add_postbox_toggles("askapache-google-404");
	postboxes.add_postbox_toggles("askapachegoogle");
	postboxes.add_postbox_toggles("settings_page_askapache-google-404");
}(jQuery));