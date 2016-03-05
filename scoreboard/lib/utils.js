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