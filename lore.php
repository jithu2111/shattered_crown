<?php
session_start();
require_once __DIR__ . '/functions.php';

$page_title = 'The Chronicle of Valdris &middot; The Shattered Crown';
$body_class = 'page-lore';
include __DIR__ . '/includes/header.php';
?>

<section class="lore">

    <p class="lore-kicker">THE CHRONICLE OF VALDRIS</p>
    <h1 class="lore-title">A Kingdom in Eclipse</h1>
    <p class="lore-sub">Read before you take the oath. Know what it is you are about to mend.</p>

    <article class="lore-block">
        <h2 class="lore-h2">The Crown of Binding</h2>
        <p>
            For three centuries, the Crown of Binding held Valdris together. It was no mere circlet &mdash; it was
            the anchor of every ley line that ran beneath the kingdom, the lattice that kept the <em>Eclipse</em>
            from falling upon the world. As long as a worthy hand wore it, the moon could not swallow the sun.
        </p>
        <p>
            The Crown was forged of three resonant shards &mdash; <strong>Ember</strong>, <strong>Frost</strong>,
            and <strong>Shadow</strong> &mdash; each drawn from a different corner of Valdris. Fused, they sang
            in harmony. Shattered, they would each call for a different master.
        </p>
    </article>

    <article class="lore-block">
        <h2 class="lore-h2">The Assassination</h2>
        <p>
            Fourteen nights ago, <strong>King Aldric</strong> was slain in his own throne room. His body was
            found with the Crown shattered around him &mdash; the three shards flung to distant corners by the
            force of the binding's collapse.
        </p>
        <p>
            The assassin was no stranger. <strong>Lord Malachar</strong>, the King's own spymaster, now sits
            upon the obsidian throne. He hunts the shards as fiercely as any seeker &mdash; for when the
            Eclipse locks into place in fourteen days, whoever holds the reforged Crown will hold Valdris
            forever.
        </p>
    </article>

    <article class="lore-block">
        <h2 class="lore-h2">The Eclipse Countdown</h2>
        <p>
            A magical eclipse is already descending. When the moon fully swallows the sun, Malachar's binding
            ritual will complete &mdash; and his reign will become permanent. Light will not return to Valdris
            in our lifetime, nor in our children's.
        </p>
        <p class="lore-quote">
            &ldquo;Fourteen days. That is all the light the world has left.&rdquo;
            <span class="lore-quote-attr">&mdash; the final raven from the High Sanctum</span>
        </p>
    </article>

    <article class="lore-block">
        <h2 class="lore-h2">Three Paths, Three Heroes</h2>
        <p>
            You will choose how to walk into this broken kingdom. Every road ends at Malachar's tower &mdash;
            but the road you take, and the hands you arrive with, will decide what Valdris becomes.
        </p>

        <div class="lore-class-grid">
            <div class="lore-class-card lore-class-warrior">
                <h3>The Forged Vanguard</h3>
                <p class="lore-class-role">Warrior &middot; Heavy Armor</p>
                <p>
                    Steel and siege. You do not sneak &mdash; you breach. Gates break under your shoulder and
                    wards shatter under your blade. Every duel is a conversation, and yours speaks louder than
                    any spell.
                </p>
            </div>

            <div class="lore-class-card lore-class-mage">
                <h3>The Void Weaver</h3>
                <p class="lore-class-role">Mage &middot; Arcanist</p>
                <p>
                    You feel the ley lines before you see the road. Wards are not walls to you &mdash; they are
                    patterns to be read and unwoven. The shards of the Crown will resonate in your hand like a
                    second heartbeat.
                </p>
            </div>

            <div class="lore-class-card lore-class-rogue">
                <h3>The Shadow Blade</h3>
                <p class="lore-class-role">Rogue &middot; Infiltrator</p>
                <p>
                    You count the exits before the introductions. Where others see a sealed keep, you see a
                    drainage tunnel. You do not fight fair, and you never planned to. The Crown will be found
                    by someone no one saw coming.
                </p>
            </div>
        </div>
    </article>

    <article class="lore-block">
        <h2 class="lore-h2">Figures You May Meet</h2>
        <ul class="lore-list">
            <li>
                <strong>King Aldric</strong> &mdash; The slain king. His final scream is bound into the first
                shard, and those who listen carefully can still hear him.
            </li>
            <li>
                <strong>Lord Malachar</strong> &mdash; The usurper. Once the King's spymaster, now a binder of
                shadow. He will not wait for you to arrive ready.
            </li>
            <li>
                <strong>Sable, the Shadow Broker</strong> &mdash; A null-field in human shape, watching the
                crossroads. She may be an ally, an obstacle, or a mirror &mdash; depending on how you approach her.
            </li>
            <li>
                <strong>The Ember Keep Garrison</strong> &mdash; A besieged fortress guarding the first shard
                under the rule of a warlord golem. Both sides of the siege will kill you if you let them.
            </li>
            <li>
                <strong>The Frozen Sanctum</strong> &mdash; An ice cave full of preserved corpses. Something
                buried here did not want to be found. You will have to decide if it deserves to stay hidden.
            </li>
        </ul>
    </article>

    <article class="lore-block">
        <h2 class="lore-h2">What Your Choices Will Shape</h2>
        <p>
            Every decision shifts your <strong>alignment</strong> &mdash; the scales between the Justiciar's
            mercy and the Usurper's cold pragmatism. Stat checks will open doors and close them. Items
            collected in Act 1 will echo into Act 3 in ways you will not see coming.
        </p>
        <p>
            There is no single &ldquo;correct&rdquo; ending. There is only the ending your choices earn.
            The Chronicle ends when you write it.
        </p>
    </article>

    <div class="lore-actions">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="character.php" class="btn btn-danger">Begin the Journey</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-danger">Take the Oath</a>
            <a href="login.php" class="btn btn-primary">Invoke Legacy</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-link">&larr; Return to the Gates</a>
    </div>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>