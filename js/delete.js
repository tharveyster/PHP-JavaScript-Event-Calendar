$(document).ready(function(){
  $(".delEventBtn").on("click",function(e) {
    e.preventDefault();
    const del_id = $(this).attr("id");
    const info = "id=" + del_id;
    $.ajax({
      type : "POST",
      url : "deleteEvent.php", //URL to the delete php script
      data : info,
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
  });
});