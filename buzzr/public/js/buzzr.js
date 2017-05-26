var socket = io('/buzzur');
var properLogout = false;

// Clear the current task from everyone
$('#clear-task').click(function(e) {
	socket.emit('instructor-clear-task', {}, function(a) {
		$('#current-task').html('No Task at This Time.');
		updateAttendees(a);
	});
});

// Send out new task to everyone
$('#push-new-task').click(function(e) {
	
	if ($('#task').val().length < 2)
		return false;
	
	pushTask(0);
});

// Send completed to instructor
$('#completed-task').click(function(e){
	clearAndSet('alert-success');
	sendStatus('alert-success');
});

// Send pause to instructor
$('#just-a-min').click(function(e){
	clearAndSet('alert-warning');
	sendStatus('alert-warning');
});

// Send help to instructor
$('#ack-help').click(function(e){
	clearAndSet('alert-danger');
	sendStatus('alert-danger');
});

// Save disable notifications
$('#turn-off-notifications').click(function(e){
	socket.emit('save-profile', [{ name: 'notifications', value: !this.checked }]);
});

// Received new task
socket.on('new-task', function(taskText) {
	clearAndSet();
	
	if (!$.isEmptyObject(taskText))
	{
		$('#current-task').html(taskText);
		$('#task-buttons').show();

		if ($('#audiotag1').length)
			$('#audiotag1')[0].play();
		
		doNotification();
	}
});

// Received clear task
socket.on('clear-task', function(msg) {
	clearAndSet();
	$('#current-task').html('No Task at This Time.');
	$('#task-buttons').hide();
});

// Received an attendee has joined
socket.on('update-attendees', function(msg) {
	updateAttendees(msg);
});

// Received an attendee has updated their status
socket.on('update-task-status', function(msg) {
	
	var dn = '#attendee-' + msg.attendeeid;
	
	// remove any previous status
	$(dn).removeClass('alert-success alert-warning alert-danger');
	
	// Update attendee status
	$(dn).addClass(msg.status);

	// Sort the list to put alert-success at the bottom
	if (msg.status == 'alert-success') {
		p = $(dn).detach();
		$('#attendees-list').append(p);
	}
});

// Warn before navigating away
/*
$(window).bind('beforeunload', function(e) {
	if (properLogout)
		return 'Please keep this window open to participate in exercises and polls.';
	return false;
});
*/

// HELPERS ===================================

// pushTask accepts either a saved taskId or 0 as parameter
// On 0, the value of the text box is used. > 0 does a DB
// lookup and pushes that taskId
function pushTask(tid)
{
	socket.emit('instructor-new-task', {
			savedTaskId: tid,
			taskText: $('#task').val(),
			saveTask: $('#saveTask').prop("checked"),
			id: instructorid
		},
		function(msg, a) {
			$('#current-task').html(msg.taskText);
			$('#tasks').show();
			$('#taskbuttons').show();
			
			updateAttendees(a);
		}
	);
}

function sendStatus(s)
{
	socket.emit('task-status', { attendeeid: attendeeid, status: s });
	return false;
}

function clearAndSet(c)
{
	$('#current-task').removeClass('alert-success alert-warning alert-danger');
	if (c !== undefined || c != '')
	{
		$('#current-task').addClass(c);
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
		$.each(msg, function(aid, data) {
			var $s = $('<span>', {
				class: 'glyphicon glyphicon-user'
			});

			var $d = $('<div>', {
				id: 'attendee-' + aid,
				text: (data.firstName + ' ' + data.lastInitial + '.'),
				class: 'alert ' + (data.status || '')
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
		return;
	
	// Do we have permission and not disabled?
	if (!Notify.needsPermission && ! $('#turn-off-notifications').prop('checked'))
	{
		var myNotification = new Notify('New Task Added!', {
			body: 'A new task was added. Check the Buzzr! window for details.'
		});
		myNotification.show();
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
