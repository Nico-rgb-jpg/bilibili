<?php
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();
$mesSeries = $user && input('vue') === 'mes-series';
$semaine = planning_semaine($mesSeries ? $user['id'] : null);
$aujourdhui = jour_actuel();
$total = array_sum(array_map('count', $semaine));

// Date de chaque jour de la semaine en cours (lundi → dimanche).
$lundi = strtotime('monday this week');
$dates = [];
foreach (array_keys(JOURS) as $j) {
    $dates[$j] = date('d/m', strtotime('+' . ($j - 1) . ' days', $lundi));
}

$title = 'Planning';
require __DIR__ . '/../templates/header.php';
?>
<section class="page">
    <div class="page-head page-head-split">
        <div>
            <p class="eyebrow">Planning de diffusion</p>
            <h1>Cette semaine sur Bilibili</h1>
            <p class="muted"><?= $total ?> sortie<?= $total > 1 ? 's' : '' ?> prévue<?= $total > 1 ? 's' : '' ?> · heure de Paris</p>
        </div>
        <?php if ($user): ?>
            <nav class="tabs" aria-label="Filtre du planning">
                <a href="planning.php" <?= !$mesSeries ? 'aria-current="true"' : '' ?>>Toutes les séries</a>
                <a href="planning.php?vue=mes-series" <?= $mesSeries ? 'aria-current="true"' : '' ?>>Ma liste uniquement</a>
            </nav>
        <?php endif; ?>
    </div>

    <?php if ($mesSeries && $total === 0): ?>
        <div class="empty-state">
            <p>Aucune série de votre liste n'a de sortie prévue cette semaine.</p>
            <a class="btn btn-ghost" href="catalogue.php?statut=En+cours">Voir les séries en cours</a>
        </div>
    <?php else: ?>
        <div class="week">
            <?php foreach (JOURS as $num => $jour): ?>
                <section class="day <?= $num === $aujourdhui ? 'is-today' : '' ?>" aria-label="<?= e($jour) ?>">
                    <header class="day-head">
                        <span class="day-name"><?= e($jour) ?></span>
                        <span class="day-date"><?= e($dates[$num]) ?><?= $num === $aujourdhui ? ' · aujourd\'hui' : '' ?></span>
                    </header>
                    <?php if ($semaine[$num]): ?>
                        <ul class="slots">
                            <?php foreach ($semaine[$num] as $c): ?>
                                <li>
                                    <a class="slot" href="serie.php?s=<?= e($c['slug']) ?>" style="--h:<?= (int) $c['teinte'] ?>">
                                        <time><?= e($c['heure']) ?></time>
                                        <strong><?= e($c['titre']) ?></strong>
                                        <span><?= e($c['genre']) ?> · <?= e($c['libelle']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="day-empty">Pas de sortie</p>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
