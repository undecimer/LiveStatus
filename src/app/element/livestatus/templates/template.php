<?php

namespace YOOtheme\LiveStatus\Element;

$isGrid = $props['layout'] === 'grid';
$alignment = $props['alignment'] ?? 'left';
$size = $props['size'] ?? '';

// Build element container
$el = $this->el('div', [
    'class' => [
        'el-livestatus',
        'el-livestatus-grid' => $isGrid,
        'el-livestatus-flow' => !$isGrid,
        'uk-grid' => $isGrid,
        'uk-grid-small' => $isGrid && $props['grid_gap'] === 'small',
        'uk-grid-medium' => $isGrid && $props['grid_gap'] === 'medium',
        'uk-grid-large' => $isGrid && $props['grid_gap'] === 'large',
        'uk-grid-divider' => $isGrid && $props['grid_divider'],
    ],
    'uk-grid' => $isGrid ? json_encode(array_filter([
        'margin' => $props['grid_gap'] !== '' ? null : ''
    ])) : false
]);

$columns = $props['grid_columns'] ?? 'auto';

// Debug logging
error_log("LiveStatus parent - Full props: " . print_r($props, true));
error_log("LiveStatus parent - Layout: " . ($isGrid ? 'grid' : 'flow'));
error_log("LiveStatus parent - Size value: '{$size}'");
error_log("LiveStatus parent - Grid columns: '{$columns}'");
error_log("LiveStatus parent - Grid gap: '{$props['grid_gap']}'");
error_log("LiveStatus parent - Alignment: '{$alignment}'");

?>

<style>
/* Base styles */
.el-livestatus {
    box-sizing: border-box;
}

/* Flow layout */
.el-livestatus.el-livestatus-flow {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    <?php if ($alignment === 'center') : ?>
    justify-content: center;
    <?php elseif ($alignment === 'right') : ?>
    justify-content: flex-end;
    <?php else : ?>
    justify-content: flex-start;
    <?php endif; ?>
}

/* Grid layout */
.el-livestatus.el-livestatus-grid {
    display: grid;
    <?php if ($columns === 'auto') : ?>
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    <?php else : ?>
    grid-template-columns: repeat(<?= $columns ?>, 1fr);
    <?php endif; ?>
}

/* Grid alignment */
.el-livestatus.el-livestatus-grid {
    <?php if ($alignment === 'center') : ?>
    justify-items: center;
    <?php elseif ($alignment === 'right') : ?>
    justify-items: end;
    <?php else : ?>
    justify-items: start;
    <?php endif; ?>
}

/* Responsive grid */
@media (max-width: 640px) {
    .el-livestatus.el-livestatus-grid {
        grid-template-columns: 1fr;
    }
}

/* Grid divider styles */
<?php if ($isGrid && $props['grid_divider']) : ?>
.uk-grid-divider {
    margin-left: -25px;
}

.uk-grid-divider > * {
    padding-left: 25px;
}

.uk-grid-divider > :not(.uk-first-column)::before {
    border-left: 1px solid #e5e5e5;
}
<?php endif; ?>
</style>

<?= $el($props, $attrs) ?>
    <?php foreach ($children as $child) : ?>
        <?php 
        // Pass properties directly to child props
        $child->props['size'] = $size;
        $child->props['element']['layout'] = $props['layout'];
        $child->props['element']['alignment'] = $alignment;
        ?>
        <?= $builder->render($child) ?>
    <?php endforeach ?>
<?= $el->end() ?>
