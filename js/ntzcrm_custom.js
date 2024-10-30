function ntzcrmclipboard() {
    var copyText = document.getElementById("ntzcrmClipInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    document.execCommand("copy");
}

jQuery(document).ready(function($) {
  $('#dirpsvtag').click(function(event) {
    $("#ntzcrm-submit").trigger("click");
  });
  jQuery('#ntzcrm-tag').submit(function(e) {
    e.preventDefault(); 
    var tag_name = jQuery("#tag_name").val();
    var plan_link = jQuery("#plan_link").val();
    if (tag_name != "") {
      $("#ntzcrm-tag-submit").attr('disabled', 'disabled');
      jQuery("#loader").show();
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: ({
          action: 'ntzcrm_add_new_tag',
          tag_name: tag_name,
          plan_link: plan_link
        }),
        success: function(response) {
          $("#ntzcrm-tag-submit").removeAttr('disabled');
          jQuery("#loader").hide();
          var obj = JSON.parse(response);
          if (obj.status != 'failed') {
            jQuery("#ntzcrm-tag").after('<span id="errmsg">' + obj.message + '</span>');
            window.location.reload(true);
          } else {
            jQuery("#ntzcrm-tag").after('<span id="errmsg">' + obj.message + '</span>');
          }
        }
      });
    }
  });
  $('#ntzcrmaccess_upload').click(function(e) {
    e.preventDefault();
    var custom_uploader = wp.media({
        title: 'Enabled Icon',
        button: {
          text: 'Upload Image'
        },
        multiple: false // Set this to true to allow multiple files to be selected
      })
      .on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();
        $('#ntzcrmaccessimg').attr('src', attachment.url);
        $('#ntzcrmaccessimgval').val(attachment.url);

      })
      .open();
  });

  $('#ntzcrmnoaccess_upload').click(function(e) {
    e.preventDefault();
    var custom_uploader = wp.media({
        title: 'Disabled Icon',
        button: {
          text: 'Upload Image'
        },
        multiple: false // Set this to true to allow multiple files to be selected
      })
      .on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();
        $('#ntzcrmnoaccessimg').attr('src', attachment.url);
        $('#ntzcrmnoaccessimgval').val(attachment.url);

      })
      .open();
  });
  $(".ntzcrmcheckbox").click(function() {
    if ($(this).prop('checked') == true) {
      $(this).val("yes");
    } else {
      $(this).val("no");
    }
  });
  $('.select2').select2({width: '100%'});   
  /*
    $( "#date_from").datepicker({changeMonth: true,changeYear: true,yearRange:"c-100:c+100",maxDate:0,dateFormat: "yy-mm-dd"});
    $( "#date_to").datepicker({changeMonth: true,changeYear: true,yearRange:"c-100:c+100",maxDate:0,dateFormat: "yy-mm-dd"});
  
    
    
      $( "#filtersub" ).autocomplete({ 
        source: function( request, response ) {
          $.ajax( {
            url: ajaxurl,
            dataType: "json",
            data: {
              action:"ntzcrm_get_users",
              term: request.term
            },
            success: function( data ) {
              response( data );
            }
          } );
        },
        minLength: 4,  
        autoFocus: true,
        select: function( event, ui ) { 
        $("#uid").val(ui.item.id);
        $("#filtersub").val(ui.item.value);
        }
      } ); 
    */
});



function ntzIsNumber(evt) {
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
}
 
