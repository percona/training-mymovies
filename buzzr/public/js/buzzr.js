var socket = io('/buzzur');

// Clear the current task from everyone
$('#clear-task').click(function(e) {
	
	socket.emit('instructor-clear-task', {}, function(msg, a) {
		
		$('#current-task').html('No Task at This Time.');
		
		updateAttendees(a);
	});
});

// Send out new task to everyone
$('#push-new-task').click(function(e) {
	
	if ($('#task').val().length < 2)
		return false;
	
	socket.emit('instructor-new-task', {
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

// Received new task
socket.on('new-task', function(taskText) {
	clearAndSet();
	$('#current-task').html(taskText);
	
	if (!$.isEmptyObject(taskText))
		$('#task-buttons').show();
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
});

// Warn before navigating away
$(window).bind('beforeunload', function(e) {
	var msg = 'Please keep this window open to participate in exercises and polls.';
	e.returnValue = msg;
	return msg;
});

// HELPERS ===================================

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
			class: 'alert ' + data.status
		});

		$d.prepend($s);
		$('#attendees-list').append($d);
		});
	}
}