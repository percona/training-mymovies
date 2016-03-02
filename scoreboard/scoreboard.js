// To Install:
// yum install epel-release -y
// yum install nodejs npm -y

// To start:
// export NODE_PATH=/usr/lib/node_modules/
// screen node scoreboard.js

// https://github.com/caolan/async

var http = require('http');
var r = require("./retrieve");
var childProcess = require("child_process");
var utils = require("./utils");

// Team object
var Team = function(name, ip)
{
    var that = {};
    that.name = name;
    that.ip = ip;
    that.homepageResult = 0;
    that.searchResult = 0;
    that.movieResult = 0;
    
    return that;
};

// We can use internal IPs since we are within a VPC
//./start-instances.php -a GENHOSTS -r us-west-1 -p BIGC | awk 'BEGIN{team=1}{print "teams.push(Team(\"" team++ "\", \""$1"\"));"}'

teams = Array();
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));
teams.push(Team("1", ""));

var init = function() {
    
    console.log("scoreboard.js: initialization starting");
    
    // Create the background process
    this._rchild = childProcess.fork("./retrieve");
    
    // Listen for messages sent from background process
    this._rchild.on('message', function(data) {
        console.log("scoreboard.js: recv'd team " + data.name + " data from background process.");
        
        var teamNum = (parseInt(data.name) - 1);
        this.teams[teamNum].homepageResult = data.homepageResult;
        this.teams[teamNum].searchResult = data.searchResult;
        this.teams[teamNum].movieResult = data.movieResult;
        
    }.bind(this));
    
    // Send the teams array to the background process
    this._rchild.send(teams);
}()

http.createServer(function (request, response)
{
    var start = new Date();
    
    output = '<html> \n\
 <head> \n\
 <title>Scoreboard</title> \n\
 <style> \n\
 \n\
 body { \n\
        font-family: arial; \n\
 } \n\
 \n\
 #homepage, #search, #other { \n\
  width: 10%; \n\
  border: 1px solid #cccccc; \n\
  float: left; \n\
  padding: 10px; \n\
  margin-left: 5px; \n\
 } \n\
 \n\
 #refreshing { \n\
  float: right; \n\
  font-style: italic; \n\
 } \n\
 </style> \n\
 <meta http-equiv="refresh" content="60"> \n\
</head> \n\
<body> \n\
<table width=100%> \n\
<tr><th>Team name</th><th>Homepage (Goal: <200ms)</th><th>Search Results (Goal: < 1000ms)</th><th>Any other page (Goal: <500ms)</tr> \n\
';
    
    for(var i = 0; i < teams.length; i++)
    {
        var team = teams[i];
        output += "<tr><th>Team " + team.name + "</th>";
        output += "<td align=center>" + utils.pretty_time( team.homepageResult ) + " " + utils.print_bar( team.homepageResult, 200 ) + "</td>";
        output += "<td align=center>" + utils.pretty_time( team.searchResult ) + " " + utils.print_bar( team.searchResult, 1000 ) + "</td>";
        output += "<td align=center>" + utils.pretty_time( team.movieResult ) + " " + utils.print_bar( team.movieResult, 500 ) + "</td>";
        output += "</tr>\n";
    }
    
    output += "</table>\n";
    output += "Page built in " + utils.pretty_time( new Date() - start ) + "<br/>";
    output += "Page last updated at" + Date() + "\n";
    output += "</body></html>";
        
    response.writeHead(200, {'Content-Type': 'text/html'});
    response.end(output + '\n');
    
}).listen(8080);

console.log('Server running at http://127.0.0.1:8080/');;
