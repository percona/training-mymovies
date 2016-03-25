var express = require('express');
var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', { title: 'Buzzr' });
});

// Students logic
router.get('/student', function(req, res, next) {
  
  // Check for session cookie
  
  
  
  res.render('chat');
});

module.exports = router;
