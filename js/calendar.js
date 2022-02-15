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
    const privacy = $('.eventPrivacy:checked').val();
    const sharedWith = $('#sharedWith').val();
    const deleteAuth = $('#deleteAuth').val();
    if(title){
      $.ajax({
        type:'POST',
        url:'includes/functions.php',
        data:'func=addEvent&date='+date+'&title='+title+'&privacy='+privacy+'&sharedWith='+sharedWith+'&deleteAuth='+deleteAuth,
        success:function(msg){
          if(msg === 'ok'){
            const dateSplit = date.split("-");
            $('#eventTitle').val('');
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
