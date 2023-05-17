<?php
/* The basic calendar code came from https://www.codexworld.com/build-event-calendar-using-jquery-ajax-php/php-event-calendar-jquery-ajax-mysql-codexworld/. That code consisted of displaying the calendar, and adding and viewing events. The functionality has been improved to allow cancellation of new events in the "Add event" section before submitting them, setting event privacy to public or private, sharing private events with other users via their usernames, deletion of events by original submitter (and approved users) only, and visible holidays. There's also a new settings section where users can change their name and email address, and add background images to each month. The ability to modify events (event creator only) has been added, so the date, title, description, privacy, shared with, and delete authority can all be changed. */

$userId = "";

ini_set('session.gc_maxlifetime', 1200);
session_start();
date_default_timezone_set('America/New_York');

if(!isset($_SESSION['userId']) && !isset($_SESSION['username'])) {
  header('Location: signIn.php');
}else{
  $userId = htmlspecialchars(strip_tags($_SESSION['userId']), ENT_QUOTES);
  $username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
}

if(isset($_POST['func']) && !empty($_POST['func'])){
  switch($_POST['func']){
    case 'getCalendar':
      getCalendar($_POST['year'],$_POST['month']);
      break;
    case 'getEvents':
      getEvents($_POST['date'],$userId);
      break;
    case 'getEvent':
      getEvent($_POST['date'],$_POST['id'],$userId);
      break;
    case 'addEvent':
      addEvent($_POST['date'],$_POST['title'],$_POST['description'],$_POST['privacy'],$_POST['sharedWith'],$_POST['deleteAuth'],$_POST['category'],$userId);
      break;
    case 'modEvent':
      modEvent($_POST['id'],$userId);
      break;
    case 'updateEvent':
      updateEvent($_POST['date'],$_POST['title'],$_POST['description'],$_POST['privacy'],$_POST['sharedWith'],$_POST['deleteAuth'],$_POST['category'],$_POST['eventId']);
      break;
    case 'deleteEvent':
      deleteEvent($_POST['id']);
      break;
    default:
      break;
  }
}

function getCalendar($year = '', $month = ''){
  $dateYear = ($year != '')?$year:date("Y");
  $dateMonth = ($month != '')?$month:date("m");
  $date = $dateYear.'-'.$dateMonth.'-01';
  $currentMonthFirstDay = (intval(date("N",strtotime($date))) === 7)?0:date("N",strtotime($date));
  $totalDaysOfMonth = cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear);
  $totalDaysOfMonthDisplay = ($currentMonthFirstDay === 0)?($totalDaysOfMonth):($totalDaysOfMonth + $currentMonthFirstDay);
  $boxDisplay = ($totalDaysOfMonthDisplay <= 35)?35:42;
  $expandBlocks = ($boxDisplay === 35)?" expanded":"";
