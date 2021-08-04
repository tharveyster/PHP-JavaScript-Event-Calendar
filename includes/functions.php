<?php
/* The basic calendar code came from https://www.codexworld.com/build-event-calendar-using-jquery-ajax-php/php-event-calendar-jquery-ajax-mysql-codexworld/. That code consisted of displaying the calendar, and adding and viewing events. The functionality has been improved to allow cancellation of new events in the "Add event" section before submitting them, setting event privacy to public or private, sharing private events with other users via their usernames, deletion of events by original submitter (and approved users) only, and visible holidays. There's also a new settings section where users can change their name and email address, and add background images to each month. */

session_start();
date_default_timezone_set('America/New_York');

$username = "";

if(isset($_SESSION['username'])) {
	$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
}

if(isset($_POST['func']) && !empty($_POST['func'])){
  switch($_POST['func']){
    case 'getCalender':
      getCalender($_POST['year'],$_POST['month']);
      break;
    case 'getEvents':
      getEvents($_POST['date']);
      break;
    case 'addEvent':
      addEvent($_POST['date'],$_POST['title'],$_POST['privacy'],$_POST['sharedWith'],$_POST['deleteAuth']);
      break;
    case 'delEvent':
      delEvent($_POST['id']);
      break;
    default:
      break;
  }
}

function getCalender($year = '', $month = ''){
  $dateYear = ($year != '')?$year:date("Y");
  $dateMonth = ($month != '')?$month:date("m");
  $date = $dateYear.'-'.$dateMonth.'-01';
  $currentMonthFirstDay = date("N",strtotime($date));
  $totalDaysOfMonth = cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear);
  $totalDaysOfMonthDisplay = ($currentMonthFirstDay === 7)?($totalDaysOfMonth):($totalDaysOfMonth + $currentMonthFirstDay);
  $boxDisplay = ($totalDaysOfMonthDisplay <= 35)?42:42;

?>
      <div id="backimg" class="calendar-wrap">
        <div class="cal-nav">
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' - 1 Month')); ?>','<?php echo date("m",strtotime($date.' - 1 Month')); ?>');">&#10092;&#10092;&#10092;</a>
          <select class="month_dropdown"><?php echo getAllMonths($dateMonth); ?></select>
          <select class="year_dropdown"><?php echo getYearList($dateYear); ?></select>
          <button class="btn btn-secondary today_btn" onclick="window.location.reload(true);">Today</button>
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' + 1 Month')); ?>','<?php echo date("m",strtotime($date.' + 1 Month')); ?>');">&#10093;&#10093;&#10093;</a>
        </div>
        <div id="event_list" class="none">
        </div>
        <div id="event_add" class="none">
          <p>Add Event on <span id="eventDateView"></span></p>
          <p><b>Event Title: </b><input type="text" id="eventTitle" value="" required /></p>
          <input type="hidden" id="eventDate" value=""/>
          <p>
            <b>Privacy Setting: </b>
            <label for="private">Private</label>
            <input type="radio" class="eventPrivacy" id="privateCheck" name="eventPrivacy" value=1 checked>
            <label for="public">Public</label>
            <input type="radio" class="eventPrivacy" id="publicCheck" name="eventPrivacy" value=0>
          </p>
          <p><b>Share With Username(s): </b><input type="text" id="sharedWith" value="" placeholder="Ex: name1,name2,name3" /></p>
          <p><b>Authorized Deleter(s): </b><input type="text" id="deleteAuth" value="" placeholder="Ex: name1,name2,name3" /></p>
          <input type="button" id="addEventBtn" value="Add"/>
          <input type="button" id="cancelAddEventBtn" value="Cancel"/>
        </div>
        <div class="calendar-days">
          <ul>
            <li>SUNDAY</li>
            <li>MONDAY</li>
            <li>TUESDAY</li>
            <li>WEDNESDAY</li>
            <li>THURSDAY</li>
            <li>FRIDAY</li>
            <li>SATURDAY</li>
          </ul>
        </div>
        <div class="calendar-dates">
          <ul>
