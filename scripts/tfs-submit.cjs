#!/usr/bin/env node
/**
 * tfs-submit.cjs — drives a UAE EOCN (TFS) unique survey link in headless Chrome.
 *
 * Answering a TFS survey "match" starts an inquiry at the Ministry, so this script
 * never decides an answer. It only ever selects an option whose text EXACTLY equals
 * the answer text it is given (a human chose it), refuses if that text matches zero
 * or several options, and verifies the page shows the answer before the final click.
 *
 *   preview <url>
 *       Read only. Returns state (form | used | unknown), the page text and the
 *       answer options on the form. Clicks nothing.
 *
 *   prepare <url> <answerText> <pdfPath>
 *       Selects the answer and saves a PDF of the filled form. Clicks nothing, so
 *       nothing is sent; a person looks at the result before anything is submitted.
 *
 *   submit <url> <answerText> <pdfAfterPath> <pdfBeforePath>
 *       Selects the answer, continues to the review page if there is one, verifies
 *       the answer is what the page now shows (aborts without submitting if not),
 *       saves a "before submit" PDF, submits, then saves the confirmation PDF.
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

function debug(msg, data) {
    process.stderr.write('DEBUG ' + msg + (data ? ': ' + JSON.stringify(data) : '') + '\n');
}

// A failed debug screenshot must never hide the real result.
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
    const info = await page.evaluate(() => {
        const selects = Array.from(document.querySelectorAll('form select'));
        return {
            title:        document.title || '',
            text:         document.body ? document.body.innerText : '',
            selectCount:  selects.length,
            options:      selects.length
                ? Array.from(selects[0].options).filter(o => o.value !== '').map(o => o.text.replace(/\s+/g, ' ').trim())
                : [],
        };
    });
    let state = 'unknown';
    if (info.selectCount > 0) {
        state = 'form';
    } else if (/survey completed|already (been )?submitted|thank you|^success/i.test(info.title + ' ' + info.text.slice(0, 800))) {
        state = 'used';
    }
    return { ...info, state };
}

/**
 * Select the option whose text equals answerText, exactly (whitespace and case
 * normalised). There must be exactly one select on the form and exactly one matching
 * option; anything else selects nothing. Re-reads the select afterwards to confirm.
 */
async function selectExact(page, answerText) {
    return page.evaluate((answerText) => {
        const norm = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
        const selects = Array.from(document.querySelectorAll('form select'));
        if (selects.length !== 1) return { ok: false, reason: 'expected exactly one answer field, found ' + selects.length };

        const sel = selects[0];
        const matches = Array.from(sel.options).filter(o => o.value !== '' && norm(o.text) === norm(answerText));
        if (matches.length !== 1) return { ok: false, reason: matches.length + ' options match "' + answerText + '" (need exactly 1)' };

        sel.value = matches[0].value;
        sel.dispatchEvent(new Event('change', { bubbles: true }));

        const chosen = sel.options[sel.selectedIndex];
        if (!chosen || norm(chosen.text) !== norm(answerText)) return { ok: false, reason: 'the selection did not take effect' };
        return { ok: true, text: chosen.text.replace(/\s+/g, ' ').trim() };
    }, answerText);
}

// After Continue the page must still show the answer we chose: either an answer
// field that is still set to it, or the answer text on the page.
async function pageShowsAnswer(page, answerText) {
    return page.evaluate((answerText) => {
        const norm = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
        const want = norm(answerText);
        const inSelect = Array.from(document.querySelectorAll('select')).some(sel => {
            const o = sel.options[sel.selectedIndex];
            return o && norm(o.text) === want;
        });
        return inSelect || norm(document.body ? document.body.innerText : '').includes(want);
    }, answerText);
}

async function preview(url) {
    const { browser, page } = await launch();
    try {
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });
        const c = await classify(page);
        debug('preview', { title: c.title, state: c.state, textLength: c.text.length, options: c.options.length });
        await browser.close();
        console.log(JSON.stringify({
            success: true,
            state:   c.state,
            title:   c.title,
            text:    c.text.slice(0, 80000),
            text_length:  c.text.length,
            select_count: c.selectCount,
            options:      c.options,
        }));
    } catch (err) {
        await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
    }
}

