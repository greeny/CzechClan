$(function(){
    $("[data-tooltip]").tooltip({ html: true });
    $(".help").tooltip({ html: true });
    $(".alert").alert();
    $("[data-confirm]").on('click', function(){
        return confirm($(this).data('confirm'));
    });
    $('li.disabled a').on('click', function(e) {
        e.preventDefault();
    });
	var hash = document.location.hash;
	hash ? $(hash).addClass('warning') : undefined;
});
