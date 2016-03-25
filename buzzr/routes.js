var express = require('express');
var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', { title: 'Buzzr' });
});

/* GET users listing. */
router.get('/users', function(req, res, next) {
  res.render('chat');
});

router.get('/users/foo', function(req, res, next) {
  res.render('chat', { title: 'Chat Foo' });
});

module.exports = router;
