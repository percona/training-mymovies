var util = require('util');

module.exports = function(app, passport) {
	
	// GET home page.
	app.get('/', function(req, res, next) {
		res.render('index', { flash: req.flash() });
	});
	
	// Students logic
	app.get('/student', function(req, res, next) {
		
		// Check for session cookie
		if (typeof req.session.studentid === 'undefined') {
			return res.redirect('/student/login');
		}
		
		// logged in with identifier
		res.render('chat', {
			firstName: req.session.firstName,
			lastInitial: req.session.lastInitial,
			studentid: req.session.studentid
		});
		
	});
	
	// just displaying the form
	app.get('/student/login', function(req, res, next) {
		res.render('student_login');
	});
	
	// When they submit the form
	app.post('/student/login', function(req, res, next) {
		
		if (!req.body) return res.sendStatus(400);
		
		// create the checks
		if (req.body.firstName1.length < 1)
		{
			req.checkBody('firstName1', 'First Name is Required.').notEmpty();
		}
		else
		{
			req.checkBody('firstName1', 'First Name must be at least 2 characters.').isAlpha().len(2,20);
		}
		
		req.checkBody('lastInitial1', 'Please provide an initial.').notEmpty();
		
		if (req.body.email1.length > 7)
		{
			req.checkBody('email1', 'Please enter a valid email address.').isEmail();
		}
		
		// do the checks
		var errors = req.validationErrors();
		console.log("Student Login Validation Errors: " + util.inspect(errors));
		console.log('Flash: ' + util.inspect(req.session.flash));
		
		if (!errors)
		{
			// Save stuff to session
			req.session.firstName = req.body.firstName1;
			req.session.lastInitial = req.body.lastInitial1;
			req.session.email = (req.body.email1 || "anon@anon.org");
			req.session.studentid = (req.body.firstName1.toUpperCase()
									+ req.body.lastInitial1.toUpperCase()
									+ (Math.floor(Date.now() / 1000)));
			
			// send to chat area
			return res.redirect('/student');
		}
		
		// Flash the errors
		req.flash('error', errors);
		
		// Render the login page
		res.render('student_login', {
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
		res.render('instructor', {
			user: req.user
		});
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