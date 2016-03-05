exports.genRandString = function() {

    var length = 20;
    var chars = "abcdefghijklmnopqrstuvwxyz ";
    var characters = chars.split('');
    var string = '';
    
    for( var i = 0; i < length; i++ )
    {
        string += characters[Math.floor(Math.random()*characters.length)];
    }
    return string;
};

exports.getRandomInt = function(min, max) {

    return Math.floor(Math.random() * (max - min + 1)) + min;
};

exports.pretty_time = function(ms) {

    if( ms < 1000 ) {
        return ms + 'ms';
    }
    else {
        var seconds = ms / 1000;
        return seconds.toFixed(2) + 's';
    }
};

exports.print_bar = function(time, goal) {

    var output = '';
    var color = "#33FF33";
    var tablewidth = 250;
    var w = time;
    
    if( time > goal * 2 )
    {
    	// red - FF3333
        w = goal * 2;
        color = "#FF3333";
    }
    else if( time > goal )
    {
    	// yellow - FFFF33
        color = "#FFFF33";
    }

    var w = Math.round( w * tablewidth / ( goal * 2));
    var v = tablewidth - w;
    return " \
<table border=1 cellpadding=0 cellspacing=0 width='" + tablewidth + "'> \
<tr> \
<td width='" + w + "' style='background-color:" + color + "'>&nbsp;</td> \
<td width='" + v + "'>&nbsp;</td> \
</tr> \
</table> \
";
};