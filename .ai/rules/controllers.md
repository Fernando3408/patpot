---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Public registration and protected ERP routes
ERP routes require the `auth` middleware. Login and registration remain guest-only; registration authenticates the new user immediately. Keep authentication endpoints rate-limited.
