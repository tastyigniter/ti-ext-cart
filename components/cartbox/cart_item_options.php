<ul class="list-unstyled small">
    <?php foreach ($itemOptions as $itemOption) { ?>
        <?php foreach ($itemOption['values'] as $itemOptionValues) { ?>
            <li>
                <span class="text-muted">
                    <?= $itemOptionValues['name']; ?>
                </span><span class="text-muted pull-right">
                    <?= currency_format($itemOptionValues['price']); ?>
                </span>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
