<?php foreach ($menuItem->menu_options->sortBy('priority') as $index => $menuOption) { ?>
    <div class="menu-option">
        <div class="option option-<?= $menuOption->display_type; ?>">
            <div class="option-details">
                <h5 class="mb-0">
                    <?= e($menuOption->option_name); ?>
                    <?php if ($menuOption->required == 1) { ?>
                        <span
                            class="small pull-right text-muted"><?= lang('igniter.cart::default.text_required'); ?></span>
                    <?php } ?>
                </h5>
                <?php if ($menuOption->min_selected > 0 OR $menuOption->max_selected > 0) { ?>
                    <p class="mb-0"><?= sprintf(lang('igniter.cart::default.text_option_summary'), $menuOption->min_selected, $menuOption->max_selected); ?></p>
                <?php } ?>
            </div>

            <?php if (count($optionValues = $menuOption->menu_option_values)) { ?>
                <input
                    type="hidden"
                    name="menu_options[<?= $index; ?>][menu_option_id]"
                    value="<?= $menuOption->menu_option_id; ?>"
                />
                <div class="option-group">
                    <?= partial('@item_option_'.$menuOption->display_type, [
                        'index' => $index,
                        'cartItem' => $cartItem,
                        'optionValues' => $optionValues->sortBy('priority'),
                    ]); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
