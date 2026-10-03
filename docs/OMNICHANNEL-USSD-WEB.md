# YelloFC omnichannel USSD/Web journey

## Principle

USSD, Web (Evina/mFilter), SMS and IVR are acquisition/access bearers for the same YelloFC games.
The canonical product truth is the YelloFC PostgreSQL platform:

CMS catalogue -> published round -> fixtures/questions -> PISI entitlement -> entry/receipt -> settlement -> CRM -> leaderboard/rewards.

USSD must not maintain a separate game definition when the canonical API is configured.

## Direct USSD shortcuts

| Game | USSD shortcut | Canonical key | PISI SID/PID | Daily jackpot |
|---|---|---|---|---:|
| Football Predictor | *8022*1# | predictor | 269 / 5677 | N1m |
| Total Goals | *8022*2# | goals | 268 / 5676 | N3m |
| Correct Score | *8022*3# | correct | 270 / 5678 | N1m |
| Soka 4 | *8022*4# | soka4 | 282 / 5706 | N2m |
| Soka 6 | *8022*5# | soka6 | 283 / 5707 | N3m |
| Soka 8 | *8022*6# | soka8 | 284 / 5708 | N10m |
| Soka Half | *8022*7# | half | 286 / 5710 | N2m |
| Soka Corners | *8022*8# | corners | 285 / 5709 | N2m |
| Soka Trivia | *8022*9# | trivia | 304 / 5777 | N2m |

The root *8022# menu continues to group Predictor games, Soka games, and More Games.
Soka Trivia is exposed under More Games and by the direct *8022*9# shortcut.

## Shared journey for all nine games

1. Customer starts from *8022#, a direct game shortcut, the YelloFC Web homepage, or a daily SMS link.
2. CMS supplies the same published game catalogue and today's published round to Web and USSD.
3. USSD selections are collected in the existing session state; Web selections are collected in the Web client.
4. If the subscriber has an active daily entitlement for that game, the base entry can be committed.
5. If no entitlement exists:
   - USSD calls PISI subscription create directly because the customer deliberately dialled the service.
   - external Web acquisition continues through Evina/mFilter before MTN/PISI.
6. PISI callback is the central subscription truth. It creates active_trial, active_paid or revoked entitlement in YelloFC PostgreSQL.
7. A valid entry creates one canonical receipt and CRM entry_placed event.
8. Daily Free Play is available only after the base entry and only when the entitlement is eligible.
9. Settlement, leaderboard points, rewards and CRM use the same canonical PostgreSQL entry regardless of acquisition bearer.
10. Daily renewal can send an SMS carrying a secure opaque YelloFC return token so the subscriber may continue on Web without subscribing again.

## Game-specific selection journeys

### Football Predictor
- 6 CMS fixtures.
- One Home / Draw / Away selection per fixture.
- Canonical selection values: 1 / 2 / 3.

### Total Goals
- 6 CMS fixtures.
- One total-goals selection per fixture: 0 through 9 or 10+.
- USSD and Web must submit the same canonical values.

### Correct Score
- 6 CMS fixtures.
- Exact Home-Away score per fixture.
- Canonical value is {home, away}.

### Soka 4
- 4 CMS fixtures.
- Nine score categories: 1-0, 2-0, 3-0, 2-1, 3-1, 3-2, Any Other, 0-0, Score Draw.
- Canonical values: 1 through 9.

### Soka 6
- 6 CMS fixtures.
- Same nine score categories as Soka 4.
- Canonical values: 1 through 9.

### Soka 8
- 8 CMS fixtures.
- Same nine score categories as Soka 4.
- Canonical values: 1 through 9.

### Soka Half
- 8 separate CMS fixtures, not four fixtures with two predictions each.
- Alternating legs: M1H / M2F / M3H / M4F / M5H / M6F / M7H / M8F.
- Home / Draw / Away per leg.
- Canonical values: 1 / 2 / 3.

### Soka Corners
- 6 CMS fixtures.
- Categories: 0-3, 4, 5, 6, 7, 8, 9, 10, 11+.
- Canonical values: 1 through 9.

### Soka Trivia
- 2 CMS fixtures.
- 4 CMS-managed questions per fixture = 8 questions.
- Question prompt and choices are loaded from the same YelloFC /rounds/today API used by Web.
- USSD stores the selected choice text for each canonical question group m0-q0 through m1-q3.
- PISI mapping: SID 304 / PID 5777.

## CMS ownership

CMS owns:
- game name, jackpot, status and channel availability;
- today's round;
- fixtures and ordering;
- Trivia prompts and choices;
- result facts and approved corrections.

USSD should not create an independent fixture or Trivia-question schedule once YELLOFC_CMS_REQUIRED=YES.

## CRM ownership

PISI activation already creates subscription_activated CRM events centrally.
The target USSD entry bridge must also create the same canonical entry_placed event that Web creates.
Acquisition source should be retained as ussd, evina, mfilter or sms-return for reporting, without creating separate customer identities.

## Migration stages

1. CMS content bridge — IMPLEMENTED ON FEATURE BRANCH: supported USSD fixtures prefer the canonical YelloFC API; Soka Trivia is activated from canonical CMS questions.
2. Entry bridge — IMPLEMENTED ON FEATURE BRANCH: after the legacy ticket is created, the USSD service sends a signed mirror to the PostgreSQL entry bridge. Existing entitlements commit immediately; otherwise a pending intent waits for the PISI callback.
3. PISI promotion — IMPLEMENTED ON BACKEND FEATURE BRANCH: verified active_trial/active_paid callbacks attach and finalize matching pending USSD intents into the same canonical entries/receipt/CRM path as Web.
4. Source attribution — IMPLEMENTED ON BACKEND FEATURE BRANCH: canonical receipt and CRM entry_placed metadata identify the source as ussd while preserving one subscriber identity.
5. Entitlement decision cutover — NOT YET ENABLED: legacy soka_subscriptions/non_soka_subscriptions usestat remains in place for the live PHP decision path until staging parity proves central daily_entitlements can replace it safely.
6. SMS return bridge — PARTIALLY AVAILABLE: opaque one-time return tokens exist in the new backend, but production daily-SMS issuance and final TTL policy still require certification.
7. Legacy retirement — FUTURE: retire duplicate MySQL gameplay/subscription state only after staging and production parity are proven.