<?php
  $dayCount = 1;
  $eventNum = 0;
  for($cb=1;$cb<=$boxDisplay;$cb++){
    if(($cb >= $currentMonthFirstDay+1 || $currentMonthFirstDay === 7) && $cb <= ($totalDaysOfMonthDisplay)){
      // Current date
      if ($dayCount < 10) {
        $dayCount = '0'.$dayCount;
      }
      $currentDate = $dateYear.'-'.$dateMonth.'-'.$dayCount;
			if(isset($_SESSION['username'])) {
				$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
			}else{
				$username = "";
			}

      // Include the database config file
      include_once 'dbConfig.php';

      // Get number of events based on the current date
      if(!isset($_SESSION['username'])) {
        $result = $con->prepare("SELECT title FROM events WHERE date = :cd AND status = 1 AND privacy = 0");
        $result->bindParam(":cd", $currentDate);
        $result->execute();
        $eventNum = $result->rowCount();
      }else{
        $result = $con->prepare("SELECT title FROM events WHERE (date = :cd AND status = 1 AND user = :un) OR (date = :cd AND status = 1 AND privacy = 0) OR (date = :cd AND status = 1 AND sharedWith LIKE :sw)");
        $sharedWith = "% ".$username." %";
        $result->bindParam(":cd", $currentDate);
        $result->bindParam(":un", $username);
        $result->bindParam(":sw", $sharedWith);
        $result->execute();
        $eventNum = $result->rowCount();
      }

      // Define date cell color
      if($eventNum === 1 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="today_event has_event date_cell">';
      }elseif($eventNum === 1 && (strtotime($currentDate) < strtotime(date("Y-m-d")))){
        echo '            <li date="'.$currentDate.'" class="past_event date_cell">';
      }elseif($eventNum > 1 && (strtotime($currentDate) < strtotime(date("Y-m-d")))){
        echo '            <li date="'.$currentDate.'" class="past_multi date_cell">';
      }elseif($eventNum === 2 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="today_2_multi has_2_multi date_cell">';
      }elseif($eventNum === 3 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="today_3_multi has_3_multi date_cell">';
      }elseif($eventNum === 4 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="today_4_multi has_4_multi date_cell">';
      }elseif($eventNum > 4 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="today_multi has_multi date_cell">';
      }elseif($eventNum === 0 && strtotime($currentDate) === strtotime(date("Y-m-d"))){
        echo '            <li date="'.$currentDate.'" class="this_day date_cell">';
      }elseif($eventNum === 1){
        echo '            <li date="'.$currentDate.'" class="has_event date_cell">';
      }elseif($eventNum === 2){
        echo '            <li date="'.$currentDate.'" class="has_2_multi date_cell">';
      }elseif($eventNum === 3){
        echo '            <li date="'.$currentDate.'" class="has_3_multi date_cell">';
      }elseif($eventNum === 4){
        echo '            <li date="'.$currentDate.'" class="has_4_multi date_cell">';
      }elseif($eventNum > 4){
        echo '            <li date="'.$currentDate.'" class="has_multi date_cell">';
      }elseif(strtotime($currentDate) > strtotime(date("Y-m-d"))){
        if(isset($_SESSION['username'])) {
          echo '            <li date="'.$currentDate.'" class="pending_day date_cell">';
        }else{
          echo '            <li date="'.$currentDate.'">';
        }
      }elseif($eventNum === 0 && (strtotime($currentDate) < strtotime(date("Y-m-d")))){
        if(isset($_SESSION['username'])) {
          echo '            <li date="'.$currentDate.'" class="date_cell past_day">';
        }else{
          echo '            <li date="'.$currentDate.'">';
        }
      }else{
        echo '            <li date="'.$currentDate.'" class="date_cell">';
      }
                
      // Date cell
      echo '<span>';
      echo $dayCount;
      echo '</span>';

      // Hover event popup
      if($eventNum > 0 || ((strtotime($currentDate) >= strtotime(date("Y-m-d"))) && isset($_SESSION['username']))){
        echo '<div id="date_popup_'.$currentDate.'" class="date_popup_wrap none">';
        echo '<div class="date_window">';
        echo '<div class="popup_event">Events ('.$eventNum.')</div>';
        echo ($eventNum > 0)?'<a href="javascript:;" onclick="getEvents(\''.$currentDate.'\');">view events</a>':'';
        if(isset($_SESSION['username'])) {
          echo '<a href="javascript:void(0);" onclick="addEvent(\''.$currentDate.'\');"><br />add event</a>';
        }
			  echo '</div></div>';
      }

      echo '</li>'."\r\n";
      $dayCount++;
    }else{
      echo '            <li><span>&nbsp;</span></li>'."\r\n";
    }
  }
	$month01 = 'blank.png';
	$month02 = 'blank.png';
	$month03 = 'blank.png';
	$month04 = 'blank.png';
	$month05 = 'blank.png';
	$month06 = 'blank.png';
	$month07 = 'blank.png';
	$month08 = 'blank.png';
	$month09 = 'blank.png';
	$month10 = 'blank.png';
	$month11 = 'blank.png';
	$month12 = 'blank.png';
	if(isset($_SESSION['username'])) {
	    $username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
	}else{
		$username = "";
	}
  include_once 'dbConfig.php';
	$images = $con->prepare("SELECT * FROM users WHERE username = :user");
	$images->bindParam(":user", $username);
	$images->execute();
  while($row = $images->fetch(PDO::FETCH_ASSOC)) {
		$month01 = $row["january"];
		$month02 = $row["february"];
		$month03 = $row["march"];
		$month04 = $row["april"];
		$month05 = $row["may"];
		$month06 = $row["june"];
		$month07 = $row["july"];
		$month08 = $row["august"];
		$month09 = $row["september"];
		$month10 = $row["october"];
		$month11 = $row["november"];
		$month12 = $row["december"];
	}
