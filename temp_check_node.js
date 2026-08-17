const fs = require('fs');
const mysql = require('mysql2/promise');

async function check() {
    try {
        const connection = await mysql.createConnection({
            host: 'localhost',
            user: 'root',
            password: '',
            database: 'plt_project'
        });
        const [rows] = await connection.execute('SELECT canvas_data FROM quote_requests WHERE id = 8');
        if (rows.length > 0) {
            const data = rows[0].canvas_data;
            console.log("RAW STRING LENGTH:", data.length);
            console.log("FIRST 500 CHARS:", data.substring(0, 500));
            const parsed = JSON.parse(data);
            console.log("POINTS:", parsed.points ? parsed.points.length : "NO POINTS");
            if (parsed.points && parsed.points.length > 0) {
                console.log("POINT 0:", parsed.points[0]);
            }
            console.log("RACKS:", parsed.racks ? parsed.racks.length : "NO RACKS");
            if (parsed.racks && parsed.racks.length > 0) {
                console.log("RACK 0:", parsed.racks[0]);
            }
        } else {
            console.log("No quote 8 found");
        }
        await connection.end();
    } catch(e) {
        console.error(e);
    }
}
check();
