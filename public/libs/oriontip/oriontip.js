(function($) {
  $.fn.oriontip = function()
  {
    this.each(function() {

      var option = $(this);
      var text = option.data("oriontip");       
 
      option.mouseover(function() {

          if(typeof option.data('oriontip') !== 'undefined') {

              if(/<\/?[a-z][\s\S]*>/i.test(text)) { // html tags check

                  setTimeout(function() {
                      create_tooltip();
                      setTimeout(function() {
                          set_tooltip_position();
                      }, 100)
                  }, 100);

              } else {
                  create_tooltip();
                  set_tooltip_position();
              }

          }
        
      });

      option.mouseleave(function() {

          if(/<\/?[a-z][\s\S]*>/i.test(text)) { // html tags check

              setTimeout(function() {

                  if ($('.oriontip:hover').length == 0 && $('[data-oriontip]:hover').length == 0 ) {
                      remove_tooltip();
                  }

                  if ($('.oriontip:hover').length != 0) {
                      $(".oriontip").mouseleave(function() {
                          remove_tooltip();
                      })
                  }
                  
              }, 100);

          } else {
              remove_tooltip();
          }

      });


      function set_tooltip_position() {
          var position = option.offset(), width = option.outerWidth();
          var tipTpos = position.top - ( $(".oriontip").outerHeight() + 15 );
          var tipLpos = position.left - ( ($(".oriontip").outerWidth()/2) - (width/2) );
          $(".oriontip").css("top", tipTpos).css("left", tipLpos);      
          $(".oriontip").css("visibility", "visible");
      }

      function create_tooltip() {
          remove_tooltip();
          $("html").append("<div class='oriontip'>"+ text +"</div>");
      }

      function remove_tooltip() {
          $(".oriontip").remove();
      }

    });

    return this;

  };
})(jQuery);