?>
      <div class="calendar-wrap">
      <div class="checkboxSection">
          <input type="hidden" id="maskCheck" name="maskCheck" class="hideThis" onclick="saveMask()"><label for="maskCheck" id="maskCheckLabel" class="hideThis none">Mask Mode</label>
          <input type="checkbox" id="imageCheck" name="imageCheck" class="hideThis" onclick="saveImage()"><label for="imageCheck" class="hideThis">Hide Image</label>
          <input type="checkbox" id="calCheck" name="calCheck" onclick="hideCalendar()"><label for="calCheck">Hide Calendar</label>
        </div>
        <div class="cal-nav">
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y", strtotime($date.' - 1 Month')); ?>','<?php echo date("m",strtotime($date.' - 1 Month')); ?>');">&#10092;&#10092;&#10092;</a>
          <select class="month_dropdown"><?php echo getAllMonths($dateMonth); ?></select>
          <select class="year_dropdown"><?php echo getYearList($dateYear); ?></select>
          <button class="btn btn-secondary today_btn" onclick="window.location.reload(true);">Today</button>
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y", strtotime($date.' + 1 Month')); ?>','<?php echo date("m",strtotime($date.' + 1 Month')); ?>');">&#10093;&#10093;&#10093;</a>
        </div>
        <div id="event_list" class="none">
        </div>
        <div id="solo_event" class="none">
        </div>
        <div id="mod_event" class="none">
        </div>
        <div id="event_add" class="none">
          <h2>Add Event on <span id="eventDateView"></span></h2>
          <div class="form-group">
            <label for="eventTitle" class="form-label">Event Title (<span id="current">0</span><span id="maximum">/ 50</span>):</label> <span id="titleError"></span>
            <input class="form-field" type="text" id="eventTitle" value="" maxlength="50" required />
          </div>
          <input type="hidden" id="eventDate" value=""/>
          <div class="form-group">
          <label for="eventDescription" class="form-label">Event Description (<span id="currentDesc">0</span><span id="maximumDesc">/ 200</span>): </label>
            <input class="form-field" type="text" id="eventDescription" value="" maxlength="200" />
          </div>
          <div class="form-group">
            <label class="form-label">Privacy Setting:</label>
            <div class="form-radio-block" id="privacy">
              <input type="radio" class="eventPrivacy" id="privateCheck" name="eventPrivacy" value=1 checked>
              <label for="privateCheck">Private</label>
              <br />
              <input type="radio" class="eventPrivacy" id="publicCheck" name="eventPrivacy" value=0>
              <label for="publicCheck">Public</label>
            </div>
          </div>
          <div class="form-group">
          <label for="sharedWith" class="form-label">Share With Username(s):</label>
          <input class="form-field" type="text" id="sharedWith" value="" placeholder="Ex: name1,name2,name3" />
          </div>
          <div class="form-group">
          <label for="deleteAuth" class="form-label">Authorized Deleter(s): </label>
          <input class="form-field" type="text" id="deleteAuth" value="" placeholder="Ex: name1,name2,name3" />
          </div>
          <div class="form-group">
            <label for="category" class="form-label">Category: </label> <span id="categoryError"></span>
            <select class="form-field form-field-select" name="category" id="category" required>
            <option value="" disabled selected>Select a category</option>
              <option value="Life">Life/Personal</option>
              <option value="Finance">Financial</option>
              <option value="Work">Work</option>
              <option value="Medical">Medical</option>
              <option value="House">Housework</option>
              <option value="Miscellaneous">Miscellaneous</option>
            </select>
          </div>
          <button class="btn btn-secondary add_btn" id="addEventBtn" value="Add">Add</button>
          <!--<input type="button" id="addEventBtn" value="Add"/>-->
          <button class="btn btn-secondary cancel_btn" id="cancelAddEventBtn" value="Cancel">Cancel</button>
          <!--<input type="button" id="cancelAddEventBtn" value="Cancel"/>-->
        </div>
        <div class="calendar-days">
          <ul class="calendar-day-list">
            <li class="calendar-day"><span>SUNDAY</span></li>
            <li class="calendar-day"><span>MONDAY</span></li>
            <li class="calendar-day"><span>TUESDAY</span></li>
            <li class="calendar-day"><span>WEDNESDAY</span></li>
            <li class="calendar-day"><span>THURSDAY</span></li>
            <li class="calendar-day"><span>FRIDAY</span></li>
            <li class="calendar-day"><span>SATURDAY</span></li>
          </ul>
        </div>
        <div id="calendar-dates" class="calendar-dates">
          <ul id="calendar-list" class="calendar-list">
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
      GLOBAL $userId;

      // Include the database config file
      include_once 'dbConfig.php';

      // Get number of events based on the current date
      $result = $con->prepare("SELECT title FROM events WHERE (date = :cd1 AND status = 1 AND user = :un) OR (date = :cd2 AND status = 1 AND privacy = 0) OR (date = :cd3 AND status = 1 AND sharedWith LIKE :sw)");
      $sharedWith = "% ".$userId." %";
      $result->execute(
        array(
          ":cd1" => $currentDate, 
          ":cd2" => $currentDate, 
          ":cd3" => $currentDate, 
          ":un" => $userId,
          ":sw" => $sharedWith
        )
      );
      $eventNum = $result->rowCount();
      $result = null;

      // Define date cell color
      if (strtotime($currentDate) === strtotime(date("Y-m-d"))) {
        $thisDay = "this_day ";
      } else {
        $thisDay = "";
      }
      if (strtotime($currentDate) < strtotime(date("Y-m-d"))) {
        $pastDay = " past_day";
      } else {
        $pastDay = "";
      }
      $eventClass = "";
      if ($eventNum === 1) {
        $eventClass = 'one_event ';
      } elseif ($eventNum === 2) {
        $eventClass = 'two_event ';
      } elseif ($eventNum === 3) {
        $eventClass = 'three_event ';
      } elseif ($eventNum === 4) {
        $eventClass = 'four_event ';
      } elseif ($eventNum === 5) {
        $eventClass = 'five_event ';
      } elseif ($eventNum > 5) {
        $eventClass = 'multi_event ';
      }
      echo '            <li data-date="'.$currentDate.'" class="calendar-list-item '.$thisDay.$eventClass.'date_cell'.$pastDay.$expandBlocks.'">';
                
      // Date cell
      echo '<span class="dayBlock">';
      echo '<span class="eventView" onclick="getEvents(\''.$currentDate.'\')">'.$dayCount.'</span>';
      echo '<span class="dayNumber" onclick="addEvent(\''.$currentDate.'\');"></span>';
      if($boxDisplay === 35 && $eventNum >= 6) {
        $events = $con->prepare("SELECT id, title, category FROM events WHERE (date = :cd1 AND status = 1 AND user = :un) OR (date = :cd2 AND status = 1 AND privacy = 0) OR (date = :cd3 AND status = 1 AND sharedWith LIKE :sw) ORDER BY FIELD(category, 'Finance', 'Work', 'Medical', 'Miscellaneous', 'Life', 'House'), title LIMIT 4");
      $sharedWith = "% ".$userId." %";
      $events->execute(
        array(
          ":cd1" => $currentDate, 
          ":cd2" => $currentDate, 
          ":cd3" => $currentDate, 
          ":un" => $userId,
          ":sw" => $sharedWith
        )
      );
    } elseif ($boxDisplay === 42 && $eventNum >= 5) {
      $events = $con->prepare("SELECT id, title, category FROM events WHERE (date = :cd1 AND status = 1 AND user = :un) OR (date = :cd2 AND status = 1 AND privacy = 0) OR (date = :cd3 AND status = 1 AND sharedWith LIKE :sw) ORDER BY FIELD(category, 'Finance', 'Work', 'Medical', 'Miscellaneous', 'Life', 'House'), title LIMIT 3");
      $sharedWith = "% ".$userId." %";
      $events->execute(
        array(
          ":cd1" => $currentDate, 
          ":cd2" => $currentDate, 
          ":cd3" => $currentDate, 
          ":un" => $userId,
          ":sw" => $sharedWith
        )
      );
    } else {
      $events = $con->prepare("SELECT id, title, category FROM events WHERE (date = :cd1 AND status = 1 AND user = :un) OR (date = :cd2 AND status = 1 AND privacy = 0) OR (date = :cd3 AND status = 1 AND sharedWith LIKE :sw) ORDER BY FIELD(category, 'Finance', 'Work', 'Medical', 'Miscellaneous', 'Life', 'House'), title");
      $sharedWith = "% ".$userId." %";
      $events->execute(
        array(
          ":cd1" => $currentDate, 
          ":cd2" => $currentDate, 
          ":cd3" => $currentDate, 
          ":un" => $userId,
          ":sw" => $sharedWith
        )
      );
    }
    $eventsHTML = '';
      if($events->rowCount() > 0){
        $eventsHTML .= '<span class="event">';
        while($row = $events->fetch()) {
          $eventId = $row['id'];
          $category = $row['category'];
          if ($category === 'Life') {
            $catStyle = 'category-life';
          } else if ($category === 'Finance') {
            $catStyle = 'category-finance';
          } else if ($category === 'Work') {
            $catStyle = 'category-work';
          } else if ($category === 'Medical') {
            $catStyle = 'category-medical';
          } else if ($category === 'House') {
            $catStyle = 'category-house';
          } else if ($category === 'Miscellaneous') {
            $catStyle = 'category-misc';
          } else {
            $catStyle = '';
          }
          $eventTitle = $row['title'];
          $eventsHTML .= '<p id="'.$eventId.'" class="events '.$catStyle.'" onclick="getEvent(\''.$currentDate.'\', \''.$eventId.'\');">'.$eventTitle.'</p>';
        }
        if($boxDisplay === 35 && $eventNum > 5) {
          $difference = $eventNum - 4;
          $eventsHTML .= '<p class="category-difference" onclick="getEvents(\''.$currentDate.'\');">+ '.$difference.' more</p>';
        }
        if($boxDisplay === 42 && $eventNum > 4) {
          $difference = $eventNum - 3;
          $eventsHTML .= '<p class="category-difference" onclick="getEvents(\''.$currentDate.'\');">+ '.$difference.' more</p>';
        }
        $eventsHTML .= '</span>';
      }
      echo $eventsHTML;
      echo '</span>';

      // Hover event popup
      if($eventNum > 0 || (strtotime($currentDate) >= strtotime(date("Y-m-d")))){
        echo '<div id="date_popup_'.$currentDate.'" class="date_popup_wrap none">';
        echo '<div class="date_window">';
        echo ($eventNum > 0)?'<div class="popup_event">Events ('.$eventNum.')</div>':'';
        echo ($eventNum > 0)?'<a href="javascript:;" onclick="getEvents(\''.$currentDate.'\');">view events</a>':'';
        echo (strtotime($currentDate) >= strtotime(date("Y-m-d")))?'<a href="javascript:void(0);" onclick="addEvent(\''.$currentDate.'\');"><br />add event</a>':'';
        echo '</div></div>';
      }

      echo '</li>'."\r\n";
      $dayCount++;
    }else{
      echo '            <li class="calendar-list-item no_date'.$expandBlocks.'">&nbsp;</li>'."\r\n";
    }
  }
  $month01 = $month02 = $month03 = $month04 = $month05 = $month06 = $month07 = $month08 = $month09 = $month10 = $month11 = $month12 = "";
  include_once 'dbConfig.php';
  $images = $con->prepare("SELECT * FROM users WHERE id = :user");
  $images->execute(
    array(
      ":user" => $userId
    )
  );
  while($row = $images->fetch()) {
    $month01 = $row["jan"];
    $month02 = $row["feb"];
    $month03 = $row["mar"];
    $month04 = $row["apr"];
    $month05 = $row["may"];
    $month06 = $row["jun"];
    $month07 = $row["jul"];
    $month08 = $row["aug"];
    $month09 = $row["sep"];
    $month10 = $row["oct"];
    $month11 = $row["nov"];
    $month12 = $row["dec"];
  }
  $images = null;
