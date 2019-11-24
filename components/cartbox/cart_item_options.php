<ul class="list-unstyled small">
    <?php foreach ($itemOptions as $itemOption) { ?>
        <li class="text-muted"><?= $itemOption->name; ?></li>
        <?php foreach ($itemOption->values as $optionValue) { ?>
            <li><?= $optionValue->name; ?>&nbsp;
                <?php if ($optionValue->price > 0) { ?>
                    (<?= currency_format($optionValue->price); ?>)
                <?php } ?>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
