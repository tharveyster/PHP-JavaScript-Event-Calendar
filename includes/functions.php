<?php
/* The basic calendar code came from https://www.codexworld.com/build-event-calendar-using-jquery-ajax-php/php-event-calendar-jquery-ajax-mysql-codexworld/. That code consisted of displaying the calendar, and adding and viewing events. The functionality has been improved to allow cancellation of new events in the "Add event" section before submitting them, setting event privacy to public or private, sharing private events with other users via their usernames, deletion of events by original submitter (and approved users) only, and visible holidays. There's also a new settings section where users can change their name and email address, and add background images to each month. */

$userId = "";

ini_set('session.gc_maxlifetime', 1200);
session_start();
date_default_timezone_set('America/New_York');

if(!isset($_SESSION['userId']) && !isset($_SESSION['username'])) {
  header('Location: signIn.php');
}else{
  $userId = htmlspecialchars(strip_tags($_SESSION['userId']), ENT_QUOTES);
}

if(isset($_POST['func']) && !empty($_POST['func'])){
  switch($_POST['func']){
    case 'getCalendar':
      getCalendar($_POST['year'],$_POST['month']);
      break;
    case 'getEvents':
      getEvents($_POST['date'],$userId);
      break;
    case 'addEvent':
      addEvent($_POST['date'],$_POST['title'],$_POST['description'],$_POST['privacy'],$_POST['sharedWith'],$_POST['deleteAuth'],$userId);
      break;
    case 'delEvent':
      delEvent($_POST['id']);
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
        <div class="cal-nav">
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y", strtotime($date.' - 1 Month')); ?>','<?php echo date("m",strtotime($date.' - 1 Month')); ?>');">&#10092;&#10092;&#10092;</a>
          <select class="month_dropdown"><?php echo getAllMonths($dateMonth); ?></select>
          <select class="year_dropdown"><?php echo getYearList($dateYear); ?></select>
          <button class="btn btn-secondary today_btn" onclick="window.location.reload(true);">Today</button>
          <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y", strtotime($date.' + 1 Month')); ?>','<?php echo date("m",strtotime($date.' + 1 Month')); ?>');">&#10093;&#10093;&#10093;</a>
        </div>
        <div id="event_list" class="none">
        </div>
        <div id="event_add" class="none">
          <h2>Add Event on <span id="eventDateView"></span></h2>
          <div class="form-group">
            <label for="eventTitle" class="form-label">Event Title (<span id="current">0</span><span id="maximum">/ 50</span>):</label>
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
          <button class="btn btn-secondary add_btn" id="addEventBtn" value="Add">Add</button>
          <!--<input type="button" id="addEventBtn" value="Add"/>-->
          <button class="btn btn-secondary cancel_btn" id="cancelAddEventBtn" value="Cancel">Cancel</button>
          <!--<input type="button" id="cancelAddEventBtn" value="Cancel"/>-->
        </div>
        <div class="calendar-days">
          <ul>
            <li><span>SUNDAY</span></li>
            <li><span>MONDAY</span></li>
            <li><span>TUESDAY</span></li>
            <li><span>WEDNESDAY</span></li>
            <li><span>THURSDAY</span></li>
            <li><span>FRIDAY</span></li>
            <li><span>SATURDAY</span></li>
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
      GLOBAL $userId;

      // Include the database config file
      include_once 'dbConfig.php';

      // Get number of events based on the current date
      $result = $con->prepare("SELECT title FROM events WHERE (date = :cd1 AND status = 1 AND user = :un) OR (date = :cd2 AND status = 1 AND privacy = 0) OR (date = :cd3 AND status = 1 AND sharedWith LIKE :sw)");
      $sharedWith = "% ".$userId." %";
      $result->bindParam(":cd1", $currentDate);
      $result->bindParam(":cd2", $currentDate);
      $result->bindParam(":cd3", $currentDate);
      $result->bindParam(":un", $userId);
      $result->bindParam(":sw", $sharedWith);
      $result->execute();
      $eventNum = $result->rowCount();
      $result = null;

      // Define date cell color
      $currentDateRes = strtotime($currentDate);
      $dateRes = strtotime(date("Y-m-d"));
      $listClasses = 'date_cell'.$expandBlocks;
      switch($eventNum) {
        case 1:
          $listClasses = 'has_event '.$listClasses;
          break;
        case 2:
          $listClasses = 'has_2_multi '.$listClasses;
          break;
        case 3:
          $listClasses = 'has_3_multi '.$listClasses;
          break;
        case 4:
          $listClasses = 'has_4_multi '.$listClasses;
          break;
        case 5:
          $listClasses = 'has_5_multi '.$listClasses;
          break;
        case 6-500:
          $listClasses = 'has_multi '.$listClasses;
          break;
      }
      $listItem = '<li data-date="'.$currentDate.'" class="'.$listClasses.'">';
      if($eventNum === 0 && $currentDateRes < $dateRes){
        $listItem = str_replace('class="date_cell', 'class="date_cell past_day', $listItem);
      }
      if($currentDateRes === $dateRes){
        $listItem = str_replace('class="', 'class="this_day ', $listItem);
      }

      echo $listItem;
                
      // Date cell
      echo '<span>';
      echo $dayCount;
      echo '</span>';

      // Hover event popup
      if($eventNum > 0 || $currentDateRes >= $dateRes){
        echo '<div id="date_popup_'.$currentDate.'" class="date_popup_wrap none">';
        echo '<div class="date_window">';
        echo '<div class="popup_event">Events ('.$eventNum.')</div>';
        echo ($eventNum > 0)?'<a href="javascript:;" onclick="getEvents(\''.$currentDate.'\');">view events</a>':'';
        echo '<a href="javascript:void(0);" onclick="addEvent(\''.$currentDate.'\');"><br />add event</a>';
        echo '</div></div>';
      }

      echo '</li>'."\r\n";
      $dayCount++;
    }else{
      echo '            <li class="no_date'.$expandBlocks.'">&nbsp;</li>'."\r\n";
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
  include_once 'dbConfig.php';
  $images = $con->prepare("SELECT * FROM users WHERE id = :user");
  $images->bindParam(":user", $userId);
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
        var imageAddress;
        var monthDropdownValue = parseInt($('.month_dropdown').val());
        switch(monthDropdownValue){
          case 1:
            imageAddress = "<?php echo $month01; ?>";
            break;
          case 2:
            imageAddress = "<?php echo $month02; ?>";
            break;
          case 3:
            imageAddress = "<?php echo $month03; ?>";
            break;
          case 4:
            imageAddress = "<?php echo $month04; ?>";
            break;
          case 5:
            imageAddress = "<?php echo $month05; ?>";
            break;
          case 6:
            imageAddress = "<?php echo $month06; ?>";
            break;
          case 7:
            imageAddress = "<?php echo $month07; ?>";
            break;
          case 8:
            imageAddress = "<?php echo $month08; ?>";
            break;
          case 9:
            imageAddress = "<?php echo $month09; ?>";
            break;
          case 10:
            imageAddress = "<?php echo $month10; ?>";
            break;
          case 11:
            imageAddress = "<?php echo $month11; ?>";
            break;
          default:
            imageAddress = "<?php echo $month12; ?>";
            break;
        }
        document.getElementsByClassName("content-wrap")[0].style.backgroundImage = "url('images/" + imageAddress + "')";
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
  $result = $con->prepare("SELECT * FROM events WHERE (date = :date1 AND status = 1 AND user = :un) OR (date = :date2 AND status = 1 AND privacy = 0) OR (date = :date3 AND status = 1 AND sharedWith LIKE :sw) ORDER BY title");
  $sharedWith = "% ".$userId." %";
  $result->bindParam(":date1", $date);
  $result->bindParam(":date2", $date);
  $result->bindParam(":date3", $date);
  $result->bindParam(":un", $userId);
  $result->bindParam(":sw", $sharedWith);
  $result->execute();
  if($result->rowCount() > 0){
    $eventListHTML = '<h2>Events on '.date("l, M d, Y",strtotime($date)).'</h2>';
    $eventListHTML .= '<table class="eventList">';
    $eventListHTML .= '<tr>';
    $eventListHTML .= '<th class="eventItem">Title</th>';
    $eventListHTML .= '<th class="eventItem">Description</th><th class="eventItem">Creator</th><th class="eventItem">Privacy</th><th class="eventItem">Shared With</th><th class="eventItem"></th>';
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $eventListHTML .= '<tr>';
      $id = $row['id'];
      $user = $row['user'];
      $eventTitle = $row['title'];
      $eventDescription = $row['description'];
      $privacy = $row['privacy'];
      $eventShared = $row['sharedWith'];
      $deleteAuth = $row['deleteAuth'];
      $eventSharedArray = explode(" , ", $eventShared);
      $eventSharedUsernameArray = array();
      foreach($eventSharedArray as $eventSharedUserId) {
        $query = $con->prepare("SELECT username FROM users WHERE id = :id");
        $query->bindParam(":id", $eventSharedUserId);
        $query->execute();
        if($query->rowCount() > 0){
          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $userShare = $row['username'];
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
        $creator->bindParam(":id", $user);
        $creator->execute();
        if($creator->rowCount() > 0) {
          while($row = $creator->fetch(PDO::FETCH_ASSOC)) {
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
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td><td class="eventItem centered">'.$eventDescription.'</td><td class="eventItem centered">'.$creatorName.'</td><td  class="eventItem centered">'.$eventPrivacy.'</td>';
      } else if ($user != $userId && strpos($deleteAuth, $authConfirm) !== false) {
        $eventListHTML .= '<td class="eventItem">'.$eventTitle.'</td><td class="eventItem centered">'.$eventDescription.'</td><td class="eventItem centered">'.$creatorName.'</td><td  class="eventItem centered">'.$eventPrivacy.'</td><td class="eventItem"></td><td class="eventItem righted"><button class="btn btn-secondary delEventBtn" id="'.$id.'">Delete</button></td>';
      } else {
        $eventListHTML .= '<td class="eventItem" id="'.$id.'">'.$eventTitle.'</td><td class="eventItem centered">'.$eventDescription.'</td><td class="eventItem centered">'.$creatorName.'</td><td class="eventItem centered">'.$eventPrivacy.'</td><td class="eventItem centered">'.$newSharedGroup.'</td><td class="eventItem righted"><button class="btn btn-secondary delEventBtn" id="'.$id.'">Delete</button></td>';
      }
      $eventListHTML .= '</tr>';
    }
    $eventListHTML .= '</table>';
    $eventListHTML .= '<script src="../js/delete.js"></script>';
  }
  echo $eventListHTML;
  $result = null;
}

/*
 * Add event to date
 */
function addEvent($date,$title,$description,$privacy,$sharedWith,$deleteAuth,$userId){
  //Include db configuration file
  include 'dbConfig.php';
  $currentDate = date("Y-m-d H:i:s");
  $sharedUsernameArray = explode(",", $sharedWith);
  $sharedIdArray = array();
  foreach($sharedUsernameArray as $sharedUsername) {
    $query = $con->prepare("SELECT id FROM users WHERE username = :sw");
    $query->bindParam(":sw", $sharedUsername);
    $query->execute();
    if($query->rowCount() > 0){
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
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
    $query->bindParam(":du", $deleteUsername);
    $query->execute();
    if($query->rowCount() > 0){
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
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
  }
  //Insert the event data into database
  $insert = $con->prepare("INSERT INTO events (title,date,created,modified,user,description,privacy,sharedWith,deleteAuth) VALUES (:title,:date,:currentDate,:modifiedDate,:un,:description,:ep,:sw,:da)");
  $insert->bindParam(":title", $title);
  $insert->bindParam(":date", $date);
  $insert->bindParam(":currentDate", $currentDate);
  $insert->bindParam(":modifiedDate", $currentDate);
  $insert->bindParam(":un", $userId);
  $insert->bindParam(":description", $description);
  $insert->bindParam(":ep", $privacy);
  $sharedWith = ' '. $sharedWith .' ';
  $insert->bindParam(":sw", $sharedWith);
  $deleteAuth = ' '. $deleteAuth .' ';
  $insert->bindParam(":da", $deleteAuth);
  $insert->execute();
  if($insert){
    echo 'ok';
  }else{
    echo 'err';
  }
  $insert = null;
}
?>