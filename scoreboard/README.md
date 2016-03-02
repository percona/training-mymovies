## MyMovies Scoreboard

To install:
- service httpd stop (assuming you have apache installed)
- yum install epel-release -y
- yum install nodejs npm -y

Edit scoreboard.js and update the Team IP addresses. In AWS, you can use the internal IPs of the student hosts.

To start:
 - export NODE_PATH=/usr/lib/node_modules/
 - screen node scoreboard.js

## How it works

scoreboard.js first creates the Team object used for storing the timer results of each team's pages. It then forks retrieve.js, who receives a copy of the team[] array. It then processes each team's 3 pages in parallel and updates the local resultData object. When all 3 pages have been fetched, we send this object back to scoreboard which updates the team object.

This constant refresh of the pages happens continueously, in the background.

On refreshes of the scoreboard webpage, the timing data is simply fetched from the resultData object. This has the effect of making the scoreboard redraw very fast as it is no longer waiting for URL fetches to complete.
