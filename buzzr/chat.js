var util = require('util');

// TODO: Save current task. Send to attendees on connect/reconnect

module.exports = function(server) {
	
	var io = require('socket.io').listen(server);
	
	// Keep state of attendees
	var attendees = {};
	
	// Current task
	var currentTask = {};
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
		
		console.log('a user connected');
		
		// On any connection, get the currentTask
		socket.emit('new-task', currentTask);
		
		// When someone closes the window
		socket.on('disconnect', function() {
			console.log('User disconnected');
			
			// Remove user from list
			delete attendees[socket.attendeeid];
			
			// broadcast new list to instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
		});
		
		// Instructor joined. Change their room.
		socket.on('instructor-joined', function(msg, cb) {
			console.log('Instructor Joined');
			
			// join instructors room. could be more than 1 instructor.
			socket.join('instructor');
			
			console.log("joined instructor room");
			console.log("attendees: " + util.inspect(attendees));
			
			// Once instructor has joined, broadcast list of attendees to other instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
			
			// Update list of attendees to self via callback
			cb(attendees)
		});
		
		// Attendee joined. Broadcast to instructors room.
		socket.on('attendee-joined', function(msg) {
			console.log('Attendee Joined: ' + util.inspect(msg));
			
			// socket-session variable
			socket.attendeeid = msg.attendeeid;
			
			// add/update attendees list
			attendees[socket.attendeeid] = msg;
			
			// Broadcast list to all instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
		});
		
		// Received a status click from attendee
		socket.on('task-status', function(msg) {
			console.log('Attendee: ' + msg.attendeeid);
			console.log('Status: ' + msg.status);
			
			// Update attendee status
			attendees[socket.attendeeid].status = msg.status;
			
			// Send task status to instructors
			socket.broadcast.to('instructor').emit('update-task-status', msg);
		});
		
		// Received new task from instructor
		socket.on('instructor-new-task', function(msg, cb) {
			console.log('Instructor - New Task: ' + msg.task_text);
			
			// Save it locally
			currentTask = msg;
			
			// Send to everyone
			socket.broadcast.emit('new-task', msg);
			
			// Return callback
			cb(msg);
		});
		
		// Instructor sends clear to all
		socket.on('instructor-clear-task', function(msg, cb) {
			console.log('Instructor - Clear Task');
			
			// Clear local cache
			currentTask = {};
			
			// Clear all attendee status
			Object.keys(attendees).forEach(function(k) {
				attendees[k].status = '';
			});
			
			// Send to everyone else
			socket.broadcast.emit('clear-task');
			
			// Return updated attendees list via callback
			cb(attendees);
		});
	
	});
};
