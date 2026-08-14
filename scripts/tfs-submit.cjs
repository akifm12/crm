#!/usr/bin/env node
/**
 * tfs-submit.cjs  <url> <responseKey> <pdfOutputPath>
 *
 * responseKey: no_match | confirmed_match | partial_match
 *
 * Drives the UAEIEC TFS survey form through headless Chrome (Puppeteer),
 * bypassing the F5 bot-detection JS challenge that blocks plain HTTP clients.
 * Saves a PDF of the confirmation page and prints JSON result to stdout.
 */

'use strict';

const NODE_PATH = process.env.NODE_PATH || '/usr/lib/node_modules';
const puppeteer = (() => {
    const paths = [
        'puppeteer',
        NODE_PATH + '/puppeteer',
        '/usr/lib/node_modules/puppeteer',
        '/usr/local/lib/node_modules/puppeteer',
    ];
    const errors = [];
    for (const p of paths) {
        try { return require(p); } catch (e) { errors.push(p + ': ' + e.message); }
    }
    console.log(JSON.stringify({ success: false, message: 'puppeteer load failed', errors }));
    process.exit(1);
})();

const [,, url, responseKey, pdfPath] = process.argv;

if (!url || !responseKey) {
    console.log(JSON.stringify({ success: false, message: 'Usage: tfs-submit.cjs <url> <responseKey> <pdfPath>' }));
    process.exit(1);
}

const KEYWORDS = {
    no_match:        ['no match', 'not found', 'no match identified', 'none identified'],
    confirmed_match: ['confirmed match', 'confirmed', 'match found', 'yes'],
    partial_match:   ['partial match', 'partial'],
};

// Click via JS — avoids "not clickable" errors from off-screen or overlay-covered elements
async function jsClick(page, selector) {
    const found = await page.evaluate((sel) => {
        const el = document.querySelector(sel);
        if (!el) return false;
        el.click();
        return true;
    }, selector);
    return found;
}

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: '/usr/bin/google-chrome-stable',
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-zygote',
            ],
        });

        const page = await browser.newPage();
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        // ── Step 1: Load the form (Chrome handles the F5 JS challenge) ──────
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });

        // Debug: log page title and URL after load
        const pageTitle = await page.title();
        const pageUrl   = page.url();
        process.stderr.write('DEBUG page1: ' + JSON.stringify({ title: pageTitle, url: pageUrl }) + '\n');

        // Check if already submitted
        const bodyText1 = await page.evaluate(() => document.body.innerText);
        if (/already submitted/i.test(bodyText1)) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Already submitted' }));
            return;
        }

        // ── Step 2: Select the answer ────────────────────────────────────────
        const keywords = KEYWORDS[responseKey] || KEYWORDS.no_match;

        const selected = await page.evaluate((keywords) => {
            const selects = document.querySelectorAll('form select');
            const info = { selectCount: selects.length, selected: null };
            for (const sel of selects) {
                for (const opt of sel.options) {
                    const text = opt.text.toLowerCase().trim();
                    if (keywords.some(kw => text.includes(kw))) {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        info.selected = { name: sel.name, text: opt.text, value: opt.value };
                        return info;
                    }
                }
                // Fallback: first non-empty option
                for (const opt of sel.options) {
                    if (opt.value) {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        info.selected = { name: sel.name, text: opt.text, value: opt.value, fallback: true };
                        return info;
                    }
                }
            }
            // Debug: list all buttons/inputs so we can see what's on the page
            const btns = Array.from(document.querySelectorAll('input[type=submit], button')).map(b => ({
                tag: b.tagName, name: b.name, value: b.value, text: b.innerText
            }));
            info.buttons = btns;
            return info;
        }, keywords);

        process.stderr.write('DEBUG page1 form: ' + JSON.stringify(selected) + '\n');

        if (!selected || !selected.selected) {
            // Save screenshot for inspection
            await page.screenshot({ path: '/tmp/tfs-debug-page1.png', fullPage: true });
            await browser.close();
            console.log(JSON.stringify({
                success: false,
                message: 'No select element found — screenshot saved to /tmp/tfs-debug-page1.png',
                debug: selected,
            }));
            return;
        }

        // ── Step 3: Click "Continue" via JS ──────────────────────────────────
        const continueClicked = await page.evaluate(() => {
            // Try value="Continue" first, then any submit/button with Continue text
            let btn = document.querySelector('[name="SubmitButton"][value="Continue"]');
            if (!btn) btn = Array.from(document.querySelectorAll('input[type=submit], button'))
                .find(b => /continue/i.test(b.value || b.innerText));
            if (!btn) return null;
            btn.click();
            return btn.value || btn.innerText;
        });

        process.stderr.write('DEBUG continue clicked: ' + JSON.stringify(continueClicked) + '\n');

        if (!continueClicked) {
            await page.screenshot({ path: '/tmp/tfs-debug-nocontinue.png', fullPage: true });
            await browser.close();
            console.log(JSON.stringify({
                success: false,
                message: 'No Continue button found — screenshot saved to /tmp/tfs-debug-nocontinue.png',
            }));
            return;
        }

        await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 30000 });

        const title2 = await page.title();
        process.stderr.write('DEBUG page2 title: ' + title2 + '\n');

        // ── Step 4: Click "Submit" via JS ─────────────────────────────────────
        const submitClicked = await page.evaluate(() => {
            let btn = document.querySelector('[name="SubmitButton"][value="Submit"]');
            if (!btn) btn = Array.from(document.querySelectorAll('input[type=submit], button'))
                .find(b => /submit/i.test(b.value || b.innerText));
            if (!btn) return null;
            btn.click();
            return btn.value || btn.innerText;
        });

        process.stderr.write('DEBUG submit clicked: ' + JSON.stringify(submitClicked) + '\n');

        if (!submitClicked) {
            await page.screenshot({ path: '/tmp/tfs-debug-nosubmit.png', fullPage: true });
            await browser.close();
            console.log(JSON.stringify({
                success: false,
                message: 'No Submit button found on page 2 — screenshot saved to /tmp/tfs-debug-nosubmit.png',
            }));
            return;
        }

        await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 30000 });

        // ── Step 5: Verify and capture PDF ───────────────────────────────────
        const finalText = await page.evaluate(() => document.body.innerText);
        const success   = /thank|submitted|success|received/i.test(finalText);

        process.stderr.write('DEBUG final page: ' + JSON.stringify({ success, excerpt: finalText.slice(0, 200) }) + '\n');

        if (pdfPath) {
            await page.pdf({ path: pdfPath, format: 'A4', printBackground: true });
        }

        await browser.close();
        console.log(JSON.stringify({
            success,
            message:  success ? 'Submitted successfully' : 'Unexpected confirmation page — check snapshot',
            selected: selected.selected,
        }));

    } catch (err) {
        if (browser) await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
        process.exit(1);
    }
})();
