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

function delEvent(id){
  $('#eventId').val(id);
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
            $('#updatedModal').modal('show');
            $('#modalText').empty();
            $('#modalText').append('Event updated successfully!');
            getCalendar('calendar_div',dateSplit[0],dateSplit[1]);
            setTimeout(function(){
              $("#updatedModal").modal("hide")
            }, 2000);
          }else{
            $('#updatedModal').modal('show');
            $('#modalText').empty();
            $('#modalText').append('Some problem occurred, please try again.');
          }
        }
      });
    }
  });
});

$(document).ready(function(){
  showPopups();
  var list = document.querySelectorAll(`[type*="checkbox"]`);
  list.forEach( el => {
    var checked = JSON.parse(localStorage.getItem(el.id));
    document.getElementById(el.id).checked = checked;
  });
  let eventCheck = list[0];
  let imageCheck = list[1];
  let events = document.querySelectorAll('.event');
  let backgroundElement = document.querySelector('#main-content');
  let date_cell = document.querySelectorAll('.date_cell');
  if (eventCheck.checked === true) {
    events.forEach(e => e.style.display = "block");
    date_cell.forEach(h => {
      h.classList.replace("has_event", "one_event");
      h.classList.replace("has_2_multi", "two_event");
      h.classList.replace("has_3_multi", "three_event");
      h.classList.replace("has_4_multi", "four_event");
      h.classList.replace("has_5_multi", "five_event");
      h.classList.replace("has_multi", "multi_event");
    });
    hidePopups();
  }
  if (imageCheck.checked === true) {
    backgroundElement.classList.add("hideImage");
  } else {
    backgroundElement.classList.remove("hideImage");
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

showPopups = function(){
  $('.date_cell').mouseenter(function(){
    date = $(this).attr('data-date');
    $(".date_popup_wrap").hide();
    $("#date_popup_"+date).show();
  });
  $('.date_cell').mouseleave(function(){
    $(".date_popup_wrap").hide();
  });
}

hidePopups = function(){
  $('.date_cell').mouseenter(function(){
    date = $(this).attr('data-date');
    $(".date_popup_wrap").hide();
    $("#date_popup_"+date).hide();
  });
}

saveEvent = function(){
  let eventCheck = document.querySelector('#eventCheck');
  localStorage.setItem(eventCheck.id, eventCheck.checked);
  let events = document.querySelectorAll('.event');
  let date_cell = document.querySelectorAll('.date_cell');
  if (eventCheck.checked === true) {
    events.forEach(e => e.style.display = "block");
    date_cell.forEach(h => {
      h.classList.replace("has_event", "one_event");
      h.classList.replace("has_2_multi", "two_event");
      h.classList.replace("has_3_multi", "three_event");
      h.classList.replace("has_4_multi", "four_event");
      h.classList.replace("has_5_multi", "five_event");
      h.classList.replace("has_multi", "multi_event");
    });
    hidePopups();
  } else {
    events.forEach(e => e.style.display = "none");
    date_cell.forEach(h => {
      h.classList.replace("one_event", "has_event");
      h.classList.replace("two_event", "has_2_multi");
      h.classList.replace("three_event", "has_3_multi");
      h.classList.replace("four_event", "has_4_multi");
      h.classList.replace("five_event", "has_5_multi");
      h.classList.replace("multi_event", "has_multi");
    })
    showPopups();
  } 
}

saveImage = function(){
  let imageCheck = document.querySelector('#imageCheck');
  localStorage.setItem(imageCheck.id, imageCheck.checked);
  let backgroundElement = document.querySelector('#main-content');
  if (imageCheck.checked === true) {
    backgroundElement.classList.add("hideImage");
  } else {
    backgroundElement.classList.remove("hideImage");
  }
}

hideCalendar = function(){
  let calCheck = document.querySelector('#calCheck');
  localStorage.setItem(calCheck.id, calCheck.checked);
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