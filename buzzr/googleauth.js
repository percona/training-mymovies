var GoogleStrategy = require('passport-google-oauth2').Strategy;
var util = require('util');

module.exports = function(passport) {
	
	passport.serializeUser(function(user, done) {
		done(null, user);
	});
	
	passport.deserializeUser(function(id, done) {
		done(null, id);
	});
	
	passport.use(new GoogleStrategy({
			clientID: '768833376258-ma64kjcma4pq09ga0p2r73nlphufktoi.apps.googleusercontent.com',
			clientSecret: 'AdESMo-xNZAMMXK-lKus3KuB',
			callbackURL: "http://127.0.0.1:3000/instructor/oauth"
		},
		function(token, refreshToken, gprofile, done) {
			
			console.log("Got user from google: ");
			console.log(" - ID:     " + gprofile.id);
			console.log(" - Name:   " + gprofile.displayName);
			console.log(" - Email:  " + gprofile.email);
			console.log(" - Domain: " + (gprofile._json.domain || 'None'));
			console.log(" - Token:  " + token);
			
			if (gprofile._json.domain != 'percona.com')
			{
				console.log("Not percona.com");
				return done(null, false, {
					message: 'Sorry, you must be an employee of Percona to sign in as speaker.'
				});
			}
			
			// Construct our user from google info
			var user = {
				id: gprofile.id,
				displayName: gprofile.displayName,
				email: gprofile.email,
				pic: gprofile.photos[0].value
			};
			
			return done(null, user);
		}
	));

};