?>
          </ul>
        </div>
      <script>
        var url = "./js/calendar.js";
        $.getScript(url);
      </script>
      <script>
        // Background images
        var imageAddress = '';
        var imageArray = ['<?php echo $month01 ?>', '<?php echo $month02 ?>', '<?php echo $month03 ?>', '<?php echo $month04 ?>', '<?php echo $month05 ?>', '<?php echo $month06 ?>', '<?php echo $month07 ?>', '<?php echo $month08 ?>', '<?php echo $month09 ?>', '<?php echo $month10 ?>', '<?php echo $month11 ?>', '<?php echo $month12 ?>'];
        for (let i = 0; i < 12; i++) {
          if (imageArray[i].split('-')[0]) {
            if (parseInt($('.month_dropdown').val()) === i + 1) {
              imageAddress = imageArray[i].split('-')[0] + "-" + (i + 1).toString().padStart(2,'0') + ".png";
            }
          }
        }
        if (imageAddress) {
        document.getElementsByClassName("content-wrap")[0].style.backgroundImage = "url('images/" + imageAddress + "')";
      } else {
          document.getElementsByClassName("content-wrap")[0].style.backgroundImage = "none";
        }
      </script>
      <script>
        var url = "./js/holidays.js";
        $.getScript(url);
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
  for($i=2019;$i<=2044;$i++)
  {
      $selectedOpt = ($i == $selected)?'selected':'';
      $options .= '<option value="'.$i.'" '.$selectedOpt.' >'.$i.'</option>';
  }
  return $options;
}

