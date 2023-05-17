function getCalendar(target_div, year, month){
  $.ajax({
    async: true,
    type:'POST',
    url:'includes/functions.php',
    data:'func=getCalendar&year='+year+'&month='+month,
    success:function(html){
      $('#'+target_div).html(html);
    }
  });
}

function getEvents(date){
  $.ajax({
    async: true,
    type:'POST',
    url:'includes/functions.php',
    data:'func=getEvents&date='+date,
    success:function(html){
      $('#event_list').html(html);
      $('#event_list').slideDown('slow');
    }
  });
}

function getEvent(date, id){
  $.ajax({
    async: true,
    type:'POST',
    url:'includes/functions.php',
    data:'func=getEvent&date='+date+'&id='+id,
    success:function(html){
      $('#mod_event').slideUp('slow');
      $('#solo_event').html(html);
      $('#solo_event').slideDown('slow');
    }
  });
}

function modEvent(id){
  $.ajax({
    async: true,
    type:'POST',
    url:'includes/functions.php',
    data:'func=modEvent&id='+id,
    success:function(html){
      $('#event_list').slideUp('slow');
      $('#solo_event').slideUp('slow');
      $('#mod_event').html(html);
      $('#mod_event').slideDown('slow');
    }
  });
}

function addEvent(date){
  var parts = date.split('-');
  var mydate = new Date(parts[0], parts[1] - 1, parts[2]); 
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  $('#eventDate').val(date);
  $('#eventDateView').html(mydate.toLocaleDateString('en-us', options));
  $('#event_list').slideUp('slow');
  $('#solo_event').slideUp('slow');
  $('#mod_event').slideUp('slow');
  $('#event_add').slideDown('slow');
  $('#eventTitle').select();
}

function deleteEvent(id){
  $("#createdModal").modal("show");
  $("#modalText").empty();
  $("#modalText").append("Are you sure you want to delete this event?");
  $("#modalText").append("<br /><br />");
  $("#modalText").append('<button id="confirmDelete" class="btn btn-secondary add_btn">Yes</button>');
  $("#modalText").append('<button id="cancelDelete" class="btn btn-secondary cancel_btn">No</button>');
  var confirmDelete = document.getElementById("confirmDelete");
  var cancelDelete = document.getElementById("cancelDelete");
  confirmDelete.onclick = function(){
    $.ajax({
      async: true,
      type : "POST",
      url : "includes/functions.php", 
      data : 'func=deleteEvent&id='+id,
      success : function() {
        const curMonth = $(".month_dropdown").val();
        const curYear = $(".year_dropdown").val();
        $("#createdModal").modal("show");
        $("#modalText").empty();
        $("#modalText").append("Event deleted successfully!");
        getCalendar("calendar_div",curYear,curMonth);
        setTimeout(function(){
          $("#createdModal").modal("hide")
        }, 2000);
      }
    });
  }
  cancelDelete.onclick = function(){
    $("#createdModal").modal("hide");
  }
}

$(document).ready(function(){
  $('#cancelAddEventBtn').on('click',function(){
    $('#event_add').slideUp('slow');
    document.getElementById("eventTitle").value = "";
    document.getElementById("current").innerText = "0";
    document.getElementById("eventDescription").value = "";
    document.getElementById("currentDesc").innerText = "0";
    document.getElementById("privateCheck").checked = true;
    document.getElementById("sharedWith").value = "";
    document.getElementById("deleteAuth").value = "";
    document.getElementById("category").value = "";
    document.getElementById("titleError").innerText = "";
    document.getElementById("categoryError").innerText = "";
  })
})

$(document).ready(function(){
  $('#cancelModEventBtn').on('click',function(){
    $('#mod_event').slideUp('slow');
    document.getElementById("eventDate").value = "";
    document.getElementById("eventTitle").value = "";
    document.getElementById("eventDescription").value = "";
    document.getElementById("privateCheck").checked = true;
    document.getElementById("sharedWith").value = "";
    document.getElementById("deleteAuth").value = "";
    document.getElementById("category").value = "";
    document.getElementById("titleError").innerText = "";
  })
})

