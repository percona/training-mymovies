var util = require('util');

module.exports = function(server) {
	
	// Get the socket
	var io = require('socket.io').listen(server);
	
	// Get the database
	var db = require('./db');
	
	// Keep state of attendees
	var attendees = {};
	
	// Current task
	var currentTask = {};
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
		
		console.log('User Connected');
		
		// Attendee and instructor, on connection, get the currentTask
		socket.emit('new-task', currentTask);
		
		// When someone closes the window
		socket.on('disconnect', function() {
			console.log('User Disconnected');
			
			// Remove user from list
			delete attendees[socket.attendeeid];
			
			// broadcast updated list to instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
		});
		
		// Instructor joined. Change their room.
		socket.on('instructor-joined', function(msg, cb) {
			console.log('Instructor Joined');
			
			// join instructors room. could be more than 1 instructor.
			socket.join('instructor');
			
			console.log("Joined Instructor Room");
			console.log("Attendees List: " + util.inspect(attendees));
			
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
			
			// Broadcast updated attendees list to all instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
		});
		
		// Received a status click from attendee
		socket.on('task-status', function(msg) {
			console.log('Attendee (' + msg.attendeeid + ') Status: ' + msg.status);
			
			// Update attendee status
			attendees[socket.attendeeid].status = msg.status;
			
			// Send task status to instructors
			socket.broadcast.to('instructor').emit('update-task-status', msg);
		});
		
		// Received new task from instructor
		socket.on('instructor-new-task', function(msg, cb) {
			console.log('Instructor - ' + msg.id);
			console.log('           - New Task:  ' + msg.taskText);
			console.log('           - Save Task: ' + msg.saveTask);
			
			// Save it locally
			currentTask = msg.taskText;
			
			// Save to database if requested
			if (msg.saveTask)
			{
				db.run("INSERT INTO savedTasks (googleid, taskText, sorder) VALUES "
					+ "('" + msg.id + "', '" + msg.taskText + "', 1)");
			}
			
			// Clear attendee statuses
			clearAttendeeStatuses();
			
			// Send to everyone
			socket.broadcast.emit('new-task', msg.taskText);
			
			// Return task and updated attendees list back to instructor
			cb(msg, attendees);
		});
		
		// Instructor sends clear to all
		socket.on('instructor-clear-task', function(msg, cb) {
			console.log('Instructor - Clear Task');
			
			// Clear local cache
			currentTask = {};
			
			// Clear all attendee status
			clearAttendeeStatuses();
			
			// Send to everyone
			socket.broadcast.emit('clear-task');
			
			// Return updated attendees list via callback
			cb(msg, attendees);
		});
	
	});
	
	function clearAttendeeStatuses() {
		Object.keys(attendees).forEach(function(k) {
			attendees[k].status = '';
		});
	};
};