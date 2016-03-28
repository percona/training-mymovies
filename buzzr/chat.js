module.exports = function(server) {
	
	var io = require('socket.io').listen(server);
	
	// Socket namespace
	var nsp = io.of('/buzzur');
	nsp.on('connection', function(socket) {
	
		console.log('a user connected');
	
		socket.on('disconnect', function() {
			console.log('user disconnected');
			clearTimeout(t);
		});
		
		// Received a status click from student
		socket.on('task-status', function(msg) {
			console.log('Student: ' + msg.studentid);
			console.log('Status: ' + msg.status);
		});
		
		// start 10 sec timeout
		var numTasks = 3;
		var tasks = [ 	'zeroth task', 'update repos', 'chown -R mysql:mysql',
						'create new database', 'some really long stringed text task for testing purposes',
						'dummy task to get things started' ];
		
		var t = setTimeout(sendRandTask, 10000);
		function sendRandTask() {
			console.log('New Task: ' + tasks[numTasks]);
			socket.emit('new-task', { task_text: tasks[numTasks] });
			
			if (numTasks > 1) {
				numTasks--;
				t = setTimeout(sendRandTask, 10000);
			}
			else
			{
				t = setTimeout(function() {
					console.log('Clear Tasks');
					socket.emit('clear-task');
				}, 10000);
			}
		};
	
	});
};
