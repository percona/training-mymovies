var http = require("http");
var async = require('async');
var utils = require('./utils');

process.on('message', function(teams)
{
    function gatherData(_team)
    {
        var resultData = { "name":_team.name, "homepageResult":0, "searchResult":0, "movieResult":0 };
        
        console.log("Begin Parallel Processing for team " + _team.name);
        
        // Do all the page loading
        async.parallel([
            function(callback)
            {
                var start = new Date();
                var req = http.get('http://' + _team.ip + '/my-movies/', function(res) {
                    resultData.homepageResult = new Date() - start;
                    callback(null, 'homepage');
                });
                req.on('socket', function(socket) {
                    socket.setTimeout(10000);
                    socket.on('timeout', function() {
                        req.abort();
                    });
                });
                req.on('error', function(err) { resultData.homepageResult = 10000; req.abort(); callback(null, 'homepage'); });
            },
            function(callback)
            {
                var start = new Date();
                var req = http.get('http://' + _team.ip + '/my-movies/search.php?search_type=movies&q=' + utils.genRandString(), function(res) {
                    resultData.searchResult = new Date() - start;
                    callback(null, 'searchpage');
                });
                req.on('socket', function(socket) {
                    socket.setTimeout(10000);
                    socket.on('timeout', function() {
                        req.abort();
                    });
                });
                req.on('error', function(err) { resultData.searchResult = 10000; req.abort(); callback(null, 'searchpage'); });
            },
            function(callback)
            {
                var start = new Date();
                var req = http.get('http://' + _team.ip + '/my-movies/movie.php?id=' + utils.getRandomInt(0, 100000),  function(res) {
                    resultData.movieResult = new Date() - start;
                    callback(null, 'moviepage');
                });
                req.on('socket', function(socket) {
                    socket.setTimeout(10000);
                    socket.on('timeout', function() {
                        req.abort();
                    });
                });
                req.on('error', function(err) { resultData.movieResult = 10000; req.abort(); callback(null, 'moviepage'); });
            },
        ],
        function(err, results)
        {
            // results is now equal to ['homepage', 'searchpage', 'moviepage']
            // on success of each function in the series
            
            console.log("Results processing done for team " + _team.name);
            
            // Let the parent know something changed
            process.send(resultData);
            
            // Refresh results in 45 seconds
            setTimeout(function() { gatherData(_team); }, 45000);
        });
    }
    
    function gatherCb(err)
    {
        if(err)
        {
            console.log("async.each() : Got error: " + err);
        }
        else
        {
            console.log("async.each() : Completed iteration.");
        }
    }
    
    this.init = function()
    {
        console.log("retrieve.js: got init");
        
        async.each(
            teams,
            function(t, cb)
            {
                gatherData(t);
                
                // cb() will execute before gatherData() is completely finished.
                cb();
            },
            gatherCb);
        
    }.bind(this)()
})

process.on('uncaughtException', function(err)
{
    console.log("retrieve.js: " + err.message + "\n" + err.stack + "\n");
})