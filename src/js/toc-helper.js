/**
 * Description
 */

jQuery(document).ready(function($){

  "use strict";

  // Find all toc
  $('.chiramise-toc').each(function(i, ul){
    var $list = $(ul);
    var target = $list.attr('data-target');
    $list.find('.chiramise-toc-item').each(function(index, item){
      var $item = $(item);
      var $link = $item.find('a');
      if ( !$link.length ){
        return true;
      }
      var id = 'chiramise-toc-anchor-' + index;
      $link.attr('href', '#' + id );
      $($(target).find('h1,h2,h3,h4,h5,h6')[index]).attr('id', id);
    });
  });
});
