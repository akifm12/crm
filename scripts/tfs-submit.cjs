#!/usr/bin/env node
/**
 * tfs-submit.cjs  <url> <responseKey> <pdfOutputPath>
 * responseKey: no_match | confirmed_match | partial_match
 */

'use strict';

const NODE_PATH = process.env.NODE_PATH || '/usr/lib/node_modules';
const puppeteer = (() => {
    const paths = ['puppeteer', NODE_PATH + '/puppeteer', '/usr/lib/node_modules/puppeteer', '/usr/local/lib/node_modules/puppeteer'];
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

function debug(msg, data) {
    process.stderr.write('DEBUG ' + msg + (data ? ': ' + JSON.stringify(data) : '') + '\n');
}

// Click an element via JS — bypasses Puppeteer's visibility/pointer checks
async function jsClick(page, selector) {
    return page.evaluate((sel) => {
        const el = document.querySelector(sel);
        if (el) { el.click(); return true; }
        return false;
    }, selector);
}

// Wait for navigation OR for a selector to appear — whichever comes first
// Handles both full-page navigation and AJAX/SPA page transitions
async function waitForPageChange(page, nextSelector, timeout = 35000) {
    return Promise.race([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout }).catch(() => 'nav-timeout'),
        page.waitForSelector(nextSelector, { timeout }).catch(() => 'selector-timeout'),
    ]);
}

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: '/usr/bin/google-chrome-stable',
            headless: true,
            args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu', '--no-zygote'],
        });

        const page = await browser.newPage();
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        // ── Step 1: Load the form ────────────────────────────────────────────
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });

        debug('page1', { title: await page.title(), url: page.url() });

        const bodyText1 = await page.evaluate(() => document.body.innerText);
        if (/already submitted/i.test(bodyText1)) {
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Already submitted' }));
            return;
        }

        // ── Step 2: Select the answer ────────────────────────────────────────
        const keywords = KEYWORDS[responseKey] || KEYWORDS.no_match;

        const formState = await page.evaluate((keywords) => {
            const info = { selects: [], buttons: [] };
            document.querySelectorAll('form select').forEach(sel => {
                info.selects.push({ name: sel.name, options: Array.from(sel.options).map(o => ({ text: o.text, value: o.value })) });
            });
            document.querySelectorAll('input[type=submit], button[type=submit], button').forEach(b => {
                info.buttons.push({ tag: b.tagName, name: b.name, value: b.value, text: b.innerText?.trim() });
            });

            // Select the matching option
            for (const sel of document.querySelectorAll('form select')) {
                for (const opt of sel.options) {
                    if (keywords.some(kw => opt.text.toLowerCase().includes(kw))) {
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
            return info;
        }, keywords);

        debug('form state', formState);

        if (!formState.selected) {
            await page.screenshot({ path: '/tmp/tfs-debug-noselect.png', fullPage: true });
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'No select/answer found on page 1 — screenshot at /tmp/tfs-debug-noselect.png', debug: formState }));
            return;
        }

        // ── Step 3: Click Continue and wait for page 2 ───────────────────────
        // Set up race BEFORE clicking so we don't miss a fast navigation
        const afterContinue = waitForPageChange(page, '[name="SubmitButton"][value="Submit"]');

        const continueClicked = await page.evaluate(() => {
            let btn = document.querySelector('[name="SubmitButton"][value="Continue"]');
            if (!btn) btn = Array.from(document.querySelectorAll('input[type=submit], button'))
                                 .find(b => /continue/i.test(b.value + ' ' + b.innerText));
            if (!btn) return null;
            btn.click();
            return btn.value || btn.innerText?.trim();
        });

        debug('continue clicked', continueClicked);

        if (!continueClicked) {
            await page.screenshot({ path: '/tmp/tfs-debug-nocontinue.png', fullPage: true });
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Continue button not found — screenshot at /tmp/tfs-debug-nocontinue.png', debug: formState }));
            return;
        }

        await afterContinue;
        debug('page2', { title: await page.title(), url: page.url() });

        // ── Step 4: Click Submit and wait for confirmation ───────────────────
        const submitClicked = await page.evaluate(() => {
            let btn = document.querySelector('[name="SubmitButton"][value="Submit"]');
            if (!btn) btn = Array.from(document.querySelectorAll('input[type=submit], button'))
                                 .find(b => /^submit$/i.test(b.value + ' ' + b.innerText?.trim()));
            if (!btn) return null;
            btn.click();
            return btn.value || btn.innerText?.trim();
        });

        debug('submit clicked', submitClicked);

        if (!submitClicked) {
            await page.screenshot({ path: '/tmp/tfs-debug-nosubmit.png', fullPage: true });

            // Log what IS on page 2 so we know what to look for
            const page2html = await page.evaluate(() => document.body.innerHTML.slice(0, 2000));
            debug('page2 html excerpt', { html: page2html });

            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Submit button not found on page 2 — screenshot at /tmp/tfs-debug-nosubmit.png' }));
            return;
        }

        // Wait for the Submit button to disappear — confirms the form processed
        await page.waitForFunction(
            () => !document.querySelector('[name="SubmitButton"][value="Submit"]'),
            { timeout: 30000 }
        ).catch(() => {}); // if it never disappears, continue anyway

        // Give AJAX content a moment to render
        await new Promise(r => setTimeout(r, 3000));

        // ── Step 5: Verify and capture PDF ───────────────────────────────────
        const finalText = await page.evaluate(() => document.body.innerText);
        const finalHtml = await page.evaluate(() => document.body.innerHTML);

        // Success: either thank-you text found, OR the Submit button is gone
        const submitGone = !await page.$('[name="SubmitButton"][value="Submit"]');
        const success    = submitGone || /thank|submitted|success|received/i.test(finalText);

        debug('final page', { success, submitGone, url: page.url(), excerpt: finalText.slice(0, 300) });

        if (pdfPath) {
            await page.pdf({ path: pdfPath, format: 'A4', printBackground: true });
        }

        await browser.close();
        console.log(JSON.stringify({
            success,
            message:  success ? 'Submitted successfully' : 'Unexpected response — check snapshot',
            selected: formState.selected,
        }));

    } catch (err) {
        if (browser) await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
        process.exit(1);
    }
})();
