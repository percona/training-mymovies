var util = require('util');

module.exports = function(server, sessionMiddleware) {
	
	// Get the socket
	var io = require('socket.io').listen(server);
	
	// Set socket to use same session manager as Express
	io.use(function(socket, next) {
        	sessionMiddleware(socket.request, socket.request.res, next);
	});
	
	// Get the database
	var db = require('./db');
	
	// Keep state of attendees
	var attendees = {};
	
	// Task-related stuff
	var currentTask = "";
	var taskHistory = [];
	taskHistory.length = 0;
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
		
		//console.log('User Connected: ' + util.inspect(socket.request.session));
		
		// When someone closes the window
		socket.on('disconnect', function() {
			console.log('User Disconnected');
			
			// Remove user from list
			var attendee = socket.request.session.attendee
			if (attendee !== undefined)
				delete attendees[attendee.id];
			
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
			
			// Send list of attendees and current task to self via callback
			cb(attendees, currentTask)
		});
		
		// Attendee joined. Broadcast to instructors room. Return attendee's profile.
		socket.on('attendee-joined', function(msg, cb) {
			console.log('Attendee Joined: ' + socket.request.session.attendee.id);
			
			// add/update attendees list
			var attendee = socket.request.session.attendee;
			attendees[attendee.id] = attendee;
			
			// Broadcast updated attendees list to all instructors
			socket.broadcast.to('instructor').emit('update-attendees', attendees);
			
			// Return profile, current task, and task history to attendee
			cb(attendee, currentTask, taskHistory);
		});
		
		// Attendee updated preference
		socket.on('save-profile', function(msg) {
			//console.log('Attendee Saved Profile: ' + util.inspect(msg) + '(' + socket.request.session.attendee.id + ')');
			
			msg.forEach(function(e) {
				socket.request.session.attendee[e.name] = e.value;
			});
			
			socket.request.session.save();
			
			// let instructors know of any changes to this attendee, like afk
			socket.broadcast.to('instructor').emit('update-attendee', socket.request.session.attendee);
		});

		// Received a status click from attendee
		socket.on('task-status', function(msg) {
			
			var attendee = socket.request.session.attendee;
			
			//console.log('Attendee (' + attendee.id + ') Status: ' + msg.status);
			
			// Update attendee status
			if (attendees[attendee.id])
			{
				socket.request.session.attendee.status = msg.status;
				attendees[attendee.id].status = msg.status;
				
				socket.request.session.save();
			}
			else
			{
				console.log('Socket-AttendeeID: ' + attendee.id + ' does not exist.');
			}
			
			// Send attendee status to instructors
			socket.broadcast.to('instructor').emit('update-attendee', attendee);
			
		});
		
		// Received new task from instructor
		socket.on('instructor-new-task', function(msg, cb) {
			console.log('Instructor - New Task');
			//console.log('           - Task:      ' + msg.taskText);
			//console.log('           - Save Task: ' + msg.saveTask);
			//console.log('           - Task ID:   ' + msg.savedTaskId);
			//console.log('           - Append:    ' + msg.appendTask);
			
			// Clear attendee statuses. Don't clear on append.
			if (!msg.appendTask)
				clearAttendeeStatuses();
			
			// If savedTaskId > 0, do db lookup
			if (msg.savedTaskId > 0)
			{
				db.get("SELECT * FROM savedTasks WHERE taskId = ?",
					msg.savedTaskId,
					function(err, row) {
						
						// Save history first
						addCurrentTaskToHistory();
						
						// Update current task from DB
						currentTask = row.taskText;
						
						// Send to everyone
						socket.broadcast.emit('new-task', currentTask, msg.appendTask, taskHistory);
						
						// Return task and updated attendees list back to instructor
						cb(currentTask, attendees);
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
				
				// Save task text locally. Append to current if requested.
				if (msg.appendTask)
					currentTask += "\n" + msg.taskText;
				else
				{
					// Not appending, save history
					addCurrentTaskToHistory();
					
					// Replace current task with new task
					currentTask = msg.taskText;
				}
				
				// Send to everyone
				socket.broadcast.emit('new-task', currentTask, msg.appendTask, taskHistory);
				
				// Return task and updated attendees list back to instructor
				cb(currentTask, attendees);
			}
		});
		
		// Instructor sends clear to all
		socket.on('instructor-clear-task', function(data, cb) {
			console.log('Instructor - Clear Task');
			
			// Save history
			addCurrentTaskToHistory();
			
			// Clear local task
			currentTask = "";
			
			// Clear all attendee status
			clearAttendeeStatuses();
			
			// Send to everyone
			socket.broadcast.emit('clear-task');
			
			// Call callback indicating things ok
			cb();
		});
	
	});
	
	function clearAttendeeStatuses()
	{
		// Clear local attendees array
		Object.keys(attendees).forEach(function(k) {
			attendees[k].status = 0;
		});
		
		// "manually" update all attendee sessions with clear status and save
		var users = nsp.clients().connected;
		Object.keys(users).forEach(function(k) {
			if (users[k].client.request.session.attendee !== undefined)
			{
				users[k].client.request.session.attendee.status = 0;
				users[k].client.request.session.save();
			}
		});
	};
	
	function addCurrentTaskToHistory()
	{
		// Add current task to end of history if not empty
		if (currentTask.trim())
			taskHistory.unshift(currentTask);
		
		// Remove off enough from the end
		taskHistory.splice(5);
	};
};
