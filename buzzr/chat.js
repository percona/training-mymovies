var util = require('util');

module.exports = function(server, sessionMiddleware) {

	// Get the socket
	var io = require('socket.io').listen(server);
	io.use(function(socket, next) {
        	sessionMiddleware(socket.request, socket.request.res, next);
	});
	
	// Get the database
	var db = require('./db');
	
	// Keep state of attendees
	var attendees = {};
	
	// Current task
	var currentTask = {};
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
		
		console.log('User Connected: ' + util.inspect(socket.request.session));
		
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
			
			// Once instructor has joined, broadcast list of attendees to other instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
			
			// Update list of attendees to self via callback
			cb(attendees)
		});
		
		// Attendee joined. Broadcast to instructors room. Return attendee's profile.
		socket.on('attendee-joined', function(msg, cb) {
			console.log('Attendee Joined: ' + util.inspect(msg));
			
			// socket-session variable
			socket.attendeeid = msg.attendeeid;
			
			// add/update attendees list
			attendees[socket.attendeeid] = msg;
			
			// Broadcast updated attendees list to all instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
			
			// Return profile to attendee
			if (cb)
				cb(socket.request.session);
		});
		
		// Attendee updated preference
		socket.on('save-profile', function(msg) {
			console.log('Attendee Saved Profile: ' + util.inspect(msg));
			msg.forEach(function(e) {
				socket.request.session[e.name] = e.value;
			});
			socket.request.session.save();
		});

		// Received a status click from attendee
		socket.on('task-status', function(msg) {
			console.log('Attendee (' + msg.attendeeid + ') Status: ' + msg.status);
			
			// Update attendee status
			if (attendees[socket.attendeeid])
				attendees[socket.attendeeid].status = msg.status;
			else
			{
				console.log('Socket-AttendeeID: ' + socket.attendeeid + ' does not exist.');
			}
			
			// Send task status to instructors
			socket.broadcast.to('instructor').emit('update-task-status', msg);
		});
		
		// Received new task from instructor
		socket.on('instructor-new-task', function(msg, cb) {
			console.log('Instructor - ' + msg.id);
			console.log('           - New Task:  ' + msg.taskText);
			console.log('           - Save Task: ' + msg.saveTask);
			console.log('           - Task ID:   ' + msg.savedTaskId);
			
			// Clear attendee statuses
			clearAttendeeStatuses();
			
			// If savedTaskId > 0, do db lookup
			if (msg.savedTaskId > 0)
			{
				db.get("SELECT * FROM savedTasks WHERE taskId = ?",
					msg.savedTaskId,
					function(err, row) {
						
						// Save it locally for reconnects
						currentTask = row.taskText;
						
						// Send to everyone
						socket.broadcast.emit('new-task', row.taskText);
						
						// Return task and updated attendees list back to instructor
						cb({ taskText: row.taskText }, attendees);
					}
				);
			}
			else
			{
				// Save to database if requested
				if (msg.saveTask)
				{
					db.run("INSERT INTO savedTasks (googleid, taskText, sorder) VALUES (?, ?, 1)", msg.id, msg.taskText);
				}
				
				// Save it locally for reconnects
				currentTask = msg.taskText;
				
				// Send to everyone
				socket.broadcast.emit('new-task', msg.taskText);
				
				// Return task and updated attendees list back to instructor
				cb(msg, attendees);
			}
		});
		
		// Instructor sends clear to all
		socket.on('instructor-clear-task', function(data, cb) {
			console.log('Instructor - Clear Task');
			
			// Clear local cache
			currentTask = {};
			
			// Clear all attendee status
			clearAttendeeStatuses();
			
			// Send to everyone
			socket.broadcast.emit('clear-task');
			
			// Return updated attendees list via callback
			cb(attendees);
		});
	
	});
	
	function clearAttendeeStatuses() {
		Object.keys(attendees).forEach(function(k) {
			attendees[k].status = '';
		});
	};
};
