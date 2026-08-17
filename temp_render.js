const { createCanvas } = require('canvas');
const fs = require('fs');

const rawData = fs.readFileSync('temp_quote8.json', 'utf8');
const data = JSON.parse(rawData);

const canvas = createCanvas(800, 500);
const ctx = canvas.getContext('2d');

ctx.fillStyle = '#090d16';
ctx.fillRect(0, 0, canvas.width, canvas.height);

const points = data.points || [];
const racks = data.racks || [];
const obstacles = data.obstacles || [];
const scale = data.currentScale || 0.12;

if (points.length === 0) {
    ctx.fillStyle = '#ffffff';
    ctx.fillText("NO POINTS", 50, 50);
} else {
    let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
    points.forEach(p => {
        if (p.x < minX) minX = p.x;
        if (p.x > maxX) maxX = p.x;
        if (p.y < minY) minY = p.y;
        if (p.y > maxY) maxY = p.y;
    });

    const centerX = (minX + maxX) / 2;
    const centerY = (minY + maxY) / 2;

    const boundsWidth = maxX - minX;
    const boundsHeight = maxY - minY;
    const margin = 60;
    const scaleX = (canvas.width - margin * 2) / boundsWidth;
    const scaleY = (canvas.height - margin * 2) / boundsHeight;
    const finalZoom = Math.min(scaleX, scaleY, 1);

    ctx.save();
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.scale(finalZoom, finalZoom);
    ctx.translate(-centerX, -centerY);

    ctx.strokeStyle = '#475569';
    ctx.lineWidth = 6 / finalZoom;
    ctx.fillStyle = 'rgba(30, 58, 138, 0.15)';
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }
    ctx.closePath();
    ctx.fill();
    ctx.stroke();

    ctx.fillStyle = '#93c5fd';
    ctx.font = 'bold ' + (14 / finalZoom) + 'px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i + 1];
        const distMm = Math.round(Math.hypot(p2.x - p1.x, p2.y - p1.y) / scale);
        
        let cx = (p1.x + p2.x) / 2;
        let cy = (p1.y + p2.y) / 2;
        
        let dx = p2.x - p1.x;
        let dy = p2.y - p1.y;
        let length = Math.hypot(dx, dy);
        let nx = -dy / length;
        let ny = dx / length;
        
        const offset = 25 / finalZoom;
        
        ctx.save();
        ctx.translate(cx + nx * offset, cy + ny * offset);
        let textAngle = Math.atan2(dy, dx);
        if (textAngle > Math.PI / 2 || textAngle < -Math.PI / 2) {
            textAngle += Math.PI;
        }
        ctx.rotate(textAngle);
        ctx.fillText(`${distMm}mm`, 0, 0);
        ctx.restore();
    }

    obstacles.forEach(obs => {
        ctx.fillStyle = 'rgba(239, 68, 68, 0.3)';
        ctx.strokeStyle = '#ef4444';
        ctx.lineWidth = 2 / finalZoom;
        const w = (obs.width || 500) * scale;
        const h = (obs.height || 500) * scale;
        ctx.fillRect(obs.x - w / 2, obs.y - h / 2, w, h);
        ctx.strokeRect(obs.x - w / 2, obs.y - h / 2, w, h);
    });

    racks.forEach(r => {
        ctx.save();
        ctx.translate(r.x, r.y);
        let angle = r.angle || 0;
        if (r.angle === undefined) {
            if (r.isHoriz) {
                angle = r.dir < 0 ? Math.PI : 0;
            } else {
                angle = r.dir < 0 ? -Math.PI / 2 : Math.PI / 2;
            }
        }
        ctx.rotate(angle);

        const singleDepthPx = (r.rackDepth || 1000) * scale;
        const holderPx = (r.isDouble ? (r.holderSize || 200) : 0) * scale;
        const isDouble = r.isDouble || false;
        const depthPx = isDouble ? (singleDepthPx * 2 + holderPx) : singleDepthPx;
        const totalLenPx = r.totalLengthPx || 0;

        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
        ctx.strokeStyle = '#0ea5e9';
        ctx.lineWidth = 4 / finalZoom;
        
        if (isDouble) {
            const topRackY = -depthPx / 2;
            const botRackY = depthPx / 2 - singleDepthPx;
            ctx.fillRect(0, topRackY, totalLenPx, singleDepthPx);
            ctx.strokeRect(0, topRackY, totalLenPx, singleDepthPx);
            ctx.fillRect(0, botRackY, totalLenPx, singleDepthPx);
            ctx.strokeRect(0, botRackY, totalLenPx, singleDepthPx);
        } else {
            ctx.fillRect(0, -depthPx / 2, totalLenPx, depthPx);
            ctx.strokeRect(0, -depthPx / 2, totalLenPx, depthPx);
        }

        const totalLenMm = Math.round(totalLenPx / scale);
        const depthMm = Math.round(depthPx / scale);
        ctx.fillStyle = '#93c5fd';
        ctx.font = 'bold ' + (11 / finalZoom) + 'px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(`${totalLenMm}mm`, totalLenPx / 2, depthPx / 2 + (12 / finalZoom));
        
        ctx.save();
        ctx.translate(-(12 / finalZoom), 0);
        ctx.rotate(-Math.PI / 2);
        ctx.fillText(`${depthMm}mm`, 0, 0);
        ctx.restore();

        ctx.restore();
    });
    ctx.restore();
}

fs.writeFileSync('temp_render.png', canvas.toBuffer());
console.log("Done");
