var toast,spinner,$tableWrap,$countRows;
$(function(){

  /* Insert span element to all ajax form submit buttons */
	$('.btn-progress').wrapInner('<span></span>');

  /* Remove disabled attribute from all ajax submit buttons */
  $('.btn-progress:not(.cdisabled)').removeAttr('disabled');

  /* Select 2 Plugin */
	select2Init();

  /* Custom Input file plugin */
  if(typeof bsCustomFileInput !== 'undefined') bsCustomFileInput.init();
	

  /* Summernote */
  $.each($('.summernote'), function(i,v){
    var lang = $(v).attr('data-lang');
    lang     = lang ? lang : 'en-US';
    lang     = lang+'-'+lang.toUpperCase();
    $(this).summernote({
      height: 200,
      lang: lang
    });
  });
  // $('.summernote').summernote({
  //   height: 200,
  //   lang: lang+'-'+lang.toUpperCase()
  // });

  /* Toast from sweetalert */
	toast = Swal.mixin({
	        toast: true,
	        position: 'top-right',
	        showConfirmButton: false,
	        timer: 2000
	      });


	spinner = '<div class="t-spinner"><div class="nb-spinner" style="border-top-color: #26e600"></div></div>';
	$tableWrap = $('.table-wrap');
	$countRows = $('.count-rows');

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

  /* Send ajax request on select change event */
	ajaxSelectChange();
  function ajaxSelectChange(){
    $(document).on('change','.ajax-select', function(){
      var data = 'data='+$(this).val();
      var target = $(this).attr('data-target');
      var cols   = $(this).attr('data-cols').split(",");

      $(target).attr('disabled', true);

      ajaxForm($(this).attr('data-route'),data,null,'POST').done(function(res){
        if(res.status){
          var data = '';
          $.each(res.data,function(i,v){
            data += '<option value="'+v[cols[0]]+'">'+v[cols[1]]+'</option>';
          });
          $(target).html(data);
          $(target).attr('disabled', false);
        }
      });
    });
  }

  // Check All Permissions
  $('.select-all-roles').on('change', function(){
    var status;
    if($(this).is(':checked')){
      status = true;
    }
    else{
      status = false;
    }
    $(this).closest('form').find('input[type="checkbox"]').prop('checked', status);
  });

  // Check Horizontal Permissions
  $('.select-h-roles').on('change', function(){
    var status;
    if($(this).is(':checked')){
      status = true;
    }
    else{
      status = false;
    }
    $(this).closest('tr').find('input[type="checkbox"]').prop('checked', status);
  });

  // Check All
  $(document).on('click', '.check_all', checkAll);
  $(document).on('click', '.checkbx', checkAllTrue);

  // Sortable Rows
  var $sortableWrap = $('.sortable-update-wrap'), $tableSort = $('.table-sort');

  if($tableSort.length){
    $tableSort.sortable({
        items: 'tbody > tr',
        cancel: 'tr[data-disable]',
        update: function(e,ui){
          $sortableWrap.fadeIn(200);
        }
    });

    $sortableWrap.find('.cls').on('click', function(){
      $tableSort.sortable("cancel");
      $sortableWrap.fadeOut(200);
    });
  }

});

/* Select 2 Plugin */
function select2Init(){
  if($('.select2').length) {
    $('.select2').select2();
  }
}

require('./admin-functions');

/****************************** SELLER Starts ******************************/
// Seller Function - Used on seller registeration page
function mergePhoneNumberWithPrefix($this) {
  $.each($this.find('.custom-num'),function(i,v){
    var prefix = $(v).find('select').val(),
        number = $(v).find('input:not([type=hidden])').val();

    var fullNumber = prefix+number;
        $(v).find('input[type="hidden"]').val(fullNumber);

  });

  return true;
}

/****************************** SELLER Ends *******************************/


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
    '<strong>Errors:</strong>'+
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
    toastFailed('Something went wrong!');

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
  msg = (msg) ? msg : 'Something went wrong. Please try again!';
  Swal.fire('Error!',msg,'error'); 
}
function swalDone(msg){
  msg = typeof msg === 'undefined' ? null : msg;
  msg = (msg) ? msg : 'Successfully Done';
  Swal.fire('Success!',msg,'success');
}
function toastDone(msg){
  toast.fire({ icon: 'success', title: msg });
}

