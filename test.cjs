const puppeteer = require('puppeteer');
(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: '/home/owy/.cache/puppeteer/chrome/linux-150.0.7871.24/chrome-linux64/chrome',
            args: ['--no-sandbox']
        });
        console.log("Success");
        await browser.close();
    } catch (e) {
        console.error(e);
    }
})();
