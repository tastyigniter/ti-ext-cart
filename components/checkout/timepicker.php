<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="order-time">
                <?= sprintf(lang('sampoyigi.cart::default.checkout.label_order_time'), $orderType == 'delivery'
                    ? lang('sampoyigi.cart::default.checkout.label_delivery') : lang('sampoyigi.cart::default.checkout.label_collection')); ?>
            </label>
            <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default <?= old('asap', '1') == 1 ? 'btn-primary active' : ''; ?>" data-btn="btn-primary">
                    <input
                        type="radio"
                        name="asap"
                        value="1"
                        <?= set_radio('asap', '1', TRUE); ?>
                    ><?= lang('sampoyigi.cart::default.checkout.text_asap'); ?>
                </label>
                <label class="btn btn-default <?= old('asap', '1') == 1 ? '' : 'btn-primary active'; ?>" data-btn="btn-primary">
                    <input
                        type="radio"
                        name="asap"
                        value="0"
                        <?= set_radio('asap', '0'); ?>
                    ><?= lang('sampoyigi.cart::default.checkout.text_later'); ?>
                </label>
            </div>
        </div>

        <div
            data-trigger="[name='asap']"
            data-trigger-action="show"
            data-trigger-condition="value[0]"
            data-trigger-closest-parent="form"
        >
            <div
                class="input-group"
                data-control="checkout-timepicker"
                data-date-format="<?= $orderDateFormat; ?>"
                data-hour-format="<?= $orderHourFormat; ?>"
                data-date-range="<?= e(json_encode($orderTimeRange)); ?>"
            >
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <select
                    name="order_date"
                    data-timepicker="date"
                    data-timepicker-selected="<?= old('order_date') ?>"
                    class="form-control"
                ></select>
                <select
                    name="order_hour"
                    data-timepicker="hour"
                    data-timepicker-selected="<?= old('order_hour') ?>"
                    class="form-control"
                ></select>
                <select
                    name="order_minute"
                    data-timepicker="minute"
                    data-timepicker-selected="<?= old('order_minute') ?>"
                    class="form-control"
                ></select>
            </div>
            <?= form_error('order_date_time', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>