function toastFailed(msg){
  toast.fire({ icon: 'error',title: msg });
}
function confirm(text){
  text = typeof text === 'undefined' ? null : text;
  return Swal.fire({
        title: 'Are you sure?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      });
}
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '))
}
function getHrefParameter(name,href) {
    return (RegExp(name + '=' + '(.+?)(&|$)').exec(href)||[,null])[1];
};

// All checkboxes are checked if .check_all is checked
function checkAll(){
  if($(this).is(':checked',true))  
  {
     $(this).closest('.table-section').find('.check_all').prop('checked',true);
     $(this).closest('.table-section').find(".checkbx").prop('checked', true);  

  } else {  
     $(this).closest('.table-section').find('.check_all').prop('checked',false);
     $(this).closest('.table-section').find(".checkbx").prop('checked',false);  

  }  
}

// .check_all is checked if all checkboxes are checked
function checkAllTrue(){
  if($('.checkbx:checked').length == $('.checkbx').length){
      $('.check_all').prop('checked',true);
  }else{
      $('.check_all').prop('checked',false);
  }
}

// Update Rows
function updateRows(e){
  var route1 = e.data.route1,
      md = e.data.md,
      $sortableWrap = $(this).closest('.sortable-update-wrap');

      var data = '';

      var pos = [],ids = [];
      $(this).closest('.table-section').find('tbody tr').each(function(i,v){
        ids.push($(this).attr('data-id'));
        pos.push($(this).attr('data-position'));
      });

      var min = Math.min.apply(Math,pos);
      data+= 'ids='+ids.join()+'&md='+md+'&min='+min;

      ajaxForm(route1,data,$sortableWrap,'PUT').done( r => {
        $sortableWrap.fadeOut(200);
      });
}

// Delete Single Row
function tDelete(e){
  var id = $(this).attr('data-id'),
      row = $(this).closest('tr'),
      route1 = e.data.route1,
      md = e.data.md,
      c = e.data.c,
      m = e.data.m,
      p = e.data.p,
      noti = e.data.noti,
      file = e.data.file;
          swal.fire({
              title: 'Are you sure?',
              html: (m) ? e.data.m :  "It will be deleted permanently!</span>",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, delete it!',
              showLoaderOnConfirm: true,
              preConfirm: function() {
                return new Promise(function(resolve) {

                  var data = 'id='+id;
                            if(md && c) data += '&md='+md+'&c='+c;
                            if(p) data+= '&p='+p;
                            if(file) data+= '&file='+file.join(); 

                  ajaxForm(route1,data,null,'DELETE').done(function(res){
                    if(res.status){
                      row.remove();
                      var total = parseInt($countRows.text()) - 1;
                      $countRows.text(total);
                      Swal.fire('Deleted!', res.message,'success');
                      if(noti){
                        $notification_li.find('ul li[data-id="'+id+'"]').remove();
                        notiCount();
                      }
                    }
                  });
                });
              }          
          }); 
}

// Delete Selected Rows
function deleteSelected(e){

  var idsArr = [],
      route1 = e.data.route1,
      md = e.data.md,
      c = e.data.c,
      m = e.data.m,
      p = e.data.p,
      noti = e.data.noti,
      file = e.data.file;

  $(".checkbx:checked").each(function() {  
      idsArr.push($(this).attr('data-id'));
  });  
  if(idsArr.length <=0){  
      Swal.fire('Oops...', 'Please select atleast one record to delete.', 'info');
  } else {
      Swal.fire({
          title: 'Are you sure?',
          html: (m) ? e.data.m : "You want to delete the selected record"+(idsArr.length > 1 ? 's': '')+"?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete '+(idsArr.length > 1 ? 'them!' : 'it!')+'',
          showLoaderOnConfirm: true,
          preConfirm: function() {
            return new Promise(function(resolve) {

              var strIds = idsArr.join(","),
                  data = 'ids='+strIds;
                        if(md && c) data += '&md='+md+'&c='+c;
                        if(p) data+= '&p='+p;
                        if(file) data += '&file='+file.join(); 

              ajaxForm(route1,data,null,'DELETE').done(function(res){
                if (res.status) {
                    var counter = 0;
                    $(".checkbx:checked").each(function() {  
                        $(this).parents("tr").remove();
                        counter++;
                    });
                    var total = parseInt($countRows.text()) - counter;
                    $countRows.text(total);
                    Swal.fire('',res.message,'success');
                    if(noti){
                      $.each(idsArr, function(i,v){
                        $notification_li.find('ul li[data-id="'+v+'"]').remove();
                      });
                      notiCount();
                    }
                }
                
              });
            });
          }            
      }); 

  }
}

// Delete all data from Table
function truncate(e){
  var md = e.data.md,
      p  = e.data.p,
      route1 = e.data.route1,
      m = e.data.m,
      noti = e.data.noti,
      file = e.data.file,
      cns = e.data.cns;

      Swal.fire({
          title: 'Are you sure?',
          html: (m) ? e.data.m : "Whole Table Data will be deleted permanently!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          showLoaderOnConfirm: true,
          preConfirm: function() {
            return new Promise(function(resolve) {

              var data = '';
                  if(md) data+= 'md='+md;
                  if(p) data+= '&p='+p;
                  if(file) data+= '&file='+file.join();
                  if(cns) data+= '&cns='+cns.join();

              ajaxForm(route1,data,null,'DELETE').done(function(res){
                if(res.status){
                  $('.ajax-table tbody').empty();
                  $('.table-pag').remove();
                  Swal.fire('Deleted!', res.message, 'success');
                  if(noti){
                    $notification_li.find('ul li').remove();
                    notiCount();
                  }
                }
              });
            });
          }           
      }); 
}

export { ajaxForm, ajaxRequest };

window.deleteSelected = deleteSelected;