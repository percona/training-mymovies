var express = require('express');
var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', { title: 'Buzzr' });
});

// Students logic
router.get('/student', function(req, res, next) {
  
  // Check for session cookie
  if (typeof req.session.views === 'undefined') {
  	req.session.views = 0;
  }
  
  req.session.views++;
  res.render('chat', {views: req.session.views});
});

module.exports = router;
