# Changelog


## 1.0.1 - 2026-10-23

- Rename the Composer package to `stoneholl/magento2-customer-local-time` and replace its corresponding legacy `stoneholl/module-customer-local-time` identity at `self.version`.
- Require the canonical Core package from version 1.0.1 and update installation/migration documentation.
- Keep Magento module identifiers, configuration, storage and licensing unchanged.

## 1.0.0

- Add a native Time Information section below Account Information with AM/PM current customer and store clocks.
- Add Client Order Date directly below Order Date, converted using the creation instant and historical timezone rules.
- Provide independent section, current customer row, current store row and order-date display switches at Default scope.
- Estimate customer timezone only from physical shipping or virtual billing country with exactly one installed IANA zone.
- Keep ambiguous/missing sources unavailable; never infer location from IP, city, postcode or customer profiles.
- Enforce native/module view ACLs before feature reads, with bounded address lookups and escaped output.
- Require offline Core ^1.0; keep Connector optional and introduce no persistence, schema, networking or telemetry.
- Validate native Magento Open Source 2.3.7-p4/PHP 7.4.33 and 2.4.9/PHP 8.5.9; PHP 8.0 syntax/grammar only.
