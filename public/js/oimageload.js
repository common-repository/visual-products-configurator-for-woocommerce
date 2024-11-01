(function($) {
	$.fn.oImageLoad = function(fn){
	    this.on("load", fn);
	    this.each( function() {
	        if ( this.complete && this.naturalWidth !== 0 ) {
	            $(this).trigger('load');
	        }
	    });
	}
})(jQuery);