# Project Report — The Shattered Crown

**Course:** CSC 4370/6370 — Web Programming · Spring 2026
**Project:** Project 2 · Topic 05 — Choose Your Path (RPG Branching Narrative)
**Developer:** Prajeeth Channa · Panther ID 002894331 (solo project)
**Submission date:** April 18, 2026

---

## Section 1 — Features Built

This is a solo project; every feature below was designed, implemented, tested, and polished by **Prajeeth Channa**. The "Sprint" column indicates when each feature reached its "Definition of Done" per the approved proposal.

### Authentication & Session Management

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| User registration with username, email, and password validation | `register.php` | Sprint 1 |
| Password hashing with `password_hash()` using `PASSWORD_DEFAULT` | `register.php`, `login.php` | Sprint 1 |
| Login with `password_verify()` and session-fixation protection via `session_regenerate_id(true)` | `login.php` | Sprint 1 |
| Logout with full session teardown and cookie clearing | `logout.php` | Sprint 1 |
| `requireLogin()` and `requireHero()` guards on all protected pages | `functions.php`, `includes/auth.php` | Sprint 1 |
| JSON-backed user store (`users.json`) with case-insensitive lookup via `findUser()` | `functions.php` | Sprint 1 |

### Character Creation & Game State

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| Landing page with auth-aware routing | `index.php` | Sprint 2 |
| Three-class character selection (Warrior / Mage / Rogue) with distinct HP / STR / WIS | `character.php`, `data/story.php` | Sprint 2 |
| Hero session initialization with inventory, score, and visited-node tracking | `character.php` | Sprint 2 |
| Full 17-node story array with three class-specific text variants per node (60 narrative strings) | `data/story.php` | Sprint 2 |

### Core Game Loop

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| Node rendering from `$nodes` array with choice form | `game.php` | Sprint 3 |
| POST handler validating `choice_id` against the current node | `game.php` | Sprint 3 |
| Stat-gate logic (`canChoose()`) with locked-choice UI showing the missing requirement | `functions.php`, `game.php` | Sprint 3 |
| Stat application (`applyStatChanges()`) for HP/STR/WIS deltas | `functions.php` | Sprint 3 |

### Branching Narrative & Inventory

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| All Acts 2 & 3 branching paths (17 gameplay nodes + 3 endings, all reachable) | `data/story.php` | Sprint 4 |
| Inventory system: `items_granted` on choices, `required_item` gating, item consumption on use | `game.php`, `functions.php` | Sprint 4 |
| Alignment score updated per choice and persisted as history array | `game.php` | Sprint 4 |
| Grad-AI Ending Predictor (`getEndingPrediction`) with confidence bar and thematic foreshadowing line | `functions.php`, `game.php` | Sprint 4 |
| Grad-AI Dynamic Node Flavoring — three text variants per node selected by class (`getNodeText`) | `functions.php`, `data/story.php` | Sprint 4 |

### Endings, Leaderboard & Analytics

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| Three unique ending screens (Heroic / Tragic / Secret) plus Death screen with class-specific text | `ending.php`, `data/story.php` | Sprint 5 |
| Final score calculation: `500 + nodes×25 + inventory×15 + |alignment|×10` | `functions.php` | Sprint 5 |
| Legacy Persona system (Justiciar / Warden / Drifter / Schemer / Usurper) derived from alignment | `ending.php` | Sprint 5 |
| Leaderboard with global top 10 and personal history, sorted via `usort()` | `leaderboard.php` | Sprint 5 |
| Leaderboard filters (ending type, class) and sort modes (score/date, asc/desc), whitelist-validated server-side | `leaderboard.php` | Sprint 5 |
| Cookie-based resume (`sc_node`) set on every node transition, read on character.php for recovery hint | `game.php`, `character.php` | Sprint 5 |
| Grad-AI Alternative Path Suggester — scans `locked_log`, computes stat gap, highlights the smallest miss with a "SO CLOSE" badge | `functions.php`, `ending.php` | Sprint 5 |
| Grad-AI Story Analytics Report — nodes-visited %, locked-path count, mercy/authority and strategic/primal bars, alignment-over-time SVG sparkline, archetype match score | `ending.php` | Sprint 5 |
| Save/Load/Continue — `saves.json` persistence per user with terminal-node cleanup | `functions.php`, `character.php`, `game.php` | Sprint 5 |
| Journey timeline replay — numbered step-by-step log of every choice made during the run | `ending.php` | Sprint 5 |

