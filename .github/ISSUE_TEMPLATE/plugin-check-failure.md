---
name: "WordPress Plugin Check Failure"
about: "Automated issue created when WordPress Plugin Check fails"
title: WordPress Plugin Check Failure
labels: bug, plugin-check, automated
assignees: []
---

## WordPress Plugin Check Failure

The WordPress Plugin Check action has identified issues with the Optimizations ACE MC plugin.

### Details

- **Test Date:** {{ date | date('YYYY-MM-DD HH:mm:ss') }}
- **Workflow Run:** [View detailed logs]({{ env.WORKFLOW_URL }})

### Next Steps

This issue was automatically created because WordPress Plugin Check found issues with the plugin. The check performed these tests:

#### Categories

- **Accessibility**: Checks for accessibility compliance issues
- **General**: General WordPress coding standards and best practices
- **Performance**: Tests that identify performance bottlenecks
- **Plugin Repo**: Requirements for the WordPress.org plugin repository
- **Security**: Security-focused checks to identify vulnerabilities

#### Specific Checks

- **i18n_usage**: Proper internationalization usage
- **code_obfuscation**: Potentially obfuscated code
- **direct_db_queries**: Direct database queries that bypass WordPress APIs
- **enqueued_scripts_in_footer**: Scripts that should be enqueued in the footer
- **enqueued_scripts_size**: Excessively large script files
- **enqueued_styles_scope**: Styles that need stronger scoping
- **file_type**: Proper file types and formats
- **late_escaping**: Output that should be escaped later
- **localhost**: References to localhost or development environments
- **no_unfiltered_uploads**: Uploads that should be filtered
- **performant_wp_query_params**: Inefficient `WP_Query` parameters
- **plugin_header_text_domain**: Correct text domain in the plugin header
- **plugin_readme**: Plugin readme file format
- **plugin_review_phpcs**: PHP CodeSniffer checks for WordPress standards
- **plugin_updater**: Plugin update mechanisms
- **trademarks**: Potential trademark violations

#### Recommended Actions

1. Review the workflow logs for specific error messages and warnings.
2. Address each identified issue in the plugin code.
3. Test using the [WordPress Plugin Check tool](https://github.com/WordPress/plugin-check) to verify fixes.
4. Submit a pull request with the necessary changes.

Once all issues are fixed, please close this issue and reference it in the changelog.

### About WordPress Plugin Check

The WordPress Plugin Check tool helps plugin authors create plugins that follow WordPress best practices. It checks for issues related to security, performance, and compatibility to ensure plugins work well in the WordPress ecosystem.
