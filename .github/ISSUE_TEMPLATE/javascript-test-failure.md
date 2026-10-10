---
name: "JavaScript Test Failure"
about: "Automated issue created when the JavaScript tests fail"
title: "JavaScript Test Failure - {{ env.FAILURE_STAGE }}"
labels: ["bug", "testing", "javascript", "automated"]
assignees: []
---

## JavaScript Test Failure

The JavaScript test job failed. A setup or installation failure does not mean the plugin is broken.

**Failure stage:** `{{ env.FAILURE_STAGE }}`

### Details

- **Node.js Version:** {{ env.NODE_VERSION }}
- **Test Date:** {{ date | date('YYYY-MM-DD HH:mm:ss') }}
- **Workflow Run:** [View detailed logs]({{ env.WORKFLOW_URL }})
- **Run ID:** {{ env.RUN_ID }}

### What the job runs

`npm test` runs `tests/js/*.test.mjs` with the Node.js test runner. The plugin has no script of its own; the tests cover the piece it adds to WP Store Locator's info window template. `tests/js/render-info-window.php` builds the template and the store data with the plugin's own filters, and the tests compile the template with Underscore and read the result with jsdom.

### Next Steps

1. Find the failed stage above and read that step's log.
2. If a test failed, check whether the template or the store data changed.
3. Fix the code or the test, then re-run the workflow.

This issue was automatically created by the CI/CD pipeline.
