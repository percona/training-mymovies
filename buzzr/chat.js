module.exports = function(server) {
	
	var io = require('socket.io').listen(server);
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
	
		console.log('a user connected');
	
		socket.on('disconnect', function() {
			console.log('user disconnected');
		});
		
		// Received a status click from student
		socket.on('task-status', function(msg) {
			console.log('Student: ' + msg.studentid);
			console.log('Status: ' + msg.status);
		});
		
		// Received new task from instructor
		socket.on('admin-new-task', function(msg) {
			
			console.log('New Task: ' + msg.task_text);
			
			// Send to all others (ie: students)
			socket.broadcast.emit('new-task', msg);
			
			// Send to self
			socket.emit('new-task', msg);
		});
		
		// Instructor sends clear to all
		socket.on('admin-clear-task', function(msg) {
			
			console.log('Clear Task');
			
			// Send to all others (ie: students)
			socket.broadcast.emit('clear-task');
			
			// Send to self
			socket.emit('clear-task');
		});
	
	});
};
