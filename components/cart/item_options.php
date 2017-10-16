<div class="menu-options">
    <?php foreach ($menuItem->menu_options as $key => $menuOption) { ?>
        <?php if ($menuOption['display_type'] == 'radio') { ?>
            <div class="option option-radio">
                <input
                    type="hidden"
                    name="menu_options[<?= $key; ?>][option_id]"
                    value="<?= $menuOption['option_id']; ?>"/>
                <input
                    type="hidden"
                    name="menu_options[<?= $key; ?>][menu_option_id]"
                    value="<?= $menuOption['menu_option_id']; ?>"/>
                <label for=""><?= $menuOption['option_name']; ?></label>

                <?php if (isset($menuOption['option_values'])) { ?>
                    <?php foreach ($menuOption['option_values'] as $option_value) { ?>
                        <?php isset($cart_option_value_ids[$key]) OR $cart_option_value_ids[$key] = [] ?>
                        <?php if (in_array($option_value['menu_option_value_id'], $cart_option_value_ids[$key]) OR (empty($cart_option_value_ids[$key]) AND $menuOption['default_value_id'] == $option_value['menu_option_value_id'])) { ?>
                            <div class="radio"><label>
                                    <input
                                        type="radio"
                                        name="menu_options[<?= $key; ?>][option_values][]"
                                        value="<?= $option_value['option_value_id']; ?>"
                                        checked="checked"/>
                                    <?= $option_value['value']; ?>
                                    <span class="price small"><?= $option_value['price']; ?></span>
                                </label></div>
                        <?php }
                        else { ?>
                            <div class="radio"><label>
                                    <input type="radio"
                                           name="menu_options[<?= $key; ?>][option_values][]"
                                           value="<?= $option_value['option_value_id']; ?>"/>
                                    <?= $option_value['value']; ?>
                                    <span class="price small"><?= $option_value['price']; ?></span>
                                </label></div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($menuOption['display_type'] == 'checkbox') { ?>
            <div class="option option-checkbox">
                <input type="hidden"
                       name="menu_options[<?= $key; ?>][option_id]"
                       value="<?= $menuOption['option_id']; ?>"/>
                <input type="hidden"
                       name="menu_options[<?= $key; ?>][menu_option_id]"
                       value="<?= $menuOption['menu_option_id']; ?>"/>
                <label for=""><?= $menuOption['option_name']; ?></label>

                <?php if (isset($menuOption['option_values'])) { ?>
                    <?php foreach ($menuOption['option_values'] as $option_value) { ?>
                        <?php isset($cart_option_value_ids[$key]) OR $cart_option_value_ids[$key] = [] ?>
                        <?php if (in_array($option_value['menu_option_value_id'], $cart_option_value_ids[$key]) OR (empty($cart_option_value_ids[$key]) AND $menuOption['default_value_id'] == $option_value['menu_option_value_id'])) { ?>
                            <div class="checkbox"><label>
                                    <input type="checkbox"
                                           name="menu_options[<?= $key; ?>][option_values][]"
                                           value="<?= $option_value['option_value_id']; ?>"
                                           checked="checked"/>
                                    <?= $option_value['value']; ?>
                                    <span class="price small"><?= $option_value['price']; ?></span>
                                </label></div>
                        <?php }
                        else { ?>
                            <div class="checkbox"><label>
                                    <input type="checkbox"
                                           name="menu_options[<?= $key; ?>][option_values][]"
                                           value="<?= $option_value['option_value_id']; ?>"/>
                                    <?= $option_value['value']; ?>
                                    <span class="price small"><?= $option_value['price']; ?></span>
                                </label></div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($menuOption['display_type'] == 'select') { ?>
            <div class="option option-select">
                <div class="form-group clearfix">
                    <div class="col-sm-5 wrap-none">
                        <input type="hidden"
                               name="menu_options[<?= $key; ?>][option_id]"
                               value="<?= $menuOption['option_id']; ?>"/>
                        <input type="hidden"
                               name="menu_options[<?= $key; ?>][menu_option_id]"
                               value="<?= $menuOption['menu_option_id']; ?>"/>

                        <?php if (isset($menuOption['option_values'])) { ?>
                            <select name="menu_options[<?= $key; ?>][option_values][]"
                                    class="form-control">
                                <option value=""><?= $menuOption['option_name']; ?></option>
                                <?php foreach ($menuOption['option_values'] as $option_value) { ?>
                                    <?php isset($cart_option_value_ids[$key]) OR $cart_option_value_ids[$key] = [] ?>
                                    <?php if (in_array($option_value['menu_option_value_id'], $cart_option_value_ids[$key]) OR (empty($cart_option_value_ids[$key]) AND $menuOption['default_value_id'] == $option_value['menu_option_value_id'])) { ?>
                                        <option value="<?= $option_value['option_value_id']; ?>"
                                                data-subtext="<?= $option_value['price']; ?>"
                                                selected="selected">
                                            <?= $option_value['value']; ?>
                                        </option>
                                    <?php }
                                    else { ?>
                                        <option value="<?= $option_value['option_value_id']; ?>"
                                                data-subtext="<?= $option_value['price']; ?>">
                                            <?= $option_value['value']; ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>
