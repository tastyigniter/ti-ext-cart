<ul class="list-group">
    <?php foreach ($itemOptions as $itemOption) { ?>
        <li>
            <span class="text-muted small"><b><?= $itemOption['name']; ?></b></span>
            <ul class="list-group">
                <?php foreach ($itemOption['values'] as $itemOptionValues) { ?>
                    <li>
                        <span class="text-muted small">
                            <?= $itemOptionValues['name']; ?>
                        </span>&nbsp;-&nbsp;<span class="text-muted small">
                            <?= currency_format($itemOptionValues['price']); ?>
                        </span>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
</ul>