async function prepare(url, answerText, pdfPath) {
    const { browser, page } = await launch();
    try {
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });
        const c = await classify(page);

        if (c.state === 'used') {
            await browser.close();
            console.log(JSON.stringify({ success: false, state: 'used', message: 'This link was already submitted (TFS shows a completed page).' }));
            return;
        }
        if (c.state !== 'form') {
            await browser.close();
            console.log(JSON.stringify({ success: false, state: c.state, message: 'The page does not show an answer form.' }));
            return;
        }

        const sel = await selectExact(page, answerText);
        if (!sel.ok) {
            const shot = await safeShot(page, 'prepare-noselect');
            await browser.close();
            console.log(JSON.stringify({ success: false, state: 'form', options: c.options, message: 'Could not select the answer safely: ' + sel.reason + (shot ? ' (screenshot: ' + shot + ')' : '') }));
            return;
        }

        if (pdfPath) await page.pdf(pdfOptions(pdfPath, url, 'FORM FILLED — NOT YET SUBMITTED'));

        await browser.close();
        // Nothing was clicked: the form was filled in a throwaway browser and discarded.
        console.log(JSON.stringify({ success: true, state: 'form', selected_text: sel.text, options: c.options, message: 'Form filled for review. Nothing was submitted.' }));
    } catch (err) {
        await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
    }
}

async function submit(url, answerText, pdfAfter, pdfBefore) {
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
        if (c.state !== 'form') {
            await browser.close();
            console.log(JSON.stringify({ success: false, state: c.state, message: 'The page does not show an answer form. Nothing was submitted.' }));
            return;
        }

        const sel = await selectExact(page, answerText);
        if (!sel.ok) {
            const shot = await safeShot(page, 'submit-noselect');
            await browser.close();
            console.log(JSON.stringify({ success: false, state: 'unverified', options: c.options, message: 'Could not select the answer safely, so nothing was submitted: ' + sel.reason + (shot ? ' (screenshot: ' + shot + ')' : '') }));
            return;
        }

        // Continue to the review page if the survey has one
        const continueClicked = await page.evaluate(() => {
            const btn = document.querySelector('[name="SubmitButton"][value="Continue"]');
            if (!btn) return null;
            btn.click();
            return btn.value;
        });
        if (continueClicked) {
            await page.waitForSelector('[name="SubmitButton"][value="Submit"]', { timeout: 15000 }).catch(() => {});
            debug('continue clicked', { title: await page.title() });
        }

        // Last check before the irreversible click: the page must show the answer we chose.
        if (!(await pageShowsAnswer(page, answerText))) {
            const shot = await safeShot(page, 'submit-unverified');
            await browser.close();
            console.log(JSON.stringify({ success: false, state: 'unverified', message: 'The page no longer shows the answer "' + answerText + '", so nothing was submitted.' + (shot ? ' (screenshot: ' + shot + ')' : '') }));
            return;
        }

        // Evidence 1: exactly what is on screen right before Submit
        if (pdfBefore) await page.pdf(pdfOptions(pdfBefore, url, 'BEFORE SUBMIT'));

        const submitClicked = await page.evaluate(() => {
            let btn = document.querySelector('[name="SubmitButton"][value="Submit"]');
            if (!btn) btn = Array.from(document.querySelectorAll('input[type=submit], button'))
                                 .find(b => /^submit$/i.test((b.value || '') + ' ' + (b.innerText || '').trim()));
            if (!btn) return null;
            btn.click();
            return btn.value || btn.innerText?.trim();
        });
        debug('submit clicked', submitClicked);

        if (!submitClicked) {
            const shot = await safeShot(page, 'nosubmit');
            await browser.close();
            console.log(JSON.stringify({ success: false, message: 'Submit button not found, nothing was submitted' + (shot ? ' (screenshot: ' + shot + ')' : '') }));
            return;
        }

        // The Submit button disappearing confirms the form processed
        await page.waitForFunction(
            () => !document.querySelector('[name="SubmitButton"][value="Submit"]'),
            { timeout: 30000 }
        ).catch(() => {});
        await new Promise(r => setTimeout(r, 3000));

        const finalText  = await page.evaluate(() => document.body.innerText);
        const submitGone = !await page.$('[name="SubmitButton"][value="Submit"]');
        const success    = submitGone || /thank|submitted|success|received|completed/i.test(finalText);
        debug('final page', { success, submitGone, excerpt: finalText.slice(0, 300) });

        // Evidence 2: the confirmation page
        if (pdfAfter) await page.pdf(pdfOptions(pdfAfter, url, 'AFTER SUBMIT'));

        await browser.close();
        console.log(JSON.stringify({
            success,
            message:       success ? 'Submitted successfully' : 'Unexpected response — check the evidence PDF',
            selected_text: sel.text,
        }));

    } catch (err) {
        await browser.close().catch(() => {});
        console.log(JSON.stringify({ success: false, message: err.message }));
    }
}

(async () => {
    const [,, mode, ...args] = process.argv;

    if (mode === 'preview' && args[0])                  return preview(args[0]);
    if (mode === 'prepare' && args[0] && args[1])       return prepare(args[0], args[1], args[2]);
    if (mode === 'submit'  && args[0] && args[1])       return submit(args[0], args[1], args[2], args[3]);

    console.log(JSON.stringify({ success: false, message: 'Usage: tfs-submit.cjs preview <url> | prepare <url> <answerText> <pdf> | submit <url> <answerText> <pdfAfter> <pdfBefore>' }));
})();
