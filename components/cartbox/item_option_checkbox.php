<div class="option option-checkbox">
    <label><?= $menuOption->option_name; ?></label>

    <?php if (count($optionValues = $menuOption->getOptionValues())) { ?>
        <?php foreach ($optionValues as $optionValue) { ?>

            <?php
            $isChecked = ($cartItem AND $cartItem->hasOptionValue($optionValue->menu_option_value_id));
            ?>

            <div class="checkbox">
                <label>
                    <input
                        type="checkbox"
                        name="menu_options[<?= $index; ?>][option_values][]"
                        value="<?= $optionValue->menu_option_value_id; ?>"
                        <?= ($isChecked OR $optionValue->isDefault()) ? 'checked="checked"' : ''; ?>
                    />
                    <?= $optionValue->name; ?>&nbsp;&nbsp;-&nbsp;&nbsp;<span class="small"><?= currency_format($optionValue->price); ?></span>
                </label>
            </div>
        <?php } ?>
    <?php } ?>
</div>
