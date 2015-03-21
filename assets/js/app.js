$(function(){
	
	$('.checkall').on('ifChecked', function(){
	    var $this = $(this)
	    $this.parents('form').find('.multiselector').iCheck('check')
	});
	
	$('.checkall').on('ifUnchecked', function(){
        var $this = $(this)
        $this.parents('form').find('.multiselector').iCheck('uncheck')
	});
	
	$('.multiDelete').on('click', function(){
		var $this = $(this)
		var $form =  $($this.attr('href'))
		$form.find('.confirm').data('form', $form)
		$form.find('.confirm').modal({show: true})		
	})
	
	$('.deleteConfirm').on('click', function(){
		var $form = $(this).closest('form')
		$form.prop( 'action', $(this).data('action') )
		$form.submit()
	})
	
	function insertParam(key, value){
		
	    key = encodeURI(key); value = encodeURI(value);

	    var kvp = document.location.search.substr(1).split('&');

	    var i=kvp.length; var x; while(i--) 
	    {
	        x = kvp[i].split('=');

	        if (x[0]==key)
	        {
	            x[1] = value;
	            kvp[i] = x.join('=');
	            break;
	        }
	    }

	    if(i<0) {kvp[kvp.length] = [key,value].join('=');}

	    document.location.search = kvp.join('&'); 
	}
	
	$('#search button').on('click', function(e){
		e.preventDefault()
		insertParam('q', $(this).parent().find('input').val())
	})
  
})