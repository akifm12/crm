#!/usr/bin/env node
/**
 * tfs-submit.cjs — drives a UAE EOCN (TFS) unique survey link in headless Chrome.
 *
 *   tfs-submit.cjs preview <url>
 *       Opens the link and reads it. Submits NOTHING. Prints the page title, the
 *       page text (so the app can check it against the tenant's clients) and a
 *       state: form | used | unknown.
 *
 *   tfs-submit.cjs submit <url> <responseKey> <pdfAfterPath> [<pdfBeforePath>]
 *       Selects the answer, saves a "before" PDF (the alert with the chosen
 *       answer visible), clicks Continue/Submit, then saves an "after" PDF.
 *       responseKey: no_match | confirmed_match | partial_match
 *
 * The last stdout line is always one JSON object. Debug goes to stderr.
 */

'use strict';

const fs   = require('fs');
const os   = require('os');
const path = require('path');

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

const CHROME = process.env.TFS_CHROME || '/usr/bin/google-chrome-stable';

const KEYWORDS = {
    no_match:        ['no match', 'not found', 'no match identified', 'none identified'],
    confirmed_match: ['confirmed match', 'confirmed', 'match found', 'yes'],
    partial_match:   ['partial match', 'partial'],
};

function debug(msg, data) {
    process.stderr.write('DEBUG ' + msg + (data ? ': ' + JSON.stringify(data) : '') + '\n');
}

// A failed debug screenshot must never hide the real result. These used to go to a
// fixed /tmp path that another OS user already owned, which threw EACCES and turned
// every "already submitted" link into an unexplained failure.
async function safeShot(page, name) {
    try {
        const dir = process.env.TFS_DEBUG_DIR || os.tmpdir();
        fs.mkdirSync(dir, { recursive: true });
        const file = path.join(dir, `tfs-${name}-${process.pid}-${Date.now()}.png`);
        await page.screenshot({ path: file, fullPage: true });
        return file;
    } catch (e) {
        debug('debug screenshot skipped', { error: e.message });
        return null;
    }
}

function pdfOptions(file, url, label) {
    return {
        path: file,
        format: 'A4',
        printBackground: true,
        displayHeaderFooter: true,
        headerTemplate: `
            <div style="font-size:9px;padding:4px 20px;width:100%;box-sizing:border-box;
                        border-bottom:1px solid #ccc;color:#333;font-family:Arial,sans-serif;">
                <strong>${label}</strong> &nbsp; TFS Survey URL:
                <span style="color:#1a56db;word-break:break-all;">${url}</span>
            </div>`,
        footerTemplate: `
            <div style="font-size:8px;padding:4px 20px;width:100%;box-sizing:border-box;
                        color:#999;font-family:Arial,sans-serif;text-align:center;">
                Captured by Blue Arrow Portal &mdash; <span class="date"></span>
            </div>`,
        margin: { top: '55px', bottom: '35px', left: '10px', right: '10px' },
    };
}

async function launch() {
    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu', '--no-zygote'],
    });
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    return { browser, page };
}

// What are we looking at? An open survey has an answer <select>; a link that was
// already used shows a "Success / survey completed" page with no form at all.
async function classify(page) {
    const info = await page.evaluate(() => ({
        title:     document.title || '',
        text:      document.body ? document.body.innerText : '',
        hasSelect: !!document.querySelector('form select'),
    }));
    let state = 'unknown';
    if (info.hasSelect) {
        state = 'form';
    } else if (/survey completed|already (been )?submitted|thank you|^success/i.test(info.title + ' ' + info.text.slice(0, 800))) {
        state = 'used';
    }
    return { ...info, state };
}

async function preview(url) {
    const { browser, page } = await launch();
    try {
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });
        const c = await classify(page);
        debug('preview', { title: c.title, state: c.state, textLength: c.text.length });
        await browser.close();
        console.log(JSON.stringify({
            success: true,
            state:   c.state,
            title:   c.title,
            text:    c.text.slice(0, 80000),
            text_length: c.text.length,
        }));
    } catch (err) {
        await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
    }
}

