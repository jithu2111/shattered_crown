# The Shattered Crown

A PHP-driven, server-side text-adventure RPG set in the fallen kingdom of Valdris. King Aldric has been slain, the Crown of Binding shattered into three fragments, and a magical eclipse rises in 14 days. The player wakes in a ruined temple and must recover the shards before Lord Malachar's reign becomes permanent.

I chose this concept because a branching-narrative RPG is the most natural vehicle for demonstrating the full breadth of server-side PHP required by the course: every player choice is a POST form validated on the server, every stat check is a PHP conditional, every node transition is a session update, and every ending is a leaderboard write. The storytelling makes the technical requirements feel earned instead of academic.

**Course:** CSC 4370/6370 — Web Programming · Spring 2026
**Project:** Project 2 · Topic 05 — Choose Your Path (RPG Branching Narrative)

---

## Team

This is a **solo project**. All design, code, narrative writing, and deployment were authored by the single team member below.

| Name             | Panther ID | Role                                         | Primary PHP Contribution                                                                                                                                                                                                                                                                                                                 |
| ---------------- | ---------- | -------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Prajeeth Channa  | 002894331  | Project Leader · Scrum Master · Sole Developer | Entire PHP codebase: auth flow (`register.php`, `login.php`, `logout.php`), session + CSRF system (`functions.php`), 17-node branching story engine (`data/story.php`, `game.php`), stat-gate and inventory logic (`canChoose`, `applyStatChanges`), save/load (`saves.json` persistence), ending + analytics pipeline (`ending.php`), leaderboard with filters (`leaderboard.php`), and all graduate-tier features (Ending Predictor, Alternative Path Suggester, Archetype Match, Alignment sparkline). |

---

## Live Deployment

- **CODD Server:** https://codd.cs.gsu.edu/~pchanna1/web/projects/shattered_crown/index.php
- **GitHub Repository:** https://github.com/jithu2111/shattered_crown

---

## Features

### Core gameplay
- **Authentication** — registration with username/email/password, login with session regeneration, logout
- **Character creation** — 3 classes (Warrior / Mage / Rogue) with distinct HP, STR, WIS stats
- **Branching narrative** — 17 story nodes across Acts 1–3, plus 4 ending nodes
- **Stat- and item-gated choices** — locked options display the requirement instead of the form
- **Inventory system** — items granted by choices, consumed when required by later choices
- **Four unique endings** — Heroic Victory, Tragic Failure, Secret Path, Pyrrhic Sacrifice (plus a Death screen)
- **Save / load / resume** — persistent save file per user, cookie-based node hint for recovery

### Graduate-tier features
- **Ending Predictor** — live confidence bar based on alignment score and progress
- **Dynamic Node Flavoring** — every node has three class-specific variants of story text
- **Alignment tracker** — each choice shifts alignment; history stored for post-game analytics
- **Alternative Path Suggester** — on the ending screen, shows the locked choices you came closest to unlocking, with "SO CLOSE" highlight on the smallest gap
- **Story Analytics Report** — nodes-visited %, locked-path count, alignment-over-time SVG sparkline, legacy persona, archetype match score
- **Legacy Persona system** — five personas (Justiciar / Warden / Drifter / Schemer / Usurper) derived from alignment
- **Leaderboard with filters** — global top 10 + personal history, filterable by ending type and class, sortable by score or date

### UI / UX
- **Responsive CSS** — flexbox + grid, breakpoints at 960 px and 600 px
- **Typewriter story reveal** — CSS keyframes with blur-off and a blinking gold caret
- **Staggered fade-in transitions** — applied across forms, story panels, ending stats, and choice cards
- **World map** — SVG lines connecting nodes, with current / visited / hidden states
- **Thematic dark-fantasy aesthetic** — Cinzel + EB Garamond fonts, parchment + gold on obsidian

### Security
- **Password hashing** — `password_hash()` with `PASSWORD_DEFAULT`
- **Session fixation protection** — `session_regenerate_id(true)` on login
- **CSRF tokens** — on every POST form (login, register, character, game), verified with `hash_equals()`
- **Server-side validation** — `trim()`, `htmlspecialchars()`, whitelisted inputs on every request
- **XSS protection** — all user-derived output escaped via a `clean()` helper
- **Session-protected pages** — `requireLogin()` and `requireHero()` guards on every game page

---

## Project Structure

