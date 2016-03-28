var express = require('express');
var app = express();

var bodyParser = require('body-parser');
var debug = require('debug')('buzzr:server');
var logger = require('morgan');

var session = require('express-session');
var flash = require('express-flash');
var FileStore = require('session-file-store')(session);

var passport = require('passport');

var validator = require('express-validator');

// Main ==============================

// view engine setup for handlebars
app.set('views', __dirname + '/views');
app.set('view engine', 'hbs');

var Handlebars = require('hbs');
Handlebars.registerHelper('json', function(context) {
    return JSON.stringify(context);
});


// HTTP Logger
app.use(logger('tiny'));

// Parse body responses
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Form validation
app.use(validator([]));

// Static Content (js, css, imgs)
app.use(express.static(__dirname + '/public'));

// Parse/Use cookies
app.use(session({
	name: 'percona-buzzr',
	cookie: {
		expires: new Date(Date.now() + (5 * 24 * 3600 * 1000))
	},
	secret: '1K37ifYLnM',
	saveUninitialized: true,
	resave: true,
	store: new FileStore()
}));

// For flash messages
app.use(flash());

// Configure google auth stuff
require('./googleauth.js')(passport);
app.use(passport.initialize());
app.use(passport.session());

// Routes
var routes = require('./routes')(app, passport);

// Get the port and start 'er up
var port = process.env.PORT || 3000;
var server = app.listen(port, function() {
	console.log("Lisening on " + port);
});

// Chat stuff
var chat = require('./chat')(server);

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