/*
 * Generate events list in HTML format
 */
function getEvents($date = '', $userId){
  // Include the database config file
  include_once 'dbConfig.php';

  $eventListHTML = '';
  $date = $date?$date:date("Y-m-d");

  // Fetch events based on the specific date
  $result = $con->prepare("SELECT * FROM events WHERE (date = :date1 AND status = 1 AND user = :un) OR (date = :date2 AND status = 1 AND privacy = 0) OR (date = :date3 AND status = 1 AND sharedWith LIKE :sw) ORDER BY FIELD(category, 'Finance', 'Work', 'Medical', 'Miscellaneous', 'Life', 'House'), title");
  $sharedWith = "% ".$userId." %";
  $result->execute(
    array(
      ":date1" => $date,
      ":date2" => $date,
      ":date3" => $date,
      ":un" => $userId,
      ":sw" => $sharedWith
    )
  );
  if($result->rowCount() > 0){
    $eventListHTML = '<h2>Events on '.date("l, F d, Y",strtotime($date)).'</h2>';
    $eventListHTML .= '<table class="eventList">';
    $eventListHTML .= '<tr class="eventRow">';
    $eventListHTML .= '<th class="eventItem">Title</th>';
    $eventListHTML .= '<th class="eventItem">Description</th>';
    $eventListHTML .= '<th class="eventItem">Creator</th>';
    $eventListHTML .= '<th class="eventItem">Privacy</th>';
    $eventListHTML .= '<th class="eventItem">Shared With</th>';
    $eventListHTML .= '<th class="eventItem">Category</th>';
    $eventListHTML .= '<th class="eventItem"></th>';
    $eventListHTML .= '</tr>';
    while($row = $result->fetch()) {
      $eventListHTML .= '<tr class="eventRow">';
      $id = $row['id'];
      $user = $row['user'];
      $eventTitle = $row['title'];
      $eventDescription = $row['description'];
      $privacy = $row['privacy'];
      $eventShared = $row['sharedWith'];
      $deleteAuth = $row['deleteAuth'];
      $category = $row['category'];
      $eventSharedArray = explode(" , ", $eventShared);
      $eventSharedUsernameArray = array();
			$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
      foreach($eventSharedArray as $eventSharedUserId) {
        $query = $con->prepare("SELECT username FROM users WHERE id = :id");
        $query->execute(
          array(
            ":id" => $eventSharedUserId
          )
        );
        if($query->rowCount() > 0){
          while($row = $query->fetch()) {
            $userShare = $row['username'];
						if ($userShare == $username) {
							$userShare = "you";
						}
            array_push($eventSharedUsernameArray, $userShare);
          }
        }
        $query = null;
      }
      $newSharedGroup = implode(", ", $eventSharedUsernameArray);
      if ($user == $userId) {
        $creatorName = "You";
      } else {
        $creator = $con->prepare("SELECT username FROM users WHERE id = :id");
        $creator->execute(
          array(
            ":id" => $user
          )
        );
        if($creator->rowCount() > 0) {
          while($row = $creator->fetch()) {
            $creatorName = $row['username'];
          }
        }
        $creator = null;
      }
      if ($privacy == '1') {
        $eventPrivacy = '<span style="color:red;">private</span>';
      } else {
        $eventPrivacy = '<span style="color:green;">public</span>';
      }
      $authConfirm = ' '.$userId.' ';
      if ($user != $userId && strpos($deleteAuth, $authConfirm) === false) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventListHTML .= '<td class="eventItem"></td>';
        $eventListHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventListHTML .= '<td class="eventItem"></td>';
      } else if ($user != $userId && strpos($deleteAuth, $authConfirm) !== false) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventListHTML .= '<td class="eventItem"></td>';
        $eventListHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventListHTML .= '<td class="eventItem righted"><button class="btn btn-secondary delEventBtn" id="'.$id.'" onclick="deleteEvent('.$id.')">Delete</button></td>';
      } else {
        $eventListHTML .= '<td class="eventItem" id="'.$id.'">'.$eventTitle.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$newSharedGroup.'</td>';
        $eventListHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventListHTML .= '<td class="eventItem righted"><button class="btn btn-secondary modEventBtn" id="'.$id.'" onclick="modEvent('.$id.')">Modify</button><button class="btn btn-secondary delEventBtn" id="'.$id.'" onclick="deleteEvent('.$id.')">Delete</button></td>';
      }
      $eventListHTML .= '</tr>';
    }
    $eventListHTML .= '</table>';
    $eventListHTML .= '<script src="./js/calendar.js"></script>';
  }
  echo $eventListHTML;
  $result = null;
}