### UI / CSS Polish

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| Responsive layout with flexbox + grid; breakpoints at 960 px and 600 px | `css/style.css` | Sprint 6 |
| Typewriter story reveal via `@keyframes storyReveal` (fade + translate + blur-off) with blinking gold caret pseudo-element | `css/style.css` | Sprint 6 |
| Staggered fade-in transitions across forms, story panels, ending stats cards, and choice cards | `css/style.css` | Sprint 6 |
| World map with SVG lines and visited / current / hidden node states | `game.php`, `css/style.css` | Sprint 3–4 |
| Thematic dark-fantasy aesthetic: Cinzel + EB Garamond, parchment + gold on obsidian | `css/style.css` | Sprint 2 |

### Security

| Feature | File(s) | Sprint |
| ------- | ------- | ------ |
| CSRF tokens on every POST form (`csrfToken`, `csrfField`, `csrfCheck`) using `random_bytes(32)` and `hash_equals()` for timing-safe verification | `functions.php`, all form pages | Sprint 6 |
| Server-side input validation (`trim()`, regex, `filter_var(FILTER_VALIDATE_EMAIL)`, length caps) with per-field errors rendered back into the same page | `register.php`, `login.php` | Sprint 1 |
| XSS protection via a `clean()` wrapper around `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` on every output | `functions.php`, all pages | Sprint 1 |
| Whitelisted GET parameters on leaderboard filters; rejects anything not in the allowed set | `leaderboard.php` | Sprint 5 |

---

## Section 2 — Challenges Faced

### Challenge 1 — Save files kept resurrecting completed runs

After finishing a playthrough and reaching an ending, returning to `character.php` still showed the "Continue Journey" save card pointing at the terminal ending node. Clicking Continue immediately redirected to `ending.php` because the session's `$_SESSION['node']` pointed at a node where `is_terminal === true`, but the leaderboard write and `deleteSave()` in `ending.php` had already run. The stale save had been written during the *last* POST in `game.php` *before* the terminal redirect, capturing the terminal node ID in `saves.json`.

Concretely, in `game.php` the order was:
```
$_SESSION['node'] = $chosen['next'];   // terminal node
saveGame();                            // persisted terminal node to saves.json
header('Location: ending.php');        // ending.php then deleted save — but session persisted too
```

The on-disk save was being deleted correctly, but the user had a terminal node stuck in their live session *and* any reload of `character.php` would re-read the file before deletion on subsequent sessions, creating a visible ghost save.

### Challenge 2 — CSRF token being wiped on login

After adding CSRF tokens in Sprint 6, login stopped working entirely. Every submitted login POST came back with "The sigil on your vow is broken." The token was being generated on GET `login.php`, embedded into the form, and checked correctly on POST — but the check was failing for *valid* submissions.

The root cause was the interaction between the CSRF token and `session_regenerate_id(true)` that I had already added in Sprint 1 for session-fixation protection. I originally believed `session_regenerate_id(true)` would destroy all session data, including the CSRF token, before the `csrfCheck()` call. After adding `var_dump($_SESSION)` before and after the call, I confirmed PHP actually preserves `$_SESSION` across regeneration by default — the real bug was elsewhere: I had written `csrfCheck()` to read `$_SESSION['csrf_token']` but was only calling `session_start()` inside the token *generator*, not the checker. On requests where the form was submitted but `csrfToken()` had never been called earlier in the request, `$_SESSION` was empty and the comparison failed.

### Challenge 3 — Alignment history becoming malformed mid-run

The Ending Predictor and, later, the alignment sparkline rendered correctly for fresh runs but crashed or rendered flat lines after a save-and-resume cycle. Inspection of `saves.json` revealed that `alignment_history` was sometimes an associative array like `{"0": 1, "2": -1}` instead of a sequential list, and `count()` was returning inconsistent values.

The cause was that I was appending to `$_SESSION['alignment_history']` before initializing it when the hero was first created, so PHP was silently promoting the append into an object-shape when JSON-decoded later. On restoration via `restoreSave()`, the field came back as an associative array that `array_merge([0], $history)` could not safely prepend to — producing the malformed sparkline input.

### Challenge 4 (bonus) — Leaderboard's "Latest Ending" card was lying

During final review I noticed the "Latest Ending" summary card on `leaderboard.php` was showing the ending of whichever run had the **highest score**, not the most recent run. Because `$my_games` was sorted by score descending for the main table, `$my_games[0]` was the best run, not the latest — a silent UX bug that only surfaced when a player had multiple runs with varying outcomes.

---

## Section 3 — Solutions & Lessons Learned

### Fix 1 — Terminal-node save cleanup with defensive reads

Two changes, both in `character.php`:

```php
$existing_save = loadSave($_SESSION['user']);
if ($existing_save) {
    $save_node = $existing_save['node'] ?? '';
    if (!isset($nodes[$save_node]) || $nodes[$save_node]['is_terminal']) {
        deleteSave($_SESSION['user']);
        $existing_save = null;
    }
}
```

