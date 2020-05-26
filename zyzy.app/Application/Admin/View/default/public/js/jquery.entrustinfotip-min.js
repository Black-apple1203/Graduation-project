/**

Vertigo Tip by www.vertigo-project.com

Requires jQuery

*/



this.vtip_entrustinfo = function() {    

    this.xOffset = -10; // x distance from mouse

    this.yOffset = 15; // y distance from mouse       

    $(".entrustinfo").unbind().hover(    

        function(e) {

            this.t = "载入中...";

            this.title = ''; 

            this.top = (e.pageY + yOffset);

			this.left = (e.pageX);

			$('body').css("cursor","help");

			var uid= $(this).attr('uid');

			var rid= $(this).attr('rid');
			var url = $(this).attr('url');

           	$('body').append( '<p id="entrustinfo" class="entrustinfo-'+uid+'" style="display:block">' + this.t + '</p>' );

			$.get(url, {"uid":uid,"rid":rid},

			function (data,textStatus){$(".entrustinfo-"+uid).html(data);}	);

			var divX=this.left+$('p#entrustinfo').width();

			var documentwidth=$(document).width()-100;

			if (divX>documentwidth)

			{

					var RY=$(document).width()-e.pageX; 

				

				 $('p#entrustinfo').css("top", this.top+"px").css("right", RY+"px").fadeIn(0);	

			}

			else

			{

				$('p#entrustinfo').css("top", this.top+"px").css("left", this.left+"px").fadeIn(0);		

			}

            	   

        },

        function() {

            this.title = this.t;

	$('body').css("cursor","");

            $("p#entrustinfo").fadeOut("slow").remove();

        }

    )          

    

};



jQuery(document).ready(function($){vtip_entrustinfo();}) 