var express = require('express');
var app = express();

var bodyParser = require('body-parser');
var debug = require('debug')('buzzr:server');
var logger = require('morgan');

var session = require('express-session');
var FileStore = require('session-file-store')(session);

var routes = require('./routes');

// Get the port and start 'er up
var port = process.env.PORT || 3000;
var server = app.listen(port, function() {
	console.log("Lisening on " + port);
});

// Chat stuff
var chat = require('./chat')(server);

// view engine setup for handlebars
app.set('views', __dirname + '/views');
app.set('view engine', 'hbs');

// HTTP Logger
app.use(logger('tiny'));

// Parse cookies
app.use(session({
	name: 'server-session-cookie-id',
	secret: 'my secret',
	saveUninitialized: true,
	resave: true,
	store: new FileStore()
}));

app.use(function printSession(req, res, next) {
	console.log('req.session', req.session);
	return next();
});

// Static Content (js, css, imgs)
app.use(express.static(__dirname + '/public'));

// Parse body responses
app.use(bodyParser());

// Routes
app.use(routes);

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