/*
 * Get a single event on click
 */
function getEvent($date = '', $eventId, $userId) {
  include_once 'dbConfig.php';
  $eventHTML = '';
  $date = $date?$date:date("Y-m-d");
  $oneEvent = $con->prepare("SELECT * FROM events WHERE id = :id");
  $oneEvent->execute(
    array(
      ":id" => $eventId,
    )
  );
  if($oneEvent->rowCount() > 0){
    $eventHTML = '<h2>Event '.$eventId.' on '.date("l, F d, Y",strtotime($date)).'</h2>';
    $eventHTML .= '<table class="eventList">';
    $eventHTML .= '<tr class="eventRow">';
    $eventHTML .= '<th class="eventItem">Title</th>';
    $eventHTML .= '<th class="eventItem">Description</th>';
    $eventHTML .= '<th class="eventItem">Creator</th>';
    $eventHTML .= '<th class="eventItem">Privacy</th>';
    $eventHTML .= '<th class="eventItem">Shared With</th>';
    $eventHTML .= '<th class="eventItem">Category</th>';
    $eventHTML .= '<th class="eventItem"></th>';
    $eventHTML .= '</tr>';
    while($row = $oneEvent->fetch()) {
      $eventHTML .= '<tr class="eventRow">';
      $id = $row['id'];
      $user = $row['user'];
      $eventTitle = $row['title'];
      $eventDescription = $row['description'];
      $privacy = $row['privacy'];
      $eventShared = $row['sharedWith'];
      $deleteAuth = $row['deleteAuth'];
      $category = $row['category'];
      $eventSharedArray = explode(" , ", $eventShared);
      $eventSharedUsernameArray = array();
			$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
      foreach($eventSharedArray as $eventSharedUserId) {
        $query = $con->prepare("SELECT username FROM users WHERE id = :id");
        $query->execute(
          array(
            ":id" => $eventSharedUserId
          )
        );
        if($query->rowCount() > 0){
          while($row = $query->fetch()) {
            $userShare = $row['username'];
						if ($userShare == $username) {
							$userShare = "you";
						}
            array_push($eventSharedUsernameArray, $userShare);
          }
        }
        $query = null;
      }
      $newSharedGroup = implode(", ", $eventSharedUsernameArray);
      if ($user == $userId) {
        $creatorName = "You";
      } else {
        $creator = $con->prepare("SELECT username FROM users WHERE id = :id");
        $creator->execute(
          array(
            ":id" => $user
          )
        );
        if($creator->rowCount() > 0) {
          while($row = $creator->fetch()) {
            $creatorName = $row['username'];
          }
        }
        $creator = null;
      }
      if ($privacy == '1') {
        $eventPrivacy = '<span style="color:red;">private</span>';
      } else {
        $eventPrivacy = '<span style="color:green;">public</span>';
      }
      $authConfirm = ' '.$userId.' ';
      if ($user != $userId && strpos($deleteAuth, $authConfirm) === false) {
        $eventHTML .= '<td class="eventItem">'.$eventTitle.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventHTML .= '<td class="eventItem"></td>';
        $eventHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventHTML .= '<td class="eventItem"></td>';
      } else if ($user != $userId && strpos($deleteAuth, $authConfirm) !== false) {
        $eventHTML .= '<td class="eventItem">'.$eventTitle.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventHTML .= '<td class="eventItem"></td>';
        $eventHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventHTML .= '<td class="eventItem righted"><button class="btn btn-secondary delEventBtn" id="'.$id.'" onclick="deleteEvent('.$id.')">Delete</button></td>';
      } else {
        $eventHTML .= '<td class="eventItem" id="'.$id.'">'.$eventTitle.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventDescription.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$creatorName.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$eventPrivacy.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$newSharedGroup.'</td>';
        $eventHTML .= '<td class="eventItem centered">'.$category.'</td>';
        $eventHTML .= '<td class="eventItem righted"><button class="btn btn-secondary modEventBtn" id="'.$id.'" onclick="modEvent('.$id.')">Modify</button><button class="btn btn-secondary delEventBtn" id="'.$id.'" onclick="deleteEvent('.$id.')">Delete</button></td>';
      }
      $eventHTML .= '</tr>';
    }
    $eventHTML .= '</table>';
    $eventHTML .= '<script src="./js/calendar.js"></script>';
  }
  echo $eventHTML;
  $oneEvent = null;

}

