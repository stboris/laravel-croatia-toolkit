# Security Policy

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a vulnerability

Please **do not** report security vulnerabilities through public GitHub issues.

Instead, report them privately via one of:

- GitHub's private vulnerability reporting: use the
  [**Report a vulnerability**](https://github.com/stboris/laravel-croatia-toolkit/security/advisories/new)
  button on this repository's Security tab
- Email: [boris.stiner@gmail.com](mailto:boris.stiner@gmail.com)

Please include a description of the issue, steps to reproduce, and the affected
version. You will receive a response within **72 hours**. If the issue is
confirmed, a fix will be released as soon as possible and you will be credited
in the release notes (unless you prefer to remain anonymous).

## Scope

This package performs outbound HTTP requests to third-party services (Sudski
registar for company lookups, HNB for exchange rates). Reports about how this
package handles responses from those services — parsing, validation, error
handling, or data exposure — are in scope. The availability or correctness of
the upstream services themselves is not.
