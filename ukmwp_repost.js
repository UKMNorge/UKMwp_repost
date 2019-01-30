$(document).on('change, keyup', 'input.bind, textarea.bind', 
    function(e) {
        $( $(this).attr('data-bind') ).html( $(this).val() );
    }
);