const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // go to login page and login
    await page.goto('http://localhost:8001/login');
    // Assuming login has username and password fields. Wait, Vendor login!
    // I can just set a session cookie manually, but it's PHP session.
    // Let's modify VendorController to bypass auth for a moment so puppeteer can load it.
    await browser.close();
})();