/*
 * Modify event
 */
function modEvent($eventId, $userId){
  include_once 'dbConfig.php';
  $modEventHTML = '';
  $modEvent = $con->prepare("SELECT * FROM events WHERE id = :id");
  $modEvent->execute(
    array(
      ":id" => $eventId,
    )
  );
  if($modEvent->rowCount() > 0){
    while($row = $modEvent->fetch()) {
      $id = $row['id'];
      $user = $row['user'];
      $eventDate = $row['date'];
      $eventTitle = $row['title'];
      $eventDescription = $row['description'];
      $eventDescription = str_replace('"', '&quot;', $eventDescription);
      $privacy = $row['privacy'];
      $eventShared = $row['sharedWith'];
      $deleteAuth = $row['deleteAuth'];
      $category = $row['category'];
      $eventSharedArray = explode(" , ", $eventShared);
      $eventSharedUsernameArray = array();
      foreach($eventSharedArray as $eventSharedUserId) {
        $query = $con->prepare("SELECT username FROM users WHERE id = :id");
        $query->execute(
          array(
            ":id" => $eventSharedUserId
          )
        );
        if($query->rowCount() > 0){
          while($row = $query->fetch()) {
            $userShare = $row['username'];
            array_push($eventSharedUsernameArray, $userShare);
          }
        }
        $query = null;
      }
      $newSharedGroup = implode(",", $eventSharedUsernameArray);
      $deleteAuthArray = explode(" , ", $deleteAuth);
      $deleteAuthUsernameArray = array();
      foreach($deleteAuthArray as $deleteAuthUserId) {
        $query = $con->prepare("SELECT username FROM users WHERE id = :id");
        $query->execute(
          array(
            ":id" => $deleteAuthUserId
          )
        );
        if($query->rowCount() > 0){
          while($row = $query->fetch()) {
            $userAuth = $row['username'];
            array_push($deleteAuthUsernameArray, $userAuth);
          }
        }
        $query = null;
      }
      $newDeleteGroup = implode(",", $deleteAuthUsernameArray);
      $authConfirm = ' '.$userId.' ';
      ?>
      <h2>Modify Event <?php echo $eventId; ?></h2>
      <div class="form-group">
        <input type="hidden" id="eventId" value="<?php echo $eventId; ?>" />
        <label for="eventDate" class="form-label">Event Date</label>
        <input class="form-field" type="date" id="eventDate" value="<?php echo $eventDate; ?>" />
      </div>
      <div class="form-group">
        <label for="eventTitle" class="form-label">Event Title (<span id="current"><?php echo ($eventTitle) ? strlen($eventTitle) : 0; ?></span><span id="maximum">/ 50</span>):</label> <span id="titleError"></span>
        <input class="form-field" type="text" id="eventTitle" value="<?php echo $eventTitle; ?>" maxlength="50" required />
      </div>
      <div class="form-group">
        <label for="eventDescription" class="form-label">Event Description (<span id="currentDesc"><?php echo strlen($eventDescription); ?></span><span id="maximumDesc">/ 200</span>): </label>
        <input class="form-field" type="text" id="eventDescription" value="<?php echo $eventDescription; ?>" maxlength="200" />
      </div>
      <div class="form-group">
        <label class="form-label">Privacy Setting:</label>
        <div class="form-radio-block" id="privacy">
          <input type="radio" class="eventPrivacy" id="privateCheck" name="eventPrivacy" value=1 <?php echo ($privacy == 1) ? "checked" : "" ?> />
          <label for="privateCheck">Private</label>
          <br />
          <input type="radio" class="eventPrivacy" id="publicCheck" name="eventPrivacy" value=0 <?php echo ($privacy == 0) ? "checked" : "" ?> />
          <label for="publicCheck">Public</label>
        </div>
      </div>
      <div class="form-group">
        <label for="sharedWith" class="form-label">Share With Username(s):</label>
        <input class="form-field" type="text" id="sharedWith" value="<?php echo $newSharedGroup; ?>" placeholder="Ex: name1,name2,name3" />
      </div>
      <div class="form-group">
        <label for="deleteAuth" class="form-label">Authorized Deleter(s): </label>
        <input class="form-field" type="text" id="deleteAuth" value="<?php echo $newDeleteGroup; ?>" placeholder="Ex: name1,name2,name3" />
      </div>
      <div class="form-group">
        <label for="category" class="form-label">Category: </label> <span id="categoryError"></span>
        <select class="form-field form-field-select" name="category" id="category" required>
          <option value="" disabled selected>Select a category</option>
          <option value="Life" <?php echo ($category === "Life") ? "selected" : ""; ?>>Life/Personal</option>
          <option value="Finance" <?php echo ($category === "Finance") ? "selected" : ""; ?>>Financial</option>
          <option value="Work" <?php echo ($category === "Work") ? "selected" : ""; ?>>Work</option>
          <option value="Medical" <?php echo ($category === "Medical") ? "selected" : ""; ?>>Medical</option>
          <option value="House" <?php echo ($category === "House") ? "selected" : ""; ?>>Housework</option>
          <option value="Miscellaneous" <?php echo ($category === "Miscellaneous") ? "selected" : ""; ?>>Miscellaneous</option>
        </select>
      </div>
      <button class="btn btn-secondary saveModEventBtn" id="'.$id.'">Update</button>
      <button class="btn btn-secondary cancel_btn" id="cancelModEventBtn" value="Cancel">Cancel</button>
      <script>
        var url = "./js/calendar.js";
        $.getScript(url);
      </script>

    <?php
    }
  }
  echo $modEventHTML;
  $modEvent = null;
}