?>
          </ul>
        </div>
      </div>
      <script>
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
            var date = $('#eventDate').val();
            var title = $('#eventTitle').val();
            var privacy = $('.eventPrivacy:checked').val();
            var sharedWith = $('#sharedWith').val();
            var deleteAuth = $('#deleteAuth').val();
            if(title){
              $.ajax({
                type:'POST',
                url:'includes/functions.php',
                data:'func=addEvent&date='+date+'&title='+title+'&privacy='+privacy+'&sharedWith='+sharedWith+'&deleteAuth='+deleteAuth,
                success:function(msg){
                  if(msg === 'ok'){
                    var dateSplit = date.split("-");
                    $('#eventTitle').val('');
                    $('#createdModal').modal('show');
                    $('#modalText').empty();
                    $('#modalText').append('Event created successfully!');
                    getCalendar('calendar_div',dateSplit[0],dateSplit[1]);
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
            date = $(this).attr('date');
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
      </script>
      <script>
        var imageAddress;
        var showDates = document.getElementsByClassName("calendar-dates")[0].children[0].children;
        if (parseInt($('.month_dropdown').val()) === 1) {
          imageAddress = "<?php echo $month01; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-01-01')) {
                var newyeDay = document.createElement('span');
                newyeDay.classList.add("holiday");
                newyeDay.append("New Year's Day");
                showDates[i].append(newyeDay);
              }
            }
          }
          var reference;
          var thirdMon;
          if ('date' in showDates[1].attributes) {
            reference = 15;
            thirdMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 22;
            thirdMon = showDates[reference].attributes[0].textContent;
          }
          var thirdMonDay = thirdMon.substr(8);
          var mlkjrDate = '-01-' + thirdMonDay;
          if (showDates[reference].attributes[0].textContent.includes(mlkjrDate)) {
            var mlkjrDay = document.createElement('span');
            mlkjrDay.classList.add("holiday");
            mlkjrDay.append("Martin Luther King Jr. Day");
            showDates[reference].append(mlkjrDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 2) {
          imageAddress = "<?php echo $month02; ?>";
          var reference;
          var thirdMon;
          if ('date' in showDates[1].attributes) {
            reference = 15;
            thirdMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 22;
            thirdMon = showDates[reference].attributes[0].textContent;
          }
          var thirdMonDay = thirdMon.substr(8);
          var presiDate = '-02-' + thirdMonDay;
          if (showDates[reference].attributes[0].textContent.includes(presiDate)) {
            var presiDay = document.createElement('span');
            presiDay.classList.add("holiday");
            presiDay.append("Presidents' Day");
            showDates[reference].append(presiDay);
          }
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-02-02')) {
                var grounDay = document.createElement('span');
                grounDay.classList.add("holiday");
                grounDay.append("Groundhog Day");
                showDates[i].append(grounDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-02-14')) {
                var valenDay = document.createElement('span');
                valenDay.classList.add("holiday");
                valenDay.append("Valentine's Day");
                showDates[i].append(valenDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 3) {
          imageAddress = "<?php echo $month03; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-03-17')) {
                var stpatDay = document.createElement('span');
                stpatDay.classList.add("holiday");
                stpatDay.append("St. Patrick's Day");
                showDates[i].append(stpatDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 4) {
          imageAddress = "<?php echo $month04; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-04-01')) {
                var aprilDay = document.createElement('span');
                aprilDay.classList.add("holiday");
                aprilDay.append("April Fools' Day");
                showDates[i].append(aprilDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-04-22')) {
                var earthDay = document.createElement('span');
                earthDay.classList.add("holiday");
                earthDay.append("Earth Day");
                showDates[i].append(earthDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 5) {
          imageAddress = "<?php echo $month05; ?>";
          var reference;
          var lastMon;
          if ('date' in showDates[36].attributes) {
            reference = 36;
            lastMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 29;
            lastMon = showDates[reference].attributes[0].textContent;
          }
          var lastMonDay = lastMon.substr(8);
          var memorDate = '-05-' + lastMonDay;
          if (showDates[reference].attributes[0].textContent.includes(memorDate)) {
            var memorDay = document.createElement('span');
            memorDay.classList.add("holiday");
            memorDay.append("Memorial Day");
            showDates[reference].append(memorDay);
          }
          var reference2;
          var secondSun;
          if ('date' in showDates[0].attributes) {
            reference2 = 7;
            secondSun = showDates[reference2].attributes[0].textContent;
          } else {
            reference2 = 14;
            secondSun = showDates[reference2].attributes[0].textContent;
          }
          var secondSunDay = secondSun.substr(8);
          var motheDate = '-05-' + secondSunDay;
          if (showDates[reference2].attributes[0].textContent.includes(motheDate)) {
            var motheDay = document.createElement('span');
            motheDay.classList.add("holiday");
            motheDay.append("Mothers' Day");
            showDates[reference2].append(motheDay);
          }
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-05-05')) {
                var cincoDay = document.createElement('span');
                cincoDay.classList.add("holiday");
                cincoDay.append("Cinco de Mayo");
                showDates[i].append(cincoDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 6) {
          imageAddress = "<?php echo $month06; ?>";
          var reference;
          var thirdSun;
          if ('date' in showDates[0].attributes) {
            reference = 14;
            thirdSun = showDates[reference].attributes[0].textContent;
          } else {
            reference = 21;
            thirdSun = showDates[reference].attributes[0].textContent;
          }
          var thirdSunDay = thirdSun.substr(8);
          var fatheDate = '-06-' + thirdSunDay;
          if (showDates[reference].attributes[0].textContent.includes(fatheDate)) {
            var fatheDay = document.createElement('span');
            fatheDay.classList.add("holiday");
            fatheDay.append("Fathers' Day");
            showDates[reference].append(fatheDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 7) {
          imageAddress = "<?php echo $month07; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-07-04')) {
                var indepDay = document.createElement('span');
                indepDay.classList.add("holiday");
                indepDay.append("Independence Day");
                showDates[i].append(indepDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 8) {
          imageAddress = "<?php echo $month08; ?>";
        }
        else if (parseInt($('.month_dropdown').val()) === 9) {
          imageAddress = "<?php echo $month09; ?>";
          var reference;
          var firstMon;
          if ('date' in showDates[1].attributes) {
            reference = 1;
            firstMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 8;
            firstMon = showDates[reference].attributes[0].textContent;
          }
          var firstMonDay = firstMon.substr(8);
          var laborDate = '-09-' + firstMonDay;
          if (showDates[reference].attributes[0].textContent.includes(laborDate)) {
            var laborDay = document.createElement('span');
            laborDay.classList.add("holiday");
            laborDay.append("Labor Day");
            showDates[reference].append(laborDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 10) {
          imageAddress = "<?php echo $month10; ?>";
          var reference;
          var secondMon;
          if ('date' in showDates[1].attributes) {
            reference = 8;
            secondMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 15;
            secondMon = showDates[reference].attributes[0].textContent;
          }
          var secondMonDay = secondMon.substr(8);
          var columDate = '-10-' + secondMonDay;
          if (showDates[reference].attributes[0].textContent.includes(columDate)) {
            var columDay = document.createElement('span');
            columDay.classList.add("holiday");
            columDay.append("Columbus Day");
            showDates[reference].append(columDay);
          }
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-10-31')) {
                var hallowee = document.createElement('span');
                hallowee.classList.add("holiday");
                hallowee.append("Halloween");
                showDates[i].append(hallowee);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 11) {
          imageAddress = "<?php echo $month11; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-11-11')) {
                var veterDay = document.createElement('span');
                veterDay.classList.add("holiday");
                veterDay.append("Veterans Day");
                showDates[i].append(veterDay);
              }
            }
          }
          var reference;
          var fourthThu;
          if ('date' in showDates[4].attributes) {
            reference = 25;
            fourthThu = showDates[reference].attributes[0].textContent;
          } else {
            reference = 32;
            fourthThu = showDates[reference].attributes[0].textContent;
          }
          var fourthThuDay = fourthThu.substr(8);
          var thankDate = '-11-' + fourthThuDay;
          if (showDates[reference].attributes[0].textContent.includes(thankDate)) {
            var thankDay = document.createElement('span');
            thankDay.classList.add("holiday");
            thankDay.append("Thanksgiving Day");
            showDates[reference].append(thankDay);
          }
        }
        else {
          imageAddress = "<?php echo $month12; ?>";
          for (var i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-12-24')) {
                var chrisEve = document.createElement('span');
                chrisEve.classList.add("holiday");
                chrisEve.append("Christmas Eve");
                showDates[i].append(chrisEve);
              }
              if (showDates[i].attributes[0].textContent.includes('-12-25')) {
                var chrisDay = document.createElement('span');
                chrisDay.classList.add("holiday");
                chrisDay.append("Christmas Day");
                showDates[i].append(chrisDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-12-31')) {
                var newyeEve = document.createElement('span');
                newyeEve.classList.add("holiday");
                newyeEve.append("New Year's Eve");
                showDates[i].append(newyeEve);
              }
            }
          }
        }
        document.getElementsByTagName("body")[0].style.backgroundImage = "url('images/" + imageAddress + "')";
        /*document.getElementsByTagName("body")[0].style.backgroundRepeat = "no-repeat";
        document.getElementsByTagName("body")[0].style.backgroundSize = "100% 100%";*/
      </script>
<?php
}

/*
 * Generate months options list for select box
 */
function getAllMonths($selected = ''){
  $options = '';
  for($i=1;$i<=12;$i++)
  {
      $value = ($i < 10)?'0'.$i:$i;
      $selectedOpt = ($value == $selected)?'selected':'';
      $options .= '<option value="'.$value.'" '.$selectedOpt.' >'.date("F", mktime(0, 0, 0, $i+1, 0, 0)).'</option>';
  }
  return $options;
}

/*
 * Generate years options list for select box
 */
function getYearList($selected = ''){
  $options = '';
  for($i=2019;$i<=2035;$i++)
  {
      $selectedOpt = ($i == $selected)?'selected':'';
      $options .= '<option value="'.$i.'" '.$selectedOpt.' >'.$i.'</option>';
  }
  return $options;
}

/*
 * Generate events list in HTML format
 */
function getEvents($date = ''){
  // Include the database config file
  include_once 'dbConfig.php';

  $eventListHTML = '';
  $date = $date?$date:date("Y-m-d");
  if(isset($_SESSION['username'])) {
    $username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
  }else{
    $username = "";
  }

  // Fetch events based on the specific date
  if(!isset($_SESSION['username'])) {
    $result = $con->prepare("SELECT * FROM events WHERE date = :date AND status = 1 AND privacy = 0");
    $result->bindParam(":date", $date);
    $result->execute();
  }else{
    $result = $con->prepare("SELECT * FROM events WHERE (date = :date AND status = 1 AND user = :un) OR (date = :date AND status = 1 AND privacy = 0) OR (date = :date AND status = 1 AND sharedWith LIKE :sw) ORDER BY title");
    $sharedWith = "% ".$username." %";
    $result->bindParam(":date", $date);
    $result->bindParam(":un", $username);
    $result->bindParam(":sw", $sharedWith);
    $result->execute();
  }
  if($result->rowCount() > 0){
    $eventListHTML = '<h2>Events on '.date("l, M d, Y",strtotime($date)).'</h2>';
    /*$eventListHTML .= '<ul class="eventList">';*/
    $eventListHTML .= '<table class="eventList">';
    $eventListHTML .= '<tr>';
    $eventListHTML .= '<th class="eventItem">Title</th>';
    if(isset($_SESSION['username'])) {
      $eventListHTML .= '<th class="eventItem">Creator</th><th class="eventItem">Privacy</th><th class="eventItem">Shared With</th><th class="eventItem"></th>';
    }
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $eventListHTML .= '<tr>';
      $id = $row['id'];
      $user = $row['user'];
      $eventTitle = $row['title'];
      $privacy = $row['privacy'];
      $eventShared = $row['sharedWith'];
      $deleteAuth = $row['deleteAuth'];
      if ($user == $username) {
        $creatorName = "You";
      } else {
        $creatorName = $user;
      }
      if ($privacy == '1') {
        $eventPrivacy = '<span style="color:red;">private</span>';
      } else {
        $eventPrivacy = '<span style="color:green;">public</span>';
      }
      if ($eventShared !== '') {
        $eventShared = str_replace(' , ', ', ', $eventShared);
      }
      $authConfirm = ' '.$username.' ';
      if(!isset($_SESSION['username'])) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td>';
      } else if ($user != $username && strpos($deleteAuth, $authConfirm) === false) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td><td class="eventItem">'.$user.'</td><td  class="eventItem">'.$eventPrivacy.'</td>';
      } else if ($user != $username && strpos($deleteAuth, $authConfirm) !== false) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td><td class="eventItem">'.$user.'</td><td  class="eventItem">'.$eventPrivacy.'</td><td></td><td class="eventItem"><button class="delEventBtn" id="'.$id.'">Delete</button></td>';
      } else {
        $eventListHTML .= '<td class="eventItem" id="'.$id.'">'.$eventTitle.'</td><td class="eventItem">'.$creatorName.'</td><td class="eventItem">'.$eventPrivacy.'</td><td class="eventItem">'.$eventShared.'</td><td class="eventItem"><button class="delEventBtn" id="'.$id.'">Delete</button></td>';
      }
      $eventListHTML .= '</tr>';
    }
    $eventListHTML .= '</table>';
  }
  echo $eventListHTML;
  echo '<script>
	$(document).ready(function(){
    $(".delEventBtn").on("click",function() {
      var date2 = $("#eventDate").val();
      var del_id = $(this).attr("id");
      var info = "id=" + del_id;
      $.ajax({
        type : "POST",
        url : "deleteEvent.php", //URL to the delete php script
        data : info,
        success : function() {
          var curMonth = $(".month_dropdown").val();
          var curYear = $(".year_dropdown").val();
          $("#createdModal").modal("show");
          $("#modalText").empty();
          $("#modalText").append("Event deleted successfully!");
          getCalendar("calendar_div",curYear,curMonth);
        }
      });
    });
  });
  </script>';
}

/*
 * Add event to date
 */
function addEvent($date,$title,$privacy,$sharedWith,$deleteAuth){
  //Include db configuration file
  include 'dbConfig.php';
  $currentDate = date("Y-m-d H:i:s");
  if(isset($_SESSION['username'])) {
    $username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
  }else{
    $username = "";
  }
  if(isset($_POST['addEventBtn'])) {
    $privacy = intval($_POST['eventPrivacy']);
    $sharedWith = strip_tags($_POST['sharedWith']);
    $deleteAuth = strip_tags($_POST['deleteAuth']);
  }
  //Insert the event data into database
  $insert = $con->prepare("INSERT INTO events (title,date,created,modified,user,privacy,sharedWith,deleteAuth) VALUES (:title,:date,:currentDate,:currentDate,:un,:ep,:sw,:da)");
  $insert->bindParam(":title", $title);
  $insert->bindParam(":date", $date);
  $insert->bindParam(":currentDate", $currentDate);
  $insert->bindParam(":un", $username);
  $insert->bindParam(":ep", $privacy);
  $sharedWith = str_replace(',', ' , ', $sharedWith);
  $sharedWith = ' '. $sharedWith .' ';
  $insert->bindParam(":sw", $sharedWith);
  $deleteAuth = str_replace(',', ' , ', $deleteAuth);
  $deleteAuth = ' '. $deleteAuth .' ';
  $insert->bindParam(":da", $deleteAuth);
  $insert->execute();
  if($insert){
    echo 'ok';
  }else{
    echo 'err';
  }
}
?>
