var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

app.set('view engine', 'html');
app.set('views', './views')

app.engine('html', require('hbs').__express);

app.get('/', function (req, res) {
	res.render('index', { title: "My Title", bigtitle: "My H1 TITLE" });
});

io.on('connection', function(socket) {
	
	console.log('a user connected');
	
	socket.on('disconnect', function() {
		console.log('user disconnected');
	});
	
	socket.on('chat message', function(msg) {
		console.log('message: ' + msg);
		io.emit('chat message', msg);
	});
	
});

http.listen(3000, function () {
	console.log("app listenting");
})
