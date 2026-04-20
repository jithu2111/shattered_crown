<?php

$class_stats = [
    'warrior' => ['hp' => 100, 'str' => 25, 'wis' => 10, 'label' => 'The Forged Warrior', 'archetype' => 'Vanguard'],
    'mage'    => ['hp' =>  70, 'str' => 10, 'wis' => 30, 'label' => 'Void Weaver',        'archetype' => 'Arcanist'],
    'rogue'   => ['hp' =>  85, 'str' => 15, 'wis' => 20, 'label' => 'Shadow Blade',       'archetype' => 'Infiltrator'],
];

$nodes = [

    // ── ACT 1 ─────────────────────────────────────────

    'node_01' => [
        'id'    => 'node_01',
        'title' => 'The Ruined Temple',
        'text_warrior' => 'Crumbled pillars line the hall. Your gauntlets scrape stone as you push through the wreckage — this place fell to siege, not time. A faint crimson glow pulses behind the broken altar.',
        'text_mage'    => 'Residual wards crackle faintly across the archway. Whoever shielded this place was powerful — and desperate. The glow behind the altar hums at a frequency you recognize: binding magic.',
        'text_rogue'   => 'Dust motes hang frozen in stale air. You count three exits, two pressure plates, and one tripwire before your second breath. Behind the altar, something glows.',
        'choices' => [
            [
                'id' => 'search_altar', 'text' => 'Investigate the altar glow',
                'next' => 'node_02', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['wis' => 2],
                'items_granted' => ['Ember Fragment'], 'alignment' => 1,
            ],
            [
                'id' => 'skip_to_crossroads', 'text' => 'Leave quickly — head for the crossroads',
                'next' => 'node_03', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_02' => [
        'id'    => 'node_02',
        'title' => 'The First Shard',
        'text_warrior' => 'You pry the shard free with brute strength. The altar cracks apart, and warmth floods your chest — the fragment of the Crown of Binding pulses against your palm like a second heartbeat.',
        'text_mage'    => 'You channel a dispel thread into the ward lattice. The shard lifts free on its own, orbiting your hand. You can feel the Crown\'s memory bleeding through — a king\'s final scream.',
        'text_rogue'   => 'You disarm the trap plate beneath the shard with a hairpin and lift it cleanly. The fragment is warm. Warm things in cold ruins mean someone wanted this found.',
        'choices' => [
            [
                'id' => 'read_inscription', 'text' => 'Study the inscription on the altar base',
                'next' => 'node_03', 'required_stat' => 'wis', 'required_val' => 15,
                'required_item' => null, 'stat_changes' => ['wis' => 3],
                'items_granted' => ['Lost Litany Scroll'], 'alignment' => 2,
            ],
            [
                'id' => 'pocket_and_go', 'text' => 'Pocket the shard and move on',
                'next' => 'node_03', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 0,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_03' => [
        'id'    => 'node_03',
        'title' => 'The Crossroads',
        'text_warrior' => 'Four roads meet beneath a dead oak. Claw marks gouge the signpost — something large came through here. The north road smells of smoke; the east, of salt and rot.',
        'text_mage'    => 'Ley lines converge here — you can feel them underfoot like buried rivers. The northern path radiates heat; the eastern one reeks of necromantic residue.',
        'text_rogue'   => 'Boot prints in the mud: three sets heading north, one dragged east. The northern prints are deep — armored soldiers. The eastern trail has blood.',
        'choices' => [
            [
                'id' => 'north_ember_keep', 'text' => 'North — toward Ember Keep',
                'next' => 'node_04', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 1,
            ],
            [
                'id' => 'east_ice_caves', 'text' => 'East — into the Ice Caves',
                'next' => 'node_05', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -1,
            ],
            [
                'id' => 'meet_sable', 'text' => 'Wait at the signpost — someone is watching',
                'next' => 'node_06', 'required_stat' => 'wis', 'required_val' => 12,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 0,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── ACT 2: NORTH PATH ─────────────────────────────

    'node_04' => [
        'id'    => 'node_04',
        'title' => 'Ember Keep Gates',
        'text_warrior' => 'The heavy iron gates groan under the pressure of the siege. Your blood boils; your training screams that a direct assault is the only way.',
        'text_mage'    => 'Siege wards flicker across the gates — military-grade, but weakening. You could unravel them with the right spell sequence or find another way in.',
        'text_rogue'   => 'Guard rotation is sloppy — siege fatigue. You spot a drainage tunnel to the left of the gate, half-hidden by rubble.',
        'choices' => [
            [
                'id' => 'storm_gates', 'text' => 'Storm the gates',
                'next' => 'node_07', 'required_stat' => 'str', 'required_val' => 20,
                'required_item' => null, 'stat_changes' => ['hp' => -20, 'str' => 3],
                'items_granted' => [], 'alignment' => 2,
            ],
            [
                'id' => 'sneak_tunnel', 'text' => 'Sneak through the drainage tunnel',
                'next' => 'node_07', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => -5],
                'items_granted' => ['Rusted Key'], 'alignment' => 0,
            ],
            [
                'id' => 'broker_route', 'text' => 'Seek the Broker\'s hidden route',
                'next' => 'node_08', 'required_stat' => null, 'required_val' => null,
                'required_item' => 'Lost Litany Scroll', 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── ACT 2: EAST PATH ──────────────────────────────

    'node_05' => [
        'id'    => 'node_05',
        'title' => 'The Ice Caves',
        'text_warrior' => 'The cold bites through your armor. Frozen corpses line the passage, their weapons still drawn. Whatever killed them didn\'t leave marks.',
        'text_mage'    => 'Cryomantic energy saturates every surface. The frost here isn\'t natural — it\'s a preservation spell that went wrong, or was made to look that way.',
        'text_rogue'   => 'The ice is too smooth to be natural. You find tool marks hidden under the frost — this cave was carved, then frozen to hide the evidence.',
        'choices' => [
            [
                'id' => 'push_deeper', 'text' => 'Push deeper into the cave',
                'next' => 'node_09', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => -10],
                'items_granted' => ['Spell Crystal'], 'alignment' => 1,
            ],
            [
                'id' => 'thaw_corpse', 'text' => 'Examine the nearest frozen body',
                'next' => 'node_09', 'required_stat' => 'wis', 'required_val' => 15,
                'required_item' => null, 'stat_changes' => ['wis' => 2],
                'items_granted' => ['Cracked Signet Ring'], 'alignment' => 2,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── SABLE ENCOUNTER ───────────────────────────────

    'node_06' => [
        'id'    => 'node_06',
        'title' => 'Sable, the Shadow Broker',
        'text_warrior' => 'A cloaked figure detaches from the shadow of the dead oak. Her eyes glow amber. "The shard, traveler. Hand it over, or suffer the consequences."',
        'text_mage'    => 'You sensed her before she moved — a void in the ley lines, a walking null-field. Sable. The Broker. "I know what you carry, arcanist."',
        'text_rogue'   => 'You spotted her three minutes ago. She\'s been following since the temple. "Don\'t look so smug," she says. "I let you see me."',
        'choices' => [
            [
                'id' => 'fight_sable', 'text' => 'Fight her',
                'next' => 'node_10', 'required_stat' => 'str', 'required_val' => 20,
                'required_item' => null, 'stat_changes' => ['hp' => -25, 'str' => 5],
                'items_granted' => ['Shadow Cloak'], 'alignment' => -2,
            ],
            [
                'id' => 'negotiate_sable', 'text' => 'Negotiate',
                'next' => 'node_10', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['wis' => 3],
                'items_granted' => ['Sable\'s Map'], 'alignment' => 1,
            ],
            [
                'id' => 'flee_sable', 'text' => 'Flee into the northern road',
                'next' => 'node_04', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => -10],
                'items_granted' => [], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── EMBER KEEP INTERIOR ───────────────────────────

    'node_07' => [
        'id'    => 'node_07',
        'title' => 'Inside Ember Keep',
        'text_warrior' => 'Flames lick the inner walls. The keep\'s defenders are dead or fleeing. In the great hall, a second shard sits on the warlord\'s throne — guarded by a construct of iron and ember.',
        'text_mage'    => 'The keep\'s inner sanctum is a maelstrom of fire magic. The second shard of the Crown floats above the warlord\'s throne, held in place by an automated golem.',
        'text_rogue'   => 'Smoke provides perfect cover. You slip past panicked guards into the great hall. The second shard is there — but so is a golem, pacing in a patrol loop.',
        'choices' => [
            [
                'id' => 'fight_golem', 'text' => 'Destroy the golem head-on',
                'next' => 'node_11', 'required_stat' => 'str', 'required_val' => 22,
                'required_item' => null, 'stat_changes' => ['hp' => -30, 'str' => 5],
                'items_granted' => ['Crown Shard: Ember'], 'alignment' => 2,
            ],
            [
                'id' => 'disable_golem', 'text' => 'Disable its rune core with magic',
                'next' => 'node_11', 'required_stat' => 'wis', 'required_val' => 20,
                'required_item' => null, 'stat_changes' => ['wis' => 4],
                'items_granted' => ['Crown Shard: Ember'], 'alignment' => 1,
            ],
            [
                'id' => 'time_patrol', 'text' => 'Time the patrol loop and steal the shard',
                'next' => 'node_11', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => -5],
                'items_granted' => ['Crown Shard: Ember'], 'alignment' => 0,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_08' => [
        'id'    => 'node_08',
        'title' => 'The Broker\'s Passage',
        'text_warrior' => 'The scroll reveals a hidden corridor beneath the keep. It\'s narrow and dark — not built for someone in plate armor, but passable.',
        'text_mage'    => 'The Litany Scroll maps ancient ley conduits running beneath the keep. You follow the resonance directly to the vault.',
        'text_rogue'   => 'The passage is a smuggler\'s dream — concealed hatches, false walls. You navigate by instinct and emerge behind the throne room.',
        'choices' => [
            [
                'id' => 'vault_ambush', 'text' => 'Enter the vault',
                'next' => 'node_11', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['wis' => 2],
                'items_granted' => ['Crown Shard: Ember', 'Vault Relic'], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── ICE CAVES DEEP ────────────────────────────────

    'node_09' => [
        'id'    => 'node_09',
        'title' => 'The Frozen Sanctum',
        'text_warrior' => 'At the cave\'s heart: a shard of the Crown, suspended in a pillar of ice. Your breath freezes mid-air. Striking the pillar would shatter it — but the whole ceiling might follow.',
        'text_mage'    => 'The ice pillar is a focused cryomantic prison. Dispelling it cleanly requires concentration you haven\'t needed since your apprenticeship.',
        'text_rogue'   => 'The shard is visible through the ice — tantalizingly close. You notice stress fractures at the pillar\'s base. One precise strike would do it.',
        'choices' => [
            [
                'id' => 'shatter_pillar', 'text' => 'Shatter the ice pillar',
                'next' => 'node_11', 'required_stat' => 'str', 'required_val' => 18,
                'required_item' => null, 'stat_changes' => ['hp' => -20],
                'items_granted' => ['Crown Shard: Frost'], 'alignment' => -1,
            ],
            [
                'id' => 'dispel_pillar', 'text' => 'Carefully dispel the cryomantic seal',
                'next' => 'node_11', 'required_stat' => 'wis', 'required_val' => 20,
                'required_item' => null, 'stat_changes' => ['wis' => 4],
                'items_granted' => ['Crown Shard: Frost'], 'alignment' => 2,
            ],
            [
                'id' => 'use_crystal', 'text' => 'Channel the Spell Crystal into the pillar',
                'next' => 'node_11', 'required_stat' => null, 'required_val' => null,
                'required_item' => 'Spell Crystal', 'stat_changes' => [],
                'items_granted' => ['Crown Shard: Frost'], 'alignment' => 1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── AFTER SABLE ───────────────────────────────────

    'node_10' => [
        'id'    => 'node_10',
        'title' => 'Sable\'s Revelation',
        'text_warrior' => 'Sable wipes blood from her lip — yours or hers, it doesn\'t matter. "Malachar has the third shard already. The tower on the ridge. You\'ll need more than muscle to take it."',
        'text_mage'    => '"Clever," Sable admits. "Malachar fortified his tower with wards I can\'t break. But you could. The third shard is there — if you survive the approach."',
        'text_rogue'   => 'Sable tosses you a worn map. "The tower. Back entrance. Malachar\'s too arrogant to guard it properly — he thinks fear is enough."',
        'choices' => [
            [
                'id' => 'to_ember_keep', 'text' => 'Head to Ember Keep first',
                'next' => 'node_04', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 1,
            ],
            [
                'id' => 'straight_to_tower', 'text' => 'Go directly to Malachar\'s Tower',
                'next' => 'node_12', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── ACT 3 ─────────────────────────────────────────

    'node_11' => [
        'id'    => 'node_11',
        'title' => 'The Ridge Road',
        'text_warrior' => 'Two shards hum in your pack. The ridge path to Malachar\'s Tower is exposed — no cover, just wind and the distant sound of war drums.',
        'text_mage'    => 'The shards resonate as you climb. The Crown is trying to reassemble — you can feel it pulling toward the tower like a compass needle.',
        'text_rogue'   => 'The ridge offers a clear view of the tower. Guards patrol the main road, but the eastern cliff face has handholds — dangerous, but unwatched.',
        'choices' => [
            [
                'id' => 'main_road', 'text' => 'Take the main road to the tower gate',
                'next' => 'node_12', 'required_stat' => 'str', 'required_val' => 20,
                'required_item' => null, 'stat_changes' => ['hp' => -15],
                'items_granted' => [], 'alignment' => 2,
            ],
            [
                'id' => 'cliff_climb', 'text' => 'Scale the eastern cliff',
                'next' => 'node_13', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => -10],
                'items_granted' => [], 'alignment' => 0,
            ],
            [
                'id' => 'use_sable_map', 'text' => 'Use Sable\'s Map to find the hidden entrance',
                'next' => 'node_13', 'required_stat' => null, 'required_val' => null,
                'required_item' => 'Sable\'s Map', 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -1,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_12' => [
        'id'    => 'node_12',
        'title' => 'Malachar\'s Tower — Front Gate',
        'text_warrior' => 'The gates are massive obsidian. Malachar\'s soldiers line the walls — but they look afraid. Whatever\'s inside frightens them more than you do.',
        'text_mage'    => 'Ward layers stack ten deep on this gate. Military, arcane, and something older — blood magic. Breaking through will take everything you have.',
        'text_rogue'   => 'Heavy guard presence, but their eyes keep drifting upward to the tower\'s peak. They\'re not guarding against intruders — they\'re watching for something coming out.',
        'choices' => [
            [
                'id' => 'force_gate', 'text' => 'Break through the gate by force',
                'next' => 'node_14', 'required_stat' => 'str', 'required_val' => 25,
                'required_item' => null, 'stat_changes' => ['hp' => -25, 'str' => 5],
                'items_granted' => [], 'alignment' => 2,
            ],
            [
                'id' => 'dispel_wards', 'text' => 'Unravel the ward lattice',
                'next' => 'node_14', 'required_stat' => 'wis', 'required_val' => 25,
                'required_item' => null, 'stat_changes' => ['wis' => 5],
                'items_granted' => [], 'alignment' => 1,
            ],
            [
                'id' => 'use_rusted_key', 'text' => 'Try the Rusted Key on the service door',
                'next' => 'node_14', 'required_stat' => null, 'required_val' => null,
                'required_item' => 'Rusted Key', 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 0,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_13' => [
        'id'    => 'node_13',
        'title' => 'Malachar\'s Tower — Hidden Entry',
        'text_warrior' => 'The passage is tight. You scrape through, armor grinding against stone. You emerge in a storage room — crates of alchemical supplies and a staircase leading up.',
        'text_mage'    => 'The hidden entry bypasses all exterior wards. You\'re inside the null zone now — your magic feels muted, slower. Malachar planned for arcanists.',
        'text_rogue'   => 'The hidden entrance opens into a forgotten cellar. Cobwebs, dust, but recent footprints — Sable\'s, you\'d wager. The stairs lead up toward the throne room.',
        'choices' => [
            [
                'id' => 'up_stairs', 'text' => 'Climb toward the throne room',
                'next' => 'node_14', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 0,
            ],
            [
                'id' => 'search_cellar', 'text' => 'Search the cellar for supplies',
                'next' => 'node_14', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => ['hp' => 15],
                'items_granted' => ['Healing Draught'], 'alignment' => 0,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_14' => [
        'id'    => 'node_14',
        'title' => 'The Throne Room of Valdris',
        'text_warrior' => 'Malachar sits on the obsidian throne, the third shard fused to the crown on his head. Dark energy coils around him. He stands. "You\'re too late, soldier. The eclipse begins."',
        'text_mage'    => 'The binding circle is already drawn. Malachar channels the third shard\'s power into the eclipse ritual. If he completes it, the Crown reforms under his will alone.',
        'text_rogue'   => 'Malachar\'s back is to you. The ritual circle hums. The third shard pulses on his brow. One clean strike — if you\'re fast enough.',
        'choices' => [
            [
                'id' => 'confront_malachar', 'text' => 'Challenge Malachar directly',
                'next' => 'node_15', 'required_stat' => 'str', 'required_val' => 22,
                'required_item' => null, 'stat_changes' => ['hp' => -30],
                'items_granted' => ['Crown Shard: Shadow'], 'alignment' => 3,
            ],
            [
                'id' => 'disrupt_ritual', 'text' => 'Disrupt the binding ritual with the shards',
                'next' => 'node_16', 'required_stat' => 'wis', 'required_val' => 22,
                'required_item' => null, 'stat_changes' => ['hp' => -15],
                'items_granted' => ['Crown Shard: Shadow'], 'alignment' => 2,
            ],
            [
                'id' => 'shadow_strike', 'text' => 'Strike from the shadows while he\'s distracted',
                'next' => 'node_17', 'required_stat' => null, 'required_val' => null,
                'required_item' => 'Shadow Cloak', 'stat_changes' => [],
                'items_granted' => ['Crown Shard: Shadow'], 'alignment' => -2,
            ],
            [
                'id' => 'submit_malachar', 'text' => 'Kneel and offer Malachar the shards',
                'next' => 'node_ending_tragic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -5,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── FINAL CONFRONTATION VARIANTS ──────────────────

    'node_15' => [
        'id'    => 'node_15',
        'title' => 'The Duel',
        'text_warrior' => 'Steel rings against dark energy. Malachar is strong — but you\'ve bled for every step of this journey, and that matters more than power. You drive him back.',
        'text_mage'    => 'You trade spell volleys across the throne room. The shards in your pack resonate with each clash, amplifying your magic beyond its limits.',
        'text_rogue'   => 'You dodge Malachar\'s first blast and close the distance. This isn\'t a fair fight — but you never planned on fighting fair.',
        'choices' => [
            [
                'id' => 'finish_heroic', 'text' => 'Shatter the Crown and end his claim forever',
                'next' => 'node_ending_heroic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 3,
            ],
            [
                'id' => 'claim_crown', 'text' => 'Take the Crown for yourself',
                'next' => 'node_ending_secret', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -5,
            ],
            [
                'id' => 'sacrifice_duel', 'text' => 'Fuse all three shards and bind the Crown to your dying breath',
                'next' => 'node_ending_pyrrhic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'required_items' => ['Crown Shard: Ember', 'Crown Shard: Frost', 'Crown Shard: Shadow'],
                'stat_changes' => [], 'items_granted' => [], 'alignment' => 4,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_16' => [
        'id'    => 'node_16',
        'title' => 'Ritual Collapse',
        'text_warrior' => 'The binding circle explodes outward. Malachar screams as the shards tear free from his control. The Crown hovers between you — incomplete, waiting.',
        'text_mage'    => 'Your counter-spell fractures the ritual matrix. The Crown\'s three shards orbit the room in chaotic arcs. You reach for them with your will.',
        'text_rogue'   => 'The ritual destabilizes. In the chaos, you snatch the third shard from mid-air. Malachar collapses. The Crown\'s pieces vibrate in your hands.',
        'choices' => [
            [
                'id' => 'restore_crown', 'text' => 'Restore the Crown and place it on the empty throne',
                'next' => 'node_ending_heroic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 3,
            ],
            [
                'id' => 'wear_crown', 'text' => 'Place the Crown on your own head',
                'next' => 'node_ending_secret', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -5,
            ],
            [
                'id' => 'sacrifice_ritual', 'text' => 'Fuse all three shards and channel the collapse through your own body',
                'next' => 'node_ending_pyrrhic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'required_items' => ['Crown Shard: Ember', 'Crown Shard: Frost', 'Crown Shard: Shadow'],
                'stat_changes' => [], 'items_granted' => [], 'alignment' => 4,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    'node_17' => [
        'id'    => 'node_17',
        'title' => 'The Silent Strike',
        'text_warrior' => 'The Shadow Cloak muffles your approach. You drive your blade through the ritual circle\'s keystone. Malachar whirls — too late.',
        'text_mage'    => 'Cloaked in shadow, you slip a nullification thread into the ritual\'s core. Malachar\'s spell collapses inward on itself.',
        'text_rogue'   => 'This is what you were born for. Silent. Precise. The blade finds the keystone, the ritual shatters, and Malachar never heard you coming.',
        'choices' => [
            [
                'id' => 'end_heroic_stealth', 'text' => 'Destroy the Crown so no one can use it',
                'next' => 'node_ending_heroic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => 3,
            ],
            [
                'id' => 'end_secret_stealth', 'text' => 'Disappear with the Crown into the night',
                'next' => 'node_ending_secret', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'stat_changes' => [],
                'items_granted' => [], 'alignment' => -5,
            ],
            [
                'id' => 'sacrifice_stealth', 'text' => 'Fuse all three shards and let the Crown consume you with it',
                'next' => 'node_ending_pyrrhic', 'required_stat' => null, 'required_val' => null,
                'required_item' => null, 'required_items' => ['Crown Shard: Ember', 'Crown Shard: Frost', 'Crown Shard: Shadow'],
                'stat_changes' => [], 'items_granted' => [], 'alignment' => 4,
            ],
        ],
        'is_terminal' => false, 'ending_type' => null,
    ],

    // ── ENDINGS ───────────────────────────────────────

    'node_ending_heroic' => [
        'id'    => 'node_ending_heroic',
        'title' => 'The Shattered Crown Restored',
        'text_warrior' => 'Through 14 days of encroaching shadow, you did not merely endure; you prevailed. The obsidian throne sits empty no longer. Your reign is forged not in blood, but in the luminous truth of the Justiciar.',
        'text_mage'    => 'The ley lines sing for the first time in a generation. You mended not just the Crown, but the weave of Valdris itself. The eclipse breaks — and dawn returns.',
        'text_rogue'   => 'No one saw you do it. No one needed to. The Crown sits restored, the throne filled by a worthier soul. You vanish into the new dawn — the kingdom never knew your name.',
        'choices' => [],
        'is_terminal' => true, 'ending_type' => 'heroic',
    ],

    'node_ending_tragic' => [
        'id'    => 'node_ending_tragic',
        'title' => 'The Eclipse Descends',
        'text_warrior' => 'You kneel, and Malachar takes the shards. The eclipse locks into place. Valdris falls into permanent shadow. Your name is forgotten — just another soldier who broke.',
        'text_mage'    => 'The binding completes. Malachar\'s Crown is whole, and you helped him do it. The last light dies behind the moon. Your knowledge meant nothing without the will to use it.',
        'text_rogue'   => 'You thought you could play both sides. Malachar accepts your offering with a smile — and then the shadows take you. There is no cunning in the dark.',
        'choices' => [],
        'is_terminal' => true, 'ending_type' => 'tragic',
    ],

    'node_ending_secret' => [
        'id'    => 'node_ending_secret',
        'title' => 'The New Tyrant',
        'text_warrior' => 'The Crown fits. Power surges through you like wildfire. Malachar is dead, but the throne demands a ruler. Valdris bows — but out of fear, not love. Was this the victory you sought?',
        'text_mage'    => 'You complete the binding yourself. The Crown obeys your will. The eclipse breaks, but the sky stays dark — because you chose it. Knowledge is power. Power is control.',
        'text_rogue'   => 'You wear the Crown in the dark where no one can see. Valdris awakens from the eclipse to find a new hand on the strings. They\'ll never know it\'s yours.',
        'choices' => [],
        'is_terminal' => true, 'ending_type' => 'secret',
    ],

    'node_ending_pyrrhic' => [
        'id'    => 'node_ending_pyrrhic',
        'title' => 'The Ashen Oath',
        'text_warrior' => 'You press all three shards into your own chest. The Crown fuses with bone and blood and will. Malachar shrieks as the binding reverses — his power pouring into you, and through you, into nothing. When the eclipse breaks, Valdris finds only a silent figure seated on the obsidian throne, armor fused to stone. You saved them. You will never leave this room.',
        'text_mage'    => 'You weave the three shards into a final, impossible spell — one that requires a living anchor. The Crown completes itself inside you. Malachar unravels. So do you, slowly, as the arcane pattern locks your soul into the throne as its new warden. The sky clears. The kingdom is saved. No one will ever hear you speak again.',
        'text_rogue'   => 'You palm all three shards and press them together against your ribs. The Crown fuses with your heartbeat — a binding only a living thief could make. Malachar screams as the shadow-weave snaps back through him. The eclipse breaks. You slump against the throne, smiling. The kingdom lives. You will not see the dawn.',
        'choices' => [],
        'is_terminal' => true, 'ending_type' => 'pyrrhic',
    ],

];