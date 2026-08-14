#!/usr/bin/env node
/**
 * tfs-submit.js  <url> <responseKey> <pdfOutputPath>
 *
 * responseKey: no_match | confirmed_match | partial_match
 *
 * Drives the UAEIEC TFS survey form through headless Chrome (Puppeteer),
 * bypassing the F5 bot-detection JS challenge that blocks plain HTTP clients.
 * Saves a PDF of the confirmation page and prints JSON result to stdout.
 */

'use strict';

// Locate Puppeteer from the global node_modules set by Browsershot
const NODE_PATH = process.env.NODE_PATH || '/usr/lib/node_modules';
const puppeteer = (() => {
    try { return require('puppeteer'); } catch (_) {}
    try { return require(NODE_PATH + '/puppeteer'); } catch (_) {}
    console.log(JSON.stringify({ success: false, message: 'puppeteer not found in ' + NODE_PATH }));
    process.exit(1);
})();

const [,, url, responseKey, pdfPath] = process.argv;

if (!url || !responseKey) {
    console.log(JSON.stringify({ success: false, message: 'Usage: tfs-submit.js <url> <responseKey> <pdfPath>' }));
    process.exit(1);
}

const KEYWORDS = {
    no_match:        ['no match', 'not found', 'no match identified', 'none identified'],
    confirmed_match: ['confirmed match', 'confirmed', 'match found', 'yes'],
    partial_match:   ['partial match', 'partial'],
};

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

        // ── Step 1: Load the form (Chrome handles the F5 JS challenge) ────────
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });

        // Check if already submitted
        const bodyText = await page.evaluate(() => document.body.innerText);
        if (/already submitted/i.test(bodyText)) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Already submitted' }));
            return;
        }

        // ── Step 2: Select the answer ─────────────────────────────────────────
        const keywords = KEYWORDS[responseKey] || KEYWORDS.no_match;

        const selected = await page.evaluate((keywords) => {
            const selects = document.querySelectorAll('form select');
            for (const sel of selects) {
                for (const opt of sel.options) {
                    const text = opt.text.toLowerCase().trim();
                    if (keywords.some(kw => text.includes(kw))) {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        return { selectName: sel.name, optionText: opt.text, optionValue: opt.value };
                    }
                }
                // Fallback: pick first non-empty option
                for (const opt of sel.options) {
                    if (opt.value) {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        return { selectName: sel.name, optionText: opt.text, optionValue: opt.value, fallback: true };
                    }
                }
            }
            return null;
        }, keywords);

        if (!selected) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'No select element found on the form page' }));
            return;
        }

        // ── Step 3: Click "Continue" ──────────────────────────────────────────
        const continueBtn = await page.$('[name="SubmitButton"][value="Continue"]');
        if (!continueBtn) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'No Continue button found' }));
            return;
        }

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 30000 }),
            continueBtn.click(),
        ]);

        // ── Step 4: Click "Submit" ────────────────────────────────────────────
        const submitBtn = await page.$('[name="SubmitButton"][value="Submit"]');
        if (!submitBtn) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'No Submit button found on confirmation page' }));
            return;
        }

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 30000 }),
            submitBtn.click(),
        ]);

        // ── Step 5: Verify success and capture PDF ────────────────────────────
        const finalText = await page.evaluate(() => document.body.innerText);
        const success   = /thank|submitted|success|received/i.test(finalText);

        if (pdfPath) {
            await page.pdf({
                path:            pdfPath,
                format:          'A4',
                printBackground: true,
            });
        }

        await browser.close();
        console.log(JSON.stringify({
            success,
            message:     success ? 'Submitted successfully' : 'Unexpected confirmation page — check snapshot',
            selected,
        }));

    } catch (err) {
        if (browser) await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
        process.exit(1);
    }
})();
