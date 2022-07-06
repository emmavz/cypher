var toast;
$(function() {

    /* Toast from sweetalert */
	toast = Swal.mixin({
      toast: true,
      position: 'top-right',
      showConfirmButton: false,
      timer: 2000
  });

    /* Insert span element to all ajax form submit buttons */
	$('.btn-progress').wrapInner('<span></span>');

    /* Remove disabled attribute from all ajax submit buttons */
    $('.btn-progress:not(.cdisabled)').removeAttr('disabled');

    /* Send CSRF token with each ajax request */
	$.ajaxSetup({
	  headers: {
	    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	  }
	});

    // Close Alert Error Messages Box
	$('.js-errors-container').on('click', '.alert button', function(){
	  $(this).closest('.alert').fadeOut(200);
	});
    // Close Alert Error Messages Box
	$('.alert button').on('click', function(){
	  $(this).closest('.alert').fadeOut(200);
	});

});

/*********************** Ajax Setup ***************************/
var ajaxForm = function(url,data,ths,method){
  ths = typeof ths === 'undefined' ? null : ths;
  method = typeof method === 'undefined' ? null : method;
  if(ths){
  	var btn = ths.find('.btn-progress');
  	btn.prop('disabled', true);
  	showLoader(btn);
  	var errorsContainer = $(ths).find('.js-errors-container');
  	if(errorsContainer.length){ errorsContainer.html(""); }
  }

  var processData,
      contentType;

  if(typeof data == 'object'){
    processData = false;
    contentType = false;
  }

  method = (method) ? method : 'POST';
  
  return $.ajax({
    type: method,
    url: url,
    data: data,
    processData : processData,
    contentType : contentType
  })
  .fail(function(jqXHR, ajaxOptions, thrownError){
    ajaxFailed(jqXHR,null,errorsContainer);
  })
  .always(function(){
     if(btn){btn.prop('disabled', false);
     btn.removeClass('prg-h').find('.spin-h').remove();
   }
  });
}

function ajaxRequest(route,data,ths,route2,method,stop){
  route2 = typeof route2 === 'undefined' ? null : route2;
  method = typeof method === 'undefined' ? null : method;
  stop   = typeof stop === 'undefined' ? null : stop;
  if(ths){
    var btn = ths.find('.btn-progress');
    btn.prop('disabled', true);
    showLoader(btn);
    var errorsContainer = $(ths).find('.js-errors-container');
    if(errorsContainer.length){ errorsContainer.html(""); }
  }

  var processData,
      contentType;

  if(typeof data == 'object'){
    processData = false;
    contentType = false;
  }

  method = (method) ? method : 'POST';

  $.ajax({
    type: method,
    url: route,
    data: data,
    processData : processData,
    contentType : contentType
  }).done(function(r){
    ajaxDone(r,route2,btn,stop,errorsContainer);
  }).fail(function(e){ajaxFailed(e,null,errorsContainer)}).
  always(function(r){
    if(ths){
      btn.prop('disabled', false);
      btn.removeClass('prg-h').find('.spin-h').remove();
    }
  });

}

function ajaxDone(res,route,btn,stop,errorsContainer){
  route = typeof route === 'undefined' ? null : route;
  stop = typeof stop === 'undefined' ? null : stop;

  if(res.status){
    if(res.url){
      window.location.assign(res.url);
      return;
    }
    else if(route){
      window.location.assign(route);
      return;
    }
    else if(res.msg){
      btn.prop('disabled',true);
      swalDone(res.msg);
    }
    else if(stop){

    }
    else{
      var currentUrl = window.location.href;
      currentUrl = currentUrl.replace(/#/g, '');
      window.location.assign(currentUrl);
    }
    //(swal) ? swalDone(res.msg) : toastDone(res.msg);
  }
  else if(!res.status && res.msg){
    swalFailed(res.msg);
  }
  if(res.errors){
    ajaxFailed(null,res.errors,errorsContainer);
  }
}

function ajaxFailed(e,errors,errorsContainer){

  e = typeof e === 'undefined' ? null : e;
  errors = typeof errors === 'undefined' ? null : errors;

  if(e && e.status == 403){
    swalFailed(e.responseJSON.message);
    return;
  }

  if(errors || e.status == 422){
    var layout = '<div class="alert alert-danger" role="alert">'+
    '<button type="button">×</button>'+
    '<strong>'+window.errorHeading+'</strong>'+
    '<ol>';

    if(errors){
      $.each(errors, function(i,v){
        layout += '<li>'+v+'</li>';
      });
    }
    else{
      $.each(e.responseJSON.errors, function(i,v){
        $.each(v, function(ii,vv){
          layout += '<li>'+vv+'</li>';
        });
      });
    }

    layout += '</ol></div>';

    errorsContainer.html(layout);
    toastFailed(window.wentWrongError);

    if($(window).scrollTop() < errorsContainer.offset().top - $(window).innerHeight() + 60){
      $('html,body').animate({
        scrollTop: errorsContainer.offset().top - $(window).innerHeight() + 60
      },700);
    }
  }
  else{
   if (typeof e.responseJSON !== 'undefined') {
      swalFailed(e.responseJSON.message);
    }
  }
}

// Add Progress Bar on Button when form submit
function showLoader(ths,color){

    if(!ths.hasClass('prg-h')){
        var width = ths.outerWidth(),
            height = ths.outerHeight(),
            color = (color) ? color: '#ffffff',
            h  = (height/2) -2+'px';
            ths.addClass('prg-h').css({width: width, height: height}).append('<div class="spin-h"><div class="nb-spinner prg" style="width: '+h+'; height: '+h+';border-top-color: '+color+'; border-left-color: '+color+'"></div></div>');
    }
}

/*********************** Helpers ***********************/
function swalFailed(msg){
  msg = typeof msg === 'undefined' ? null : msg;
  msg = (msg) ? msg : window.wentWrongError2;
  Swal.fire('Error!',msg,'error'); 
}

function swalDone(msg){
  msg = typeof msg === 'undefined' ? null : msg;
  msg = (msg) ? msg : window.successfullyDone;
  Swal.fire('Success!',msg,'success');
}

function toastDone(msg){
  toast.fire({ icon: 'success', title: msg });
}

function toastFailed(msg){
  toast.fire({ icon: 'error',title: msg });
}

export { ajaxForm, ajaxRequest };