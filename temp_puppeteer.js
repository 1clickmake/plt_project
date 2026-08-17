const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // Login
    await page.goto('http://localhost:8001/login');
    // Assuming we don't know the exact login details, let's just bypass auth by setting session cookie if possible?
    // Wait, let's look at the database to see the user's login.
    // Or I can modify VendorController temporarily to bypass auth for localhost?
    await browser.close();
})();