```
shattered_crown/
├── index.php                 Landing page
├── lore.php                  Chronicle of Valdris (worldbuilding + no-spoiler intro)
├── register.php              Account creation
├── login.php                 Authentication
├── logout.php                Session teardown
├── character.php             Class selection + save detection
├── game.php                  Main game loop (node + choices + world map)
├── ending.php                Endings + stats panel + analytics
├── leaderboard.php           Hall of Legends (global + personal)
├── reset.php                 Wipe current run
├── functions.php             Shared helpers (auth, save/load, scoring, CSRF, AI)
├── includes/
│   ├── auth.php              requireLogin() bootstrap
│   ├── header.php            Shared top chrome
│   └── footer.php            Shared bottom chrome
├── data/
│   └── story.php             17 story nodes + 3 endings + class stat table
├── css/
│   └── style.css             Single stylesheet for every page
├── users.json                Registered accounts (password hashes only)
├── scores.json               Leaderboard entries (created on first completion)
└── saves.json                Per-user mid-run save state
```

---

## Setup

### Requirements
- PHP 8.0+ (uses `match`, nullsafe, typed returns)
- Any web server that can route `.php` requests (Apache, Nginx, PHP built-in server, CODD)
- Write access to the project directory so PHP can update `users.json`, `scores.json`, and `saves.json`

### Local run
```bash
cd shattered_crown
php -S localhost:8000
```
Open http://localhost:8000 in a browser.

### Deployment to CODD
1. Upload the entire `shattered_crown/` folder to `~pchanna1/web/projects/` on the CODD server.
2. Ensure `users.json`, `scores.json`, and `saves.json` are writable by the web server user (typically `chmod 664`).
3. Visit `https://codd.cs.gsu.edu/~pchanna1/web/projects/shattered_crown/index.php` in an incognito window to verify a clean first-run flow.

---

## Usage

1. **Take the Oath** — register a new account from the landing page (username, email, password).
2. **Invoke Legacy** — log in with your credentials.
3. **Choose Your Adventurer** — pick a hero name and one of three classes.
4. **Play the story** — read each node, select a choice (locked choices show the stat or item you're missing). Your alignment shifts on every decision.
5. **Watch the Ending Predictor** — the sidebar forecasts Heroic / Tragic / Secret based on your running alignment.
6. **Reach an ending** — confrontation at Malachar's Tower concludes the run.
7. **Review your legend** — the ending screen shows your score, persona, archetype match, alignment graph, alternative paths you almost unlocked, and a journey timeline.
8. **Hall of Legends** — compare your best run against global top 10; filter by ending or class.

---

## Gameplay mechanics

### Classes
| Class   | HP  | STR | WIS | Role                                       |
| ------- | --- | --- | --- | ------------------------------------------ |
| Warrior | 100 | 25  | 10  | Vanguard — heavy armor, storms gates       |
| Mage    | 70  | 10  | 30  | Arcanist — unravels wards, channels magic  |
| Rogue   | 85  | 15  | 20  | Infiltrator — sneaks, times patrols        |

### Scoring
Final score = `500 base + (nodes visited × 25) + (inventory × 15) + (|alignment| × 10)`

### Alignment
Each choice shifts alignment by −5 to +3. Totals map to a legacy persona:
- `≥ 8` Justiciar · `≥ 3` Warden · `−2..+2` Drifter · `≤ −3` Schemer · `≤ −8` Usurper

---

## Development

### Branching strategy
Single `main` branch. Feature branches per sprint (`feature/authentication`, `feature/hero-session-story`, `feature/game-core`, `feature/all-branching-paths`), merged via pull request at each sprint boundary.

### Sprint plan (18 days · Apr 1–18)
- **Sprint 1** — project setup, register/login with session auth
- **Sprint 2** — landing, character select, full 17-node story array
- **Sprint 3** — game loop, POST choice handler, stat-gate UI
- **Sprint 4** — Acts 2 & 3 branching, inventory, Ending Predictor, Dynamic Node Flavoring
- **Sprint 5** — endings, leaderboard, cookie resume, Alternative Path Suggester, Analytics Report
- **Sprint 6** — CSS polish (typewriter, fade-ins), end-to-end testing, README, demo video

### Commit conventions
`[type]: [short description]` — examples: `feat: Add save/load with saves.json`, `fix: Correct stat gate on Mage Vision choice`, `style: Apply typewriter animation to story text`.

---

## AI disclosure

Claude Code (Anthropic) was used during development for:
- Sprint planning discussion and verification against the proposal
- Boilerplate generation for CSS (fade-in keyframes, filter UI styles)
- Scaffolding for the Alternative Path Suggester and Alignment sparkline
- README and journal drafting

All gameplay design, narrative writing (17 nodes × 3 class variants), story branching structure, PHP game logic, and final code integration were authored and reviewed by the team member listed above.

---

## License

Original coursework for CSC 4370/6370. Not licensed for reuse. All narrative content © Valdris 1342 — all light is temporary.