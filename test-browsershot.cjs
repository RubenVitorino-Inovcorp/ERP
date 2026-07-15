const request = {"url":"file:///tmp/dummy.html","action":"pdf","options":{"args":[],"viewport":{"width":800,"height":600},"displayHeaderFooter":false,"executablePath":"/home/owy/.cache/puppeteer/chrome/linux-150.0.7871.24/chrome-linux64/chrome"}};
const puppeteer = require('puppeteer');
(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: request.options.executablePath,
            args: request.options.args,
            headless: 'new'
        });
        console.log("Success browsershot mock");
        await browser.close();
    } catch (e) {
        console.error(e);
    }
})();