/*
 * Add event to date
 */
function addEvent($date,$title,$description,$privacy,$sharedWith,$deleteAuth,$category,$userId){
  //Include db configuration file
  include 'dbConfig.php';
  $currentDate = date("Y-m-d H:i:s");
  $sharedUsernameArray = explode(",", $sharedWith);
  $sharedIdArray = array();
  foreach($sharedUsernameArray as $sharedUsername) {
    $query = $con->prepare("SELECT id FROM users WHERE username = :sw");
    $query->execute(
      array(
        ":sw" => $sharedUsername
      )
    );
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $id = $row['id'];
        array_push($sharedIdArray,$id);
      }
    }
    $query = null;
  }
  $sharedWith = implode(" , ", $sharedIdArray);
  $deleteUsernameArray = explode(",", $deleteAuth);
  $deleteIdArray = array();
  foreach($deleteUsernameArray as $deleteUsername) {
    $query = $con->prepare("SELECT id FROM users WHERE username = :du");
    $query->execute(
      array(
        ":du" => $deleteUsername
      )
    );
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $id = $row['id'];
        array_push($deleteIdArray,$id);
      }
    }
    $query = null;
  }
  $deleteAuth = implode(" , ", $deleteIdArray);
  if(isset($_POST['addEventBtn'])) {
    $description = strip_tags($_POST['description']);
    $privacy = intval($_POST['eventPrivacy']);
    $sharedWith = strip_tags($_POST['sharedWith']);
    $deleteAuth = strip_tags($_POST['deleteAuth']);
    $category = strip_tags($_POST['category']);
  }
  //Insert the event data into database
  $insert = $con->prepare("INSERT INTO events (title,date,created,modified,user,description,privacy,sharedWith,deleteAuth,category) VALUES (:title,:date,:currentDate,:modifiedDate,:un,:description,:ep,:sw,:da,:cat)");
  $sharedWith = ' '. $sharedWith .' ';
  $deleteAuth = ' '. $deleteAuth .' ';
  $insert->execute(
    array(
      ":title" => $title,
      ":date" => $date,
      ":currentDate" => $currentDate,
      ":modifiedDate" => $currentDate,
      ":un" => $userId,
      ":description" => $description,
      ":ep" => $privacy,
      ":sw" => $sharedWith,
      ":da" => $deleteAuth,
      ":cat" => $category
    )
  );
  if($insert){
    echo 'ok';
  }else{
    echo 'err';
  }
  $insert = null;
}

