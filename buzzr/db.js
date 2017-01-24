var sqlite3 = require('sqlite3');

var db = new sqlite3.cached.Database('buzzr.db');

db.serialize(function() {
	
	db.run("CREATE TABLE IF NOT EXISTS instructors ("
		+ "googleid TEXT)");
	
	db.run("CREATE TABLE IF NOT EXISTS savedTasks ("
		+ "taskId INTEGER PRIMARY KEY AUTOINCREMENT,"
		+ "googleid TEXT,"
		+ "taskText TEXT,"
		+ "sorder INTEGER)");
	
});

module.exports = db;