And in `game.php`, the "live hero with terminal node" case now redirects to `ending.php` instead of showing the stale save:

```php
if (isset($_SESSION['hero'], $_SESSION['node'])
    && isset($nodes[$_SESSION['node']])
    && !$nodes[$_SESSION['node']]['is_terminal']) {
    header('Location: game.php');
    exit;
}
```

**Lesson:** Persisted state is a tri-valued space — `valid`, `invalid-but-recoverable`, and `invalid-because-done`. I was only handling the first two. Adding a "stale-ness" check at every read site (not just the write site) made the whole system self-healing.

### Fix 2 — Bulletproof the CSRF check

Added `if (session_status() === PHP_SESSION_NONE) session_start();` at the top of `csrfCheck()`, mirroring what `csrfToken()` already did, so every entry point initializes the session before reading `$_SESSION['csrf_token']`. Also switched from raw `===` string compare to `hash_equals($known, $sent)` for timing-safe verification — a best-practice I had glossed over.

**Lesson:** Security helpers must be *self-sufficient*. If a helper's correctness depends on the caller having already done something (like `session_start()`), then the helper is a footgun. Making each helper idempotent eliminated an entire category of "works on one page, fails on another" bugs.

### Fix 3 — Explicit type initialization and defensive prepending

In `character.php`, when the hero is created, I now initialize the session keys as explicit typed empty arrays:

```php
$_SESSION['hero']['nodes_visited'] = [];
$_SESSION['alignment_history']     = [];
```

And in `ending.php`, the sparkline builder now defensively coerces back to a list:

```php
$alignment_history = array_values($_SESSION['alignment_history'] ?? []);
$series = array_merge([0], $alignment_history);
```

`array_values()` strips any string keys that leaked in during JSON round-trips, guaranteeing a clean sequential list.

**Lesson:** JSON does not preserve the distinction between PHP's sequential and associative arrays. Any data that flows through `json_encode` → `json_decode` and gets merged back into PHP arrays must be **explicitly re-listified** with `array_values()` if you want to treat it as a sequence. This is a subtle trap that would bite harder in a larger project.

### Fix 4 — Separate data-sort from display-sort

`leaderboard.php` now computes two views of `$my_games`:

```php
$my_games = array_values(array_filter($scores, fn($s) => strcasecmp($s['username'], $username) === 0));
usort($my_games, fn($a, $b) => $b['score'] <=> $a['score']);  // table display

$my_latest = $my_games;
usort($my_latest, fn($a, $b) => strtotime($b['date'] ?? '0') <=> strtotime($a['date'] ?? '0'));
$my_latest = $my_latest[0] ?? null;  // "Latest Ending" card
```

The summary card now correctly shows the latest run, and the history table still sorts by score.

**Lesson:** "Sort the data once and reuse it" is a tempting optimization but a lie when the same data has two different meanings in two different UI contexts. Allocating a second sorted copy costs almost nothing for 10–100 entries and eliminates a whole class of "which array index means what" bugs.

---

### What I would do differently if starting over

If I were starting this project fresh, the single biggest change would be **committing to a real persistence layer from day one instead of incrementally growing three separate JSON files.** The project currently manages `users.json`, `scores.json`, and `saves.json`, each with its own ad-hoc load/save pair, its own race condition potential (mitigated with `LOCK_EX` but never truly eliminated), and its own schema that only exists in my head. Every time I added a feature — the alignment history, the locked log, the journey log — I had to trace through three files and mentally verify that each data shape round-tripped safely.

Early in Sprint 2 I should have either (a) moved to SQLite with three tables and PDO prepared statements, which would have enforced schemas and given me transactions for free, or (b) collapsed all state into a single `game_state.json` per user with a versioned schema. Either choice would have eliminated Challenge 3 entirely and made Challenge 1 easier to reason about.

Second, I would write a **single end-to-end smoke test** — a tiny PHP script that spins up a user, plays a canonical path through to each of the three endings, and asserts the resulting leaderboard row — before adding any Grad-AI features. In this project I caught the "latest ending" bug (#4 above) by eye at the very end; a ten-line smoke test would have caught it the day I wrote it. The storytelling-heavy domain actually makes it *easier* to write such tests, because the expected ending for a canonical path is deterministic.

Finally, I would split `functions.php` into `auth.php`, `game.php`, `scoring.php`, and `ai.php` from the start, rather than letting it grow past 300 lines. The current structure works, but grading-friendly organization is easier to build up than to retrofit the night before submission.

The overall experience reinforced that **the boring parts of a project — state shape, error handling, helper ergonomics — are where most of the real bugs live.** The branching story, the Grad-AI features, and the CSS polish were the fun parts and largely worked on the first try. The bugs I actually burned hours on were all about data at rest and data in transit.