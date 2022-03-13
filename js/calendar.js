function getCalendar(target_div, year, month){
  $.ajax({
    type:'POST',
    url:'includes/functions.php',
    data:'func=getCalender&year='+year+'&month='+month,
    success:function(html){
      $('#'+target_div).html(html);
    }
  });
}

function getEvents(date){
  $.ajax({
    type:'POST',
    url:'includes/functions.php',
    data:'func=getEvents&date='+date,
    success:function(html){
      $('#event_list').html(html);
      $('#event_list').slideDown('slow');
    }
  });
}

function addEvent(date){
  $('#eventDate').val(date);
  $('#eventDateView').html(date);
  $('#event_list').slideUp('slow');
  $('#event_add').slideDown('slow');
  $('#eventTitle').select();
}

function delEvent(id){
  $('#eventId').val(id);
}

$(document).ready(function(){
  $('#cancelAddEventBtn').on('click',function(){
    $('#event_add').slideUp('slow');
    document.getElementById("eventTitle").value = "";
    document.getElementById("eventDescription").value = "";
    document.getElementById("privateCheck").checked = true;
    document.getElementById("sharedWith").value = "";
    document.getElementById("deleteAuth").value = "";
  })
})

$(document).ready(function(){
  $('#addEventBtn').on('click',function(e){
    e.preventDefault();
    const date = $('#eventDate').val();
    const title = $('#eventTitle').val();
    const description = $('#eventDescription').val();
    const privacy = $('.eventPrivacy:checked').val();
    const sharedWith = $('#sharedWith').val();
    const deleteAuth = $('#deleteAuth').val();
    if(title){
      $.ajax({
        type:'POST',
        url:'includes/functions.php',
        data:'func=addEvent&date='+date+'&title='+title+'&description='+description+'&privacy='+privacy+'&sharedWith='+sharedWith+'&deleteAuth='+deleteAuth,
        success:function(msg){
          if(msg === 'ok'){
            const dateSplit = date.split("-");
            $('#eventTitle').val('');
            $('#eventDescription').val('');
            $('#createdModal').modal('show');
            $('#modalText').empty();
            $('#modalText').append('Event created successfully!');
            getCalendar('calendar_div',dateSplit[0],dateSplit[1]);
            setTimeout(function(){
              $("#createdModal").modal("hide")
            }, 2000);
          }else{
            $('#createdModal').modal('show');
            $('#modalText').empty();
            $('#modalText').append('Some problem occurred, please try again.');
          }
        }
      });
    }
  });
});

$(document).ready(function(){
  $('.date_cell').mouseenter(function(){
    date = $(this).attr('data-date');
    $(".date_popup_wrap").fadeOut();
    $("#date_popup_"+date).fadeIn();
  });
  $('.date_cell').mouseleave(function(){
    $(".date_popup_wrap").fadeOut();
  });
  $('.month_dropdown').on('change',function(){
    getCalendar('calendar_div', $('.year_dropdown').val(), $('.month_dropdown').val());
  });
  $('.year_dropdown').on('change',function(){
    getCalendar('calendar_div', $('.year_dropdown').val(), $('.month_dropdown').val());
  });
  $(document).click(function(){
    $('#event_list').slideUp('slow');
  });
});

$('#eventTitle').on('keyup',function(){
  var characterCount = $(this).val().length,
    current = $('#current'),
    maximum = $('#maximum');
  current.text(characterCount);
  if (characterCount < 50) {
    current.css('color', '#006600', 'font-weight', 'normal');
  }
  if (characterCount > 44 && characterCount < 50) {
    current.css('color', '#ffff00');
    current.css('font-weight', 'normal');
  }
  if (characterCount >= 50) {
    maximum.css('color', '#880000');
    maximum.css('font-weight', 'bold');
    current.css('color', '#880000');
    current.css('font-weight', 'bold');
  } else {
    maximum.css('color', '#000');
    maximum.css('font-weight', 'normal');
  }
});

$('#eventDescription').on('keyup',function(){
  var characterCount = $(this).val().length,
    currentDesc = $('#currentDesc'),
    maximumDesc = $('#maximumDesc');
  currentDesc.text(characterCount);
  if (characterCount < 200) {
    currentDesc.css('color', '#006600', 'font-weight', 'normal');
  }
  if (characterCount > 174 && characterCount < 200) {
    currentDesc.css('color', '#ffff00');
    currentDesc.css('font-weight', 'normal');
  }
  if (characterCount >= 200) {
    maximumDesc.css('color', '#880000');
    maximumDesc.css('font-weight', 'bold');
    currentDesc.css('color', '#880000');
    currentDesc.css('font-weight', 'bold');
  } else {
    maximumDesc.css('color', '#000');
    maximumDesc.css('font-weight', 'normal');
  }
});