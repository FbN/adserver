$(function(){
	
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
	
	$('#checkall').on('ifChecked', function(){
	    var $this = $(this)
	    $this.parents('form').find('.multiselector').iCheck('check')
	});
	
	$('#checkall').on('ifUnchecked', function(){
        var $this = $(this)
        $this.parents('form').find('.multiselector').iCheck('uncheck')
	});
	
	$('#multiDelete').on('click', function(){
		var $this = $(this)
		var $form =  $this.parents('form')
		$('#conferma').data('form', $form)
		$('#conferma').modal({show: true})		
	})
	
	$('#deleteConfirm').on('click', function(){
		var form = $('#conferma').data('form')
		form.prop( 'action', $(this).data('action') )
		form.submit()
	})
	
	$('#search button').on('click', function(e){
		e.preventDefault()
		insertParam('q', $(this).parent().find('input').val())
	})
	
	$('#customerSearch').on('click', function(e){
		e.preventDefault()			
		var agentId = $('#agentId').val()
		var body = $(this).parents('.modal-body')
		body.find('.alert').remove()
		$('#addCustomerList tbody tr').remove()
		$.getJSON( uris['customers.search'], {
			q: $('#searchCustomers').val(),
			id: agentId
		}).done(function( data ) {
			  $.each( data.data, function( i, item ) {
				  var tr =  $('<tr data-id="'+item.id+'" data-gid="'+item.gid+'"><td class="formCell">'+item.email+'</td><td class="formCell">'+item.label+'</td><td><a href="javascript:;"><i class="fa fa-plus-square"></i></a></td></tr>')
				  tr.click(function(){
					  var id = $(this).data('id')					  
					  var uri = uris['agents.customers.add'].replace("{id}", agentId).replace("{cid}", id)
					  body.find('.alert').remove()
					  $.post( uri, function( data ) {
						  	body.append('<div class="alert alert-success" role="alert">Cliente aggiunto</div>')
					  		});
				  })
				  $('#addCustomerList tbody').append(tr)
			  })
		})
	})
	
	$('#addCustomer').on('hide.bs.modal', function (e) {
		document.location.reload(true) 
	})
  
})