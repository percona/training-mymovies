var socket = io('/buzzur');

var ALERT_NONE = 0, ALERT_DANGER = 1, ALERT_WARNING = 2, ALERT_SUCCESS = 3;
var statuses = { 0: 'none', 1: 'alert-danger', 2: 'alert-warning', 3: 'alert-success' };

// Clear the current task from everyone
$('#clear-task').click(function(e) {
	socket.emit('instructor-clear-task', {}, function(a) {

		$('#current-task').html('No task at this time.');

		// clear attendees list of status
		$('#attendees-list').children().each(function() {
			$(this).removeClass('alert-success alert-warning alert-danger');
		});
	});
});

// Send out new task to everyone
$('#push-new-task').click(function(e) {

	if ($('#task').val().length < 2)
		return false;

	pushTask(0, false);
});

// Append to the current task
$('#append-task').click(function(e) {

	if ($('#task').val().length < 2)
		return false;

	pushTask(0, true);
});

// Send completed to instructor
$('#completed-task').click(function(e){
	clearAndSet('alert-success');
	sendStatus(ALERT_SUCCESS);
});

// Send pause to instructor
$('#just-a-min').click(function(e){
	clearAndSet('alert-warning');
	sendStatus(ALERT_WARNING);
});

// Send help to instructor
$('#ack-help').click(function(e){
	clearAndSet('alert-danger');
	sendStatus(ALERT_DANGER);
});

// Save disable notifications
$('#toggle-notifications').change(function(e) {
	socket.emit('save-profile', [{ name: 'notifications', value: this.checked }]);
});

// Toggle AFK status
$('#toggle-isAfk').change(function(e) {
	var isAfk = !this.checked;
	socket.emit('save-profile', [{ name: 'isAfk', value: isAfk }]);
	
	$('#task-buttons').children().each(function() {
		$(this).prop('disabled', isAfk);
	});
	
});

// Received new task
socket.on('new-task', function(taskText, appendTask, taskHistory) {
	setTask(taskText, appendTask);
	updateTaskHistory(taskHistory);
});

// Received clear task from instructor
socket.on('clear-task', function(msg) {
	clearAndSet();
	$('#current-task').html('No task at this time.');
	$('#task-buttons').hide();
});

// Someone has joined. Receive new attendee list.
socket.on('update-attendees', function(msg) {
	updateAttendees(msg);
});

// Received update to attendee status
socket.on('update-attendee', function(a) {
	
	var dn = '#attendee-' + a.id;
	
	// remove any previous status
	$(dn).removeClass('alert-success alert-warning alert-danger');
	
	// Update attendee status
	$(dn).addClass(statuses[a.status]);
	
	// Update AFK status icon
	var sp = $(dn + ' > span');
	if (a.isAfk && sp.hasClass('glyphicon-user'))
		sp.removeClass('glyphicon-user').addClass('glyphicon-time');
	else if (!a.isAfk && sp.hasClass('glyphicon-time'))
		sp.removeClass('glyphicon-time').addClass('glyphicon-user');

	// Sort the list to put alert-success at the bottom
	if (a.status == ALERT_SUCCESS) {
		p = $(dn).detach();
		$('#attendees-list').append(p);
	}
});

// HELPERS ===================================

function setTask(tt, appendTask)
{
	if (!appendTask)
		clearAndSet();
	
	if ($.isEmptyObject(tt))
		return true;

	$('#current-task').html(tt);
	$('#task-buttons').show();

	// If appending, don't repeat sounds/notifications
	if (!appendTask)
	{
		if ($('#audiotag1').length)
			$('#audiotag1')[0].play();
		
		doNotification();
	}
}

function updateTaskHistory(hist)
{
	$('#task-history').empty();
	
	if ($.isEmptyObject(hist))
	{
		$('#task-history').text('No task history.');
	}
	else
	{
		// Show immediately previous task at top of list
		hist.forEach(function(v) {
			var $d = $('<div>', {
					text: v,
					class: 'task-history-item'
			});

			$('#task-history').append($d);
		});
	}
}

// pushTask accepts either a saved taskId or 0 as parameter
// On 0, the value of the text box is used. > 0 does a DB
// lookup and pushes that taskId.
// appendTask: (true/false)
function pushTask(tid, appendTask)
{
	socket.emit('instructor-new-task', {
			savedTaskId: tid,
			taskText: $('#task').val(),
			saveTask: $('#saveTask').prop("checked"),
			appendTask: appendTask || false,
			id: instructorid
		},
		function(taskText, a) {
			$('#current-task').html(taskText);
			$('#task').val('');
			updateAttendees(a);
		}
	);
}

function sendStatus(s)
{
	socket.emit('task-status', { status: s });
	return false;
}

function clearAndSet(c)
{
	$('#current-task-panel-body').removeClass('alert-success alert-warning alert-danger');
	if (c !== undefined || c != '')
	{
		$('#current-task-panel-body').addClass(c);
	}
}

// Helper function for updating attendees list
function updateAttendees(msg) {

	$('#attendees-list').empty();

	if ($.isEmptyObject(msg))
	{
		$('#attendees-list').text('None');
	}
	else
	{
		var attendeeIds = Object.keys(msg);
		attendeeIds.sort(function(a, b) {
			return msg[a].status - msg[b].status;
		});
		
		$.each(attendeeIds, function(i, v) {
			var a = msg[v];
			
			var $s = $('<span>', {
				class: 'glyphicon ' + (a.isAfk ? 'glyphicon-time' : 'glyphicon-user')
			});

			var $d = $('<div>', {
				id: 'attendee-' + a.id,
				text: (a.firstName + ' ' + a.lastInitial + '.'),
				class: 'alert ' + (statuses[a.status] || '')
			});

			$d.prepend($s);
			$('#attendees-list').append($d);
		});
	}
}

function doNotification()
{
	// Check that library has loaded
	if (typeof Notify != 'function')
	{
		console.log("notify.js was not loaded.");
		return;
	}
	
	// Do we have permission and not disabled?
	if (!Notify.needsPermission)
	{
		if ($('#toggle-notifications').prop('checked'))
		{
			var myNotification = new Notify('New Task Added!', {
				body: 'A new task was added. Check the Buzzr! window for details.'
			});
			myNotification.show();
		}
	}
	else if (Notify.isSupported())
	{
		Notify.requestPermission(onPermissionGranted, onPermissionDenied);
	}
}

function onPermissionGranted()
{
	console.log('Permission has been granted by the user');
}

function onPermissionDenied()
{
	console.warn('Permission has been denied by the user');
}