/*
 * Submit updated event
 */
function updateEvent($date,$title,$description,$privacy,$sharedWith,$deleteAuth,$category,$eventId){
  //Include db configuration file
  include 'dbConfig.php';
  $currentDate = date("Y-m-d H:i:s");
  $sharedUsernameArray = explode(",", $sharedWith);
  $sharedIdArray = array();
  foreach($sharedUsernameArray as $sharedUsername) {
    $query = $con->prepare("SELECT id FROM users WHERE username = :sw");
    $query->execute(
      array(
        ":sw" => $sharedUsername
      )
    );
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $id = $row['id'];
        array_push($sharedIdArray,$id);
      }
    }
    $query = null;
  }
  $sharedWith = implode(" , ", $sharedIdArray);
  $deleteUsernameArray = explode(",", $deleteAuth);
  $deleteIdArray = array();
  foreach($deleteUsernameArray as $deleteUsername) {
    $query = $con->prepare("SELECT id FROM users WHERE username = :du");
    $query->execute(
      array(
        ":du" => $deleteUsername
      )
    );
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $id = $row['id'];
        array_push($deleteIdArray,$id);
      }
    }
    $query = null;
  }
  $deleteAuth = implode(" , ", $deleteIdArray);
  if(isset($_POST['saveModEventBtn'])) {
    $description = strip_tags($_POST['description']);
    $privacy = intval($_POST['eventPrivacy']);
    $sharedWith = strip_tags($_POST['sharedWith']);
    $deleteAuth = strip_tags($_POST['deleteAuth']);
    $category = strip_tags($_POST['category']);
    $eventId = ($_POST['eventId']);
  }
  //Insert the event data into database
  $update = $con->prepare("UPDATE events
                            SET
                              title = :title,
                              date = :date,
                              modified = current_timestamp,
                              description = :description,
                              privacy = :ep,
                              sharedWith = :sw,
                              deleteAuth = :da,
                              category = :cat
                            WHERE
                              id = :id");
  $sharedWith = ' '. $sharedWith .' ';
  $deleteAuth = ' '. $deleteAuth .' ';
  $update->execute(
    array(
      ":title" => $title,
      ":date" => $date,
      ":description" => $description,
      ":ep" => $privacy,
      ":sw" => $sharedWith,
      ":da" => $deleteAuth,
      ":cat" => $category,
      ":id" => $eventId
    )
  );
  if($update){
    echo 'ok';
  }else{
    echo 'err';
  }
  $update = null;
}

/*
 * Delete event
 */
function deleteEvent($id){
  include 'dbConfig.php';

  $query = $con->prepare("DELETE FROM events WHERE id = :id");
  $query->bindParam(':id', $id);
  $query->execute();
}
?>