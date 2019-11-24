<?php foreach ($optionValues as $optionValue) { ?>
    <?php
    $optionIndex = $optionValue->menu_option_value_id;
    $isChecked = ($cartItem AND $cartItem->hasOptionValue($optionIndex));
    ?>
    <div class="custom-control custom-radio">
        <input
            type="radio"
            id="menuOptionRadio<?= $optionIndex; ?>"
            class="custom-control-input"
            name="menu_options[<?= $index; ?>][option_values][]"
            value="<?= $optionValue->menu_option_value_id; ?>"
            data-option-price="<?= $optionValue->price; ?>"
            <?= ($isChecked OR $optionValue->isDefault()) ? 'checked="checked"' : ''; ?>
        >
        <label
            class="custom-control-label w-100"
            for="menuOptionRadio<?= $optionIndex; ?>"
        >
            <?= $optionValue->name; ?>
            <span class="pull-right"><?= currency_format($optionValue->price); ?></span>
        </label>
    </div>
<?php } ?>
