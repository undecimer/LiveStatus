<?php

namespace YOOtheme\LiveStatus\Element;

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus',
        'el-livestatus-grid' => $props['layout'] === 'grid',
        'el-livestatus-flow' => $props['layout'] === 'flow',
        'uk-grid' => $props['layout'] === 'grid',
        'uk-grid-{grid_gap}' => $props['grid_gap'] && $props['layout'] === 'grid',
        'uk-grid-divider' => $props['grid_divider'],
        'uk-flex-{alignment}' => $props['alignment'],
    ]
]);

$size = $props['size'] ?? '';
$columns = $props['grid_columns'] ?? 'auto';

// Debug logging
error_log("LiveStatus parent - Full props: " . print_r($props, true));
error_log("LiveStatus parent - Size value: '{$size}'");

?>

<style>
.el-livestatus {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.el-livestatus.el-livestatus-grid {
    display: grid;
    <?php if ($columns === 'auto') : ?>
    grid-template-columns: repeat(auto-fill, minmax(min-content, 1fr));
    <?php else : ?>
    grid-template-columns: repeat(<?= $columns ?>, 1fr);
    <?php endif; ?>
    gap: 10px;
}

.el-livestatus.el-livestatus-flow {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.el-livestatus > * {
    margin: 0 !important;
}

@media (max-width: 640px) {
    .el-livestatus.el-livestatus-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?= $el($props, $attrs) ?>
    <?php foreach ($children as $child) : ?>
        <?= $builder->render($child, ['element' => $props]) ?>
    <?php endforeach ?>
<?= $el->end() ?>
