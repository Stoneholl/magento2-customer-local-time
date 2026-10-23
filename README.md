# Stoneholl Customer Local Time

Free MIT Magento Admin order helper. Shows current customer/store time and an optional customer-local order creation
date. Offline Stoneholl Core ^1.0 is required; Connector, pairing and paid entitlements are not required.

## Features and settings

Open **Stores > Configuration > Stoneholl > Customer Local Time**, at **Default Config** scope.

| Setting | Default | Behavior |
| --- | --- | --- |
| Show Time Information | No | Separate section below Account Information; current clocks in AM/PM format. |
| Show Current Customer Time | Yes | Customer clock inside that section. |
| Show Current Store Time | Yes | Clock in the order's own store-view timezone inside that section. |
| Show Client Order Date | No | Customer-local creation date/time directly below native Order Date. |

The two current-clock settings appear when Time Information is enabled. Turning both off hides the whole section.
Client Order Date works independently of that section and its row settings. Configuration accepts only native Yes/No
values at Default scope. Website/store-scope writes are rejected.

Current clocks use the same server UTC instant at page load; reload the native page to update them. They do not tick
or poll. Client Order Date converts the UTC creation instant using the estimated zone's rules at that historical
instant, in `M j, Y, g:i:s A` format. It is not checkout, processing or fulfillment duration. Native Order Date remains
unchanged and follows Magento's configured Admin/store timezones.

Grant both **View Customer Local Time** (`Stoneholl_CustomerLocalTime::view`) and native order-view permission
(`Magento_Sales::actions_view`). Configuration permission (`Stoneholl_CustomerLocalTime::config`) does not grant view
access. Disabled/denied placements and missing native order context render no feature payload. For third-party
order-visibility restrictions, verify native current-order authorization before granting restricted operators access.

## Estimate limitations

- Physical orders use only the shipping order-address country; virtual orders use billing. Missing shipping never
  falls back to billing. Live customer profiles/addresses are not queried.
- A country must be valid in Magento and have exactly one installed PHP IANA timezone identifier. Multi-zone countries,
  including the USA, remain unavailable even when their current offsets match. No capital-city or regional guessing.
- An address country does not confirm a customer's current location or the timezone used by their browser at checkout.
  Historical country/zone membership is not reconstructed. Both customer displays are estimates.
- Missing/unknown/multi-zone sources and invalid creation timestamps show Unavailable. A valid store clock remains
  available independently. Deleted stores or invalid store timezones never fall back to another store/browser zone.
- Maintain the server/PHP timezone database. DST and fractional offsets follow its installed rules; it can change results.
- No safe-to-call badge, contact policy, working hours or automatic action is inferred.

## Installation

This source is prepared for 1.0.0; public publication is a separate release step. After the package is published:

```bash
composer require stoneholl/magento2-customer-local-time:^1.0
bin/magento module:enable Stoneholl_Core Stoneholl_CustomerLocalTime
bin/magento setup:upgrade --safe-mode=1
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f --area adminhtml --theme Magento/backend en_US
bin/magento cache:clean
```

Before upgrade, take and verify a database backup. Keep every originally enabled module enabled during declarative
upgrade; never isolate optional modules before upgrade. Temporary isolation belongs only to subsequent runtime/DI
checks. Enable the desired displays through the settings above.

## Privacy and cost

No external services, networking, IP/geolocation lookup, browser capture, tracking, customer attributes, persistence,
schema, cron or order mutation. No name, street, postcode, telephone or live profile is processed. No customer result is
written to sessions, logs, shared caches or browser storage; failures log fixed diagnostic codes only.

Reuse the loaded order-address snapshot where available. Each customer-based placement otherwise performs at most
one country-only address query, constrained by address ID, parent order ID and address type with LIMIT 1. Both customer
placements together may perform two such lookups. Country metadata is reference data, and zone lists are cached only
per country for the request. Store-only display skips customer-address reads; customer-only skips store timezone
resolution. Both rows disabled skips order reads for the current-time section. No order/history/profile scans.

## Compatibility and validation

Declared target: Magento Open Source 2.3.7-p4–2.4.9, PHP 7.4–8.5, including 8.0. Composer ranges are install-time
declarations and do not certify every combination. Exact local runtime evidence:

| Magento Open Source | PHP | Database | tzdb |
| --- | --- | --- | --- |
| 2.3.7-p4 | 7.4.33 | MySQL 5.7.44 | 2022.1 |
| 2.4.9 | 8.5.9 | MariaDB 12.3.2 | 2026.3 |

PHP 8.0 is syntax/grammar validation only. Other pairs, Adobe Commerce, third-party row-level restrictions and
nonstandard Admin themes remain unverified. Native install/upgrade checks were performed during initial implementation;
final UI/config revisions use focused runtime/template, DI, ACL, scope, escaping and switch-combination validation.
Early prototype timings are not a benchmark for this final two-placement version. Hosted source CI is a separate gate.

The native date-row adapter inserts one escaped row after the first Order Date row in `order-information-table`.
Custom Admin templates lacking that anchor are left untouched and need separate verification. No core/vendor template
is copied or overridden. Current-time layout block: `stoneholl.customer_local_time`, in `extra_customer_info`.
Classes are internal; no public timezone-source adapter API or arbitrary attribute mapping is supplied.

## Development and removal

Run `phpunit.xml.dist`, `phpcs.xml.dist` and Magento checks in an existing Docker service. Keep tests, fixtures, CI,
quality configurations and release metadata in the private source repository; public artifacts contain runtime files
and this README, CHANGELOG and LICENSE only.

To remove, back up first, disable/remove only this module, then run normal Magento upgrade/DI/cache maintenance with
all other originally enabled modules preserved. Configuration lives under `stoneholl_customer_local_time/*`. There are
no module-owned order/customer rows or tables. Keep shared Core while other packages require it.

## Composer package naming

The canonical Composer name is `stoneholl/magento2-customer-local-time`. The former `stoneholl/module-customer-local-time` name is a legacy identity. The renamed package replaces that identity at `self.version`, so Composer installs one copy of the Magento module. Existing immutable releases remain unchanged.

When migrating an existing root requirement, back up the project and remove the old requirement without updating, then require the new package with its dependencies:

```bash
composer remove stoneholl/module-customer-local-time --no-update
composer require stoneholl/magento2-customer-local-time --with-all-dependencies
```

Run the Magento upgrade, compilation and cache commands documented above according to your deployment mode. Keep native `Stoneholl_*` module names and configuration paths unchanged.
