/**
Vertigo Tip by www.vertigo-project.com
Requires jQuery
*/

this.vtip = function() {    
    this.xOffset = -10; // x distance from mouse
    this.yOffset = 15; // y distance from mouse       
    $(".vtip").unbind().hover(    
        function(e) {
            this.t = $(this).attr("title");
            this.title = ''; 
            this.top = (e.pageY + yOffset);
			this.left = (e.pageX + xOffset);
			//alert($('p#vtip').css("right")); 
			$('body').css("cursor","help");
			//alert(this.t);
			$('p#vtip').width()>450?$('p#vtip').width(450):'';
            $('body').append( '<p id="vtip">' + this.t + '</p>' );		 
            $('p#vtip').css("top", this.top+"px").css("left", this.left+"px").fadeIn(0);			   
        },
        function() {
            this.title = this.t;
			$('body').css("cursor","");
            $("p#vtip").fadeOut("slow").remove();
        }
    ).mousemove(
        function(e) {
         this.top = (e.pageY + yOffset);
         this.left = (e.pageX + xOffset);                         
         $("p#vtip").css("top", this.top+"px").css("left", this.left+"px"); 
        }
    );            
    
};

jQuery(document).ready(function($){vtip();}) 