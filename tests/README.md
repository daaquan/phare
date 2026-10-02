# Application tests

Run the suite with `./vendor/bin/pest`. Run an individual area by passing its
file or directory, for example `./vendor/bin/pest tests/Feature/Auth`.
Check test formatting with `./vendor/bin/pint --test tests`.

The suite requires the project's PHP dependencies, the Phalcon extension and
PDO SQLite for database cases. `phpunit.xml` selects the testing environment.
`tests/Pest.php` rebuilds the SQLite schema before each case, including the
administrator flag, two-factor fields and passkeys table. It rejects other
database drivers before running schema operations.

## Coverage priorities

- API registration and login: successful requests, required fields and invalid
  credentials.
- Guest and protected pages: Inertia components and redirects, including security
  settings write endpoints and the broadcasting administration routes.
- Access middleware: guest/authenticated, verified/unverified and admin/non-admin
  decisions, stopping dispatch on denial, and recent/missing/expired password
  confirmation with intended URL preservation.
- Two-factor authentication: all six RFC 6238 SHA1 vectors truncated to six
  digits, step boundaries, malformed input, secret encoding, provisioning URI
  encoding, enabled/pending states and one-time recovery-code consumption.
- Passkeys: discoverable login options, fresh challenges, known base64url vectors,
  missing challenge rejection and challenge consumption on malformed requests.
- Inertia validation errors: empty object serialization and one-time consumption.
- Password reset tokens: creation, incorrect tokens and deletion.

## Remaining gaps

The two posts HTTP cases remain skipped because of the documented Phalcon/SQLite
result-set crash. Recovery-code persistence is replaced with a recording `save()`
in the unit test; it verifies the model logic, not an actual database write.

Authenticated settings writes and complete two-factor challenge flows still need
integration coverage with real persistence. Passkey registration, valid signed
login assertions and credential ownership on deletion need browser or
protocol-level fixtures. Password reset expiry and throttling, email verification
link handling and CSRF enforcement also deserve dedicated cases. There is no
frontend test runner or browser suite configured in this repository.

These tests cover important regressions; passing them does not establish complete
application or branch coverage. No coverage percentage is claimed.
