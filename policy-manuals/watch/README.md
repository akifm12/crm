# Regulatory watch

A daily check of the official sources behind the policy manuals. It reports changes to the admin
page **Tools > Policy Manuals > Regulatory updates**. It never edits a manual.

Runs on the administrator's PC, because MoET, the National Committee and FATF refuse server traffic
and need a real browser.

| Source | Method |
|---|---|
| MoET AML page (legislation, circulars, guidance) | HTTP, compares the list of documents and their file timestamps |
| MoET responsible sourcing page | HTTP, compares the content and reference documents |
| EOCN TFS guidance | HTTP, compares the PDF |
| National Committee high-risk page | headless Chrome, compares the FATF links it publishes |
| FATF call for action and increased monitoring | headless Chrome, finds the latest publications and compares the country lists |
| EU CAHRA list | quarterly reminder only (the site sits behind a terms-of-use page, so it is checked by hand) |

## Install (done on 4 Nov 2026)

1. Copy `watch.cjs` to `C:\Users\akif\.policy-watch\`.
2. Create `config.json` there: `{ "endpoint": "https://bluearrow.ae/api/v1/policy-watch", "token": "<random>" }`.
3. Put the same token in the server's `.env` as `POLICY_WATCH_TOKEN`.
4. Windows Task Scheduler task **BlueArrow Policy Watch**: daily 07:30, run `node watch.cjs` in that folder,
   "start when available" so a missed run happens when the PC is next on.

`node watch.cjs --dry` shows what would be reported without sending or saving anything.
`state.json` (next to the script) is the last known state; delete a source's entry to re-baseline it.
The admin page warns if no check has reported for 48 hours or a source cannot be read.
