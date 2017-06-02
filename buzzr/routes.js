var util = require('util');
var crypto = require('crypto');

module.exports = function(app, passport) {
	
	// Get the database
	var db = require('./db');
	
	// GET home page.
	app.get('/', function(req, res, next) {
		res.render('index', { flash: req.flash() });
	});
	
	// Attendee logic
	app.get('/attendee', function(req, res, next) {
		
		// Check for session cookie
		if (typeof req.session.attendee === 'undefined' || typeof req.session.attendee.id === 'undefined') {
			return res.redirect('/attendee/login');
		}
		
		// logged in with identifier
		var attendee = req.session.attendee;
		res.render('attendee_chat', {
			firstName: attendee.firstName,
			lastInitial: attendee.lastInitial,
			emailHash: attendee.emailHash
		});
		
	});
	
	// just displaying the form
	app.get('/attendee/login', function(req, res, next) {
		res.render('attendee_login');
	});
	
	// When they submit the form
	app.post('/attendee/login', function(req, res, next) {
		
		if (!req.body) return res.sendStatus(400);
		
		// create the checks
		if (req.body.firstName1.length < 1)
		{
			req.checkBody('firstName1', 'First name is required.').notEmpty();
		}
		
		req.checkBody('lastInitial1', 'Please provide an initial.').notEmpty();
		
		if (req.body.email1.length > 7)
		{
			req.checkBody('email1', 'Please enter a valid email address.').isEmail();
		}
		
		// do the checks
		var errors = req.validationErrors();
		console.log("Attendee Login Validation Errors: " + util.inspect(errors));
		console.log('Flash: ' + util.inspect(req.session.flash));
		
		if (!errors)
		{
			// Create attendee object and save to session
			var attendee = {
				firstName: req.body.firstName1,
				lastInitial: req.body.lastInitial1,
				email: (req.body.email1 || "anon@anon.org"),
				emailHash: (crypto.createHash('md5').update(req.body.email1.toLowerCase()).digest('hex')),
				id: (req.body.firstName1.toUpperCase()
									+ req.body.lastInitial1.toUpperCase()
									+ (Math.floor(Date.now() / 1000))),
				notifications: true,
				status: 0,
				isAfk: false
			};
			req.session.attendee = attendee;
			
			// send to chat area
			return res.redirect('/attendee');
		}
		
		// Flash the errors
		req.flash('error', errors);
		
		// Render the login page
		res.render('attendee_login', {
			flash: req.flash(),
			formvals: {
				firstName1: (req.body.firstName1 || ''),
				lastInitial1: (req.body.lastInitial1 || ''),
				email1: (req.body.email1 || '')
			}
		});
		
	});
	
	// Instructor google auth. This redirects to google's login page.
	app.get('/instructor/auth',
		passport.authenticate('google', {
			scope: ['profile', 'email'],
			prompt: "select_account"
		})
	);
	
	// Instructor callback after being auth'd by google.
	app.get('/instructor/oauth',
		passport.authenticate('google', {
			successRedirect: '/instructor',
			failureRedirect: '/',
			failureFlash: true
		})
	);
	
	// Instructor page. If logged in, displays info from google profile.
	app.get('/instructor', isLoggedIn, function(req, res, next) {
		
		// get saved tasks for this instructor
		db.all("SELECT * FROM savedTasks WHERE googleid = '"
			+ req.user.id + "' ORDER BY sorder",
			function(err, rows) {
				
				// if error, create a row-ish object containing the error
				if (err != null)
					rows = [ { taskId: 1, taskText: err } ];
				
				// Render the page
				res.render('instructor', {
					tasks: rows,
					user: req.user
				});
			}
		);
		
	});
	
	app.get('/logout', function(req, res) {
		req.logout();
		res.redirect('/');
	});
};

function isLoggedIn(req, res, next) {

	// if user is authenticated in the session, carry on 
	if (req.isAuthenticated())
	{
		console.log("User authenticated");
		return next();
	}
	
	// if they aren't redirect them to the home page
	console.log("User un-authenticated");
	res.redirect('/instructor/auth');
}