async function submit(url, responseKey, pdfAfter, pdfBefore) {
    const { browser, page } = await launch();
    try {
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });

        const c = await classify(page);
        debug('page1', { title: c.title, state: c.state });

        if (c.state === 'used') {
            await browser.close();
            console.log(JSON.stringify({ success: false, state: 'used', message: 'This link was already submitted (TFS shows a completed page).' }));
            return;
        }

        const keywords = KEYWORDS[responseKey] || KEYWORDS.no_match;

        // Select the answer
        const formState = await page.evaluate((keywords) => {
            const info = { selects: [], buttons: [] };
            document.querySelectorAll('form select').forEach(sel => {
                info.selects.push({ name: sel.name, options: Array.from(sel.options).map(o => ({ text: o.text, value: o.value })) });
            });
            document.querySelectorAll('input[type=submit], button[type=submit], button').forEach(b => {
                info.buttons.push({ tag: b.tagName, name: b.name, value: b.value, text: b.innerText?.trim() });
            });

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
            const shot = await safeShot(page, 'noselect');
            await browser.close();
            console.log(JSON.stringify({ success: false, state: c.state, message: 'No answer field found on the page' + (shot ? ' (screenshot: ' + shot + ')' : ''), debug: formState }));
            return;
        }

        // Evidence 1: the alert as received, with the chosen answer visible
        if (pdfBefore) {
            await page.pdf(pdfOptions(pdfBefore, url, 'BEFORE SUBMIT'));
        }

        // Continue (review page), then Submit
        const continueClicked = await page.evaluate(() => {
            const btn = document.querySelector('[name="SubmitButton"][value="Continue"]');
            if (!btn) return null;
            btn.click();
            return btn.value;
        });

        if (continueClicked) {
            await page.waitForSelector('[name="SubmitButton"][value="Submit"]', { timeout: 15000 }).catch(() => {});
            debug('continue clicked, now on page 2', { title: await page.title() });
        }

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
            const shot = await safeShot(page, 'nosubmit');
            const page2html = await page.evaluate(() => document.body.innerHTML.slice(0, 2000));
            debug('page2 html excerpt', { html: page2html });
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Submit button not found' + (shot ? ' (screenshot: ' + shot + ')' : '') }));
            return;
        }

        // The Submit button disappearing confirms the form processed
        await page.waitForFunction(
            () => !document.querySelector('[name="SubmitButton"][value="Submit"]'),
            { timeout: 30000 }
        ).catch(() => {});

        await new Promise(r => setTimeout(r, 3000));

        const finalText = await page.evaluate(() => document.body.innerText);
        const submitGone = !await page.$('[name="SubmitButton"][value="Submit"]');
        const success    = submitGone || /thank|submitted|success|received|completed/i.test(finalText);

        debug('final page', { success, submitGone, url: page.url(), excerpt: finalText.slice(0, 300) });

        // Evidence 2: the completion page
        if (pdfAfter) {
            await page.pdf(pdfOptions(pdfAfter, url, 'AFTER SUBMIT'));
        }

        await browser.close();
        console.log(JSON.stringify({
            success,
            message:  success ? 'Submitted successfully' : 'Unexpected response — check snapshot',
            selected: formState.selected,
        }));

    } catch (err) {
        await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
    }
}

(async () => {
    const [,, mode, ...args] = process.argv;

    if (mode === 'preview' && args[0]) return preview(args[0]);
    if (mode === 'submit'  && args[0]) return submit(args[0], args[1] || 'no_match', args[2], args[3]);

    // Backwards compatible with the old positional call: <url> <responseKey> <pdf>
    if (mode && /^https?:/i.test(mode)) return submit(mode, args[0] || 'no_match', args[1], null);

    console.log(JSON.stringify({ success: false, message: 'Usage: tfs-submit.cjs preview <url> | submit <url> <responseKey> <pdfAfter> [<pdfBefore>]' }));
})();
