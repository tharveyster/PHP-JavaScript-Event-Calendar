var session_timeout = 1000 * 60 * 45;
// 1000 milliseconds in a second *
// 60 seconds in a minute *
// 20 minutes
var reloadpage = "signOut.php?sessionExpired=true";
var timeout = null;
function start() {
  if (timeout) clearTimeout(timeout);
  timeout = setTimeout(
    "location.replace('" + reloadpage + "');",
    session_timeout
  );
}
