chat = function(s) {
	
	var io = require('socket.io').listen(s);
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
	
		console.log('a user connected');
	
		socket.on('disconnect', function() {
			console.log('user disconnected');
		});
	
		socket.on('chat message', function(msg) {
			console.log('message: ' + msg);
			nsp.emit('chat message', msg);
		});
	
	});
};

module.exports = chat;