$(document).ready(function(){
  $('.modEventBtn').on('click',function(e){
    e.preventDefault();
    $('#event_list').slideUp('slow');
    $('#solo_event').slideUp('slow');
    $('#event_add').slideUp('slow');
    $('#mod_event').slideDown('slow');
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
    const category = $('#category').val();
    if (title === '') {
      $('#titleError').html('Title is required');
    } else {
      $('#titleError').html('');
    }
    if (category === '' || category === null) {
      $('#categoryError').html('Category is required');
    } else {
      $('#categoryError').html('');
    }
    if(title && category){
      $.ajax({
        async: true,
        type:'POST',
        url:'includes/functions.php',
        data:'func=addEvent&date='+date+'&title='+title+'&description='+description+'&privacy='+privacy+'&sharedWith='+sharedWith+'&deleteAuth='+deleteAuth+'&category='+category,
        success:function(msg){
          if(msg === 'ok'){
            const dateSplit = date.split("-");
            $('#eventTitle').val('');
            $('#eventDescription').val('');
            $('#sharedWith').val('');
            $('#deleteAuth').val('');
            $('#category').val('');
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
  $('.saveModEventBtn').on('click',function(e){
    e.preventDefault();
    const eventId = $('#eventId').val();
    const date = $('#eventDate').val();
    const title = $('#eventTitle').val();
    const description = $('#eventDescription').val();
    const privacy = $('.eventPrivacy:checked').val();
    const sharedWith = $('#sharedWith').val();
    const deleteAuth = $('#deleteAuth').val();
    const category = $('#category').val();
    if (date === '') {
      $('#dateError').html('Date is required');
    } else {
      $('#dateError').html('');
    }
    if (title === '') {
      $('#titleError').html('Title is required');
    } else {
      $('#titleError').html('');
    }
    if (category === '' || category === null) {
      $('#categoryError').html('Category is required');
    } else {
      $('#categoryError').html('');
    }
    if(date && title && category){
      $.ajax({
        async: true,
        type:'POST',
        url:'includes/functions.php',
        data:'func=updateEvent&date='+date+'&title='+title+'&description='+description+'&privacy='+privacy+'&sharedWith='+sharedWith+'&deleteAuth='+deleteAuth+'&category='+category+'&eventId='+eventId,
        success:function(msg){
          if(msg === 'ok'){
            const dateSplit = date.split("-");
            $('#eventTitle').val('');
            $('#eventDescription').val('');
            $('#sharedWith').val('');
            $('#deleteAuth').val('');
            $('#category').val('');
            $('#createdModal').modal('show');
            $('#modalText').empty();
            $('#modalText').append('Event updated successfully!');
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
  var list = document.querySelectorAll(`[type*="checkbox"]`);
  list.forEach( el => {
    var checked = JSON.parse(localStorage.getItem("cal-"+el.id));
    document.getElementById(el.id).checked = checked;
  });
  let maskCheck = list[0];
  let imageCheck = list[1];
  let event = document.querySelectorAll('.event');
  let events = document.querySelectorAll('.events');
  let backgroundElement = document.querySelector('#main-content');
  let date_cell = document.querySelectorAll('.date_cell');
  let mask = document.querySelector('#maskCheck');
  let maskLabel = document.querySelector('#maskCheckLabel');
  if (maskCheck.checked === true) {
    date_cell.forEach(h => {
      h.classList.add("masked");
    });
  } else {
    date_cell.forEach(h => {
      h.classList.remove("masked");
    })
  }
  if (imageCheck.checked === true) {
    backgroundElement.classList.add("hideImage");
    mask.type = 'hidden';
    maskLabel.classList.add('none');
  } else {
    backgroundElement.classList.remove("hideImage");
    mask.type = 'checkbox';
    maskLabel.classList.remove('none');
  }
  let calTitle = document.querySelector('.title');
  let nav = document.querySelector('.cal-nav');
  let calDays = document.querySelector('.calendar-days');
  let calDates = document.querySelector('.calendar-dates');
  let hideChk = document.querySelectorAll('.hideThis');
  let loginSection = document.querySelector('.loginMessageLink');
  let settingsSection = document.querySelector('.settingsLink');
  if (calCheck.checked === true) {
    calTitle.style.visibility = "hidden";
    nav.style.visibility = "hidden";
    calDays.style.visibility = "hidden";
    calDates.style.visibility = "hidden";
    loginSection.style.visibility = "hidden";
    settingsSection.style.visibility = "hidden";
    hideChk.forEach( el => {
      el.style.display = "none";
    });
  } else {
    calTitle.removeAttribute("style");
    nav.removeAttribute("style");
    calDays.removeAttribute("style");
    calDates.removeAttribute("style");
    loginSection.removeAttribute("style");
    settingsSection.removeAttribute("style");
    hideChk.forEach( el => {
      el.removeAttribute("style");
    });
  }

  $('.month_dropdown').on('change',function(){
    getCalendar('calendar_div', $('.year_dropdown').val(), $('.month_dropdown').val());
  });
  $('.year_dropdown').on('change',function(){
    getCalendar('calendar_div', $('.year_dropdown').val(), $('.month_dropdown').val());
  });
  $(document).click(function(){
    $('#event_list').slideUp('slow');
    $('#solo_event').slideUp('slow');
  });

  var titleCharacterCount = $('#eventTitle').val().length,
    current = $('#current'),
    maximum = $('#maximum');
  if (titleCharacterCount < 50) {
    current.css('color', '#006600', 'font-weight', 'normal');
  }
  if (titleCharacterCount > 44 && titleCharacterCount < 50) {
    current.css('color', '#ffff00');
    current.css('font-weight', 'normal');
  }
  if (titleCharacterCount >= 50) {
    maximum.css('color', '#880000');
    maximum.css('font-weight', 'bold');
    current.css('color', '#880000');
    current.css('font-weight', 'bold');
  } else {
    maximum.css('color', '#000');
    maximum.css('font-weight', 'normal');
  }

  var descCharacterCount = $('#eventDescription').val().length,
    currentDesc = $('#currentDesc'),
    maximumDesc = $('#maximumDesc');
  if (descCharacterCount < 200) {
    currentDesc.css('color', '#006600', 'font-weight', 'normal');
  }
  if (descCharacterCount > 174 && descCharacterCount < 200) {
    currentDesc.css('color', '#ffff00');
    currentDesc.css('font-weight', 'normal');
  }
  if (descCharacterCount >= 200) {
    maximumDesc.css('color', '#880000');
    maximumDesc.css('font-weight', 'bold');
    currentDesc.css('color', '#880000');
    currentDesc.css('font-weight', 'bold');
  } else {
    maximumDesc.css('color', '#000');
    maximumDesc.css('font-weight', 'normal');
  }
});

saveMask = function(){
  let maskCheck = document.querySelector('#maskCheck');
  localStorage.setItem("cal-"+maskCheck.id, maskCheck.checked);
  let date_cell = document.querySelectorAll('.date_cell');
  if (maskCheck.checked === true) {
    date_cell.forEach(h => {
      h.classList.add("masked");
    });
  } else {
    date_cell.forEach(h => {
      h.classList.remove("masked");
    })
  } 
}

saveImage = function(){
  let imageCheck = document.querySelector('#imageCheck');
  localStorage.setItem("cal-"+imageCheck.id, imageCheck.checked);
  let backgroundElement = document.querySelector('#main-content');
  let mask = document.querySelector('#maskCheck');
  let maskLabel = document.querySelector('#maskCheckLabel');
  let date_cell = document.querySelectorAll('.date_cell');
  if (imageCheck.checked === true) {
    backgroundElement.classList.add("hideImage");
    mask.type = 'hidden';
    maskLabel.classList.add('none');
    date_cell.forEach(h => {
      h.classList.remove("masked");
    })
  } else {
    backgroundElement.classList.remove("hideImage");
    mask.type = 'checkbox';
    maskLabel.classList.remove('none');
  }
}

hideCalendar = function(){
  let calCheck = document.querySelector('#calCheck');
  localStorage.setItem("cal-"+calCheck.id, calCheck.checked);
  let calTitle = document.querySelector('.title');
  let nav = document.querySelector('.cal-nav');
  let calDays = document.querySelector('.calendar-days');
  let calDates = document.querySelector('.calendar-dates');
  let loginSection = document.querySelector('.loginMessageLink');
  let settingsSection = document.querySelector('.settingsLink');
  let hideChk = document.querySelectorAll('.hideThis');
  if (calCheck.checked === true) {
    calTitle.style.visibility = "hidden";
    nav.style.visibility = "hidden";
    calDays.style.visibility = "hidden";
    calDates.style.visibility = "hidden";
    loginSection.style.visibility = "hidden";
    settingsSection.style.visibility = "hidden";
    hideChk.forEach( el => {
      el.style.display = "none";
    });
  } else {
    calTitle.removeAttribute("style");
    nav.removeAttribute("style");
    calDays.removeAttribute("style");
    calDates.removeAttribute("style");
    loginSection.removeAttribute("style");
    settingsSection.removeAttribute("style");
    hideChk.forEach( el => {
      el.removeAttribute("style");
    });
  }
}

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
