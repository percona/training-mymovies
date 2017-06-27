var express = require('express');
var app = express();

var bodyParser = require('body-parser');
var debug = require('debug')('buzzr:server');
var logger = require('morgan');

var session = require('express-session');
var flash = require('express-flash');

var SQLiteStore = require('connect-sqlite3')(session);

var passport = require('passport');

var validator = require('express-validator');

// Main ==============================

// Setup database
var db = require('./db');

// view engine setup for handlebars
app.set('views', __dirname + '/views');
app.set('view engine', 'hbs');

// HTTP Logger
app.use(logger('tiny'));

// Parse body responses
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Form validation
app.use(validator([]));

// Static Content (js, css, imgs)
app.use('/js', express.static(__dirname + '/public/js'));
app.use('/js', express.static(__dirname + '/node_modules/jquery/dist'));
app.use('/js', express.static(__dirname + '/node_modules/notifyjs/dist'));
app.use('/js', express.static(__dirname + '/node_modules/bootstrap-toggle/js'));

app.use('/css', express.static(__dirname + '/public/css'));
app.use('/css', express.static(__dirname + '/node_modules/bootstrap/dist/css'));
app.use('/css', express.static(__dirname + '/node_modules/bootstrap-toggle/css'));

app.use('/images', express.static(__dirname + '/public/images'));
app.use('/audio', express.static(__dirname + '/public/audio'));
app.use('/fonts', express.static(__dirname + '/node_modules/bootstrap/dist/fonts'));

// Parse/Use cookies
var sessionMiddleware = session({
	name: 'percona-buzzr',
	cookie: {
		expires: new Date(Date.now() + (5 * 24 * 3600 * 1000))
	},
	secret: '1K37ifYLnM',
	saveUninitialized: true,
	resave: true,
	store: new SQLiteStore({
		table: 'sessions',
		db: 'buzzr'
	})
});

// For express
app.use(sessionMiddleware);

// For flash messages
app.use(flash());

// Get the port and start 'er up
var port = process.env.npm_package_config_port;
if (port == undefined) {
  console.log("Port undefined. Try 'npm config set buzzr:port 8080'");
  process.exit(1);
}

// Configure google auth stuff
require('./googleauth.js')(passport, port);
app.use(passport.initialize());
app.use(passport.session());

// Routes
var routes = require('./routes')(app, passport);

var server = app.listen(port, function() {
	console.log("Lisening on " + port);
}).on('error', function(err) {
	if (err.errno === 'EADDRINUSE')
		console.log("Port " + port + " is in use. Try another one by running 'npm config set buzzr:port NNNN'");
	else
		console.log(err);
});

// Chat stuff
var chat = require('./chat')(server, sessionMiddleware);

// catch 404 and forward to error handler
app.use(function(req, res, next) {
  var err = new Error('Not Found');
  err.status = 404;
  next(err);
});

// development error handler
// will print stacktrace
if (app.get('env') === 'development') {
  app.use(function(err, req, res, next) {
    res.status(err.status || 500);
    res.render('error', {
      message: err.message,
      error: err
    });
  });
}

// production error handler
// no stacktraces leaked to user
app.use(function(err, req, res, next) {
  res.status(err.status || 500);
  res.render('error', {
    message: err.message,
    error: {}
  });
});
