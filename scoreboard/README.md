# MyMovies Scoreboard

## Ansible-based Install

1. Use ansible script from percona/training-material to deploy
 - `ansible-playbook -i ansible_hosts_hou -l db1-T10 ansible_playbooks/scoreboard.yml`

## Manual Install

1. Install NodeJS and Plugins
 - `yum install epel-release -y`
 - `yum install nodejs nodejs-async nodejs-child-process-close -y`
2. Clone Repo
 - `git clone https://github.com/percona/training-mymovies`
 - After cloning, `cd training-mymovies/scoreboard`
 - Edit/Create teams.json
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
