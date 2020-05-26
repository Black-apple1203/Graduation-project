///////////////////////////////////////////////////////////////////////////////
//	Programador: Enrique Mel閚dez Estrada
//	Fecha: 24 - Julio - 2007
//	Version: 0.3
///////////////////////////////////////////////////////////////////////////////

jQuery.fn.rowSizing = function(o) {
	// default parameters, properties or settings
	o = jQuery.extend({
		selectRows : '>tbody>tr',
		initialCollapsed : true,
		classImg : 'rowSizing',
		cssHoverOver : {backgroundColor:'#fed'},
		cssHoverOut : {backgroundColor:''},
		classHover : 'jquery_rowSizing_hover',
		classCollapse : 'jquery_rowSizing_collapse',
		title : 'Expande/Contrae esta fila',
		imgOff: '',
		imgOn : '',
		animationSpeed : 1000,
		speed : false /* speed or compatibility... */
	}, o || {});
		
	if (o.speed) {
		/** FASTER BUT LESS JQUERY-sh, then less compatible and compact **/
		var img =	'<img '+
					'onmouseover="jQuery.rowSizing.mouseover(this,\''+o.classHover+'\',\''+o.cssHoverOver+'\')" '+
					'onmouseout="jQuery.rowSizing.mouseout(this,\''+o.classHover+'\',\''+o.cssHoverOut+'\')" '+
					'title="'+ o.title +'" '+
					'class="'+ o.classImg +'" '+
					'src="'+ o.imgOff +'" '+
					'lowsrc="'+ o.imgOn +'" '+
					'onclick="$.rowSizing.click(this,\''+o.classCollapse+'\', '+o.animationSpeed+')" '+
					'></img>';
		return this.find(o.selectRows)
				.each(function(){
					var th = this.cells[0];
					th.innerHTML = img + th.innerHTML;
					if (o.initialCollapsed) this.className += ' '+o.classCollapse;
					})
					;
	}
	else {
		/** SLOWER BUT MORE JQUERYsh, then more compatible and compact **/
		var img =	'<img '+
					'title="'+ o.title +'" '+
					'class="'+ o.classImg +'" '+
					'src="'+ o.imgOff +'" '+
					'lowsrc="'+ o.imgOn +'" '+
					'></img>';
		return this.find(o.selectRows)
				.addClass(o.classCollapse)
				.find('> td:first-child')
					.prepend(img)
					.find('> img.'+o.classImg)
						.click(function(){
							var s = this.src; this.src = this.lowsrc; this.lowsrc = s;
							var row = $(this).parents('tr').get(0); //e.parentNode.parentNode;
							var $row = $(row);
							if (o.animationSpeed && $.browser.msie) {
								row.toggle = !!!row.toggle;
								if (!!!row.expandedHeight)
								{	
									row.collapsedHeight = row.clientHeight;
									$row.removeClass(o.classCollapse);
									row.expandedHeight = row.clientHeight;
									$row.addClass(o.classCollapse);
								}
								var h =  (row.toggle) ? row.expandedHeight : row.collapsedHeight;
								$row.animate({ height: h}, o.animationSpeed, function(){$row.toggleClass(o.classCollapse).height()});
							}
							else
								$row.toggleClass(o.classCollapse)// .height(); incredible, height() is to fix IE?????? 
							//return false;
							})
						.hover(
							function(){$(this).parents('tr').eq(0).addClass(o.classHover).css(o.cssHoverOver); return false},
							function(){$(this).parents('tr').eq(0).removeClass(o.classHover).css(o.cssHoverOut); return false}
							)
							;
	}
};

jQuery.rowSizing = {
	click : function(e,a, speed) {
				var s = e.src; e.src = e.lowsrc; e.lowsrc = s;
				var row = $(e).parents('tr').get(0); //e.parentNode.parentNode;
					
				if (speed && $.browser.msie) {
					row.toggle = !!!row.toggle;
					if (!!!row.expandedHeight)
					{	
						row.collapsedHeight = $(row).height();//row.clientHeight;
						$(row).removeClass(a);
						row.expandedHeight = $(row).height();//row.clientHeight;
						$(row).addClass(a);
					}
					var h =  (row.toggle) ? row.expandedHeight : row.collapsedHeight;
					s = a;
					$(row).animate({ height: h}, speed, function(){$(this).toggleClass(s).height()});
				}
				else
					$(row).toggleClass(a).height(); // incredible, height() is to fix IE?????? 
				return false;
			},
	mouseover : function(e,s,css){
				$(e).parents('tr').eq(0).addClass(s).css(css);
				return false;
			},
	mouseout : function(e,s,css){
				$(e).parents('tr').eq(0).removeClass(s).css(css);
				return false;
			}
};