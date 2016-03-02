## MyMovies Scoreboard

1. Install NodeJS and Plugins
 - `yum install epel-release -y`
 - `yum install nodejs npm -y`
2. Clone Repo
 - `git clone https://github.com/percona/training-material`
 - You will have to login with github.com credentials since this is a private repo.
 - After cloning, `cd training-material/scoreboard`
2. Edit Config
 - Edit scoreboard.js and update the Team IP address object. In AWS, you can use the internal IPs of the student hosts.
 - `./start-instances.php -r us-west-1 -p BFG -a GENSCOREBOARD`
3. Start scoreboard
 - Run the following:
 - `export NODE_PATH=/usr/lib/node_modules/`
 - `screen node scoreboard.js`
4. Load scoreboard
 - Load up in a browser: `http://ip.of.your.machine:8080/`

## How it works

scoreboard.js first creates the Team object used for storing the timer results of each team's pages. It then forks retrieve.js, who receives a copy of the team[] array. It then processes each team's 3 pages in parallel and updates the local resultData object. When all 3 pages have been fetched, we send this object back to scoreboard which updates the team object.

This constant refresh of the pages happens continueously, in the background.

On refreshes of the scoreboard webpage, the timing data is simply fetched from the resultData object. This has the effect of making the scoreboard redraw very fast as it is no longer waiting for URL fetches to complete.
