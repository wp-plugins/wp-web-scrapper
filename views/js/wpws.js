jQuery(document).ready(function($) {
  $('#wpws-sandbox').tabs(); 
  $('#load_default_args').click(function() { 
    $( '#args' ).val( $(this).attr('data-args') );
  });
});
